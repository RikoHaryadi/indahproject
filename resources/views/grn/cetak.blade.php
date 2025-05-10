@extends('layout.mainlayout')

@section('content')
<div class="container">
    <h1>Nota Pembelian</h1>
    <p>Kode Supplier: {{ $grn->kode_suplier }}</p>
    <p>Nama Supplier: {{ $grn->nama_suplier }}</p>
    <p>Tanggal: {{ $grn->created_at->format('d-m-Y H:i:s') }}</p>

    <table class="table">
        <thead>
            <tr>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Harga</th>
                <th>Quantity</th>
                <th>Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($grn->details as $item)
            <tr>
                <td>{{ $item->kode_barang }}</td>
                <td>{{ $item->nama_barang }}</td>
                <td>{{ number_format($item->harga, 2) }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format($item->jumlah, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h3>Total: {{ number_format($grn->total, 2) }}</h3>

    <!-- Tombol Simpan ke PDF -->
    <a href="{{ route('grn.cetak-pdf', $grn->id) }}" class="btn btn-primary mt-3">Simpan ke PDF</a>
</div>

<script>
    window.print(); // Cetak otomatis
</script>
@endsection
