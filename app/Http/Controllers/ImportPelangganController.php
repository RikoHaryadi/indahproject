<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\PelangganImport;
use Maatwebsite\Excel\Facades\Excel;

class ImportPelangganController extends Controller
{
    public function showImportForm()
    {
        return view('pelanggan.import'); // Buat tampilan upload file
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        Excel::import(new PelangganImport, $request->file('file'));

        return redirect()->route('pelanggan.index')->with('success', 'Data pelanggan berhasil diimpor!');
    }
}
