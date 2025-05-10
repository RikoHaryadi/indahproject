@extends('layout.mainlayout')
@section('title', 'Cetak Biaya')
@section('content')
<div class="container">
    <h1>Laporan Biaya</h1>
    <p><strong>Kode Transaksi:</strong> {{ $biayaresume->kode_transaksi }}</p>
    <p><strong>Tanggal Transaksi:</strong> {{ $biayaresume->created_at }}</p>
    <p><strong>Total Biaya:</strong> Rp {{ number_format($biayaresume->total, 2, ',', '.') }}</p>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Kode Akun</th>
                <th>Nama Akun</th>
                <th>Jumlah</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($biayaItems as $item)
            <tr>
                <td>{{ $item->kode_akun }}</td>
                <td>{{ $item->nama_akun }}</td>
                <td>Rp {{ number_format($item->jumlah, 2, ',', '.') }}</td>
                <td>{{ $item->keterangan }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <a href="{{ route('biaya.cetak-pdf', $biayaresume->kode_transaksi) }}" class="btn btn-primary">Cetak PDF</a>
</div>
<script>
    window.print(); // Cetak otomatis
</script>
@endsection
