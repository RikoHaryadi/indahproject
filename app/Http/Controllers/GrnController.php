<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Grn;
use App\Models\GrnDetail;
use App\Models\Supplier;
use App\Models\Barang;
use App\Models\MasterBarang;
use PDF;

class GrnController extends Controller
{
    public function index()
    {
        $grn = grn::with('details')->get(); // Ambil semua data grn beserta detailnya
        $supplierList = Supplier::all(); // Ambil daftar supplier
        $barangList = Barang::all(); // Ambil daftar supplier
        $masterbarangList = MasterBarang::all(); // Ambil daftar supplier
        return view('grn.index', compact('grn', 'supplierList', 'barangList', 'masterbarangList')); // Kirim data ke view
    }

    public function store(Request $request)
    {
        
        // foreach ($request->items as $item) {
        //     $exists = \App\Models\Barang::where('kode_barang', $item['kode_barang'])->exists();
        //     if (!$exists) {
        //         dd("Kode barang {$item['kode_barang']} tidak ditemukan di tabel barang.");
        //     }
    //     \Log::info('Request Data:', $request->all()); // Log data request
    // \DB::enableQueryLog(); // Mengaktifkan log query
        $data = $request->validate([
            'kode_suplier' => 'required',
            'nama_suplier' => 'required',
            'items' => 'required|array',
            'items.*.kode_barang' => 'required|exists:masterbarang,kode_barang',
            'items.*.nama_barang' => 'required',
            'items.*.harga' => 'required|numeric',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.jumlah' => 'required|numeric',
        ]);
        // dd(\DB::getQueryLog());
    
       
    
        // Simpan data penjualan
        $grn = Grn::create([
            'kode_suplier' => $data['kode_suplier'],
            'nama_suplier' => $data['nama_suplier'],
            'total' => array_sum(array_column($data['items'], 'jumlah')),
        ]);
    
        foreach ($data['items'] as $item) {
            $barang = Barang::where('kode_barang', $item['kode_barang'])->first();
    

              // Hitung dus, lusin, pcs berdasarkan quantity dan isi dus
        
            // Kurangi stok barang
            $barang->stok += $item['quantity'];
            $isidus = $barang->isidus; // Ambil isi dus dari barang
            $quantity = $item['quantity'];

            $dus = intdiv($quantity, $isidus);
            $sisaPcs = $quantity % $isidus;

            $lusin = intdiv($sisaPcs, 12);
            $pcs = $sisaPcs % 12;
            $barang->stok_dus = $dus;
            $barang->stok_lsn = $lusin;
            $barang->stok_pcs = $pcs;
            $barang->save();
    
            $grn->details()->create($item);
        }
    
        if ($request->action === 'save_and_print') {
            return redirect()->route('grn.cetak', $grn->id);
        }
    
        return redirect()->route('grn.index')->with('success', 'Penjualan berhasil disimpan.');
    }
    public function cetak($id)
        {
            // Ambil data GRN berdasarkan ID
            $grn = Grn::with('details')->findOrFail($id);

            // Kirim data ke view cetak
            return view('grn.cetak', compact('grn'));
        }
        public function cetakPdf($id)
        {
            $grn = Grn::with('details')->findOrFail($id);
        
            // Generate PDF dari view 'grn.cetak'
            $pdf = PDF::loadView('grn.cetak', compact('grn'))
            ->setPaper('a4', 'portrait'); // Pilih ukuran dan orientasi kertas;
        
            // Unduh file PDF
            return $pdf->download('nota_pembelian.pdf');
        }
        
    
        public function daftar(Request $request)
        {
            $query = Grn::query();
        
            // Filter berdasarkan tanggal
            if ($request->filled('tanggal_dari') && $request->filled('tanggal_sampai')) {
                $query->whereBetween('created_at', [
                    $request->tanggal_dari . ' 00:00:00',
                    $request->tanggal_sampai . ' 23:59:59'
                ]);
            }
        
            // Filter berdasarkan kode pelanggan
            if ($request->filled('kode_suplier')) {
                $query->where('kode_suplier', $request->kode_suplier);
            }
        
            $grn = $query->orderBy('created_at', 'desc')->get();
        
            return view('grn.daftargrn', compact('grn'));
        }
}
