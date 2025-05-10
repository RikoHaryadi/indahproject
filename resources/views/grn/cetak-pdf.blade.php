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
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Nota Pembelian</h1>
    <p><strong>Kode Supplier:</strong> {{ $grn->kode_supplier }}</p>
    <p><strong>Nama Supplier:</strong> {{ $grn->nama_supplier }}</p>
    <p><strong>Tanggal:</strong> {{ $grn->created_at->format('d-m-Y H:i:s') }}</p>

    <table>
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
</body>
</html>
