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

        // 1) Validasi basic input
        $request->validate([
            'penjualan_id'      => 'required|exists:penjualan,id',
            'items'             => 'required|array',
            'items.*.detail_id' => 'required|exists:penjualan_detail,id',
            'items.*.retur_dus'   => 'required|integer|min:0',
            'items.*.retur_lusin' => 'required|integer|min:0',
            'items.*.retur_pcs'   => 'required|integer|min:0',
        ]);

        // 2) Mulai transaksi DB untuk atomic
        DB::transaction(function() use ($request) {
            // Ambil header penjualan asli
            $penj = Penjualan::with('pelanggan')->find($request->penjualan_id);

            // Generate id_retur unik (misal: "RT-YYYYMMDD-XXXX")
            $timestamp   = Carbon::now()->format('YmdHis');
            $idRetur     = 'RT-' . $timestamp;

            // 3) Siapkan total_discount dan total (kita akan hitung di detail)
            $totalRetur        = 0;
            $totalDiscountRetur= 0; // jika perlu, di sini bisa diisi sesuai aturan discount

            // 4) Simpan header retur
            $retur = Retur::create([
                'id_retur'        => $idRetur,
                'id_faktur'       => $penj->id_faktur,
                'kode_sales'      => $penj->kode_sales,
                'nama_sales'      => $penj->nama_sales,
                'kode_pelanggan'  => $penj->kode_pelanggan,
                'nama_pelanggan'  => $penj->nama_pelanggan,
                'total_discount'  => $totalDiscountRetur,
                'total'           => 0, // sementara 0, kita update di akhir
            ]);

            // 5) Proses tiap item dalam request untuk disimpan di returdetails
            foreach ($request->items as $item) {
                $detailId    = $item['detail_id'];
                $returDus    = intval($item['retur_dus']);
                $returLusin  = intval($item['retur_lusin']);
                $returPcs    = intval($item['retur_pcs']);

                // Ambil data detail penjualan asli
                $detailAsli = PenjualanDetail::find($detailId);
                if (! $detailAsli) {
                    // Jika tidak ditemukan, continue (meskipun validasi seharusnya mencegah ini)
                    continue;
                }

                // Validasi logika: 
                // - returDus tidak boleh > detailAsli->dus, 
                // - returLusin tidak boleh > detailAsli->lusin,
                // - returPcs tidak boleh > detailAsli->pcs.
                if ($returDus > $detailAsli->dus
                    || $returLusin > $detailAsli->lusin
                    || $returPcs > $detailAsli->pcs) {
                    // Kita lempar Exception agar transaksi rollback
                    throw new \Exception("Jumlah retur melebihi jumlah penjualan untuk kode: {$detailAsli->kode_barang}");
                }

                // Hitung quantity (PCS) asli dan quantity retur (PCS)
                $isidus         = optional($detailAsli->barang)->isidus ?? 1; 
                $quantityAsli   = $detailAsli->quantity; // (misal: dus*isidus + lusin*12 + pcs)
                $quantityRetur  = $returDus * $isidus + $returLusin * 12 + $returPcs;

                if ($quantityRetur <= 0) {
                    // Jika user tidak memasukkan retur sama sekali, skip
                    continue;
                }

                // Hitung jumlah (subtotal) untuk retur: 
                // Misal kita hanya mengalikan harga * quantityRetur
                $hargaPerItem = $detailAsli->harga; 
                $jumlahRetur  = $hargaPerItem * $quantityRetur;

                // (Opsional) Hitung discount per baris retur jika diperlukan, 
                // di sini kita set 0.
                $discountBaris = 0;

                // Simpan detail retur
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

                // Tambahkan ke total keseluruhan
                $totalRetur += $jumlahRetur;
                $totalDiscountRetur += $discountBaris;

                // 6) Update stok di tabel barang: barang.stok += quantityRetur
                Barang::where('kode_barang', $detailAsli->kode_barang)
                      ->increment('stok', $quantityRetur);
            }

            // 7) Update nilai total dan total_discount di header retur
            $retur->update([
                'total_discount' => $totalDiscountRetur,
                'total'          => $totalRetur,
            ]);
        });

        // 8) Setelah sukses, redirect kembali dengan pesan
        return redirect()->route('retur.form')
                         ->with('success', 'Retur Penjualan berhasil disimpan dan stok telah diupdate.');
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

