<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Po;
use App\Models\PoDetail;
use App\Models\Pelanggan;
use App\Models\Penjualan;
use App\Models\Piutang;
use App\Models\PenjualanDetail;
use App\Models\Barang;
use App\Models\MasterBarang;
use PDF;
use Illuminate\Support\Facades\Log;

class MultiplepoController extends Controller
{
    public function index()
    {
       
        // Ambil semua data PO dengan relasi PoDetails (jika diperlukan)
    $po = Po::with('PoDetails')->get();

    // Ambil daftar PO yang belum diproses (asumsikan status "belum diproses" disimpan sebagai 0)
    $poList = Po::where('status', 0)->get();
    
    if ($poList->isEmpty()) {
        // Anda bisa memilih untuk menampilkan view dengan pesan kosong, bukan mengembalikan response JSON
        return view('po_selection', compact('po', 'poList'))->with('error', 'Data PO tidak ditemukan.');
    }
    
    // Ambil data lainnya
    $pelangganList = Pelanggan::all();
    $barangList = Barang::all();
    $masterbarangList = MasterBarang::all();
    
    return view('po_selection', compact('po', 'poList', 'pelangganList', 'barangList', 'masterbarangList'));
}

public function processStep2(Request $request)
{
    $selectedPoIds = $request->input('selected_po');
    if(empty($selectedPoIds)) {
        return redirect()->back()->with('error', 'Harap pilih setidaknya satu PO.');
    }
    
    // Ambil PO beserta detail item-nya (misal dengan relasi poDetails)
    $poDetails = Po::with('poDetails')->whereIn('id', $selectedPoIds)->get();
    
    // Aggregasi item berdasarkan kode_barang
    $aggregatedItems = [];
    foreach($poDetails as $po) {
        foreach($po->poDetails as $item) {
            $kode = $item->kode_barang;
            if (!isset($aggregatedItems[$kode])) {
                $aggregatedItems[$kode] = [
                    'kode_barang' => $kode,
                    'nama_barang' => $item->nama_barang,
                    'total_dus'   => $item->dus,
                    'total_lsn'   => $item->lusin,
                    'total_pcs'   => $item->pcs,
                    'stok'        => $item->barang->stok ?? 0,
                    'isi'         => $item->barang->isidus ?? 10,
                ];
            } else {
                $aggregatedItems[$kode]['total_dus'] += $item->dus;
                $aggregatedItems[$kode]['total_lsn'] += $item->lusin;
                $aggregatedItems[$kode]['total_pcs'] += $item->pcs;
            }
        }
    }
    
    
    // Kirim data aggregated beserta PO yang dipilih ke view step 2
    return view('po_process_step2', [
        'aggregatedItems' => $aggregatedItems,
        'selectedPoIds'   => $selectedPoIds,
        'poDetails'       => $poDetails, // jika diperlukan untuk referensi
    ]);
}

public function processFinal(Request $request)
{
    try {
        // Generate prefix faktur berdasarkan tanggal hari ini
        $prefix = 'SI-' . date('Ymd') . '-';
        // Ambil penjualan terakhir dengan prefix faktur hari ini untuk menentukan nomor urut
        $lastSale = \App\Models\Penjualan::where('id_faktur', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();
        $counter = 1;
        if ($lastSale) {
            // Ambil 4 digit terakhir dari id_faktur dan increment
            $lastNumber = (int)substr($lastSale->id_faktur, -4);
            $counter = $lastNumber + 1;
        }

        // Ambil data PO terpilih
        $selectedPoIdsStr = $request->input('selected_po_ids'); // misalnya "54,59,60"
        if (!$selectedPoIdsStr) {
            return response()->json(['success' => false, 'message' => 'Tidak ada PO yang dipilih.'], 400);
        }
        $selectedPoIds = explode(',', $selectedPoIdsStr);

        // Proses setiap PO secara terpisah
        foreach ($selectedPoIds as $poId) {
            $po = Po::with('poDetails')->find($poId);
            if (!$po) {
                continue; // Lewati jika PO tidak ditemukan
            }

            // Ambil data pelanggan dari PO
            $kode_pelanggan = $po->kode_pelanggan;
            $nama_pelanggan = $po->nama_pelanggan;

            // Pertama, hitung total kotor PO (menggunakan effective quantity)
            $totalKotor = 0;
            foreach ($po->poDetails as $detail) {
                $barang = Barang::where('kode_barang', $detail->kode_barang)->first();
                if (!$barang) {
                    continue;
                }
                // Effective quantity: gunakan nilai minimal antara quantity PO dan stok yang tersedia
                $effectiveQuantity = min($detail->quantity, $barang->stok);
                $lineTotal = $detail->harga * $effectiveQuantity;
                $totalKotor += $lineTotal;
            }

            // Tentukan tarif disc1 berdasarkan total kotor PO
            if ($totalKotor > 1000000) {
                $disc1Rate = 0.02;
            } elseif ($totalKotor > 500000) {
                $disc1Rate = 0.01;
            } else {
                $disc1Rate = 0;
            }

            $totalDiscount = 0;
            $totalNet = 0;

            // Generate nomor faktur untuk record penjualan ini
            $id_faktur = $prefix . str_pad($counter, 4, '0', STR_PAD_LEFT);
            $counter++;

            // Buat record penjualan dengan menambahkan id_faktur
            $penjualan = \App\Models\Penjualan::create([
                'id_faktur'       => $id_faktur,
                'kode_pelanggan'  => $kode_pelanggan,
                'nama_pelanggan'  => $nama_pelanggan,
                'total_discount'  => 0, // akan diupdate setelah perhitungan detail
                'total'           => 0, // akan diupdate setelah perhitungan detail
                'created_at'      => now(),
            ]);

            // Proses setiap detail PO
            foreach ($po->poDetails as $detail) {
                $barang = Barang::where('kode_barang', $detail->kode_barang)->first();
                if (!$barang) {
                    continue;
                }
                // Effective quantity: jangan melebihi stok yang tersedia
                $effectiveQuantity = min($detail->quantity, $barang->stok);
                if ($effectiveQuantity <= 0) {
                    continue;
                }
                $lineTotal = $detail->harga * $effectiveQuantity;

                // Diskon disc1 selalu berlaku sesuai tarif total PO
                $lineDisc1 = $lineTotal * $disc1Rate;

                // Cek syarat disc2: contoh untuk kode_barang tertentu
                $disc2Rate = 0;
                if ($detail->kode_barang == "21132689" && $detail->dus > 3) {
                    $disc2Rate = 0.10;
                } elseif ($detail->kode_barang == "62072025" && $detail->dus > 3) {
                    $disc2Rate = 0.10;
                }
                $lineDisc2 = $lineTotal * $disc2Rate;

                // Total diskon untuk baris ini
                $lineDiscount = $lineDisc1 + $lineDisc2;

                // Nilai netto per baris
                $netLine = $lineTotal - $lineDiscount;

                // Simpan detail penjualan
                \App\Models\PenjualanDetail::create([
                    'penjualan_id' => $penjualan->id,
                    'kode_barang'  => $detail->kode_barang,
                    'nama_barang'  => $detail->nama_barang,
                    'harga'        => $detail->harga,
                    'dus'          => $detail->dus,
                    'lusin'        => $detail->lusin,
                    'pcs'          => $detail->pcs,
                    'quantity'     => $effectiveQuantity,
                    'disc1'        => $disc1Rate * 100,
                    'disc2'        => $disc2Rate * 100,
                    'disc3'        => 0,
                    'disc4'        => 0,
                    'jumlah'       => $netLine,
                    'created_at'   => now(),
                ]);

                // Update total discount dan total netto untuk PO ini
                $totalDiscount += $lineDiscount;
                $totalNet += $netLine;

                // Update stok barang
                $barang->stok -= $effectiveQuantity;
                $barang->save();
                            // Update status PO menjadi 1 (sudah diproses)
                $po->status = 1;
                $po->save();
            }

            // Update record penjualan dengan total discount dan total netto
            $penjualan->total_discount = $totalDiscount;
            $penjualan->total = $totalNet;
            $penjualan->save();

            // Buat record di table piutang dengan data yang sama
            \App\Models\Piutang::create([
                'id_faktur'      => $id_faktur,
                'kode_pelanggan' => $kode_pelanggan,
                'nama_pelanggan' => $nama_pelanggan,
                'total'          => $totalNet,
                'pembayaran' => 0,
                'sisapiutang' => $totalNet,
                'created_at'     => now(),
            ]);


        }

        return response()->json(['success' => true]);
    } catch (\Exception $e) {
        \Log::error("Error in processFinal: " . $e->getMessage(), $e->getTrace());
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}




public function list(Request $request)
{
    $query = Penjualan::query();

    // Jika tanggal_dari atau tanggal_sampai tidak diisi, gunakan tanggal hari ini sebagai default
    if (!$request->filled('tanggal_dari') || !$request->filled('tanggal_sampai')) {
        $today = date('Y-m-d');
        $query->whereBetween('created_at', [
            $today . ' 00:00:00',
            $today . ' 23:59:59'
        ]);
    } else {
        // Jika filter tanggal diisi oleh user, gunakan nilai tersebut
        $query->whereBetween('created_at', [
            $request->tanggal_dari . ' 00:00:00',
            $request->tanggal_sampai . ' 23:59:59'
        ]);
    }

    // Filter berdasarkan kode pelanggan jika ada
    if ($request->filled('kode_pelanggan')) {
        $query->where('kode_pelanggan', $request->kode_pelanggan);
    }

    $penjualan = $query->orderBy('created_at', 'desc')->get();

    return view('penjualan.daftarjual', compact('penjualan'));
}

}
