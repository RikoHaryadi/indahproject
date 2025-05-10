@extends('layout.mainlayout')
@section('title', 'Edit Supplier')

@section('content')
    <h1>Edit Supplier</h1>

    <form action="{{ route('supplier.update', $supplier->Kode_suplier) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row mb-3">
        <label for="Nama_suplier" class="col-sm-2 col-form-label col-form-label-sm">Nama:</label>
        <div class="col-sm-10">
            <input type="text" id="Nama_suplier" class="form-control" name="Nama_suplier" value="{{ $supplier->Nama_suplier }}" required>
        </div>
    </div>
    <div class="row mb-3">
        <label for="alamat" class="col-sm-2 col-form-label col-form-label-sm">Alamat:</label>
        <div class="col-sm-10">
            <input type="text" id="alamat" class="form-control" name="alamat" value="{{ $supplier->alamat }}" required>
        </div>
    </div>
    <div class="row mb-3">
        <label for="telepon" class="col-sm-2 col-form-label col-form-label-sm">Telepon:</label>
        <div class="col-sm-10">
            <input type="text" id="telepon" class="form-control" name="telepon" value="{{ $supplier->telepon }}" required>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="{{ route('suplier.index') }}" class="btn btn-secondary">Batal</a>
</form>

@endsection
