<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\Piutang;
use App\Models\Dt;
use App\Models\Dtt;
use App\Models\Salesman;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon; // pastikan sudah di-import di atas


class PiutangController extends Controller
{
    public function search(Request $request)
    {
        $search = $request->q;

        $piutang = Piutang::where('id_faktur', 'LIKE', "%{$search}%")
            ->orWhere('kode_pelanggan', 'LIKE', "%{$search}%")
            ->limit(10)
            ->get();

        return response()->json($piutang->map(function ($item) {
            return [
                'id_faktur' => $item->id_faktur,
                'text' => "{$item->id} - {$item->kode_pelanggan}",
                'kode_pelanggan' => $item->kode_pelanggan,
                'nama_pelanggan' => $item->nama_pelanggan,
                'total' => $item->total,
                'sisapiutang' => $item->sisapiutang,
            ];
        }));
    }



   
public function update(Request $request, $id)
{
    $dt = Dt::with('ddt')->findOrFail($id);

    $data = $request->validate([
        'details' => 'required|array',
        'details.*.id' => 'required|exists:ddt,id',
        'details.*.bayar' => 'required|numeric|min:0',
        'details.*.sisa_piutang' => 'required|numeric|min:0',
    ]);

   foreach ($data['details'] as $detailData) {
    $ddt = $dt->ddt()->find($detailData['id']); // Ambil detail dari relasi ddt
    if (!$ddt) {
        continue; // lewati jika tidak ditemukan
    }
        // Ambil total sisa piutang untuk toko terkait
       $totalSisa = \App\Models\Piutang::where('id_faktur', $ddt->id_faktur)
                    ->sum('sisapiutang');

        if ($totalSisa == 0) {
            // Lewati update atau bisa mengembalikan error jika ada input pembayaran
            continue; // atau Anda bisa set nilai bayar dan sisa menjadi 0
        }

       $ddt->update([
        'bayar' => $detailData['bayar'],
        'sisapiutang' => $detailData['sisa_piutang'],
    ]);

        $piutang = \App\Models\Piutang::where('id_faktur', $ddt->id_faktur)->first();
        if ($piutang) {
            $currentBayar = $piutang->bayar ?? 0;
            $newBayar   = $detailData['bayar'];
            $piutang->update([
                'bayar'       => $currentBayar + $newBayar,
                'pembayaran'  => $newBayar,
                'sisapiutang' => $detailData['sisa_piutang'],
            ]);
        }

        Pembayaran::create([
           'id_faktur'      => $ddt->id_faktur,
        'kode_pelanggan' => $ddt->kode_pelanggan,
        'nama_pelanggan' => $ddt->nama_pelanggan,
        'total'          => $ddt->total,
        'bayar'          => $detailData['bayar'],
        'sisapiutang'    => $detailData['sisa_piutang'],
        ]);
    }

    $dt->is_updated = true;
    $dt->save();

    return redirect()->route('dt.index')->with('success', 'Pembayaran berhasil diperbarui.');
}




// 


public function showCariEdit(Request $request)
{
    $dt = null;

    // Jika ada query parameter 'edit_id', cari data DT
    if ($request->has('edit_id')) {
        $id = $request->input('edit_id');
        $dt = Dt::with('ddt')->find($id);

        // Jika data tidak ditemukan, redirect kembali dengan pesan error
        if (!$dt) {
            return redirect()->route('dt.cari_edit')
                             ->with('error', 'Data dengan ID ' . $id . ' tidak ditemukan.');
        }

        // Jika data sudah diupdate, jangan tampilkan form edit
        if ($dt->is_updated) {
            return redirect()->route('dt.cari_edit')
                             ->with('error', 'Pembayaran untuk ID ' . $id . ' sudah diperbarui dan tidak dapat diedit lagi.');
        }
    }

    return view('dt.cari_edit', compact('dt'));
}
public function indexPiutang(Request $request)
{
    ini_set('max_execution_time', 300);
   $query = Piutang::query()->where('sisapiutang', '>', 0);

    if ($request->filled('kode_pelanggan')) {
        $query->where('kode_pelanggan', $request->kode_pelanggan);
    }

    $piutangList = $query->get();

    // Ambil semua pelanggan, jadikan koleksi indexed by kode_pelanggan
    $pelangganMap = Pelanggan::all()->keyBy('Kode_pelanggan');

    // Filter berdasarkan jatuh tempo
    if ($request->filled('jatuh_tempo')) {
        $tanggalFilter = Carbon::parse($request->jatuh_tempo);
        $query->whereHas('pelanggan', function ($q) use ($tanggalFilter) {
            $q->whereRaw('DATE_ADD(piutang.created_at, INTERVAL pelanggan.top DAY) <= ?', [$tanggalFilter->toDateString()]);
        });
    }

    // Ambil semua data pembayaran terlebih dahulu
    $pembayaranList = Pembayaran::all();

      foreach ($piutangList as $piutang) {
        $top = $pelangganMap[$piutang->kode_pelanggan]->top ?? 0;

        $piutang->jatuhTempo = Carbon::parse($piutang->created_at)
                                ->addDays($top)
                                ->format('d-m-Y');
    }

    $pelangganList = Pelanggan::all();

   
    return view('piutang.index', compact('piutangList', 'pelangganList'));
}
public function checkFakturExists(Request $request)
{
    $idFaktur = $request->input('id_faktur');

    // Cek apakah sudah pernah masuk ke dt yang belum diupdate
    $exists = DB::table('ddt')
        ->join('dt', 'ddt.dt_id', '=', 'dt.id')
        ->where('ddt.id_faktur', $idFaktur)
        ->where('dt.is_updated', 0)
        ->exists();

    return response()->json(['exists' => $exists]);
}



    public function destroy($id)
    {
    $dt = Dt::findOrFail($id);
        $dt->ddt()->delete();
        $dt->delete();

        return redirect()->route('dt.index')->with('success', 'Data berhasil dihapus.');
    }
}
