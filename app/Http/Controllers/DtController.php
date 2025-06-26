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


class DtController extends Controller
{
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
 public function daftar(Request $request)
{
      $dt = Dt::with('ddt')->get(); // gunakan eager loading
    //  

  

    return view('dt.daftardt', compact('dt'));
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
            $dt->ddt()->create([
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
        $dt = Dt::with('ddt')->findOrFail($id);
        return view('dt.cetak', compact('dt'));
    }

public function edit($id)
{
    // dd(Dtt::where('dt_id', $id)->get());
   $dt = Dt::with('ddt')->findOrFail($id);
    $salesmanList = Salesman::all();
    $piutangList = Piutang::where('sisapiutang', '>', 0)->get();

    return view('dt.edit', compact('dt', 'salesmanList', 'piutangList'));
}
}

