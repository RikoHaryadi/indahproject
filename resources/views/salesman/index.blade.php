@extends('layout.mainlayout')
@section('title', 'Master Pelanggan')

@section('content')
    <h1>Master Pelanggan</h1>

    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    <!-- Tombol Add Pelanggan -->
    <button onclick="document.getElementById('addPelangganForm').style.display='block'" class="btn btn-success">Add Pelanggan</button>

    <!-- Form Add Pelanggan -->
    <div id="addPelangganForm" style="display: none; margin-top: 20px;">
        <form action="{{ route('salesman.store') }}" method="POST">
            @csrf
            <div class="row mb-3">
                <label for="kode_sales" class="col-sm-2 col-form-label col-form-label-sm">Kode Salesman:</label>
                <div class="col-sm-10">
                <input type="text" class="form-control" id="kode_sales" name="kode_sales" required>
                </div>
            </div>
            <div class="row mb-3">
                <label for="nama_salesman" class="col-sm-2 col-form-label col-form-label-sm">Nama Salesman:</label>
                <div class="col-sm-10">
                <input type="text" class="form-control" id="nama_salesman" name="nama_salesman" required>
                </div>
            </div>
            <div class="row mb-3">
                <label for="alamat" class="col-sm-2 col-form-label col-form-label-sm">Alamat:</label>
                <div class="col-sm-10">
                <input type="text" class="form-control" id="alamat" name="alamat" required>
                </div>
            </div>
            <div class="row mb-3">
                <label for="telepon" class="col-sm-2 col-form-label col-form-label-sm">Telepon:</label>
                <div class="col-sm-10">
                <input type="text" class="form-control" id="telepon" name="telepon" required>
                </div>
            </div>
            <div class="row mb-3">
                <label for="typesalesman" class="col-sm-2 col-form-label col-form-label-sm">Type salesman:</label>
                <div class="col-sm-10">
                <input type="text" class="form-control" id="typesalesman" name="typesalesman" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
            <button type="button" onclick="document.getElementById('addPelangganForm').style.display='none'" class="btn btn-primary">Batal</button>
            @if($errors->any())
            <ul class="text-danger">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            @endif   
        </form>
    </div>

    <!-- Tabel Data Pelanggan -->
    <table class="table table-success table-striped" style="margin-top:20px;">
        <thead>
            <tr>
                <th>Kode Salesman</th>
                <th>Nama Salesman</th>
                <th>Alamat</th>
                <th>Telepon</th>
                <th>Type Salesman</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($salesmanList as $data)
                <tr>
                    <td>{{ $data->kode_sales }}</td>
                    <td>{{ $data->nama_salesman }}</td>
                    <td>{{ $data->alamat }}</td>
                    <td>{{ $data->telepon }}</td>
                    <td>{{ $data->typesalesman }}</td>
                    <td>
                        <!-- Tombol Edit -->
                        <a href="{{ route('salesman.edit', $data->kode_sales) }}" class="btn btn-warning">Edit</a>

                        <!-- Tombol Delete -->
                        <form action="{{ route('salesman.destroy', $data->kode_sales) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus Pelanggan ini?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
