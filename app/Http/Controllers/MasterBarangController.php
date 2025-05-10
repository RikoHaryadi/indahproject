<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterBarang;
use App\Models\Barang;
use App\Models\Kategori;


class MasterBarangController extends Controller
{
    public function index()
    {
        $masterbarangList = MasterBarang::all();
        $kategoriList = Kategori::all(); // Ambil daftar pelanggan
      
        return view('masterbarang', compact('masterbarangList', 'kategoriList')); // Kirim data ke view
  
    }

    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'kode_barang' => 'required|string|max:255',
            'nama_barang' => 'required|string|max:255',
            'hargapcs' => 'required|numeric|min:0',
            'hargapcsjual' => 'required|numeric|min:0',
            'isidus' => 'required|numeric|min:0',
            'kategori' => 'required|string|max:25',
        ], [
            'kode_barang.unique' => 'Kode Barang Sudah digunakan',
        ]);

        // Simpan data baru ke database
        MasterBarang::create([
            'kode_barang' => $request->kode_barang,
            'nama_barang' => $request->nama_barang,
            'hargapcs' => $request->hargapcs,
            'hargapcsjual' => $request->hargapcsjual,
            'isidus' => $request->isidus,
            'kategori' => $request->kategori,
        ]);
        Barang::create([
            'kode_barang' => $request->kode_barang,
            'nama_barang' => $request->nama_barang,
            'isidus' => $request->isidus,
            'harga' => $request->hargapcs,
            'nilairp' => $request->hargapcsjual,
            'stok' => 0,
    
            
        ]);

        return redirect()->route('masterbarang.index')->with('success', 'Barang berhasil ditambahkan!');
    }

    public function edit($kode)
    {
        $masterbarang = MasterBarang::where('kode_barang', $kode)->firstOrFail();
        $barang = Barang::where('kode_barang', $kode)->firstOrFail();
        $kategoriList = Kategori::all(); // Ambil daftar kategori
    
        return view('masterbarang-edit', compact('masterbarang', 'kategoriList'));
    }
    
    public function update(Request $request, $kode)
    {
        $request->validate([
            'nama_barang' => 'required|string|max:255',
            'hargapcs' => 'required|numeric|min:0',
            'hargapcsjual' => 'required|numeric|min:0',
            'isidus' => 'required|numeric|min:0',
            'kategori' => 'required|string|max:25',
        ]);
    
        $masterbarang = MasterBarang::where('kode_barang', $kode)->firstOrFail();
        $masterbarang->update([
            'nama_barang' => $request->nama_barang,
            'hargapcs' => $request->hargapcs,
            'hargapcsjual' => $request->hargapcsjual,
            'isidus' => $request->isidus,
            'kategori' => $request->kategori,
        ]);
        $barang = Barang::where('kode_barang', $kode)->firstOrFail();
        $barang->update([
            'nama_barang' => $request->nama_barang,
            'harga' => $request->hargapcs,
            'isidus' => $request->isidus,
            'nilairp' => $request->hargapcsjual,
        ]);
    
        return redirect()->route('masterbarang.index')->with('success', 'Barang berhasil diperbarui!');
    }
    
    public function destroy($kode)
    {
        $masterbarang = MasterBarang::where('kode_barang', $kode)->firstOrFail();
        $masterbarang->delete();
    
        return redirect()->route('masterbarang.index')->with('success', 'Barang berhasil dihapus!');
    }

    public function importCSV(Request $request)
{
    // Validasi file CSV
    $request->validate([
        'csv_file' => 'required|mimes:csv,txt',
    ]);

    // Ambil file yang diupload
    $file = $request->file('csv_file');

    // Buka file CSV dan baca isinya
    if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
        // Jika file CSV memiliki header, ambil baris pertama sebagai header dan lewati
        $header = fgetcsv($handle, 1000, ';');
        
        // Inisialisasi counter untuk menghitung data yang berhasil diimpor
        $dataInserted = 0;

        // Looping setiap baris data CSV
        while (($data = fgetcsv($handle, 1000, ';')) !== false) {
            // Pastikan jumlah kolom sesuai, misalnya 6 kolom
            if (count($data) < 6) {
                continue; // Lewati baris yang tidak lengkap
            }

            // Asumsikan urutan kolom: kode_barang, nama_barang, hargapcs, hargapcsjual, isidus, kategori
            $kode_barang  = $data[0];
            $nama_barang  = $data[1];
            $hargapcs     = $data[2];
            $hargapcsjual = $data[3];
            $isidus       = $data[4];
            $kategori     = $data[5];

            // Optional: cek apakah barang dengan kode yang sama sudah ada
            $existing = \App\Models\MasterBarang::where('kode_barang', $kode_barang)->first();
            if ($existing) {
                // Jika ingin lewati data yang sudah ada, bisa uncomment baris berikut:
                // continue;

                // Atau, jika ingin update data yang sudah ada, bisa tambahkan logika update di sini
            } else {
                // Simpan ke tabel MasterBarang
                \App\Models\MasterBarang::create([
                    'kode_barang'  => $kode_barang,
                    'nama_barang'  => $nama_barang,
                    'hargapcs'     => $hargapcs,
                    'hargapcsjual' => $hargapcsjual,
                    'isidus'       => $isidus,
                    'kategori'     => $kategori,
                ]);

                // Simpan ke tabel Barang
                \App\Models\Barang::create([
                    'kode_barang'  => $kode_barang,
                    'nama_barang'  => $nama_barang,
                    'isidus'       => $isidus,
                    'harga'        => $hargapcs,
                    'nilairp'      => $hargapcsjual,
                    'stok' => 0,
                ]);

                // Increment counter ketika data berhasil diinsert
                $dataInserted++;
            }
        }
        fclose($handle);
        return redirect()->route('masterbarang.index')
            ->with('success', "Import CSV berhasil. Data yang diimport: {$dataInserted} baris.");
    }

    return redirect()->route('pelanggan.index')
        ->with('error', 'Gagal membuka file CSV.');
}


  
}


