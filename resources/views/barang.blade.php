@extends('layout.mainlayout')
@section('title', 'Stok')

@section('content')
    <h1>Laporan Stok Barang</h1>

    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    <!-- Tombol Tambah Barang -->
    <!-- <button type="button" onclick="document.getElementById('modalAdd').style.display='block'" class="btn btn-success">
        Add Barang
    </button> -->

    <!-- Modal Form -->
    
    <form action="{{ route('stok.export') }}" method="GET" style="margin-bottom: 15px;">
    <button type="submit" class="btn btn-success">Export ke Excel</button>
</form>
<div class="d-flex justify-content-start">
    <!-- Tombol Export ke Excel -->

    <table class="table table-dark table-striped-columns" style="margin-top:20px; font-size: 14px;">
        <thead>
            <tr>
                <th>No.</th>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Isi Dus</th>
                <th>Harga</th>
                <th>Stok Dus</th>
                <th>Stok Lsn</th>
                <th>Stok pcs</th>
                <th>Nilai Rp</th>
            
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($barangList as $data)
                @if($data->stok > 0) <!-- Filter barang dengan stok > 0 -->
                @php
                // Hitung stok dus, lsn, dan pcs
                $stok_dus = floor($data->stok / $data->isidus); // Jumlah dus
                $stok_sisa_pcs = $data->stok % $data->isidus;   // Sisa pcs setelah dus
                $stok_lsn = floor($stok_sisa_pcs / 12);         // Hitung lusin (1 lsn = 12 pcs)
                $stok_pcs = $stok_sisa_pcs % 12;                // Sisa pcs setelah lusin
            @endphp
            <tr>
            <td>{{ $no++ }}</td>
                <td>{{ $data->kode_barang }}</td>
                <td>{{ $data->nama_barang }}</td>
                <td>{{ $data->isidus }}</td>
                <td>{{ number_format($data->nilairp, 2) }}</td>
                <td>{{ $stok_dus }}</td>
                <td>{{ $stok_lsn }}</td>
                <td>{{ $stok_pcs }}</td>
                <td>{{ number_format($data->nilairp * $data->stok, 2) }}</td>
            </tr>
            @endif
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="8">Total Stok</th>
                <th>{{ number_format($totalNilaiStok, 2) }}</th>
            </tr>
        </tfoot>
    </table>
</div>


<script>
    function calculateChange() {
        const harga = parseFloat(document.getElementById('harga').value || 0);
        const stok = parseFloat(document.getElementById('stok').value || 0);
        const nilairp = harga * stok;
        document.getElementById('nilairp').value = nilairp;
    }
</script>
@endsection
