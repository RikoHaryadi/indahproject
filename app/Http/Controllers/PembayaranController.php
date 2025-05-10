<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembayaran;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Pelanggan;
use App\Models\Bukubesar;

class PembayaranController extends Controller
{
    public function index()
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

    return view('pembayaran.index', compact('data'));
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
        Bukubesar::create([
            'kode_akun' => '4-10001',
            'nama_akun' => 'PELUNASAN',
            'debet' => $data['pembayaran'],
            'uraian' => 'Pelunasan Toko, ' . $data['nama_pelanggan'],
        ]);

        return redirect()->route('pembayaran.index')->with('success', 'Pembayaran berhasil disimpan.');
    }

    public function daftar(Request $request)
    {
        $query = Penjualan::query();
    
        // Filter berdasarkan tanggal
        if ($request->filled('tanggal_dari') && $request->filled('tanggal_sampai')) {
            $query->whereBetween('created_at', [
                $request->tanggal_dari . ' 00:00:00',
                $request->tanggal_sampai . ' 23:59:59'
            ]);
        }
    
        // Filter berdasarkan kode pelanggan
        if ($request->filled('kode_pelanggan')) {
            $query->where('kode_pelanggan', $request->kode_pelanggan);
        }
    
        $penjualan = $query->orderBy('created_at', 'desc')->get();
    
        return view('pembayaran.daftarpiutang', compact('penjualan'));
    }
}
