<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Pelanggan;
use App\Models\Barang;
use App\Models\Salesman;       // Jika Salesman tampil di sisi form Retur
use App\Models\Retur;
use App\Models\ReturDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;


class ReturController extends Controller
{
    public function showForm()
    {
        return view('retur.form-retur');
    }
      /**
     * AJAX: Mengambil detail penjualan (header + detail item) berdasarkan penjualan.id
     * URL: GET /retur-penjualan/details/{id}
     */
    public function getPenjualanDetails($id)
    {
        // Ambil data header penjualan + relasi pelanggan
      $penj = Penjualan::with('pelanggan')->find($id);

        if (! $penj) {
            return response()->json(['error' => 'Penjualan tidak ditemukan'], 404);
        }

        // Ambil detail item penjualan
        $details = PenjualanDetail::where('penjualan_id', $id)->get()->map(function($det) {
            // Ambil isi dus dari master Barang
            $isiDus = optional($det->barang)->isidus ?? 1;

            return [
                'id'           => $det->id,
                'kode_barang'  => $det->kode_barang,
                'nama_barang'  => $det->nama_barang,
                'harga'        => $det->harga,
                'dus'          => $det->dus,
                'lusin'        => $det->lusin,
                'pcs'          => $det->pcs,
                'isidus'       => $isiDus,
                'quantity'     => $det->quantity, // total PCS sebelumnya
            ];
        });

        return response()->json([
            'penjualan' => [
                'id'             => $penj->id,
                'id_faktur'      => $penj->id_faktur,
                'kode_sales'     => $penj->kode_sales,
                'nama_sales'     => $penj->nama_sales,
                'kode_pelanggan' => $penj->kode_pelanggan,
                'nama_pelanggan' => $penj->nama_pelanggan,
                'alamat'         => optional($penj->pelanggan)->alamat,
            ],
            'details'   => $details,
        ]);
    }

    /**
     * Menangani submit form Retur: 
     * - Simpan header ke tabel 'returs'
     * - Simpan detail ke tabel 'returdetails'
     * - Update stok barang (menambah stok kembali)
     */
    public function processRetur(Request $request)
{
    Log::info('Masuk ke proses retur!');
    $kodeBarangError = null; // Tambahkan ini

    $request->validate([
        'penjualan_id'      => 'required|exists:penjualan,id',
        'items'             => 'required|array',
        'items.*.detail_id' => 'required|exists:penjualan_detail,id',
        'items.*.retur_dus'   => 'required|integer|min:0',
        'items.*.retur_lusin' => 'required|integer|min:0',
        'items.*.retur_pcs'   => 'required|integer|min:0',
    ]);

    try {
        DB::transaction(function () use ($request) {
            $penj = Penjualan::with('pelanggan')->find($request->penjualan_id);
            $timestamp   = Carbon::now()->format('YmdHis');
            $idRetur     = 'RT-' . $timestamp;

            $totalRetur = 0;
            $totalDiscountRetur = 0;

            // 1x simpan header retur di awal, agar ID bisa dipakai untuk detail
            $retur = Retur::create([
                'id_retur'        => $idRetur,
                'id_faktur'       => $penj->id_faktur,
                'kode_sales'      => $penj->kode_sales,
                'nama_sales'      => $penj->nama_sales,
                'kode_pelanggan'  => $penj->kode_pelanggan,
                'nama_pelanggan'  => $penj->nama_pelanggan,
                'total_discount'  => 0,
                'total'           => 0,
            ]);

            foreach ($request->items as $item) {
                $detailAsli = PenjualanDetail::find($item['detail_id']);
                if (! $detailAsli) continue;

                $returDus   = intval($item['retur_dus']);
                $returLusin = intval($item['retur_lusin']);
                $returPcs   = intval($item['retur_pcs']);

                $isidus         = optional($detailAsli->barang)->isidus ?? 1;
                $quantityAsli   = $detailAsli->quantity;
                $quantityRetur  = $returDus * $isidus + $returLusin * 12 + $returPcs;

                $returSebelumnya = ReturDetail::where('kode_barang', $detailAsli->kode_barang)
                    ->whereHas('retur', function ($query) use ($request) {
                        $query->where('id_faktur', Penjualan::find($request->penjualan_id)->id_faktur);
                    })
                    ->sum('quantityretur');
                    $kodeBarangError = $detailAsli->kode_barang; // Simpan dulu

                if ($quantityRetur + $returSebelumnya > $quantityAsli) {
                    // Batal transaksi dengan exception
                    throw new Exception("Jumlah retur melebihi penjualan untuk kode: {$detailAsli->kode_barang}");
                }

                if ($quantityRetur <= 0) {
                    continue;
                }

                $hargaPerItem = $detailAsli->harga;
                $jumlahRetur  = $hargaPerItem * $quantityRetur;
                $discountBaris = 0;

                ReturDetail::create([
                    'retur_id'      => $retur->id,
                    'kode_barang'   => $detailAsli->kode_barang,
                    'nama_barang'   => $detailAsli->nama_barang,
                    'harga'         => $hargaPerItem,
                    'dus'           => $detailAsli->dus,
                    'lusin'         => $detailAsli->lusin,
                    'pcs'           => $detailAsli->pcs,
                    'quantity'      => $quantityAsli,
                    'dusretur'      => $returDus,
                    'lusinretur'    => $returLusin,
                    'pcsretur'      => $returPcs,
                    'quantityretur' => $quantityRetur,
                    'jumlah'        => $jumlahRetur,
                    'created_at'    => Carbon::now()->format('Y-m-d'),
                ]);

                $totalRetur += $jumlahRetur;
                $totalDiscountRetur += $discountBaris;

                Barang::where('kode_barang', $detailAsli->kode_barang)
                    ->increment('stok', $quantityRetur);
            }

            $retur->update([
                'total_discount' => $totalDiscountRetur,
                'total'          => $totalRetur,
            ]);
        });

        return redirect()->route('retur.form')->with('success', 'Retur Penjualan berhasil disimpan dan stok telah diupdate.');
    } catch (Exception $e) {
        // Kembali ke form jika gagal karena retur berlebih
        return redirect()->back()
            ->withInput()
            ->with('retur_error', $e->getMessage())
            ->with('fokus_kode', $kodeBarangError); // Gunakan variabel aman
    }
}
public function getDetailPenjualan($kode)
{
    $penjualan = Penjualan::with('detailBarang', 'pelanggan')
        ->where('kode_pelanggan', $kode)
        ->orderBy('tanggal', 'desc')
        ->first();

    if (!$penjualan) {
        return response()->json(['message' => 'Data penjualan tidak ditemukan'], 404);
    }

    return response()->json($penjualan);
}
    
}

