@extends('layout.mainlayout')
@section('title', 'Master Supplier')

@section('content')
    <h1>Master Supplier</h1>

    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    <!-- Tombol Add Supplier -->
    <button onclick="document.getElementById('addSupplierForm').style.display='block'" class="btn btn-success">Add Supplier</button>

    <!-- Form Add Supplier -->
    <div id="addSupplierForm" style="display: none; margin-top: 20px;">
        <form action="{{ route('supplier.store') }}" method="POST">
            @csrf
            <div class="row mb-3">
                <label for="nama" class="col-sm-2 col-form-label col-form-label-sm">Kode Supplier:</label>
                <div class="col-sm-10">
                <input type="text" class="form-control" id="Kode_Supplier" name="Kode_suplier" required>
                </div>
            </div>
            <div class="row mb-3">
                <label for="nama" class="col-sm-2 col-form-label col-form-label-sm">Nama:</label>
                <div class="col-sm-10">
                <input type="text" class="form-control" id="Nama_Supplier" name="Nama_suplier" required>
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
            <button type="submit" class="btn btn-primary">Simpan</button>
            <button type="button" onclick="document.getElementById('addSupplierForm').style.display='none'" class="btn btn-primary">Batal</button>
        </form>
    </div>

    <!-- Tabel Data Supplier -->
    <table class="table table-success table-striped" style="margin-top:20px;">
        <thead>
            <tr>
                <th>Kode Supplier</th>
                <th>Nama</th>
                <th>Alamat</th>
                <th>Telepon</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($supplierList as $data)
                <tr>
                    <td>{{ $data->Kode_suplier }}</td>
                    <td>{{ $data->Nama_suplier }}</td>
                    <td>{{ $data->alamat }}</td>
                    <td>{{ $data->telepon }}</td>
                    <td>
                        <!-- Tombol Edit -->
                        <a href="{{ route('supplier.edit', $data->Kode_suplier) }}" class="btn btn-warning">Edit</a>

                        <!-- Tombol Delete -->
                        <form action="{{ route('supplier.destroy', $data->Kode_suplier) }}" method="POST" style="display:inline;">
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
