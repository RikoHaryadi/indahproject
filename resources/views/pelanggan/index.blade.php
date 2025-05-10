@extends('layout.mainlayout')
@section('title', 'Master Pelanggan')

@section('content')
    <h1>Master Pelanggan</h1>

    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    <!-- Tombol Add Pelanggan -->
    <button onclick="document.getElementById('addPelangganForm').style.display='block'" class="btn btn-success">Add Pelanggan</button>

    <!-- Tombol Import CSV -->
    <button onclick="document.getElementById('importCSVForm').style.display='block'" class="btn btn-primary">Import CSV</button>

    <!-- Form Import CSV -->
    <div id="importCSVForm" style="display: none; margin-top: 20px;">
        <form action="{{ route('pelanggan.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="csv_file" class="form-label">Pilih file CSV</label>
                <input type="file" name="csv_file" id="csv_file" accept=".csv" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Import</button>
            <button type="button" onclick="document.getElementById('importCSVForm').style.display='none'" class="btn btn-secondary">Batal</button>
        </form>
    </div>

    <!-- Form Add Pelanggan -->
    <div id="addPelangganForm" style="display: none; margin-top: 20px;">
        <form action="{{ route('pelanggan.store') }}" method="POST">
            @csrf
            <div class="row mb-3">
                <label for="Kode_pelanggan" class="col-sm-2 col-form-label col-form-label-sm">Kode Pelanggan:</label>
                <div class="col-sm-3">
                    <input type="text" class="form-control" id="Kode_pelanggan" name="Kode_pelanggan" required>
                </div>
                <label for="Nama_pelanggan" class="col-sm-1 col-form-label col-form-label-sm">Nama:</label>
                <div class="col-sm-5">
                    <input type="text" class="form-control" id="Nama_pelanggan" name="Nama_pelanggan" required>
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
                <div class="col-sm-2">
                    <input type="text" class="form-control" id="telepon" name="telepon" required>
                </div>
                <label for="top" class="col-sm-1 col-form-label col-form-label-sm">TOP:</label>
                <div class="col-sm-2">
                    <input type="text" class="form-control" id="top" name="top" required>
                </div>
                <label for="kredit_limit" class="col-sm-2 col-form-label col-form-label-sm">Kredit Limit:</label>
                <div class="col-sm-3">
                    <input type="text" class="form-control" id="kredit_limit" name="kredit_limit" required>
                </div>
            </div>
            <div class="row mb-3">
                <label for="kode_sales" class="col-sm-2 col-form-label col-form-label-sm">Kode Sales:</label>
                <div class="col-sm-3">
                    <select id="kode_sales" class="kode_sales form-control" name="kode_sales" required onchange="updatePelangganDetails(this)">
                        <option value="" disabled selected>Pilih Sales</option>
                        @foreach($salesmanList as $salesman)
                            <option value="{{ $salesman->kode_sales }}"
                                data-nama="{{ $salesman->nama_salesman }}"
                                data-alamat="{{ $salesman->alamat }}"
                                data-telepon="{{ $salesman->telepon }}">
                                {{ $salesman->kode_sales }}-{{ $salesman->nama_salesman }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <label for="nama_sales" class="col-sm-1 col-form-label col-form-label-sm">Nama Salesman:</label>
                <div class="col-sm-2">
                    <input type="text" class="form-control" id="nama_sales" name="nama_sales" required>
                </div>
                <label for="hari_kunjungan" class="col-sm-2 col-form-label col-form-label-sm">Hari Kunjungan:</label>
                <div class="col-sm-2">
                    <input type="text" class="form-control" id="hari_kunjungan" name="hari_kunjungan" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
            <button type="button" onclick="document.getElementById('addPelangganForm').style.display='none'" class="btn btn-secondary">Batal</button>
        </form>
    </div>

    <!-- Tabel Data Pelanggan -->
    <table class="table table-success table-striped" style="margin-top:20px; font-size: 12px;">
        <thead>
        <tr style="font-size: 10px;">
                <th>Kode Pelanggan</th>
                <th>Nama</th>
                <th>Alamat</th>
                <th>Telepon</th>
                <th>TOP</th>
                <th>Kredit Limit</th>
                <th>Salesman</th>
                <th>Hari Kunjungan</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pelangganList as $data)
            <tr style="font-size: 10px;">
                    <td>{{ $data->Kode_pelanggan }}</td>
                    <td>{{ $data->Nama_pelanggan }}</td>
                    <td>{{ $data->alamat }}</td>
                    <td>{{ $data->telepon }}</td>
                    <td>{{ $data->top }}</td>
                    <td>{{ $data->kredit_limit }}</td>
                    <td>{{ $data->kode_sales }}-{{ $data->nama_sales }}</td>
                    <td>{{ $data->hari_kunjungan }}</td>
                    <td>
                        <!-- Tombol Edit -->
                        <a href="{{ route('pelanggan.edit', $data->Kode_pelanggan) }}" class="btn btn-warning">Edit</a>

                        <!-- Tombol Delete -->
                        <form action="{{ route('pelanggan.destroy', $data->Kode_pelanggan) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus Pelanggan ini?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <script>
        function updatePelangganDetails() {
            const selectedOption = document.querySelector('#kode_sales option:checked');
            document.getElementById('nama_sales').value = selectedOption.dataset.nama || '';
        }
    </script>
@endsection
