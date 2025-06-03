<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Pelanggan;
use App\Models\Barang;
use App\Models\Salesman;
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

    public function getPenjualanDetails($id)
    {
        $penj = Penjualan::with('pelanggan')->find($id);

        if (! $penj) {
            return response()->json(['error' => 'Penjualan tidak ditemukan'], 404);
        }

      $details = PenjualanDetail::where('penjualan_id', $id)->get()->map(function($det) {
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
        'quantity'     => $det->quantity,
        'disc1'        => $det->disc1,
        'disc2'        => $det->disc2,
        'disc3'        => $det->disc3,
        'disc4'        => $det->disc4,
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
            'details' => $details,
        ]);
    }

    public function processRetur(Request $request)
    {
        Log::info('Masuk ke proses retur!');
        $kodeBarangError = null;

        $request->validate([
            'penjualan_id'      => 'required|exists:penjualan,id',
            'items'             => 'required|array',
            'items.*.detail_id' => 'required|exists:penjualan_detail,id',
            'items.*.retur_dus'   => 'required|integer|min:0',
            'items.*.retur_lusin' => 'required|integer|min:0',
            'items.*.retur_pcs'   => 'required|integer|min:0',
        ]);

        try {
            DB::transaction(function () use ($request, &$kodeBarangError) {
                $penj = Penjualan::with('pelanggan')->find($request->penjualan_id);
                $timestamp = Carbon::now()->format('YmdHis');
                $idRetur = 'RT-' . $timestamp;

                $totalRetur = 0;
                $totalDiscountRetur = 0;

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

                    $isidus = optional($detailAsli->barang)->isidus ?? 1;
                    $quantityAsli = $detailAsli->quantity;
                    $quantityRetur = $returDus * $isidus + $returLusin * 12 + $returPcs;

                    $returSebelumnya = ReturDetail::where('kode_barang', $detailAsli->kode_barang)
                        ->whereHas('retur', function ($query) use ($request) {
                            $query->where('id_faktur', Penjualan::find($request->penjualan_id)->id_faktur);
                        })
                        ->sum('quantityretur');

                    $kodeBarangError = $detailAsli->kode_barang;

                    if ($quantityRetur + $returSebelumnya > $quantityAsli) {
                        throw new Exception("Jumlah retur melebihi penjualan untuk kode: {$detailAsli->kode_barang}");
                    }

                    if ($quantityRetur <= 0) {
                        continue;
                    }

                    $hargaPerItem = $detailAsli->harga;
                    $jumlahRetur  = $hargaPerItem * $quantityRetur;

                    $disc1 = floatval($detailAsli->disc1);
                    $disc2 = floatval($detailAsli->disc2);
                    $disc3 = floatval($detailAsli->disc3);
                    $disc4 = floatval($detailAsli->disc4);

                    $discAmount1 = $jumlahRetur * ($disc1 / 100);
                    $afterDisc1  = $jumlahRetur - $discAmount1;

                    $discAmount2 = $afterDisc1 * ($disc2 / 100);
                    $afterDisc2  = $afterDisc1 - $discAmount2;

                    $discAmount3 = $afterDisc2 * ($disc3 / 100);
                    $afterDisc3  = $afterDisc2 - $discAmount3;

                    $discAmount4 = $afterDisc3 * ($disc4 / 100);
                    $afterDisc4  = $afterDisc3 - $discAmount4;

                    $totalDiskon = $discAmount1 + $discAmount2 + $discAmount3 + $discAmount4;
                    $jumlahReturSetelahDiskon = $afterDisc4;

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
                        'jumlah'        => $jumlahReturSetelahDiskon,
                        'created_at'    => Carbon::now()->format('Y-m-d'),
                    ]);

                    $totalRetur += $jumlahReturSetelahDiskon;
                    $totalDiscountRetur += $totalDiskon;

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
            return redirect()->back()
                ->withInput()
                ->with('retur_error', $e->getMessage())
                ->with('fokus_kode', $kodeBarangError);
        }
    }

    public function getDetailPenjualan($kode)
    {
        $penjualan = Penjualan::with('detailBarang', 'pelanggan')
            ->where('kode_pelanggan', $kode)
            ->orderBy('tanggal', 'desc')
            ->first();

        if (! $penjualan) {
            return response()->json(['message' => 'Data penjualan tidak ditemukan'], 404);
        }

        return response()->json($penjualan);
    }

    public function daftarRetur()
{
    $returList = Retur::orderBy('created_at', 'desc')->get();

    return view('retur.daftar-retur', compact('returList'));
}
public function cetak($id)
{
    $retur = Retur::with('details')->findOrFail($id);

    // Cek jika sudah dibatalkan
    if ($retur->status === 'dibatalkan') {
        return redirect()->route('retur.daftar')->with('error', 'Retur ini telah dibatalkan dan tidak dapat dicetak.');
    }

    return view('retur.cetak-retur', compact('retur'));
}

public function cancel($id)
{
    try {
        DB::transaction(function () use ($id) {
            $retur = Retur::with('details')->findOrFail($id);

            foreach ($retur->details as $detail) {
                // Kurangi stok yang sebelumnya ditambahkan saat retur
                Barang::where('kode_barang', $detail->kode_barang)
                    ->decrement('stok', $detail->quantityretur);

                // Hapus detail
                $detail->delete();
            }

            // Hapus retur utama
            $retur->delete();
        });

        return redirect()->route('retur.daftar')->with('success', 'Retur berhasil dibatalkan dan stok dikembalikan.');
    } catch (\Exception $e) {
        return redirect()->route('retur.daftar')->with('error', 'Gagal membatalkan retur: ' . $e->getMessage());
    }
}
public function batalkan($id)
{
    try {
        DB::transaction(function () use ($id) {
            $retur = Retur::with('details')->findOrFail($id);

            // Cek apakah sudah dibatalkan
            if ($retur->status === 'dibatalkan') {
                throw new \Exception('Retur sudah dibatalkan sebelumnya.');
            }

            // Kembalikan stok
            foreach ($retur->details as $detail) {
                Barang::where('kode_barang', $detail->kode_barang)
                    ->decrement('stok', $detail->quantityretur);
            }

            // Update status retur
            $retur->status = 'dibatalkan';
            $retur->save();
        });

        return redirect()->route('retur.daftar')->with('success', 'Retur berhasil dibatalkan & stok dikembalikan.');
    } catch (\Exception $e) {
        return redirect()->route('retur.daftar')->with('error', 'Gagal membatalkan retur: ' . $e->getMessage());
    }
}


}
