<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Cetak Retur</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #333; padding: 5px; text-align: left; }
        h2 { text-align: center; }
    </style>
</head>
<body>
    <h2>RETUR PENJUALAN</h2>

    <p>No. Retur: <strong>{{ $retur->id_retur }}</strong></p>
    <p>No. Faktur: {{ $retur->id_faktur }}</p>
    <p>Tanggal: {{ \Carbon\Carbon::parse($retur->created_at)->format('d-m-Y') }}</p>
    <p>Pelanggan: {{ $retur->kode_pelanggan }} - {{ $retur->nama_pelanggan }}</p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Jumlah Retur</th>
                <th>Harga</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($retur->details as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->kode_barang }}</td>
                    <td>{{ $item->nama_barang }}</td>
                    <td>{{ $item->quantityretur }}</td>
                    <td>Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->jumlah, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p style="text-align: right; margin-top: 10px;">
        <strong>Total Retur: Rp {{ number_format($retur->total, 0, ',', '.') }}</strong>
    </p>

    <script>
        window.print();
    </script>
</body>
</html>
