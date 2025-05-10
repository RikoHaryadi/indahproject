<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Faktur</title>
</head>
<body>
    <h1>Rekap Faktur</h1>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Kode Pelanggan</th>
                <th>Nama Pelanggan</th>
                <th>Total Faktur (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($faktur as $item)
            <tr>
                <td>{{ $item->id_faktur }}</td>
                <td>{{ $item->kode_pelanggan }}</td>
                <td>{{ $item->nama_pelanggan }}</td>
                <td>{{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <p>Total Nilai Semua Faktur: <strong>Rp {{ number_format($totalNilaiFaktur, 2) }}</strong></p>
</body>
</html>
