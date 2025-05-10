@extends('layout.mainlayout')
@section('title', 'Edit Barang')

@section('content')
    <h1>Edit Barang</h1>

    <form action="{{ route('barang.update', $barang->kode_barang) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row mb-3">
            <label for="nama_barang" class="col-sm-2 col-form-label col-form-label-sm">Nama Barang:</label>
            <div class="col-sm-10">
            <input type="text" id="nama_barang" class="form-control" name="nama_barang" value="{{ $barang->nama_barang }}" required>
            </div>
        </div>
        <div class="row mb-3">
            <label for="harga" class="col-sm-2 col-form-label col-form-label-sm">Harga:</label>
            <div class="col-sm-10">
            <input type="number" id="harga" class="form-control" name="harga" value="{{ $barang->harga }}" required oninput="calculateChange()">
            </div>
        </div>
        <div class="row mb-3">
            <label for="stok" class="col-sm-2 col-form-label col-form-label-sm">Stok:</label>
            <div class="col-sm-10">
            <input type="number"  class="form-control" id="stok" name="stok" value="{{ $barang->stok }}" required oninput="calculateChange()">
            </div>
            <div class="row mb-3">
                <label for="nilairp" class="col-sm-2 col-form-label col-form-label-sm">Nilai Rp:</label>
                <div class="col-sm-10"> 
                    <input type="number" class="form-control" placeholder="0" id="nilairp" name="nilairp" readonly>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('barang.index') }}" class="btn btn-primary">Batal</a>
    </form>
    <script>
    function calculateChange() {
        const harga = parseFloat(document.getElementById('harga').value || 0);
        const stok = parseFloat(document.getElementById('stok').value || 0);
        const nilairp = harga * stok;
        document.getElementById('nilairp').value = nilairp;
    }
</script>
@endsection
