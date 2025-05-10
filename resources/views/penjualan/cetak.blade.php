@extends('layout.mainlayout')

@section('content')
<div class="container" style="max-width: 1200px; margin: 0 auto; padding: 20px;">
    <h1>Nota Penjualan</h1>
    <p><strong>Kode Pelanggan:</strong> {{ $penjualan->kode_pelanggan }}</p>
    <p><strong>Nama Pelanggan:</strong> {{ $penjualan->nama_pelanggan }}</p>
    <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($penjualan->created_at)->format('d-m-y') }}</p>
    <p><strong>Jatuh Tempo:</strong> 
        @if ($penjualan->pelanggan && $penjualan->pelanggan->top)
            {{ \Carbon\Carbon::parse($penjualan->created_at)->addDays(\Carbon\Carbon::parse($penjualan->pelanggan->top))->format('d-m-Y') }}
        @else
            -
        @endif
    </p>
    <table class="table table-bordered table-sm">
    <thead class="table-light">
            <tr style="font-size: 12px;">
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Harga</th>
                <th>Dus.Lsn.Pcs</th>
                <th>Disc1</th>
                <th>Disc2</th>
                <th>Disc3</th>
                <th>Disc4</th>
                <th>Jumlah</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($penjualan->details as $item)
            @php
                // Ambil nilai isi dus dari barang terkait
                $isidus = $item->barang->isidus ?? 1; // Default ke 1 jika isidus tidak ada
                $dus = floor($item->quantity / $isidus); // Hitung jumlah dus
                $sisaAfterDus = $item->quantity % $isidus; // Sisa setelah dus
                $lsn = floor($sisaAfterDus / 12); // Hitung jumlah lusin
                $pcs = $sisaAfterDus % 12; // Sisa pcs
            @endphp
            <tr style="font-size: 12px;">
                <td>{{ $item->kode_barang }}</td>
                <td>{{ $item->nama_barang }}</td>
                <td>{{ number_format($item->harga, 2) }}</td>
                <td>{{ $dus }} . {{ $lsn }} . {{ $pcs }}</td>
                <td>{{ number_format($item->disc1) }}%</td>
                <td>{{ number_format($item->disc2) }}%</td>
                <td>{{ number_format($item->disc3) }}%</td>
                <td>{{ number_format($item->disc4) }}%</td>
                <td>{{ number_format($item->jumlah, 2) }}</td>
            </tr>
           
        @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="7"></th>
                <th style="font-size:12px;" colspan="1">Disc :</th>
                <th style="font-size:12px;">Rp.{{ number_format($penjualan->total_discount) }}</th>
            </tr>
            <tr>
                <th colspan="7"></th>
                <th style="font-size:12px;" colspan="1">Total Net :</th>
                <th style="font-size:12px;">Rp.{{ number_format($penjualan->total) }}</th>
            </tr>
        </tfoot>
    </table>

    <!-- Tombol Simpan ke PDF -->
    <a href="{{ route('penjualan.cetak-pdf', $penjualan->id) }}" class="btn btn-primary mt-3">Simpan ke PDF</a>
</div>

<script>
    window.print(); // Cetak otomatis
</script>
@endsection
