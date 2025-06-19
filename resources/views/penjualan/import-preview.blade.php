@extends('layout.mainlayout')
@section('title', 'Preview Import Penjualan')

@section('content')
<div class="container mt-4">
    <h4>Preview CSV Sebelum Import</h4>
    <form method="POST" action="{{ route('penjualan.import.do') }}">
        @csrf

        <input type="hidden" name="csv_data" value="{{ $csv_data }}">
        <input type="hidden" name="delimiter" value="{{ $delimiter }}">

        <div class="table-responsive mt-3">
            <table class="table table-bordered table-sm table-striped">
                <thead class="table-dark">
                    <tr>
                        @foreach ($header as $col)
                            <th>{{ $col }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr>
                            @foreach ($row as $value)
                                <td>{{ $value }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <button type="submit" class="btn btn-success mt-3">Import Sekarang</button>
        <a href="{{ route('penjualan.import.form') }}" class="btn btn-secondary mt-3">Kembali</a>
    </form>
</div>
@endsection
