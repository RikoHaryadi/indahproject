@extends('layout.mainlayout')
@section('title', 'Dashboard')
@section('content')
<div class="container">
    <h1>Dashboard</h1>
    <div class="row">
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
    </div>
</div>

@endsection
