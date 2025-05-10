@extends('layout.mainlayout')
@section('title', 'Edit Custumer')

@section('content')
    <h1>Edit Custumer</h1>

    <form action="{{ route('pelanggan.update', $pelanggan->Kode_pelanggan) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row mb-3">
        <label for="Nama_pelanggan" class="col-sm-2 col-form-label col-form-label-sm">Nama:</label>
        <div class="col-sm-10">
            <input type="text" id="Nama_pelanggan" class="form-control" name="Nama_pelanggan" value="{{ $pelanggan->Nama_pelanggan }}" required>
        </div>
    </div>
    <div class="row mb-3">
        <label for="alamat" class="col-sm-2 col-form-label col-form-label-sm">Alamat:</label>
        <div class="col-sm-10">
            <input type="text" id="alamat" class="form-control" name="alamat" value="{{ $pelanggan->alamat }}" required>
        </div>
    </div>
    <div class="row mb-3">
        <label for="telepon" class="col-sm-2 col-form-label col-form-label-sm">Telepon:</label>
        <div class="col-sm-10">
            <input type="text" id="telepon" class="form-control" name="telepon" value="{{ $pelanggan->telepon }}" required>
        </div>
    </div>
    <div class="row mb-3">
        <label for="top" class="col-sm-2 col-form-label col-form-label-sm">TOP:</label>
        <div class="col-sm-10">
            <input type="text" id="top" class="form-control" name="top" value="{{ $pelanggan->top }}" required>
        </div>
    </div>
    <div class="row mb-3">
        <label for="kredit_limit" class="col-sm-2 col-form-label col-form-label-sm">Kredit Limit:</label>
        <div class="col-sm-10">
            <input type="text" id="kredit_limit" class="form-control" name="kredit_limit" value="{{ $pelanggan->kredit_limit }}" required>
        </div>
    </div>
    <div class="row mb-3">
            <label for="kode_sales" class="col-sm-2 col-form-label col-form-label-sm">Kode Sales:</label>
                <div class="col-sm-3">
                <select id="kode_sales" class="kode_sales form-control" name="kode_sales" required onchange="updatePelangganDetails(this)">
                    <option value="" disabled {{ !$pelanggan->kode_sales ? 'selected' : '' }}Ubah Sales>
                    @foreach($salesmanList as $salesman)
                        <option value="{{ $salesman->kode_sales }}"
                            data-nama="{{ $salesman->nama_salesman }}"
                            data-alamat="{{ $salesman->alamat }}"
                            data-telepon="{{ $salesman->telepon }}"
                            {{ $pelanggan->kode_sales == $salesman->kode_sales ? 'selected' : '' }}>
                            {{ $salesman->kode_sales }} - {{ $salesman->nama_salesman }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="row mb-3">
                <label for="nama" class="col-sm-2 col-form-label col-form-label-sm">Nama Salesman:</label>
                <div class="col-sm-10">
                <input type="text" class="form-control" id="nama_sales" name="nama_sales" value="{{ $pelanggan->nama_sales }}" required>
                </div>
            </div>
            <div class="row mb-3">
                <label for="hari_kunjungan" class="col-sm-2 col-form-label col-form-label-sm">Hari Kunjungan:</label>
                <div class="col-sm-10">
                <input type="text" class="form-control" id="hari_kunjungan" name="hari_kunjungan" value="{{ $pelanggan->hari_kunjungan }}" required>
                </div>
            </div>
    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="{{ route('pelanggan.index') }}" class="btn btn-secondary">Batal</a>
</form>

<script>

function updatePelangganDetails() {
    const selectedOption = document.querySelector('#kode_sales option:checked');
    document.getElementById('nama_sales').value = selectedOption.dataset.nama || '';

 }
</script>
@endsection
