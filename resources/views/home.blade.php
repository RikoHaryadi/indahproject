@extends('layout.mainlayout')
@section('title', 'Dashboard')
@section('content')
<div class="container">
    <h1>Dashboard</h1>
    <div class="row">
      
       @if(session('user_level') != 1)
            <div class="col-md-3">
                <div class="card text-white bg-info mb-3">
                    <div class="card-header">SO Semua Sales (Hari Ini)</div>
                    <div class="card-body">
                        <p class="card-text mb-1">Jumlah SO: {{ $soCountHariIni }}</p>
                        <p class="card-title">Total Omset: {{ number_format($soTotalHariIni, 2) }}</p>
                    </div>
                </div>
            </div>
        @endif
          {{-- kartu per‑sales --}}
            @if(session('user_level') == 1)
            {{-- kalau sales, hanya kartu untuk dirinya sendiri --}}
            @php
                $me = session('username');
                $info = $soBySales[$me] ?? ['count'=>0,'total'=>0];
            @endphp
            <div class="col-md-3">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-header">SO {{ $me }} (Hari Ini)</div>
                    <div class="card-body">
                        <p class="card-text mb-1">Jumlah SO: {{ $info['count'] }}</p>
                        <h5 class="card-title">Omset: {{ number_format($info['total'], 2) }}</h5>
                    </div>
                </div>
            </div>
              {{-- kartu outlet hari ini --}}
    <div class="col-md-6">
        <div class="card text-dark bg-light mb-3">
            <div class="card-header">Outlet Kunjungan {{ $me }} ({{ \Carbon\Carbon::now()->format('d-m-Y') }})</div>
            <div class="card-body">
                @if($todayOutlets->isEmpty())
                    <p class="card-text">Tidak ada kunjungan hari ini.</p>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($todayOutlets as $outlet)
                            <li class="list-group-item">{{ $outlet }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
        @else
           <div class="col-md-3">
            <div class="card text-white bg-info mb-3">
                <div class="card-header">Penjualan Hari Ini</div>
                <div class="card-body">
                    <h5 class="card-title">{{ number_format($penjualanHariIni, 2) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success mb-3">
                <div class="card-header">Penjualan Cash Hari Ini</div>
                <div class="card-body">
                    <h5 class="card-title">{{ number_format($penjualanCashHariIni, 2) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3">
                <div class="card-header">Total Penjualan Bulan Ini</div>
                <div class="card-body">
                    <h5 class="card-title">{{ number_format($penjualanBulanIni, 2) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning mb-3">
                <div class="card-header">Total Piutang</div>
                <div class="card-body">
                    <h5 class="card-title">{{ number_format($totalPiutang, 2) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-danger mb-3">
                <div class="card-header">Total Hutang</div>
                <div class="card-body">
                    <h5 class="card-title">{{ number_format($totalHutang, 2) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-danger mb-3">
                <div class="card-header">Nilai Stok</div>
                <div class="card-body">
                    <h5 class="card-title">{{ number_format($totalNilaiStok, 2) }}</h5>
                </div>
            </div>
        </div>
            {{-- kalau admin/spv, tampil semua sales di array --}}
            @foreach($soBySales as $kode => $info)
                <div class="col-md-3">
                    <div class="card text-white bg-warning mb-3">
                        <div class="card-header">SO {{ $kode }} (Hari Ini)</div>
                        <div class="card-body">
                            <p class="card-text mb-1">Jumlah SO: {{ $info['count'] }}</p>
                            <p class="card-title">Omset: {{ number_format($info['total'], 2) }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
   
    </div>
</div>

@endsection
