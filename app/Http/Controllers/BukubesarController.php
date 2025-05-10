<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bukubesar;
use App\Models\KodeAkun;
use App\Models\Biaya;

class BukubesarController extends Controller
{
    public function index(Request $request)
    {
        $kodeakunList = KodeAkun::all();

        // Ambil parameter filter
        $tanggalDari = $request->input('tanggal_dari');
        $tanggalSampai = $request->input('tanggal_sampai');
        $kodeAkun = $request->input('kode_akun');

        // Query dasar
        $query = Bukubesar::query();

        // Filter berdasarkan tanggal
        if ($tanggalDari && $tanggalSampai) {
            $query->whereBetween('created_at', [$tanggalDari, $tanggalSampai]);
        }

        // Filter berdasarkan kode akun
        if ($kodeAkun) {
            $query->where('kode_akun', $kodeAkun);
        }

        // Eksekusi query
        $bukubesarList = $query->get();

        return view('akuntan.bukubesar', compact('kodeakunList', 'bukubesarList'));
    }
    

}
