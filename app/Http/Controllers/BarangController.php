<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StokExport;

class BarangController extends Controller
{
    public function index()
{
    // Ambil hanya stok > 0, dan batasi hasil per halaman
    $barangList = Barang::where('stok', '>', 0)->paginate(100); // tampil 100 item per halaman

    // Hitung total nilai stok hanya untuk yang ditampilkan
    $totalNilaiStok = $barangList->sum(function ($barang) {
        return $barang->nilairp * $barang->stok;
    });

    return view('barang', compact('barangList', 'totalNilaiStok'));
}
    public function store(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'kode_barang' => 'required|string|max:255',
            'nama_barang' => 'required|string|max:255',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|numeric|min:0',
            'nilairp' => 'required|numeric|min:0',
        ]);

        // Simpan data baru ke database
        Barang::create($validated);
        

        return redirect()->route('barang.index')->with('success', 'Barang berhasil ditambahkan!');
    }
    public function edit($kode)
{
    $barang = Barang::where('kode_barang', $kode)->firstOrFail();
    return view('barang-edit', ['barang' => $barang]);
}

public function update(Request $request, $kode)
{
    $request->validate([
        'nama_barang' => 'required|string|max:100',
        'harga' => 'required|numeric|min:0',
        'stok' => 'required|numeric|min:0',
        'nilairp' => 'required|numeric|min:0',
    ]);

    $barang = Barang::where('kode_barang', $kode)->firstOrFail();
    $barang->update([
        'nama_barang' => $request->nama_barang,
        'harga' => $request->harga,
        'stok' => $request->stok,
        'nilairp' => $request->nilairp,
    ]);

    return redirect()->route('barang.index')->with('success', 'Barang berhasil diperbarui!');
}

public function destroy($kode)
{
    $barang = Barang::where('kode_barang', $kode)->firstOrFail();
    $barang->delete();

    return redirect()->route('barang')->with('success', 'Barang berhasil dihapus!');
}
public function createJual()
{
    $pelangganList = Pelanggan::all();    
    $barangList = Barang::all();
    // return view('barang-jual', ['barangList' => $barangList]);
    return view('barang-jual', compact('pelangganList', 'barangList'));
}

public function storeJual(Request $request)
{
    $request->validate([
        'kode_barang' => 'required|exists:barang,kode_barang',
        'quantity' => 'required|numeric|min:1',
    ]);

    $barang = Barang::where('kode_barang', $request->kode_barang)->first();

    // Validasi stok
    if ($request->quantity > $barang->stok) {
        return redirect()->back()->withErrors(['quantity' => 'Stok tidak mencukupi!']);
    }

    $jumlah = $barang->harga * $request->quantity;

    // Simpan transaksi
    DB::table('barang_jual')->insert([
        'kode_barang' => $barang->kode_barang,
        'nama_barang' => $barang->nama_barang,
        'harga' => $barang->harga,
        'stok' => $barang->stok,
        'quantity' => $request->quantity,
        'jumlah' => $jumlah,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Kurangi stok
    $barang->update([
        'stok' => $barang->stok - $request->quantity,
    ]);

    return redirect()->route('barang.jual.create')->with('success', 'Barang berhasil dijual!');
}
 public function search(Request $request)
    {
        $q = $request->get('q', '');
        $data = Barang::where('kode_barang', 'like', "%{$q}%")
                      ->orWhere('nama_barang', 'like', "%{$q}%")
                      ->limit(20)
                      ->get(['kode_barang', 'nama_barang', 'harga', 'isidus', 'stok', 'nilairp']);

        return response()->json($data);
    }

public function exportExcel()
{
    return Excel::download(new StokExport, 'stok_barang.xlsx');
}
}
