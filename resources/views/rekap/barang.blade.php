<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Barang</title>
</head>
<body>
    <h1>Rekap Barang</h1>
    <table border="1">
        <thead>
            <tr>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Isi</th>
                <th>Total Quantity</th>
                <th>Dus</th>
                <th>Lusin</th>
                <th>PCS</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($barang as $item)
            <tr>
                <td>{{ $item['kode_barang'] }}</td>
                <td>{{ $item['nama_barang'] }}</td>
                <td>{{ $item['isi'] }}</td>
                <td>{{ $item['total_quantity'] }}</td>
                <td>{{ $item['dus'] }}</td>
                <td>{{ $item['lusin'] }}</td>
                <td>{{ $item['pcs'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
