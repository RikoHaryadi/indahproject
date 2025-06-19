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
            @php $totalOmset = 0; @endphp
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
                  
                    <a href="{{ route('retur.cetak', $retur->id) }}"
                    class="btn btn-outline-dark btn-sm"
                    data-bs-toggle="tooltip" data-bs-placement="top" title="Cetak Faktur"
                    target="_blank">
                        <i class="fas fa-print"></i>
                    </a>
                    @if ($retur->status != 'dibatalkan')
                    <form action="{{ route('retur.batalkan', $retur->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin membatalkan retur ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm"
                               
                                data-bs-toggle="tooltip" data-bs-placement="top" title="Batalkan Retur">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                       
                    @else
                     <button class="btn btn-outline-danger btn-sm"
                            data-bs-toggle="tooltip" disabled data-bs-placement="top" title="Canceled">
                          <i class="fas fa-trash"></i>
                          </button>
                    @endif
                </td>
            </tr>
             @php $totalOmset += $retur->total; @endphp
        @endforeach
    </tbody>
          <tfoot>
            <tr>
                <th colspan="6">Total Retur</th>
                <th>{{ number_format($totalOmset, 2) }}</th>
                <th></th>
                <th></th>
            </tr>
        </tfoot>
</table>
</div>
<style>
    .table-dark {
        font-size: 12px;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        tooltipTriggerList.forEach(function (tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl)
        })
    });
</script>
@endsection
