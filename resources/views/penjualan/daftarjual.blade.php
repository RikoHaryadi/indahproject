@extends('layout.mainlayout')
@section('title', 'Daftar Transaksi')
@section('content')
<div class="container">
    <h1>Daftar Penjualan</h1>

    {{-- Form Filter --}}
    <form method="GET" action="{{ route('penjualan.daftarjual') }}" class="mb-3">
        <div class="row">
            <div class="col-md-4">
                <label for="tanggal_dari" class="form-label">Dari Tanggal:</label>
                <input type="date" name="tanggal_dari" id="tanggal_dari" class="form-control" 
                       value="{{ request('tanggal_dari') ?? date('Y-m-d') }}">
            </div>
            <div class="col-md-4">
                <label for="tanggal_sampai" class="form-label">Sampai Tanggal:</label>
                <input type="date" name="tanggal_sampai" id="tanggal_sampai" class="form-control" 
                       value="{{ request('tanggal_sampai') ?? date('Y-m-d') }}">
            </div>
            <div class="col-md-4">
                <label for="kode_pelanggan" class="form-label">Kode Pelanggan:</label>
                <input type="text" name="kode_pelanggan" id="kode_pelanggan" class="form-control" placeholder="Masukkan Kode Pelanggan" 
                       value="{{ request('kode_pelanggan') }}">
            </div>
        </div>
        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route('penjualan.daftarjual') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    {{-- Tabel Penjualan --}}
    <table class="table table-bordered table-hover table-striped">
        <thead class="table-dark">
        
            <tr>
                <th>ID</th>
                <th>ID Faktur</th>
                <th>Tanggal</th>
                <th>Jatuh Tempo</th>
                <th>Kode Pelanggan</th>
                <th>Nama Pelanggan</th>
                <th>Total</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @php $totalOmset = 0; @endphp
            @foreach ($penjualan as $item)
            <tr>
                <td>{{ $item->id }}</td>
                <td>{{ $item->id_faktur}}</td>
                <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d-m-Y') }}</td>
                <td>
                    @if($item->pelanggan && $item->pelanggan->top)
                        {{ \Carbon\Carbon::parse($item->created_at)->addDays($item->pelanggan->top)->format('d-m-Y') }}
                    @else
                        -
                    @endif
                </td>
                <td>{{ $item->kode_pelanggan }}</td>
                <td>{{ $item->nama_pelanggan }}</td>
                <td>{{ number_format($item->total, 2) }}</td>
                    <td class="text-center">
                        <!-- Tombol Cetak (jika Anda punya) -->
                        <a href="{{ route('penjualan.cetak', $item->id) }}"
                           class="btn btn-secondary btn-sm" target="_blank">
                            Cetak
                        </a>
                        <!-- Tombol Edit -->
                        <a href="{{ route('penjualan.edit', $item->id) }}"
                           class="btn btn-primary btn-sm">
                            Edit
                        </a>
                    </td>
            </tr>
            @php $totalOmset += $item->total; @endphp
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="6">Total Omset</th>
                <th>{{ number_format($totalOmset, 2) }}</th>
                <th></th>
            </tr>
        </tfoot>
    </table>
</div>
@endsection
