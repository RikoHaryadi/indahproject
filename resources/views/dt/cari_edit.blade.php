@extends('layout.mainlayout')
@section('title', 'Cari Edit Pembayaran DT')

@section('styles')
<style>
    .container {
        max-width: 800px; /* Sesuaikan ukuran agar cukup untuk form edit */
        margin-top: 50px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    table, th, td {
        border: 1px solid #000;
    }
    th, td {
        padding: 8px;
        text-align: left;
    }
    .header-section {
        margin-bottom: 20px;
    }
    .form-control {
        width: 100%;
    }
</style>
@endsection

@section('content')
<div class="container">
    <h1>Cari Edit Pembayaran DT</h1>

    <!-- Tampilkan pesan error (jika ada) -->
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <!-- Form Pencarian -->
    <form id="search-form" action="{{ route('dt.cari_edit') }}" method="GET">
        <div class="form-group">
            <label for="edit-id">Masukkan Nomor ID Pembayaran DT</label>
            <input type="text" class="form-control" id="edit-id" name="edit_id" placeholder="Contoh: 1" required>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Cari</button>
    </form>

    <!-- Tampilkan form edit jika data DT ditemukan -->
    @if($dt)
    <hr>
    <h2>Update Pembayaran DT</h2>

    <!-- Tampilkan data DT utama -->
    <div class="header-section">
        <p><strong>ID Faktur Collector:</strong> {{ $dt->id_faktur }}</p>
        <p><strong>Nama Collector:</strong> {{ $dt->colector }}</p>
        <p><strong>Total DT:</strong> {{ number_format($dt->totaldt, 2) }}</p>
    </div>

    <!-- Jika semua detail sudah lunas, tampilkan pesan -->
    @php
        $allLunas = true;
        foreach ($dt->details as $detail) {
            if ($detail->sisapiutang > 0) {
                $allLunas = false;
                break;
            }
        }
    @endphp

    @if($allLunas)
        <div class="alert alert-info">
            Semua piutang untuk toko ini sudah lunas. Tidak dapat menerima pembayaran tambahan.
        </div>
    @endif

    <!-- Form untuk update pembayaran -->
    <form method="POST" action="{{ route('dt.update', $dt->id) }}">
        @csrf
        @method('PUT')
        <table>
            <thead>
                <tr>
                    <th>ID Faktur</th>
                    <th>Kode Pelanggan</th>
                    <th>Nama Pelanggan</th>
                    <th>Total Faktur</th>
                    <th>Bayar</th>
                    <th>Sisa Piutang</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dt->details as $index => $detail)
                <tr>
                    <td>
                        <input type="hidden" name="details[{{ $index }}][id]" value="{{ $detail->id }}">
                        <input type="hidden" name="details[{{ $index }}][id_faktur]" value="{{ $detail->id_faktur }}">
                        {{ $detail->id_faktur }}
                    </td>
                    <td>
                        <input type="hidden" name="details[{{ $index }}][kode_pelanggan]" value="{{ $detail->kode_pelanggan }}">
                        {{ $detail->kode_pelanggan }}
                    </td>
                    <td>
                        <input type="hidden" name="details[{{ $index }}][nama_pelanggan]" value="{{ $detail->nama_pelanggan }}">
                        {{ $detail->nama_pelanggan }}
                    </td>
                    <td>
                    <input type="hidden" class="total_faktur" name="details[{{ $index }}][total_faktur]" value="{{ $detail->sisapiutang }}">
                        {{ number_format($detail->sisapiutang, 2) }}
                    </td>
                    <td>
                        <input type="number" class="bayar form-control" name="details[{{ $index }}][bayar]" 
                               value="{{ $detail->bayar }}" step="0.01" min="0"
                               @if($detail->sisapiutang == 0) readonly @endif>
                    </td>
                    <td>
                        <input type="number" class="sisa_piutang form-control" name="details[{{ $index }}][sisa_piutang]" value="{{ $detail->sisapiutang }}" step="0.01" readonly>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <!-- Nonaktifkan tombol submit jika semua piutang sudah lunas -->
        @if(!$allLunas)
            <button type="submit" class="btn btn-success">Update Pembayaran</button>
        @endif
    </form>
    @endif
</div>
@endsection


<script>
document.addEventListener("DOMContentLoaded", function() {
    // Fungsi untuk menghitung sisa piutang pada tiap baris: sisa = total_faktur - bayar
    function updateRowSisaPiutang(row) {
    let total = parseFloat(row.querySelector('.total_faktur').value) || 0;
    let bayar = parseFloat(row.querySelector('.bayar').value) || 0;
    let sisa = total - bayar;
    row.querySelector('.sisa_piutang').value = sisa.toFixed(2);
}

    // Pasang event listener untuk setiap input bayar, kecuali jika sudah readonly
    document.querySelectorAll('.bayar').forEach(function(input) {
        if (!input.hasAttribute('readonly')) {
            input.addEventListener('input', function() {
                let row = this.closest('tr');
                updateRowSisaPiutang(row);
            });
        }
    });
});
</script>