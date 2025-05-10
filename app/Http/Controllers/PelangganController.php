<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pelanggan;
use App\Models\Salesman;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
class PelangganController extends Controller
{
    public function index()
    {
        $pelangganList = Pelanggan::all();
        $salesmanList = Salesman::all();
        return view('pelanggan.index', compact('salesmanList'), ['pelangganList' => $pelangganList]);
    }

    public function edit($Kode_pelanggan)
    {
        $pelanggan = Pelanggan::where('Kode_pelanggan', $Kode_pelanggan)->firstOrFail();
        $salesmanList = Salesman::all(); // Ambil semua data salesman
        return view('pelanggan.edit-pelanggan', [
        'pelanggan' => $pelanggan,
        'salesmanList' => $salesmanList, // Kirimkan ke view
    ]);
    }

    public function update(Request $request, $Kode_pelanggan)
    {
        $request->validate([
            'Nama_pelanggan' => 'required|string|max:100',
            'alamat' => 'required|string|max:255',
            'telepon' => 'required|string|max:15',
            'top' => 'required|numeric',
            'kredit_limit' => 'required|numeric',
            'kode_sales' => 'required|string|max:10',
            'nama_sales' => 'required|string|max:50',
            'hari_kunjungan' => 'required|string|max:20',
        ]);

        $pelanggan = Pelanggan::where('Kode_pelanggan', $Kode_pelanggan)->firstOrFail();
        $pelanggan->update([
            'Nama_pelanggan' => $request->Nama_pelanggan,
            'alamat' => $request->alamat,
            'telepon' => $request->telepon,
            'top' => $request->top,
            'kredit_limit' => $request->kredit_limit,
            'kode_sales' => $request->kode_sales,
            'nama_sales' => $request->nama_sales,
            'hari_kunjungan' => $request->hari_kunjungan,
        ]);

        return redirect()->route('pelanggan.index')->with('success', 'Pelanggan berhasil diperbarui!');
    }


public function destroy($Kode_pelanggan)
{
    $pelanggan = Pelanggan::where('Kode_pelanggan', $Kode_pelanggan)->firstOrFail();
    $pelanggan->delete();

    return redirect()->route('pelanggan.index')->with('success', 'Pelanggan berhasil dihapus!');
}

public function store(Request $request)
    {
        $request->validate([
            'Kode_pelanggan' => 'required|string|max:10|unique:pelanggan,Kode_pelanggan',
            'Nama_pelanggan' => 'required|string|max:100',
            'alamat' => 'required|string|max:255',
            'telepon' => 'required|string|max:15',
            'top' => 'required|numeric',
            'kredit_limit' => 'required|numeric',
            'kode_sales' => 'required|string|max:10',
            'nama_sales' => 'required|string|max:50',
            'hari_kunjungan' => 'required|string|max:20',
        ]);

        Pelanggan::create($request->all());

        return redirect()->route('pelanggan.index')->with('success', 'Pelanggan berhasil ditambahkan!');
    }

    public function search(Request $request)
{
    $search = $request->q;

    $pelanggan = Pelanggan::where('Kode_pelanggan', 'LIKE', "%{$search}%")
        ->orWhere('Nama_pelanggan', 'LIKE', "%{$search}%")
        ->limit(10)
        ->get();

    return response()->json($pelanggan);
}

public function import(Request $request)
{
    // Validasi file yang diupload
    $request->validate([
        'csv_file' => 'required|mimes:csv,txt'
    ]);

    $file = $request->file('csv_file');

    // Buka file CSV untuk dibaca
    if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
        $header = null;
        $rowNumber = 0;
        $dataInserted = 0;

        while (($row = fgetcsv($handle, 1000, ';')) !== false) {
            $rowNumber++;

            // Ambil header dari baris pertama
            if (!$header) {
                $header = $row;
                continue;
            }

            // Pastikan jumlah kolom pada baris data sama dengan header
            if(count($header) !== count($row)) {
                \Log::warning("Baris ke-$rowNumber tidak memiliki jumlah kolom yang sesuai. Dilewati.");
                continue;
            }

            // Gabungkan header dan row untuk membentuk array asosiatif
            $data = array_combine($header, $row);

            // Contoh validasi per baris (bisa dikembangkan sesuai kebutuhan)
            $validator = Validator::make($data, [
                'Kode_pelanggan'  => 'required|unique:pelanggan,Kode_pelanggan',
                'Nama_pelanggan'  => 'required',
                'alamat'          => 'required',
                'telepon'         => 'required',
                'top'             => 'required',
                'kredit_limit'    => 'required|numeric',
                'kode_sales'      => 'required',
                'nama_sales'      => 'required',
                'hari_kunjungan'  => 'required',
            ]);

            if ($validator->fails()) {
                // Lewati baris ini jika validasi gagal
                continue;
            }

            // Simpan data ke database
            Pelanggan::create([
                'Kode_pelanggan' => $data['Kode_pelanggan'],
                'Nama_pelanggan' => $data['Nama_pelanggan'],
                'alamat'         => $data['alamat'],
                'telepon'        => $data['telepon'],
                'top'            => $data['top'],
                'kredit_limit'   => $data['kredit_limit'],
                'kode_sales'     => $data['kode_sales'],
                'nama_sales'     => $data['nama_sales'],
                'hari_kunjungan' => $data['hari_kunjungan'],
            ]);

            $dataInserted++;
        }
        fclose($handle);

        return redirect()->route('pelanggan.index')
            ->with('success', "Import CSV berhasil. Data yang diimport: {$dataInserted} baris.");
    }

    return redirect()->route('pelanggan.index')
        ->with('error', 'Gagal membuka file CSV.');
}

}

