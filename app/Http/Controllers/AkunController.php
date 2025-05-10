<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KodeAkun;
use App\Models\Bukubesar;

class AkunController extends Controller
{
    public function index()
    {
        $kodeakunList = KodeAkun::all();
        $bukubesarList = Bukubesar::all();
        return view('akuntan.kodeakun',  compact('kodeakunList', 'bukubesarList'));
    }
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'kode_akun' => 'required|string|unique:kodeakun,kode_akun',
            'nama_akun' => 'required|string|max:50',
            'kelompok_akun' => 'required|string|max:10',
        ], [
            'kode_akun.unique' => 'Kode akun sudah digunakan. Silakan gunakan kode lain.',
        ]);
    
        // Jika lolos validasi, simpan data
        KodeAkun::create([
            'kode_akun' => $request->kode_akun,
            'nama_akun' => $request->nama_akun,
            'kelompok_akun' => $request->kelompok_akun,
        ]);
        Bukubesar::create([
            'kode_akun' => $request->kode_akun,
            'nama_akun' => $request->nama_akun,
        ]);
    
    
        return redirect()->back()->with('success', 'Kategori berhasil ditambahkan.');
    }
    

public function destroy($kode)
{
    $kodeakun = KodeAkun::where('kode_akun', $kode)->firstOrFail();
    $kodeakun->delete();

    return redirect()->route('akuntan.kodeakun')->with('success', 'Kategori berhasil dihapus!');
}
}
