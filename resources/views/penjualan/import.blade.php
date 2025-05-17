@extends('layout.mainlayout')
@section('title','Import Penjualan CSV')
@section('content')
<div class="container mt-4">
  <h1>Import Data Penjualan dari CSV</h1>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <form action="{{ route('penjualan.import') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="mb-3">
      <label for="csv_file" class="form-label">Pilih file CSV</label>
      <input type="file" name="csv_file" id="csv_file"  class="form-control @error('csv_file') is-invalid @enderror" required>
        @error('csv_file')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
    <button type="submit" class="btn btn-primary">Upload & Import</button>
  </form>

  <hr>
  <h5>Format Kolom CSV yang Diperlukan:</h5>
  <pre>
Salesman,SalesmaneNama,INVNumber,INVDate,Outlet,Outlet Name,OutletAddress,SKUCode,ProductName,DUS,LSN,PCS,TotalQuantity(PCS),HARGA/DUS,GROSS,Discount,PPN,Net
  </pre>
</div>
@endsection
