@extends('layout.mainlayout')
@section('title', 'Proses PO - Step 1')
@section('content')
<div class="container">
    <h1>Daftar SO Diproses</h1>
    <form method="POST" action="{{ route('so.process.step2') }}">
        @csrf
        
        <table class="table table-bordered table-sm" style="font-size:12px;">
            <thead class="table-success">
                <tr>
                    <th>Pilih</th>
                    <th>No PO</th>
                    <th>Kode Pelanggan</th>
                    <th>Nama Pelanggan</th>
                    <th>Total Rupiah PO</th>
                </tr>
            </thead>
            <tbody>
                @foreach($poList as $po)
                <tr>
                    <td>
                        <input type="checkbox" name="selected_po[]" value="{{ $po->id }}">
                    </td>
                    <td>{{ $po->id }}</td>
                    <td>{{ $po->kode_pelanggan }}</td>
                    <td>{{ $po->nama_pelanggan }}</td>
                    <td>{{ number_format($po->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <button type="submit" class="btn btn-primary">Proses</button>
    </form>
</div>
@endsection
