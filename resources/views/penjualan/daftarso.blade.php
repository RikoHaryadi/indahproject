@extends('layout.mainlayout')
@section('title', 'Daftar SO')
@section('content')
<div class="container">
    <h1>Daftar Penjualan</h1>

    {{-- Form Filter --}}
    <form method="GET" action="{{ route('penjualan.daftarso') }}" class="mb-3">
        <div class="row">
            <div class="col-md-4">
                <label for="tanggal_dari" class="form-label">Dari Tanggal:</label>
                <input type="date" name="tanggal_dari" id="tanggal_dari" class="form-control" value="{{ request('tanggal_dari') }}">
            </div>
            <div class="col-md-4">
                <label for="tanggal_sampai" class="form-label">Sampai Tanggal:</label>
                <input type="date" name="tanggal_sampai" id="tanggal_sampai" class="form-control" value="{{ request('tanggal_sampai') }}">
            </div>
            <div class="col-md-4">
                <label for="kode_pelanggan" class="form-label">Kode Pelanggan:</label>
                <input type="text" name="kode_pelanggan" id="kode_pelanggan" class="form-control" placeholder="Masukkan Kode Pelanggan" value="{{ request('kode_pelanggan') }}">
            </div>
        </div>
        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route('penjualan.daftarso') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    {{-- Tabel Penjualan --}}
    <table class="table table-bordered border-primary fs-8">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tanggal</th>
                <th>Kode Pelanggan</th>
                <th>Nama Pelanggan</th>
                <th>Total</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalOmset = 0;
            @endphp
            @foreach ($po as $item)
            <tr>
                <td>{{ $item->id }}</td>
                <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d-m-Y') }}</td>
                <td>{{ $item->kode_pelanggan }}</td>
                <td>{{ $item->nama_pelanggan }}</td>
                <td>{{ number_format($item->total, 2) }}</td>
                <td>
                    @if ($item->status != 1)
                        <button class="btn btn-danger btn-sm delete-btn" data-id="{{ $item->id }}">Hapus</button>
                    @else
                        <button class="btn btn-secondary btn-sm" disabled>SO Sudah Diproses</button>
                    @endif
                </td>
            </tr>
           @endforeach
        </tbody>
    </table>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('.delete-btn').click(function() {
            let poId = $(this).data('id');
            let row = $(this).closest('tr');

            if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                $.ajax({
                    url: `/penjualan/so/${poId}`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        alert(response.success);
                        row.remove(); // Hapus baris dari tabel
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON.error);
                    }
                });
            }
        });
    });
</script>

@endsection
