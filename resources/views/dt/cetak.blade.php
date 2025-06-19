@extends('layout.mainlayout')
@section('title', 'Cetak DT')
@section('content')
<div class="container">
    <h4>DT ID: {{ $dt->id }}</h4>
    <p>Colector: {{ $dt->colector }}</p>
    <p>Tanggal: {{ \Carbon\Carbon::parse($dt->created_at)->format('d-m-Y') }}</p>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID Faktur</th>
                <th>Nama Pelanggan</th>
                <th>Total</th>
                <th>Bayar</th>
                <th>Sisa Piutang</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($dt->details as $detail)
            <tr>
                <td>{{ $detail->id_faktur }}</td>
                <td>{{ $detail->nama_pelanggan }}</td>
                <td>{{ number_format($detail->total) }}</td>
                <td>{{ number_format($detail->bayar) }}</td>
                <td>{{ number_format($detail->sisapiutang) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
