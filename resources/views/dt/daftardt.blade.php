@extends('layout.mainlayout')
@section('title', 'Daftar DT')
@section('content')
<div class="container">
    <h1>Daftar DT</h1>
    <table class="table table-bordered table-hover table-striped">
        <thead class="table-dark">
        
            <tr>
                <th>ID</th>
                <th>Status</th>
                <th>Colector</th>
                <th>Tanggal</th>
                <th>Jumlah Faktur</th>
                <th>Nilai DT</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @php $totalOmset = 0; @endphp
            @foreach ($dt as $item)
            <tr>
                <td>{{ $item->id }}</td>
                <td>{{ $item->is_updated }}</td>
                <td>{{ $item->colector}}</td>
                <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d-m-Y') }}</td>
                <td>{{ $item->ddt->count() }}</td> <!-- Hitung jumlah faktur dari relasi -->
                <td>{{ number_format($item->totaldt) }}</td>
                <td>
                    <a href="{{ route('dt.cetak', $item->id) }}" class="btn btn-sm btn-primary">Cetak</a>
                    <a href="{{ route('dt.edit', $item->id) }}" class="btn btn-sm btn-warning">Edit</a>
                    <form action="{{ route('dt.destroy', $item->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Yakin ingin menghapus?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger">Hapus</button>
                    </form>
                </td>

                 
            </tr>
            @php $totalOmset += $item->totaldt; @endphp
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5">Total Omset</th>
                <th>{{ number_format($totalOmset, 2) }}</th>
                
            </tr>
        </tfoot>
    </table>
</div>
@endsection
