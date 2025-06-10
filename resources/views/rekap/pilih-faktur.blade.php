@extends('layout.mainlayout')

@section('content')
<div class="container">
    <h1>Pilih Faktur untuk Rekap</h1>
    <form method="GET" action="{{ url('rekap/pilih-faktur') }}">
    @csrf <!-- jika menggunakan POST, tapi GET tidak butuh CSRF token -->
    <label>Tanggal Dari:</label>
    <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" required>

    <label>Tanggal Sampai:</label>
    <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}" required>

    <label>Sales:</label>
  <select name="kode_sales" required>
    <option value="">-- Pilih Sales --</option>
    <option value="all" {{ request('kode_sales') == 'all' ? 'selected' : '' }}>-- Semua Sales --</option>
    @foreach($salesList as $salesItem)
        <option value="{{ $salesItem->kode_sales }}"
            {{ request('kode_sales') == $salesItem->kode_sales ? 'selected' : '' }}>
            {{ $salesItem->nama_salesman }}
        </option>
    @endforeach
</select>

    <button type="submit">Filter</button>
</form>
    <form action="{{ route('rekap.hasil-rekap') }}" method="POST">
        @csrf
         <!-- Cek apakah $fakturs ada isinya -->
            @if(!empty($fakturs))
        <table class="table table-bordered">
            <thead>
                <tr>
                    
                     <th>
                        <input type="checkbox" id="checkAll"> Pilih Semua
                    </th>
                    <th>Sales</th>
                    <th>Nama Sales</th>
                    <th>ID Faktur</th>
                    <th>Tanggal</th>
                    <th>Kode Pelanggan</th>
                    <th>Nama Pelanggan</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($fakturs as $f)
                <tr>
                
                    <td>
                        <input type="checkbox" class="faktur-checkbox" name="faktur_ids[]" value="{{ $f->id }}">
                    </td>
                    <td>{{ $f->kode_sales }}</td>
                    <td>{{ $f->nama_sales }}</td>
                    <td>{{ $f->id_faktur }}</td>
                    <td>{{ \Carbon\Carbon::parse($f->created_at)->format('d-m-Y') }}</td>
                    <td>{{ $f->kode_pelanggan }}</td>
                    <td>{{ $f->nama_pelanggan }}</td>
                    <td>Rp.{{ number_format($f->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
        <button type="submit" class="btn btn-primary mt-3">Proses Rekap</button>
    </form>
</div>
<!-- JavaScript untuk Pilih Semua -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const checkAll = document.getElementById('checkAll');
        const checkboxes = document.querySelectorAll('.faktur-checkbox');

        checkAll.addEventListener('change', function () {
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    });
</script>
@endsection
