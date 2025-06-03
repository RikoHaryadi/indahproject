@extends('layout.mainlayout')

@section('title', 'Daftar Retur Penjualan')

@section('content')
<div class="container">
    <h4 class="mb-4">Daftar Transaksi Retur</h4>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

    <table class="table table-bordered table-hover table-striped">
        <thead class="table-dark">
            <tr>
                <th>No</th>
                <th>No Retur</th>
                <th>No Faktur</th>
                <th>Tanggal Retur</th>
                <th>Kode Pelanggan</th>
                <th>Nama Pelanggan</th>
                <th>Total Retur</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
             @foreach ($returList as $no => $retur)
            <tr>
                <td>{{ $no + 1 }}</td>
                <td>{{ $retur->id_retur }}</td>
                <td>{{ $retur->id_faktur }}</td>
                <td>{{ \Carbon\Carbon::parse($retur->created_at)->format('d-m-Y') }}</td>
                <td>{{ $retur->kode_pelanggan }}</td>
                <td>{{ $retur->nama_pelanggan }}</td>
                <td>Rp {{ number_format($retur->total, 0, ',', '.') }}</td>
                <td>
                    @if ($retur->status == 'dibatalkan')
                        <span class="badge bg-danger">Dibatalkan</span>
                    @else
                        <span class="badge bg-success">Aktif</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('retur.cetak', $retur->id) }}" target="_blank" class="btn btn-sm btn-primary">Cetak</a>

                    @if ($retur->status != 'dibatalkan')
                        <form action="{{ route('retur.batalkan', $retur->id) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Yakin ingin membatalkan retur ini?')">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-danger">Cancel</button>
                        </form>
                    @else
                        <button class="btn btn-sm btn-secondary" disabled>Cancelled</button>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
</div>
@endsection
