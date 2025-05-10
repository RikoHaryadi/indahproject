<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Penjualan</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color:rgb(99, 94, 94); }
    </style>
</head>
<body>
    <h1>Nota Penjualan</h1>
    <p><strong>Kode Pelanggan:</strong> {{ $penjualan->kode_pelanggan }}</p>
    <p><strong>Nama Pelanggan:</strong> {{ $penjualan->nama_pelanggan }}</p>
    <p><strong>Tanggal:</strong> {{ $penjualan->created_at->format('d-m-Y') }}</p>
    <p><strong>Jatuh Tempo:</strong> 
        @if ($penjualan->pelanggan && $penjualan->pelanggan->top)
            {{ $penjualan->created_at->addDays($penjualan->pelanggan->top)->format('d-m-Y') }}
        @else
            -
        @endif
    </p>
    <table>
        <thead>
            <tr style="font-size:10px;">
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
            <tr style="font-size:10px;">
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
    </table>

    <h3>Total: {{ number_format($penjualan->total, 2) }}</h3>
</body>
</html>
