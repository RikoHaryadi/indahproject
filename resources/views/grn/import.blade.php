@extends('layout.mainlayout')
@section('title', 'Import GRN')
@section('content')
<div class="container">
    <h4>Import Barang Masuk (GRN) dari CSV</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @elseif(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('grn.import') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-group mb-3">
            <label for="csv_file">Pilih File CSV:</label>
            <input type="file" name="file" required>
        </div>

        <button type="submit">Import</button>
       <a href="{{ route('grn.template') }}" class="btn btn-success">Download Template CSV</a>
    </form>
</div>
@endsection
