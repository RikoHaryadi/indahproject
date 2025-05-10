<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kategori;

class KategoriController extends Controller
{
    public function index()
    {
        $kategoriList = Kategori::all();

        // // Menghitung total nilai Rp (harga * stok)
        // $totalNilaiStok = $kategoriList->sum(function ($kategori) {
        //     return $kategori->harga * $kategori->stok;
        // });

        return view('Kategori', compact('kategoriList'));
    }
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'kode_kategori' => 'required|string|unique:kategori,kode_kategori',
            'nama_kategori' => 'required|string|max:255',
        ], [
            'kode_kategori.unique' => 'Kode kategori sudah digunakan. Silakan gunakan kode lain.',
        ]);
    
        // Jika lolos validasi, simpan data
        Kategori::create([
            'kode_kategori' => $request->kode_kategori,
            'nama_kategori' => $request->nama_kategori,
        ]);
    
        return redirect()->back()->with('success', 'Kategori berhasil ditambahkan.');
    }
    public function edit($kode)
{
    $kategori = Kategori::where('kode_kategori', $kode)->firstOrFail();
    return view('kategori-edit', ['kategori' => $kategori]);
}

public function update(Request $request, $kode)
{
    $request->validate([
        'nama_kategori' => 'required|string|max:50',

    ]);

    $kategori = Kategori::where('kode_kategori', $kode)->firstOrFail();
    $kategori->update([
        'nama_kategori' => $request->nama_kategori,
      
    ]);

    return redirect()->route('kategori.index')->with('success', 'Kategori berhasil diperbarui!');
}

public function destroy($kode)
{
    $kategori = Kategori::where('kode_kategori', $kode)->firstOrFail();
    $kategori->delete();

    return redirect()->route('kategori.index')->with('success', 'Kategori berhasil dihapus!');
}

}
