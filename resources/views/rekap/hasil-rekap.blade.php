@extends('layout.mainlayout')

@section('content')
<div class="container">
    <h1>Hasil Rekap</h1>

    <!-- Faktur yang Dipilih -->
    <h3>Faktur yang Dipilih</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>ID Faktur</th>
                <th>Kode Pelanggan</th>
                <th>Nama Pelanggan</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($fakturTerpilih as $index => $faktur)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $faktur->id_faktur }}</td>
                    <td>{{ $faktur->kode_pelanggan }}</td>
                    <td>{{ $faktur->nama_pelanggan }}</td>
                    <td>Rp.{{ number_format($faktur->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Rekap Barang -->
    <h3>Rekap Barang</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Total Quantity</th>
                <th>Isi/Dus</th>
                <th>Dus</th>
                <th>Lusin</th>
                <th>Pcs</th>
            </tr>
        </thead>
        <tbody>
            @php $totalDus = 0; @endphp <!-- Variabel untuk menghitung total dus -->
            @foreach ($barang as $item)
                @php $totalDus += $item['dus']; @endphp <!-- Tambahkan dus setiap item -->
                <tr>
                    <td>{{ $item['kode_barang'] }}</td>
                    <td>{{ $item['nama_barang'] }}</td>
                    <td>{{ $item['total_quantity'] }}</td>
                    <td>{{ $item['isi'] }}</td>
                    <td>{{ $item['dus'] }}</td>
                    <td>{{ $item['lusin'] }}</td>
                    <td>{{ $item['pcs'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Menampilkan Total Dus -->
    <h3>Total Dus</h3>
    <p><strong>{{ $totalDus }}</strong> dus</p>

    <!-- Total Nilai Faktur -->
    <h3>Total Nilai Faktur</h3>
    <p><strong>Rp.{{ number_format($totalNilaiFaktur, 2) }}</strong></p>
</div>
@endsection
