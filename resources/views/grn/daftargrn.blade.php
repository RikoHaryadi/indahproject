@extends('layout.mainlayout')
@section('title', 'Daftar Grn')
@section('content')
<div class="container">
    <h1>Daftar Hutang</h1>

    {{-- Form Filter --}}
    <form method="GET" action="{{ route('grn.daftargrn') }}" class="mb-3">
        <div class="row">
            <div class="col-md-4">
                <label for="tanggal_dari" class="form-label">Dari Tanggal:</label>
                <input type="date" name="tanggal_dari" id="tanggal_dari" class="form-control" value="{{ request('tanggal_dari') }}">
            </div>
            <div class="col-md-4">
                <label for="tanggal_sampai" class="form-label">Sampai Tanggal:</label>
                <input type="date" name="tanggal_sampai" id="tanggal_sampai" class="form-control" value="{{ request('tanggal_sampai') }}">
            </div>
            <div class="col-md-4">
                <label for="kode_suplier" class="form-label">Kode Supplier:</label>
                <input type="text" name="kode_suplier" id="kode_suplier" class="form-control" placeholder="Masukkan Kode Supplier" value="{{ request('kode_pelanggan') }}">
            </div>
        </div>
        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route('grn.daftargrn') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    {{-- Tabel Penjualan --}}
    <table class="table table-bordered border-primary fs-8">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tanggal</th>
                <th>Kode Supplier</th>
                <th>Nama Supplier</th>
                <th>Total</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalOmset = 0;
            @endphp
            @foreach ($grn as $item)
            <tr>
                <td>{{ $item->id }}</td>
                <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d-m-Y') }}</td>
                <td>{{ $item->kode_suplier }}</td>
                <td>{{ $item->nama_suplier }}</td>
                <td>{{ number_format($item->total, 2) }}</td>
                <td>
                    <a href="{{ route('grn.cetak', $item->id) }}" class="btn btn-secondary" target="_blank">Cetak</a>
                </td>
            </tr>
            @php
                $totalOmset += $item->total;
            @endphp
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4">Total Omset</th>
                <th>{{ number_format($totalOmset, 2) }}</th>
                <th></th>
            </tr>
        </tfoot>
    </table>
</div>
@endsection
