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

    public function index()
    {
        $dt = Dt::with('ddt')->get();
        $penjualanList = Penjualan::all();
        $piutangList = Piutang::where('sisapiutang', '>', 0)->get();
        $salesmanList = Salesman::all();

        return view('dt.index', compact('dt', 'salesmanList', 'penjualanList', 'piutangList'));
    }

    // Menyimpan data penjualan ke dalam form DT
    public function store(Request $request)
    {
        // Perbarui aturan validasi sesuai dengan nama field di form
        $data = $request->validate([
            'id_colector' => 'required',
            'colector' => 'required',
            'totaldt' => 'required',
            'items' => 'required|array',
            'items.*.id_faktur' => 'required',
            'items.*.kode_pelanggan' => 'required',
            'items.*.nama_pelanggan' => 'required',
            'items.*.top' => 'required|integer',
            'items.*.total' => 'required|numeric',
            'items.*.bayar' => 'required|numeric',
            'items.*.sisapiutang' => 'required|numeric',
        ]);
            // ✅ Validasi faktur sudah pernah masuk dan belum diproses
    foreach ($request->items as $item) {
        $idFaktur = $item['id_faktur'];

        $exists = DB::table('ddt')
            ->join('dt', 'ddt.dt_id', '=', 'dt.id')
            ->where('ddt.id_faktur', $idFaktur)
            ->where('dt.is_updated', false)
            ->exists();

        if ($exists) {
            return back()->withErrors(['Faktur ' . $idFaktur . ' sudah masuk daftar tagihan sebelumnya dan belum diproses.'])->withInput();
        }
    }

        // Simpan data ke tabel dt
        $dt = Dt::create([
            'id_colector' => $data['id_colector'], // sesuaikan dengan nama field di model atau tabel Anda
            'colector' => $data['colector'],
            'totaldt' => array_sum(array_column($data['items'], 'total')),
        ]);

        // Simpan detail penjualan ke dalam tabel dt_details
        foreach ($data['items'] as $item) {
            $dt->details()->create([
                'id_faktur' => $item['id_faktur'],
                'kode_pelanggan' => $item['kode_pelanggan'],
                'nama_pelanggan' => $item['nama_pelanggan'],
                'top' => $item['top'],
                'total' => $item['total'],
                'bayar' => $item['bayar'],
                'sisapiutang' => $item['sisapiutang'],
            ]);
        }

        if ($request->action === 'save_and_print') {
            return redirect()->route('dt.cetak', $dt->id);
        }

        // return redirect()->route('dt.index')->with('success', 'Data penjualan berhasil disimpan.');
        return redirect()->route('dt.index')->with('success', 'Daftar Tagihan disimpan dengan No ID: ' . $dt->id);
    }
    public function cetak($id)
    {
        $dt = Dt::with('details')->findOrFail($id);
        return view('dt.cetak', compact('dt'));
    }

public function update(Request $request, $id)
{
    $dt = Dt::with('details')->findOrFail($id);

    $data = $request->validate([
        'details' => 'required|array',
        'details.*.id' => 'required|exists:ddt,id',
        'details.*.bayar' => 'required|numeric|min:0',
        'details.*.sisa_piutang' => 'required|numeric|min:0',
    ]);

    foreach ($data['details'] as $detailData) {
        $detail = $dt->details()->find($detailData['id']);

        // Ambil total sisa piutang untuk toko terkait
        $totalSisa = \App\Models\Piutang::where('id_faktur', $detail->id_faktur)
                        ->sum('sisapiutang');
        if ($totalSisa == 0) {
            // Lewati update atau bisa mengembalikan error jika ada input pembayaran
            continue; // atau Anda bisa set nilai bayar dan sisa menjadi 0
        }

        $detail->update([
            'bayar' => $detailData['bayar'],
            'sisapiutang' => $detailData['sisa_piutang'],
        ]);

        $piutang = Piutang::where('id_faktur', $detail->id_faktur)->first();
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
            'id_faktur'       => $detail->id_faktur,
            'kode_pelanggan'  => $detail->kode_pelanggan,
            'nama_pelanggan'  => $detail->nama_pelanggan,
            'total'    => $detail->total,
            'bayar'           => $detailData['bayar'],
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
        $dt = Dt::with('details')->find($id);

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
 public function daftar(Request $request)
{
      $dt = Dt::with('ddt')->get(); // gunakan eager loading
    //  

  

    return view('dt.daftardt', compact('dt'));
}

public function edit($id)
{
    // dd(Dtt::where('dt_id', $id)->get());
   $dt = Dt::with('ddt')->findOrFail($id);
    $salesmanList = Salesman::all();
    $piutangList = Piutang::where('sisapiutang', '>', 0)->get();

    return view('dt.edit', compact('dt', 'salesmanList', 'piutangList'));
}
public function updatedt(Request $request, $id)
{
   $data = $request->validate([
        'id_colector' => 'required',
        'colector' => 'required',
        'totaldt' => 'required',
        'items' => 'required|array',
        'items.*.id_faktur' => 'required',
        'items.*.kode_pelanggan' => 'required',
        'items.*.nama_pelanggan' => 'required',
        'items.*.top' => 'required|integer',
        'items.*.total' => 'required|numeric',
        'items.*.bayar' => 'required|numeric',
        'items.*.sisapiutang' => 'required|numeric',
    ]);

     // Update header-nya dulu
    $dt = Dt::findOrFail($id);
    $dt->id_colector = $request->id_colector;
    $dt->colector = $request->colector;
    $dt->totaldt = $request->totaldt;
    $dt->save();

    // Hapus detail lama
   $existingDetails = Dtt::where('dt_id', $id)->pluck('id_faktur')->toArray();

$submittedFakturs = collect($request->items)->pluck('id_faktur')->toArray();

// Hapus faktur yang tidak ada lagi dalam request
Dtt::where('dt_id', $id)
    ->whereNotIn('id_faktur', $submittedFakturs)
    ->delete();

// Simpan atau update data item
foreach ($request->items as $item) {
    Dtt::updateOrCreate(
        ['dt_id' => $id, 'id_faktur' => $item['id_faktur']], // kunci unik
        [
            'kode_pelanggan' => $item['kode_pelanggan'],
            'nama_pelanggan' => $item['nama_pelanggan'],
            'top' => $item['top'],
            'total' => $item['total'],
            'bayar' => $item['bayar'],
            'sisapiutang' => $item['sisapiutang'],
        ]
    );
}

    return redirect()->route('dt.index')->with('success', 'Data DT berhasil diperbarui.');
}
    public function destroy($id)
    {
    $dt = Dt::findOrFail($id);
        $dt->details()->delete();
        $dt->delete();

        return redirect()->route('dt.index')->with('success', 'Data berhasil dihapus.');
    }
}
