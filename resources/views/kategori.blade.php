@extends('layout.mainlayout')
@section('title', 'Kategori')

@section('content')
    <h1>Ini Adalah Halaman Kategori</h1>
    <h3>Kategori List</h3>

    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    <!-- Tombol Tambah Barang -->
    <button type="button" onclick="document.getElementById('modalAdd').style.display='block'" class="btn btn-success">
        Add Barang
    </button>

    <!-- Modal Form -->
    <div id="modalAdd" style="display: none;">
        <form action="{{ route('kategori.store') }}" method="POST">
            @csrf
            <div class="row mb-3">
                <label for="kode_kategori" class="col-sm-2 col-form-label col-form-label-sm">Kode Kategori:</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" placeholder="Kode Kategori" id="kode_kategori" name="kode_kategori" required>
                </div>
            </div>
            <div class="row mb-3">
                <label for="nama_kategori" class="col-sm-2 col-form-label col-form-label-sm">Nama Kategori:</label>
                <div class="col-sm-10"> 
                    <input type="text" class="form-control" placeholder="Nama Barang" id="nama_kategori" name="nama_kategori" required>
                </div>
            </div>
           
            <button type="submit" class="btn btn-primary">Simpan</button>
            <button type="button" onclick="document.getElementById('modalAdd').style.display='none'" class="btn btn-primary">Batal</button>
        </form>
    </div>

    
<div class="d-flex justify-content-start">
    <table class="table table-dark table-striped-columns" style="margin-top:20px;">
        <thead>
            <tr>
                <th>No.</th>
                <th>Kode Kategori</th>
                <th>Nama Kategori</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($kategoriList as $data)
            <tr>
                <td>{{$loop->iteration}}</td>
                <td>{{$data->kode_kategori}}</td>
                <td>{{$data->nama_kategori}}</td>
                <td>
                    <!-- Tombol Edit -->
                    <a href="{{ route('kategori.edit', $data->kode_kategori) }}" class="btn btn-warning">Edit</a>

                    <!-- Tombol Delete -->
                    <form action="{{ route('kategori.destroy', $data->kode_kategori) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus barang ini?')">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>


@endsection
