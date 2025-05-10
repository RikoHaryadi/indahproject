<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Biaya</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid black; }
        th, td { padding: 10px; text-align: left; }
    </style>
</head>
<body>
    <h1>Laporan Biaya</h1>
    <p><strong>Kode Transaksi:</strong> {{ $resume->kode_transaksi }}</p>
    <p><strong>Tanggal Transaksi:</strong> {{ $resume->created_at }}</p>
    <p><strong>Total Biaya:</strong> Rp {{ number_format($resume->total, 2, ',', '.') }}</p>

    <table>
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
</body>
</html>
