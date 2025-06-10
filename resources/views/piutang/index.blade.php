@extends('layout.mainlayout')
@section('title', 'Daftar Piutang')
@section('content')
<div class="container">
    <h4 class="mb-4">Daftar Piutang</h4>

    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-4">
            <label>Filter Pelanggan</label>
            <select name="kode_pelanggan" class="form-control">
                <option value="">-- Semua Pelanggan --</option>
                @foreach($pelangganList as $pelanggan)
                    <option value="{{ $pelanggan->kode_pelanggan }}" {{ request('kode_pelanggan') == $pelanggan->Kode_pelanggan ? 'selected' : '' }}>
                        {{ $pelanggan->Nama_pelanggan }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label>Jatuh Tempo Hingga</label>
            <input type="date" name="jatuh_tempo" class="form-control" value="{{ request('jatuh_tempo') }}">
        </div>
        <div class="col-md-4 align-self-end">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route('piutang.index') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>No</th>
                <th>No Faktur</th>
                <th>Tanggal</th>
                <th>Jatuh Tempo</th>
                <th>Kode Pelanggan</th>
                <th>Nama Pelanggan</th>
                <th>Total Faktur</th>
                <th>Pembayaran</th>
                <th>Sisa Piutang</th>
            </tr>
        </thead>
        <tbody>
           @php $grandTotal = 0; @endphp

@forelse($piutangList as $index => $p)
    @php
        $jatuhTempo = $p->pelanggan && $p->pelanggan->top
        ? \Carbon\Carbon::parse($p->created_at)->addDays($p->pelanggan->top)->format('d-m-Y')
        : '-';

        $grandTotal += $p->sisapiutang;
    @endphp

    <tr>
        <td>{{ $index + 1 }}</td>
        <td>{{ $p->id_faktur }}</td>
        <td>{{ \Carbon\Carbon::parse($p->created_at)->format('d-m-Y') }}</td>
        <td>{{ $jatuhTempo }}</td>
        <td>{{ $p->kode_pelanggan }}</td>
        <td>{{ $p->nama_pelanggan }}</td>
        <td class="text-end">{{ number_format($p->total, 2) }}</td>
        <td class="text-end">{{ number_format($p->pembayaran, 2) }}</td>
        <td class="text-end">{{ number_format($p->sisapiutang, 2) }}</td>
    </tr>
@empty
    <tr>
        <td colspan="9" class="text-center">Tidak ada data piutang.</td>
    </tr>
@endforelse
        </tbody>
        <tfoot>
            <tr class="table-secondary">
                <td colspan="8" class="text-end"><strong>Total Sisa Piutang</strong></td>
                <td class="text-end"><strong>{{ number_format($grandTotal, 0, ',', '.') }}</strong></td>
            </tr>
        </tfoot>
    </table>
</div>
@endsection
