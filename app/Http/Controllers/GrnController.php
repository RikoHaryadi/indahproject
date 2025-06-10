<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Grn;
use App\Models\GrnDetail;
use App\Models\Supplier;
use App\Models\Barang;
use App\Models\MasterBarang;
use PDF;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

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
                $query->whereBetween('date', [
                    $request->tanggal_dari . ' 00:00:00',
                    $request->tanggal_sampai . ' 23:59:59'
                ]);
            }
        
            // Filter berdasarkan kode pelanggan
            if ($request->filled('kode_suplier')) {
                $query->where('kode_suplier', $request->kode_suplier);
            }
        
            $grn = $query->orderBy('date', 'desc')->get();
        
            return view('grn.daftargrn', compact('grn'));
        }
        public function formImport()
{
    return view('grn.import');
}

public function import(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:csv,txt',
    ]);

    $file = $request->file('file');
    $path = $file->getRealPath();

    $rows = array_map('str_getcsv', file($path));
    
    // Ubah delimiter ke ';' (karena file Anda pakai titik koma)
    $rows = array_map(function($row) {
        return str_getcsv(implode(';', $row), ';');
    }, $rows);

    if (count($rows) < 1) {
        return back()->with('error', 'File CSV kosong atau tidak terbaca.');
    }

   $header = array_shift($rows); // Buang baris header
    $firstRow = $rows[0]; // Ambil data baris pertama setelah header

    // Validasi jumlah kolom minimal
    if (count($firstRow) < 9) {
        return back()->with('error', 'Format CSV tidak sesuai. Pastikan semua kolom tersedia.');
    }

    $noinvoice     = trim($firstRow[0]);
    $dateInput     = trim($firstRow[1]);
    $kode_suplier  = trim($firstRow[2]);
    $nama_suplier  = trim($firstRow[3]);

try {
    $date = Carbon::parse($dateInput)->format('Y-m-d');
} catch (\Exception $e) {
    return back()->with('error', 'Format tanggal tidak valid.');
}
    $total = 0;
    $details = [];

    // Validasi kode_barang
    foreach ($rows as $index => $row) {
        $kode_barang = trim($row[4]);

        if (!Barang::where('Kode_barang', $kode_barang)->exists()) {
            return back()->with('error', "Kode barang '$kode_barang' pada baris ke-" . ($index+1) . " tidak tersedia di database.");
        }

        $jumlah = (float) str_replace(',', '', $row[8]);
        $total += $jumlah;

        $details[] = [
            'kode_barang'  => trim($row[4]),
            'nama_barang'  => trim($row[5]),
            'harga'        => (float) str_replace(',', '', $row[6]),
            'quantity'     => (int) $row[7],
            'jumlah'       => $jumlah,
        ];
    }

    // Simpan ke database
    DB::beginTransaction();
    try {
        $grn = Grn::create([
            'noinvoice'     => $noinvoice,
            'kode_suplier'  => $kode_suplier,
            'nama_suplier'  => $nama_suplier,
            'total'         => $total,
            'date'          => $date,
        ]);

        foreach ($details as $detail) {
            Grndetail::create([
                'grn_id'       => $grn->id,
                'kode_barang'  => $detail['kode_barang'],
                'nama_barang'  => $detail['nama_barang'],
                'harga'        => $detail['harga'],
                'quantity'     => $detail['quantity'],
                'jumlah'       => $detail['jumlah'],
            ]);
             Barang::where('Kode_barang', $detail['kode_barang'])->increment('stok', $detail['quantity']);
        }

        DB::commit();
        return back()->with('success', 'Impor GRN berhasil.');
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
    }
}

//// Jangan Ditembak ////
public function uploadTemplateGRN()
{
    $path = storage_path('app/public/template_import_grn.csv');

    // Cek jika file belum ada, baru buat
    if (!File::exists($path)) {
        $header = "kode_suplier,nama_suplier,kode_barang,nama_barang,harga,quantity,jumlah\n";
        File::put($path, $header);
    }

    return response()->download($path);
}
}
