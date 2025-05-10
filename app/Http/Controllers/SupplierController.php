<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supplier;

class SupplierController extends Controller
{
    public function index()
    {
        $supplierList = Supplier::all();
        return view('suplier.index', ['supplierList' => $supplierList]);
    }

    public function edit($Kode_suplier)
    {
        $supplier = Supplier::where('Kode_suplier', $Kode_suplier)->firstOrFail();
        return view('suplier.edit-supplier', ['supplier' => $supplier]);
    }

    public function update(Request $request, $Kode_suplier)
    {
        $request->validate([
            'Nama_suplier' => 'required|string|max:100',
            'alamat' => 'required|string|max:255',
            'telepon' => 'required|string|max:15',
        ]);

        $supplier = Supplier::where('Kode_suplier', $Kode_suplier)->firstOrFail();
        $supplier->update([
            'Nama_suplier' => $request->Nama_suplier,
            'alamat' => $request->alamat,
            'telepon' => $request->telepon,
        ]);

        return redirect()->route('suplier.index')->with('success', 'Supplier berhasil diperbarui!');
    }


public function destroy($Kode_suplier)
{
    $supplier = Supplier::where('Kode_suplier', $Kode_suplier)->firstOrFail();
    $supplier->delete();

    return redirect()->route('suplier.index')->with('success', 'Supplier berhasil dihapus!');
}

public function store(Request $request)
    {
        $request->validate([
            'Kode_suplier' => 'required|string|max:10|unique:Supplier,Kode_suplier',
            'Nama_suplier' => 'required|string|max:100',
            'alamat' => 'required|string|max:255',
            'telepon' => 'required|string|max:15',
        ]);

        Supplier::create($request->all());

        return redirect()->route('suplier.index')->with('success', 'Supplier berhasil ditambahkan!');
    }
}
