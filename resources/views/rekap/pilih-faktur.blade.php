@extends('layout.mainlayout')

@section('content')
<div class="container">
    <h1>Pilih Faktur untuk Rekap</h1>
    <form action="{{ route('rekap.hasil-rekap') }}" method="POST">
        @csrf
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Pilih</th>
                    <th>No</th>
                    <th>ID Faktur</th>
                    <th>Tanggal</th>
                    <th>Kode Pelanggan</th>
                    <th>Nama Pelanggan</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($faktur as $index => $item)
                <tr>
                    <td>
                        <input type="checkbox" name="faktur_ids[]" value="{{ $item->id }}" id="faktur_{{ $item->id }}">
                    </td>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->id_faktur }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d-m-Y') }}</td>
                    <td>{{ $item->kode_pelanggan }}</td>
                    <td>{{ $item->nama_pelanggan }}</td>
                    <td>Rp.{{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <button type="submit" class="btn btn-primary mt-3">Proses Rekap</button>
    </form>
</div>
@endsection
