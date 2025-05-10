@extends('layout.mainlayout')
@section('title', 'Buku Besar')
@section('content')
<div class="container">
    <h1>Buku Besar </h1>

    {{-- Form Filter --}}
    <form method="GET" action="{{ route('akuntan.bukubesar') }}" class="mb-3">
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
                <label for="kode_akun" class="form-label">Kode Akun:</label>
                <select name="kode_akun" id="kode_akun" class="form-control">
                    <option value="" {{ request('kode_akun') == '' ? 'selected' : '' }}>Semua Kode Akun</option>
                    @foreach($kodeakunList as $kodeakun)
                        <option value="{{ $kodeakun->kode_akun }}" {{ request('kode_akun') == $kodeakun->kode_akun ? 'selected' : '' }}>
                            {{ $kodeakun->kode_akun }} - {{ $kodeakun->nama_akun }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route('akuntan.bukubesar') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    {{-- Tabel Buku Besar --}}
    <table class="table table-bordered border-primary fs-8">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tanggal</th>
                <th>Kode Akun</th>
                <th>Uraian</th>
                <th>Debet</th>
                <th>Kredit</th>
            </tr>
        </thead>
        <tbody>
    @php
        $totalOmset = 0;
    @endphp
    @foreach($bukubesarList as $data)
    @if(!is_null($data->debet) || !is_null($data->kredit))
            <tr>
                <td>{{ $data->id }}</td>
                <td>{{ \Carbon\Carbon::parse($data->created_at)->format('d-m-Y') }}</td>
                <td>{{ $data->kode_akun }} - {{ $data->nama_akun }}</td>
                <td>{{ $data->uraian }}</td>
                <td>{{ number_format($data->debet, 2) }}</td>
                <td>{{ number_format($data->kredit, 2) }}</td>
            </tr>
            @php
                $totalOmset += $data->debet - $data->kredit;
            @endphp
        @endif
    @endforeach
</tbody>
        <tfoot>
            <tr>
                <th colspan="4">Saldo</th>
                <th>{{ number_format($totalOmset, 2) }}</th>
                <th></th>
            </tr>
        </tfoot>
    </table>
</div>
@endsection