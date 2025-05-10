@extends('layout.mainlayout')
@section('title', 'Kode Akun')

@section('content')
    <h1>Buat Kode Akun</h1>

    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    <!-- Tombol Add Supplier -->
    <button onclick="document.getElementById('addSupplierForm').style.display='block'" class="btn btn-success">Add Kode Akun</button>

    <!-- Form Add Supplier -->
    <div id="addSupplierForm" style="display: none; margin-top: 20px;">
        <form action="{{ route('kodeakun.store') }}" method="POST">
            @csrf
            <div class="row mb-3">
                <label for="kode-akun" class="col-sm-2 col-form-label col-form-label-sm">Kode Akun:</label>
                <div class="col-sm-10">
                <input type="text" class="form-control" id="kode_akun" name="kode_akun" required>
                </div>
            </div>
            <div class="row mb-3">
                <label for="nama" class="col-sm-2 col-form-label col-form-label-sm">Nama Akun:</label>
                <div class="col-sm-10">
                <input type="text" class="form-control" id="nama_akun" name="nama_akun" required>
                </div>
            </div>
            <div class="row mb-3">
                <label for="alamat" class="col-sm-2 col-form-label col-form-label-sm">Kelempok Akun:</label>
                <div class="col-sm-10">
                <input type="text" class="form-control" id="kelompok_akun" name="kelompok_akun" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
            <button type="button" onclick="document.getElementById('addSupplierForm').style.display='none'" class="btn btn-primary">Batal</button>
        </form>
    </div>

    <!-- Tabel Data Supplier -->
    <table class="table table-success table-striped" style="margin-top:20px;">
        <thead>
            <tr>
                <th>Kode Akun</th>
                <th>Nama Akun</th>
                <th>Kelompok Akun</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($kodeakunList as $data)
                <tr>
                    <td>{{ $data->kode_akun }}</td>
                    <td>{{ $data->nama_akun }}</td>
                    <td>{{ $data->kelompok_akun }}</td>
                    <td>
                       
                        <!-- Tombol Delete -->
                        <form action="{{ route('kodeakun.destroy', $data->kode_akun) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus Supplier ini?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
