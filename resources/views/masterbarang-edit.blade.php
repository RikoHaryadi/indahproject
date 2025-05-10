@extends('layout.mainlayout')
@section('title', 'Edit Barang')

@section('content')
    <h1>Edit Barang</h1>

    <form action="{{ route('masterbarang.update', $masterbarang->kode_barang) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row mb-3">
            <label for="nama_barang" class="col-sm-2 col-form-label col-form-label-sm">Nama Barang:</label>
            <div class="col-sm-10">
            <input type="text" id="nama_barang" class="form-control" name="nama_barang" value="{{ $masterbarang->nama_barang }}" required>
            </div>
        </div>
        <div class="row mb-3">
            <label for="hargapcs" class="col-sm-2 col-form-label col-form-label-sm">Harga Beli:</label>
            <div class="col-sm-10">
            <input type="number" id="hargapcs" class="form-control" name="hargapcs" value="{{ $masterbarang->hargapcs }}" required>
            </div>
        </div>
        <div class="row mb-3">
            <label for="hargapcsjual" class="col-sm-2 col-form-label col-form-label-sm">Harga Jual:</label>
            <div class="col-sm-10">
            <input type="number"  class="form-control" id="hargapcsjual" name="hargapcsjual" value="{{ $masterbarang->hargapcsjual }}" required>
            </div>
        </div>
            <div class="row mb-3">
                <label for="isidus" class="col-sm-2 col-form-label col-form-label-sm">ISI DUS:</label>
                <div class="col-sm-10"> 
                    <input type="number" class="form-control" placeholder="0" id="isidus" name="isidus" value="{{ $masterbarang->isidus }}" required>
                </div>
            </div>
            <div class="row mb-3">
                <label for="kategori" class="form-label">Kategori</label>
                <div class="col-sm-10"> 
                    <select name="kategori" id="kategori" class="form-control" required>
                        <option value="" disabled>Pilih Kategori</option>
                        @foreach ($kategoriList as $kategori)
                            <option value="{{ $kategori->nama_kategori }}" 
                                    {{ $masterbarang->kategori == $kategori->nama_kategori ? 'selected' : '' }}>
                                {{ $kategori->nama_kategori }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('barang.index') }}" class="btn btn-primary">Batal</a>
    </form>
    
@endsection
