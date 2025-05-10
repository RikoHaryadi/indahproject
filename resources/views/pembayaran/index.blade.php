@extends('layout.mainlayout')

@section('content')
<div class="container">
    <h1>Menu Pembayaran</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered border-primary fs-8">
        <thead>
            <tr>
                <th>Kode Pelanggan</th>
                <th>Nama Pelanggan</th>
                <th>Total Hutang</th>
                <th>Total Pembayaran</th>
                <th>Sisa Piutang</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalKeseluruhanHutang = 0;
                $totalKeseluruhanPembayaran = 0;
                $totalKeseluruhanPiutang = 0;
            @endphp
            @foreach($data as $item)
                @php
                    $totalKeseluruhanHutang += $item['total_hutang'];
                    $totalKeseluruhanPembayaran += $item['pembayaran'];
                    $totalKeseluruhanPiutang += $item['sisa_piutang'];
                @endphp
                <tr>
                    <td>{{ $item['kode_pelanggan'] }}</td>
                    <td>{{ $item['nama_pelanggan'] }}</td>
                    <td>{{ number_format($item['total_hutang'], 2) }}</td>
                    <td>{{ number_format($item['pembayaran'], 2) }}</td>
                    <td>{{ number_format($item['sisa_piutang'], 2) }}</td>
                    <td>
                        <button class="btn btn-primary" onclick="openPaymentForm('{{ $item['kode_pelanggan'] }}', '{{ $item['nama_pelanggan'] }}', '{{ $item['total_hutang'] }}')">Bayar</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2">Total Keseluruhan</th>
                <th>{{ number_format($totalKeseluruhanHutang, 2) }}</th>
                <th>{{ number_format($totalKeseluruhanPembayaran, 2) }}</th>
                <th>{{ number_format($totalKeseluruhanPiutang, 2) }}</th>
                <th></th>
            </tr>
        </tfoot>
    </table>
    @if($data->isEmpty())
    <tr>
        <td colspan="6" class="text-center">Tidak ada pelanggan dengan sisa piutang.</td>
    </tr>
@endif


    <form id="payment-form" method="POST" action="{{ route('pembayaran.store') }}" style="display: none;">
        @csrf
        <input type="hidden" name="kode_pelanggan" id="kode_pelanggan">
        <input type="hidden" name="nama_pelanggan" id="nama_pelanggan">
        <input type="hidden" name="total_hutang" id="total_hutang">
        <div class="form-group">
            <label for="pembayaran">Jumlah Pembayaran:</label>
            <input type="number" name="pembayaran" id="pembayaran" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Simpan</button>
    </form>
</div>

<script>
    function openPaymentForm(kodePelanggan, namaPelanggan, totalHutang) {
        document.getElementById('payment-form').style.display = 'block';
        document.getElementById('kode_pelanggan').value = kodePelanggan;
        document.getElementById('nama_pelanggan').value = namaPelanggan;
        document.getElementById('total_hutang').value = totalHutang;
    }
</script>
@endsection
