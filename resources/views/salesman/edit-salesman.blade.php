@extends('layout.mainlayout')
@section('title', 'Edit Custumer')

@section('content')
    <h1>Edit Custumer</h1>

    <form action="{{ route('salesman.update', $salesman->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row mb-3">
        <label for="nama_salesman" class="col-sm-2 col-form-label col-form-label-sm">Nama:</label>
        <div class="col-sm-10">
            <input type="text" id="nama_salesman" class="form-control" name="nama_salesman" value="{{ $salesman->nama_salesman }}" required>
        </div>
    </div>
    <div class="row mb-3">
        <label for="alamat" class="col-sm-2 col-form-label col-form-label-sm">Alamat:</label>
        <div class="col-sm-10">
            <input type="text" id="alamat" class="form-control" name="alamat" value="{{ $salesman->alamat }}" required>
        </div>
    </div>
    <div class="row mb-3">
        <label for="telepon" class="col-sm-2 col-form-label col-form-label-sm">Telepon:</label>
        <div class="col-sm-10">
            <input type="text" id="telepon" class="form-control" name="telepon" value="{{ $salesman->telepon }}" required>
        </div>
    </div>
    <div class="row mb-3">
        <label for="typesalesman" class="col-sm-2 col-form-label col-form-label-sm">typesalesman:</label>
        <div class="col-sm-10">
            <input type="text" id="typesalesman" class="form-control" name="typesalesman" value="{{ $salesman->typesalesman }}" required>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="{{ route('salesman.index') }}" class="btn btn-secondary">Batal</a>
</form>

@endsection
