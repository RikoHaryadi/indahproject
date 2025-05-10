<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Salesman;

class SalesmanController extends Controller
{
    public function index()
    {
        $salesmanList = Salesman::all();
        return view('salesman.index', ['salesmanList' => $salesmanList]);
    }

    public function edit($kode_sales)
    {
        $salesman = Salesman::where('kode_sales', $kode_sales)->firstOrFail();
        return view('salesman.edit-salesman', ['salesman' => $salesman]);
    }

    public function update(Request $request, $kode_sales)
    {
        $request->validate([
            'nama_salesman' => 'required|string|max:100',
            'alamat' => 'required|string|max:255',
            'telepon' => 'required|string|max:15',
            'typesalesman' => 'required|string',
        ]);

        $salesman = Salesman::where('kode_sales', $kode_sales)->firstOrFail();
        $salesman->update([
            'nama_salesman' => $request->nama_salesman,
            'alamat' => $request->alamat,
            'telepon' => $request->telepon,
            'typesalesman' => $request->typesalesman,
        ]);

        return redirect()->route('salesman.index')->with('success', 'Pelanggan berhasil diperbarui!');
    }


public function destroy($kode_sales)
{
    $salesman = Salesman::where('kode_sales', $kode_sales)->firstOrFail();
    $salesman->delete();

    return redirect()->route('salesman.index')->with('success', 'Pelanggan berhasil dihapus!');
}

public function store(Request $request)
    {
        $request->validate([
            'kode_sales' => 'required|string|max:10',
            'nama_salesman' => 'required|string|max:50',
            'alamat' => 'required|string|max:100',
            'telepon' => 'required|string|max:15',
            'typesalesman' => 'required|string|max:15',
        ]);

        Salesman::create($request->all());

        return redirect()->route('salesman.index')->with('success', 'Pelanggan berhasil ditambahkan!');
        
        // \Log::info($request->all()); // Tambahkan ini untuk debugging
        // $request->validate([...]);
        // ...

    }
}
