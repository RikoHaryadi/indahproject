<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penjualan;
use App\Models\Pembayaran;
use App\Models\Pelanggan;
use App\Models\Grn; // Pastikan model GRN sudah ada
use App\Models\Barang;
use App\Models\Piutang;

class DashboardController extends Controller
{
    public function index(Request $request)
{
    // Jika belum login, redirect ke halaman login
    if (!$request->session()->has('logged_in')) {
        return redirect()->route('login');
    }
    
    // Penjualan hari ini
    $penjualanHariIni = Penjualan::whereDate('created_at', today())->sum('total');

    // Penjualan cash hari ini
    $penjualanCashHariIni = Pembayaran::whereDate('created_at', today())->sum('bayar');

    // Total penjualan bulan ini
    $penjualanBulanIni = Penjualan::whereMonth('created_at', date('m'))
        ->whereYear('created_at', date('Y'))
        ->sum('total');
        
    $barangList = Barang::all();
    $totalNilaiStok = $barangList->sum(function ($barang) {
        return $barang->nilairp * $barang->stok;
    });
    
    // Total piutang (sisa_piutang di table pembayaran)
    $penjualantotal = Penjualan::sum('total');
    $pembayarantotal = Piutang::sum('pembayaran');
    $Piutang = Piutang::sum('sisapiutang');
    $totalF = Piutang::sum('total');
    $totalPiutang = $totalF - $Piutang;
    // Total hutang (ambil dari table grn)
    $totalHutang = Grn::sum('total');

    return view('home', compact(
        'penjualanHariIni',
        'penjualanCashHariIni',
        'penjualanBulanIni',
        'totalPiutang',
        'totalHutang',
        'totalNilaiStok'
    ));
}

   
    public function piutang()
{
    $data = Pelanggan::all()->map(function ($pelanggan) {
        $totalHutang = Penjualan::where('kode_pelanggan', $pelanggan->Kode_pelanggan)
            ->sum('total') ?? 0; // Ganti 'total' jika nama kolom berbeda
        $totalPembayaran = Pembayaran::where('kode_pelanggan', $pelanggan->Kode_pelanggan)
            ->sum('pembayaran') ?? 0; // Ganti 'pembayaran' jika nama kolom berbeda
        $sisaPiutang = $totalHutang - $totalPembayaran;

        return [
            'kode_pelanggan' => $pelanggan->Kode_pelanggan,
            'nama_pelanggan' => $pelanggan->Nama_pelanggan,
            'total_hutang' => $totalHutang,
            'pembayaran' => $totalPembayaran,
            'sisa_piutang' => $sisaPiutang,
        ];
    })->filter(function ($item) {
        return $item['sisa_piutang'] > 0; // Hanya tampilkan pelanggan dengan sisa piutang
    });

    return view('home', compact('data'));
}



    public function store(Request $request)
    {
        $data = $request->validate([
            'kode_pelanggan' => 'required',
            'nama_pelanggan' => 'required',
            'total_hutang' => 'required|numeric',
            'pembayaran' => 'required|numeric|min:0',
        ]);

        $sisaPiutang = $data['total_hutang'] - $data['pembayaran'];

        Pembayaran::create([
            'kode_pelanggan' => $data['kode_pelanggan'],
            'nama_pelanggan' => $data['nama_pelanggan'],
            'total_hutang' => $data['total_hutang'],
            'pembayaran' => $data['pembayaran'],
            'sisa_piutang' => $sisaPiutang,
        ]);

        return redirect()->route('pembayaran.index')->with('success', 'Pembayaran berhasil disimpan.');
    }
}
