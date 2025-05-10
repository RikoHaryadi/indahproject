@extends('layout.mainlayout')
@section('title', 'Daftar PO')
@section('content')
<div class="container">
    <h1>Daftar PO</h1>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No PO</th>
                <th>Kode Pelanggan</th>
                <th>Nama Pelanggan</th>
                <th>Total</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($poList as $po)
            <tr>
                <td>{{ $po->id }}</td>
                <td>{{ $po->kode_pelanggan }}</td>
                <td>{{ $po->nama_pelanggan }}</td>
                <td>{{ number_format($po->total, 2) }}</td>
                <td>{{ $po->status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
