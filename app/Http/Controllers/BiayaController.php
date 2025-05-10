<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bukubesar;
use App\Models\KodeAkun;
use App\Models\Biaya;
use App\Models\Biayaresume;
use PDF;

class BiayaController extends Controller
{

    public function index()
    {
        $kode_transaksi = 'TRX' . time(); // Membuat kode transaksi unik
        $kodeakunList = KodeAkun::all(); // Ambil data akun
        $biayaresumeList = Biayaresume::all();
        return view('akuntan.biaya', compact('kodeakunList', 'kode_transaksi', 'biayaresumeList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
           'kode_transaksi' => 'required|string|max:20|unique:biayaresume,kode_transaksi',
            'created_at' => 'required|date',
            'items' => 'required|array|min:1', // Pastikan items adalah array dan memiliki setidaknya satu elemen
            'items.*.kode_akun' => 'required|string|max:10',
            'items.*.nama_akun' => 'required|string|max:50',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.keterangan' => 'required|string|max:50',
        ]);
    
        $total = 0;
    
        foreach ($validated['items'] as $item) {
            Biaya::create([
                'kode_transaksi' => $validated['kode_transaksi'],
                'kode_akun' => $item['kode_akun'],
                'nama_akun' => $item['nama_akun'],
                'jumlah' => $item['jumlah'],
                'keterangan' => $item['keterangan'],
                'created_at' => $validated['created_at'],
            ]);
            Bukubesar::create([
                'created_at' => $validated['created_at'],
                'kode_akun' => $item['kode_akun'],
                'nama_akun' => $item['nama_akun'],
                'kredit' => $item['jumlah'],
                'uraian' => $item['keterangan'],
            ]);
    
            $total += $item['jumlah'];
        }
    
        // Update atau buat data di biayaresume
        Biayaresume::updateOrCreate(
            ['kode_transaksi' => $validated['kode_transaksi']], // Kondisi pencarian
             [
        'created_at' => $validated['created_at'], // Data yang akan disimpan
        'total' => $total,
             ]
        );
        
        if ($request->action === 'save_and_print') {
            return redirect()->route('akuntan.cetakbiaya', ['kode_transaksi' => $validated['kode_transaksi']]);
        }
        
    
        return redirect()->route('akuntan.biaya')->with('success', 'Data berhasil disimpan!');
    }
   
    public function cetak($kode_transaksi)
    {
        // Ambil data biaya berdasarkan kode transaksi
        $biayaItems = Biaya::where('kode_transaksi', $kode_transaksi)->get();
        $biayaresume = Biayaresume::where('kode_transaksi', $kode_transaksi)->firstOrFail();
    
        // Return view untuk cetak
        return view('akuntan.cetakbiaya', compact('biayaItems', 'biayaresume'));
    }
    

    public function cetakPdf($kode_transaksi)
    {
        $biayaItems = Biaya::where('kode_transaksi', $kode_transaksi)->get();
        $resume = Biayaresume::where('kode_transaksi', $kode_transaksi)->first();

        if (!$resume) {
            abort(404, 'Data transaksi tidak ditemukan.');
        }

        $pdf = PDF::loadView('akuntan.cetakbiaya-pdf', compact('biayaItems', 'resume'))
                  ->setPaper('a4', 'portrait');

        return $pdf->stream('laporan_biaya_' . $kode_transaksi . '.pdf');
    }
}

