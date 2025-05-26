@extends('layout.mainlayout')
@section('title', 'Daftar SO')
@section('content')
<div class="container">
    <h1>Daftar Penjualan</h1>

    {{-- Form Filter --}}
    <form method="GET" action="{{ route('penjualan.daftarso') }}" class="mb-3">
        <div class="row">
            <div class="col-md-3">
                <label for="tanggal_dari" class="form-label">Dari Tanggal:</label>
                <input type="date" name="tanggal_dari" id="tanggal_dari" class="form-control" value="{{ request('tanggal_dari') }}">
            </div>
            <div class="col-md-3">
                <label for="tanggal_sampai" class="form-label">Sampai Tanggal:</label>
                <input type="date" name="tanggal_sampai" id="tanggal_sampai" class="form-control" value="{{ request('tanggal_sampai') }}">
            </div>
            <div class="col-md-3">
                <label for="kode_pelanggan" class="form-label">Kode Pelanggan:</label>
                <input type="text" name="kode_pelanggan" id="kode_pelanggan" class="form-control" placeholder="Masukkan Kode Pelanggan" value="{{ request('kode_pelanggan') }}">
            </div>
              <div class="col-md-3">
                <label for="kode_sales" class="form-label">Kode Sales:</label>
                <input type="text" name="kode_sales" id="kode_sales" class="form-control"
                       placeholder="Masukkan Kode Sales"
                       value="{{ request('kode_sales') }}"
                       {{ $userLevel === 1 ? 'readonly' : '' }}>
            </div>
        </div>
        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route('penjualan.daftarso') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>
    {{-- Ringkasan --}}
    @php
        $jumlahBaris = $po->count();
        $totalOmset  = $po->sum('total');
    @endphp
    <div class="mb-3">
        <strong>Jumlah SO:</strong> {{ $jumlahBaris }} &nbsp;|&nbsp;
        <strong>Total Omset:</strong> {{ number_format($totalOmset, 2) }}
    </div>

    {{-- Tabel Penjualan --}}
    <table class="table table-bordered border-primary fs-8">
        <thead>
            <tr>
                <th>No</th>
                <th>ID</th>
                <th>Tanggal</th>
                <th>Sales</th>
                <th>Kode Pelanggan</th>
                <th>Nama Pelanggan</th>
                 <th class="text-end">Total</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalOmset = 0;
            @endphp
            @foreach ($po as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->id }}</td>
                <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d-m-Y') }}</td>
                <td>{{ $item->kode_sales }} - {{ $item->nama_sales }}</td>
                <td>{{ $item->kode_pelanggan }}</td>
                <td>{{ $item->nama_pelanggan }}</td>
                <td>{{ number_format($item->total, 2) }}</td>
                <td class="text-center">
                    @if ($item->status != 1)
                        <button class="btn btn-danger btn-sm delete-btn" data-id="{{ $item->id }}">Hapus</button>
                    @else
                        <button class="btn btn-secondary btn-sm" disabled>SO Sudah Diproses</button>
                    @endif
                </td>
            </tr>
           @endforeach
            @if($po->isEmpty())
           <tr>
             <td colspan="7" class="text-center">Tidak ada data.</td>
           </tr>
           @endif
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
