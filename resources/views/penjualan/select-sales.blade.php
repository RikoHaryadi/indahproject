@extends('layout.mainlayout')
@section('title','Pilih Sales')
@section('content')
<div class="container">
  <h2>Pilih Sales</h2>
  <form method="POST" action="{{ route('po.handle-select-sales') }}">
    @csrf
    <div class="mb-3">
      <label for="kode_sales" class="form-label">Sales:</label>
      <select name="kode_sales" id="kode_sales" class="form-control" required>
        <option value="" disabled selected>Pilih Sales</option>
        @foreach($salesmanList as $s)
          <option value="{{ $s->kode_sales }}">
            {{ $s->kode_sales }} – {{ $s->nama_salesman }}
          </option>
        @endforeach
      </select>
    </div>
    <button class="btn btn-primary">Lanjut</button>
  </form>
</div>
@endsection
