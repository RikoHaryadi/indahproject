@extends('layout.mainlayout')
@section('title', 'Edit Barang')

@section('content')
    <h1>Edit Kategori</h1>

    <form action="{{ route('kategori.update', $kategori->kode_kategori) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row mb-3">
            <label for="nama_kategori" class="col-sm-2 col-form-label col-form-label-sm">Nama Kategori:</label>
            <div class="col-sm-10">
            <input type="text" id="nama_kategori" class="form-control" name="nama_kategori" value="{{ $kategori->nama_kategori }}" required>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('kategori.index') }}" class="btn btn-primary">Batal</a>
    </form>
    
@endsection
