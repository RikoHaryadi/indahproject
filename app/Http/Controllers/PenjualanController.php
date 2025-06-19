<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Pelanggan;
use App\Models\Barang;
use App\Models\Grn;
use App\Models\Po;
use App\Models\PoDetail;
use App\Models\Salesman;
use App\Models\Piutang;
use Illuminate\Support\Facades\DB; 
use PDF;

class PenjualanController extends Controller
{
    public function index()
    {
        $penjualan = Penjualan::with('details')->get(); // Ambil semua data penjualan beserta detailnya
        $pelangganList = Pelanggan::all(); // Ambil daftar pelanggan
        $barangList = Barang::all(); // Ambil daftar pelanggan
        $poList = Po::all(); // Atau filter sesuai kebutuhan
        $salesmanList = Salesman::all(); // Atau filter sesuai kebutuhan
        return view('penjualan.index', compact('penjualan', 'pelangganList', 'barangList', 'poList', 'salesmanList')); // Kirim data ke view
    }

    public function store(Request $request)
    {
        // Validasi input
        $data = $request->validate([
            'po_id' => 'required',
            'kode_pelanggan' => 'required',
            'nama_pelanggan' => 'required',
            'created_at' => 'required|date',
            'total_discount' => 'required|numeric',
            'items' => 'required|array',
            'items.*.kode_barang' => 'required|exists:barang,kode_barang',
            'items.*.nama_barang' => 'required',
            'items.*.harga' => 'required|numeric',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.disc1' => 'required|numeric',
            'items.*.disc2' => 'required|numeric',
            'items.*.disc3' => 'required|numeric',
            'items.*.disc4' => 'required|numeric',
            'items.*.jumlah' => 'required|numeric',
        ]);

         // **Validasi kode barang tidak boleh duplikat dalam satu transaksi**
    $items = $data['items'];
    $usedKodeBarang = [];

    foreach ($items as $item) {
        if (in_array($item['kode_barang'], $usedKodeBarang)) {
            return redirect()->back()->withErrors(['msg' => 'Kode barang ' . $item['kode_barang'] . ' sudah ada dalam daftar!'])->withInput();
        }
        $usedKodeBarang[] = $item['kode_barang'];
    }
    
        // Periksa stok setiap barang sebelum menyimpan
        foreach ($data['items'] as $item) {
            $barang = Barang::where('kode_barang', $item['kode_barang'])->first();
            if (!$barang || $barang->stok < $item['quantity']) {
                return back()->withErrors([
                    'items' => "Stok barang {$item['kode_barang']} tidak mencukupi. Stok tersedia: " . ($barang ? $barang->stok : 0),
                ])->withInput(); // Berhenti dan kembalikan halaman sebelumnya
            }
        }

        
    
        // Hitung total discount dengan cara menghitung nilai rupiah discount per item
    $totalDiscount = array_reduce($data['items'], function ($carry, $item) {
        $harga = $item['harga'];
        $quantity = $item['quantity'];
        $totalKotor = $harga * $quantity;
        $discPercent = $item['disc1'] + $item['disc2'] + $item['disc3'] + $item['disc4'];
        $discountValue = $totalKotor * ($discPercent / 100);
        return $carry + $discountValue;
    }, 0);
        $po = Po::find($data['po_id']);
    if ($po->status == 1) {
        return back()->withErrors(['po_id' => 'PO sudah diproses sebelumnya.'])->withInput();
    }
    
        // Simpan data penjualan
        $penjualan = Penjualan::create([
            'kode_pelanggan' => $data['kode_pelanggan'],
            'nama_pelanggan' => $data['nama_pelanggan'],
            'total_discount' => $totalDiscount,
            'total' => array_sum(array_column($data['items'], 'jumlah')),
            'created_at' => $data['created_at'],
        ]);
    
        // Kurangi stok barang dan simpan detail penjualan
        foreach ($data['items'] as $item) {
            $barang = Barang::where('kode_barang', $item['kode_barang'])->first();
            $barang->stok -= $item['quantity'];
            $barang->save();
            
             // Hitung dus, lusin, pcs berdasarkan quantity dan isi dus
        $isidus = $barang->isidus; // Ambil isi dus dari barang
        $quantity = $item['quantity'];

        $dus = intdiv($quantity, $isidus);
        $sisaPcs = $quantity % $isidus;

        $lusin = intdiv($sisaPcs, 12);
        $pcs = $sisaPcs % 12;
            
            $penjualan->details()->create([
                'kode_barang' => $item['kode_barang'],
                'nama_barang' => $item['nama_barang'],
                'harga' => $item['harga'],
                'quantity' => $item['quantity'],
                'jumlah' => $item['jumlah'],
                'dus' => $dus,
                'lusin' => $lusin,
                'pcs' => $pcs,
                'disc1' => $item['disc1'],
                'disc2' => $item['disc2'],
                'disc3' => $item['disc3'],
                'disc4' => $item['disc4'],
               
            ]);
        }
    
        // Jika ada permintaan cetak
        if ($request->action === 'save_and_print') {
            $pdf = PDF::loadView('penjualan.cetak-pdf', compact('penjualan'))
                ->setPaper('a4', 'portrait');
            return $pdf->stream('nota_penjualan_' . $penjualan->id . '.pdf');
        }

        $po->status = 1;
        $po->save();
    
        // Redirect ke halaman index dengan pesan sukses
        return redirect()->route('penjualan.index')->with('success', 'Penjualan berhasil disimpan.');
    }

    


    
    
    // public function daftar()
    // {
    //     $penjualan = Penjualan::with('details')->get(); // Ambil semua data penjualan beserta detailnya
    //     return view('penjualan.daftarjual', compact('penjualan')); // Kirim data ke view
    // }
    public function daftar(Request $request)
{
    $query = Penjualan::query();

    // Filter berdasarkan tanggal
    if ($request->filled('tanggal_dari') && $request->filled('tanggal_sampai')) {
        $query->whereBetween('created_at', [
            $request->tanggal_dari . ' 00:00:00',
            $request->tanggal_sampai . ' 23:59:59'
        ]);
    }

    // Filter berdasarkan kode pelanggan
    if ($request->filled('kode_pelanggan')) {
        $query->where('kode_pelanggan', $request->kode_pelanggan);
    }

    $penjualan = $query->orderBy('created_at', 'desc')->get();

    return view('penjualan.daftarjual', compact('penjualan'));
}
public function cetak($id)
{
    $penjualan = Penjualan::with(['details.barang'])->findOrFail($id);
    return view('penjualan.cetak', compact('penjualan'));
}
    

public function cetakPdf($id)
{
    $penjualan = Penjualan::with(['details.barang'])->findOrFail($id);

    $pdf = PDF::loadView('penjualan.cetak-pdf', compact('penjualan'))
              ->setPaper('a4', 'portrait'); // Pilih ukuran dan orientasi kertas
    return $pdf->stream('nota_penjualan_' . $penjualan->id . '.pdf');
}

public function show($poId)
{
    $po = Po::with('poDetails')->where('id', $poId)->first();

    if (!$po) {
        return response()->json(['message' => 'PO tidak ditemukan'], 404);
    }

    return response()->json([
        'kode_pelanggan' => $po->kode_pelanggan,
        'nama_pelanggan' => $po->nama_pelanggan,
        'po_details' => $po->poDetails->map(function ($detail) {
            return [
                'kode_barang' => $detail->kode_barang,
                'nama_barang' => $detail->nama_barang,
                'harga' => $detail->harga,
                'isidus' => $detail->barang->isidus ?? 1, // Ambil dari relasi barang
                'dus' => $detail->dus,
                'lusin' => $detail->lusin,
                'pcs' => $detail->pcs,
                'quantity' => $detail->quantity,
                'jumlah' => $detail->jumlah,
            ];
        }),
    ]);
}
   // 1) Tampilkan form edit
    public function edit($id)
    {
        $penj = Penjualan::with('details')->findOrFail($id);

        // Supaya bisa menampilkan nama pelanggan, alamat, telepon:
        // Misal ada relasi pelanggan: return $this->belongsTo(Pelanggan::class, 'kode_pelanggan', 'Kode_pelanggan');
        // Kalau belum ada relasi, kita bisa cari manual:
        $pel = Pelanggan::where('Kode_pelanggan', $penj->kode_pelanggan)->first();

        // Kirimkan $penj, juga data pelanggan (nama, alamat, telepon)
        return view('penjualan.editpenjualan', [
            'penj'      => $penj,
            'pelanggan' => $pel,
        ]);
    }

    // 2) Proses update
    public function update(Request $request, $id)
{
    $request->validate([
        'kode_sales'     => 'required|string|max:20',
        'nama_sales'     => 'required|string|max:100',
        'kode_pelanggan' => 'required|string|max:20',
        'nama_pelanggan' => 'required|string|max:100',
        'created_at'     => 'required|date',
        'items'          => 'required|array|min:1',
        'items.*.kode_barang'  => 'required|string',
        'items.*.nama_barang'  => 'required|string',
        'items.*.harga'        => 'required|numeric|min:0',
        'items.*.dus'          => 'required|integer|min:0',
        'items.*.lsn'          => 'required|integer|min:0',
        'items.*.pcs'          => 'required|integer|min:0',
        'items.*.isidus'       => 'required|integer|min:1',
        'items.*.quantity'     => 'required|integer|min:1',
        'items.*.stok'         => 'required|integer',
        'items.*.disc1'        => 'required|numeric|min:0',
        'items.*.disc2'        => 'required|numeric|min:0',
        'items.*.disc3'        => 'required|numeric|min:0',
        'items.*.disc4'        => 'required|numeric|min:0',
        'items.*.jumlah'       => 'required|numeric|min:0',
    ]);

    DB::transaction(function() use ($request, $id) {
        $penj = Penjualan::findOrFail($id);

        $items = $request->items;

        $totalKotor = 0;
        $totalDiscount = 0;
        foreach ($items as $item) {
            $jumlah = $item['jumlah'];
            $totalKotor += $jumlah;

            $discPercent = $item['disc1'] + $item['disc2'] + $item['disc3'] + $item['disc4'];
            $hargaQty = $item['harga'] * $item['quantity'];
            $totalDiscount += $hargaQty * ($discPercent / 100);
        }

        $totalNet = $totalKotor;

        // Update data header penjualan
        $penj->update([
            'kode_sales'     => $request->kode_sales,
            'nama_sales'     => $request->nama_sales,
            'kode_pelanggan' => $request->kode_pelanggan,
            'nama_pelanggan' => $request->nama_pelanggan,
            'total_discount' => $totalDiscount,
            'total'          => $totalNet,
            'created_at'     => $request->created_at,
        ]);

        // Hapus detail lama
        $penj->details()->delete();

        // Tambahkan detail baru
        foreach ($items as $item) {
            $penj->details()->create([
                'kode_barang' => $item['kode_barang'],
                'nama_barang' => $item['nama_barang'],
                'harga'       => $item['harga'],
                'quantity'    => $item['quantity'],
                'jumlah'      => $item['jumlah'],
                'dus'         => $item['dus'],
                'lusin'       => $item['lsn'],
                'pcs'         => $item['pcs'],
                'disc1'       => $item['disc1'],
                'disc2'       => $item['disc2'],
                'disc3'       => $item['disc3'],
                'disc4'       => $item['disc4'],
                  'created_at'  => $penj->created_at, // ambil dari tanggal penjualan
            ]);
        }

        // Update atau insert ke tabel piutang berdasarkan kolom id_faktur (bukan id)
        Piutang::updateOrCreate(
            ['id_faktur' => $penj->id_faktur],
            [
                'kode_pelanggan' => $request->kode_pelanggan,
                'nama_pelanggan' => $request->nama_pelanggan,
                'total'          => $totalNet,
                'pembayaran'     => 0,
                'sisapiutang'    => $totalNet,
                'created_at'     => $request->created_at,
            ]
        );
    });

    return redirect()->route('penjualan.index')->with('success', 'Data penjualan & piutang berhasil diperbarui.');
}
    /**
     * AJAX: mencari faktur (id_faktur) berdasarkan query 'q'
     * Mengembalikan JSON: [{ id: penjualan.id, id_faktur: penjualan.id_faktur }, …]
     */
    public function searchFaktur(Request $request)
{
    $query = $request->input('q');

    $results = Penjualan::where('id', 'like', "%{$query}%")
        ->orWhere('id_faktur', 'like', "%{$query}%")
        ->orWhere('nama_pelanggan', 'like', "%{$query}%")
        ->limit(10)
        ->get();

    return response()->json($results);
}


public function destroy($id)
    {
        $penjualan = Penjualan::findOrFail($id);

        // Validasi jika sudah ada pembayaran
        $piutang = Piutang::where('id_faktur', $penjualan->id_faktur)->first();
        if ($piutang && $piutang->pembayaran > 0) {
            return back()->with('error', 'Faktur tidak bisa dibatalkan karena sudah ada pembayaran.');
        }

        // Ambil semua detail item
        $details = PenjualanDetail::where('penjualan_id', $penjualan->id)->get();

        foreach ($details as $item) {
            // Kembalikan stok
            Barang::where('kode_barang', $item->kode_barang)
                  ->increment('stok', $item->quantity);
        }

        // Hapus detail, piutang, dan header penjualan
        PenjualanDetail::where('penjualan_id', $penjualan->id)->delete();
        Piutang::where('id_faktur', $penjualan->id_faktur)->delete();
        $penjualan->delete();

        return redirect()->route('penjualan.daftarjual')
                         ->with('success', 'Faktur berhasil dibatalkan dan stok dikembalikan.');
    }


}
