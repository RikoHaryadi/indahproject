@extends('layout.mainlayout')
@section('title', 'Master Barang')

@section('content')
    <h5>Master Barang</h5>
   

    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    <!-- Tombol Tambah Barang -->
    <button type="button" onclick="document.getElementById('modalAdd').style.display='block'" class="btn btn-success">
        Add Barang
    </button>
    <!-- Tombol Import CSV -->
<button type="button" onclick="document.getElementById('modalImport').style.display='block'" class="btn btn-info">
    Import CSV
</button>

<!-- Modal Import CSV -->
<div id="modalImport" style="display: none; margin-top: 20px;">
    <form action="{{ route('masterbarang.import') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row mb-3">
            <label for="csv_file" class="col-sm-2 col-form-label">Pilih File CSV:</label>
            <div class="col-sm-10">
                <input type="file" name="csv_file" id="csv_file" accept=".csv" class="form-control" required>
            </div>
        </div>
        <div class="row mb-1">
            
            <div class="col-sm-10">
                <p style="color: green;">Header Template Csv = kode_barang;nama_barang;hargapcs;hargapcsjual;isidus;kategori</p>
            </div>
        </div>
        <button type="submit" class="btn btn-success">Import</button>
        <button type="button" onclick="document.getElementById('modalImport').style.display='none'" class="btn btn-secondary">Batal</button>
    </form>
</div>


    <!-- Modal Form -->
    <div id="modalAdd" style="display: none;">
        <form action="{{ route('masterbarang.store') }}" method="POST">
            @csrf
            <div class="row mb-3">
                <label for="kode_barang" class="col-sm-2 col-form-label col-form-label-sm">Kode Barang:</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" placeholder="Kode Barang" id="kode_barang" name="kode_barang" required>
                </div>
            </div>
            <div class="row mb-3">
                <label for="nama_barang" class="col-sm-2 col-form-label col-form-label-sm">Nama Barang:</label>
                <div class="col-sm-10"> 
                    <input type="text" class="form-control" placeholder="Nama Barang" id="nama_barang" name="nama_barang" required>
                </div>
            </div>
            <div class="row mb-3">
                <label for="hargapcs" class="col-sm-2 col-form-label col-form-label-sm">Harga Beli /Pcs:</label>
                <div class="col-sm-10"> 
                    <input type="number" class="form-control" placeholder="Harga Beli" id="hargapcs" name="hargapcs" required>
                </div>
            </div>
            <div class="row mb-3">
                <label for="hargapcsjual" class="col-sm-2 col-form-label col-form-label-sm">Harga Jual /pcs:</label>
                <div class="col-sm-10"> 
                    <input type="number" class="form-control" placeholder="harga Jual" id="hargapcsjual" name="hargapcsjual" required>
                </div>
            </div>
            <div class="row mb-3">
                <label for="isidus" class="col-sm-2 col-form-label col-form-label-sm">Isi / Dus:</label>
                <div class="col-sm-10"> 
                    <input type="number" class="form-control" placeholder="0" id="isidus" name="isidus" required>
                </div>
            </div>
            <div class="row mb-3">
                <label for="kategori" class="form-label">Kategori</label>
                <div class="col-sm-10"> 
                    <select name="kategori" id="kategori" class="form-control" required>
                        <option value="" disabled selected>Pilih Kategori</option>
                        @foreach ($kategoriList as $kategori)
                            <option value="{{ $kategori->nama_kategori }}">{{ $kategori->nama_kategori }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
            <button type="button" onclick="document.getElementById('modalAdd').style.display='none'" class="btn btn-primary">Batal</button>
        </form>
    </div>

    
<div class="d-flex justify-content-start">
   <table class="table table-bordered table-striped">
        <thead class="table-dark" style="font-size: 12px;">
            <tr style="font-size: 10px;">
                <th style="font-size: 10px;">No.</th>
                <th style="font-size: 10px;">Kode Barang</th>
                <th style="font-size: 10px;">Nama Barang</th>
                <th style="font-size: 10px;">Harga beli/Pcs</th>
                <th style="font-size: 10px;">Harga jual/Pcs</th>
                <th style="font-size: 10px;">isi/Dus</th>
                <th style="font-size: 10px;">Kategori</th>
                <th style="font-size: 10px;">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($masterbarangList as $data)
            <tr>
                <td style="font-size: 10px;">{{$loop->iteration}}</td>
                <td style="font-size: 10px;">{{$data->kode_barang}}</td>
                <td style="font-size: 10px;">{{$data->nama_barang}}</td>
                <td style="font-size: 10px;">{{$data->hargapcs}}</td>
                <td style="font-size: 10px;">{{$data->hargapcsjual}}</td>
                <td style="font-size: 10px;">{{$data->isidus}}</td>
                <td style="font-size: 10px;">{{$data->kategori}}</td>
                <td style="font-size: 10px;">
                    <!-- Tombol Edit -->
                    <a href="{{ route('masterbarang.edit', $data->kode_barang) }}" class="btn btn-warning" style="font-size: 10px;">Edit</a>

                    <!-- Tombol Delete -->
                    <form action="{{ route('masterbarang.destroy', $data->kode_barang) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" style="font-size: 10px;" onclick="return confirm('Apakah Anda yakin ingin menghapus barang ini?')">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<style>
    .btn {
        style="font-size: 10px;"
    }
</style>

@endsection
