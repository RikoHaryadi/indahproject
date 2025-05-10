<?php

namespace App\Http\Controllers;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Pelanggan;
use App\Models\Barang;
use App\Models\Grn;

use Illuminate\Http\Request;

class RekapController extends Controller
{
    public function rekapBarang()
    {
        // Mengambil data penjualan detail dan mengelompokkan berdasarkan kode barang
        $barang = PenjualanDetail::all()->groupBy('kode_barang')->map(function ($items) {
            $totalQuantity = $items->sum('quantity');
            $isiDus = $items->first()->barang->isidus ?? 12; // Ambil isi_dus dari relasi barang (default: 1)

            $dus = intdiv($totalQuantity, $isiDus);
            $sisa = $totalQuantity % $isiDus;
            $lusin = intdiv($sisa, 12);
            $pcs = $sisa % 12;

            return [
                'kode_barang' => $items->first()->kode_barang,
                'nama_barang' => $items->first()->nama_barang,
                'total_quantity' => $totalQuantity,
                'isi' => $isiDus,
                'dus' => $dus,
                'lusin' => $lusin,
                'pcs' => $pcs,
            ];
        });

        return view('rekap.barang', compact('barang'));
    }
    public function rekapFaktur()
    {
        // Mengambil semua data faktur
        $faktur = Penjualan::all();
        $totalNilaiFaktur = $faktur->sum('total');

        return view('rekap.faktur', compact('faktur', 'totalNilaiFaktur'));
    }


    public function pilihFaktur()
    {
        // Ambil semua faktur
        $faktur = Penjualan::all();
        return view('rekap.pilih-faktur', compact('faktur'));
    }

    public function prosesRekap(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'faktur_ids' => 'required|array',
            'faktur_ids.*' => 'exists:penjualan,id',
        ]);

        // Ambil faktur yang dipilih
        $fakturTerpilih = Penjualan::whereIn('id', $validated['faktur_ids'])->get();

        // Rekap barang berdasarkan faktur yang dipilih
        $barang = PenjualanDetail::whereIn('penjualan_id', $validated['faktur_ids'])
            ->get()
            ->groupBy('kode_barang')
            ->map(function ($items) {
                $totalQuantity = $items->sum('quantity');
                $isiDus = $items->first()->barang->isidus ?? 12;

                $dus = intdiv($totalQuantity, $isiDus);
                $sisa = $totalQuantity % $isiDus;
                $lusin = intdiv($sisa, 12);
                $pcs = $sisa % 12;

                return [
                    'kode_barang' => $items->first()->kode_barang,
                    'nama_barang' => $items->first()->nama_barang,
                    'total_quantity' => $totalQuantity,
                    'isi' => $isiDus,
                    'dus' => $dus,
                    'lusin' => $lusin,
                    'pcs' => $pcs,
                ];
            });

        // Total nilai faktur
        $totalNilaiFaktur = $fakturTerpilih->sum('total');

        return view('rekap.hasil-rekap', compact('fakturTerpilih', 'barang', 'totalNilaiFaktur'));
    }
}
