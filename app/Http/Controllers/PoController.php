<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Po;
use App\Models\PoDetail;
use App\Models\Pelanggan;
use App\Models\Barang;
use App\Models\MasterBarang;
use App\Models\Salesman;
use App\Models\Supplier;
use PDF;
use Illuminate\Support\Facades\Log;


class poController extends Controller
{
    public function index()
    {
       
        // Ambil semua data PO dengan relasi PoDetails (jika diperlukan)
    $po = Po::with('PoDetails')->get();

    // Ambil daftar PO yang belum diproses (asumsikan status "belum diproses" disimpan sebagai 0)
    $poList = Po::where('status', 0)->get();
        $pelangganList = Pelanggan::all();
    $barangList = Barang::all();
    $masterbarangList = MasterBarang::all();
    $salesmanList = Salesman::all();
    $supplierList = Supplier::all(); // Ambil daftar supplier
      $userLevel = session('user_level');
    $userSales = session('username');      // atau simpan kode_sales di session
    
    if ($poList->isEmpty()) {
        // Anda bisa memilih untuk menampilkan view dengan pesan kosong, bukan mengembalikan response JSON
          return view('penjualan.po', [
            'po'               => $po,
            'poList'           => $poList,
            'pelangganList'    => $pelangganList,
            'barangList'       => $barangList,
            'masterbarangList' => $masterbarangList,
            'salesmanList'     => $salesmanList,
            'supplierList'     => $supplierList,
            'userLevel'        => $userLevel,
            'userSales'        => $userSales,
            'userLevel' => $userLevel,   // <-- tambahkan ini
        ])->with('error', 'Data PO tidak ditemukan.');
    }
    
    
    // Ambil data lainnya
    // $pelangganList = Pelanggan::all();
    // $barangList = Barang::all();
    // $masterbarangList = MasterBarang::all();
    // $salesmanList = Salesman::all();
    // $supplierList = Supplier::all(); // Ambil daftar supplier
       return view('penjualan.po', [
        'po'               => $po,
        'poList'           => $poList,
        'pelangganList'    => $pelangganList,
        'barangList'       => $barangList,
        'masterbarangList' => $masterbarangList,
        'salesmanList'     => $salesmanList,
        'supplierList'     => $supplierList,
        'userLevel'        => $userLevel,
        'userSales'        => $userSales,
    ]);
}

   public function store(Request $request)
{
    $data = $request->validate([
        'kode_sales' => 'required',
        'nama_salesman' => 'required',
        'kode_pelanggan' => 'required',
        'created_at' => 'required',
        'nama_pelanggan' => 'required',
        
        'items' => 'required|array',
        'items.*.kode_barang' => 'required|exists:barang,kode_barang',
        'items.*.nama_barang' => 'required',
        'items.*.harga' => 'required|numeric',
        'items.*.quantity' => 'required|integer|min:1',
        'items.*.jumlah' => 'required|numeric',
    ]);
    $items = $data['items'];
    $usedKodeBarang = [];

    foreach ($items as $item) {
        if (in_array($item['kode_barang'], $usedKodeBarang)) {
            return redirect()->back()->withErrors(['msg' => 'Kode barang ' . $item['kode_barang'] . ' sudah ada dalam daftar!'])->withInput();
        }
        $usedKodeBarang[] = $item['kode_barang'];
    }

    // Simpan data PO
    $po = Po::create([
        'kode_sales' => $data['kode_sales'],
        'nama_sales' => $data['nama_salesman'],
        'kode_pelanggan' => $data['kode_pelanggan'],
        'created_at' => $data['created_at'],
        'status' => 0,
        'nama_pelanggan' => $data['nama_pelanggan'],
        'total' => array_sum(array_column($data['items'], 'jumlah')),
    ]);

    foreach ($data['items'] as $item) {
        $barang = Barang::where('kode_barang', $item['kode_barang'])->first();

        if (!$barang) {
            return redirect()->back()->withErrors(['error' => 'Barang tidak ditemukan.'])->withInput();
        }

        // Hitung dus, lusin, pcs berdasarkan quantity dan isi dus
        $isidus = $barang->isidus; // Ambil isi dus dari barang
        $quantity = $item['quantity'];

        $dus = intdiv($quantity, $isidus);
        $sisaPcs = $quantity % $isidus;

        $lusin = intdiv($sisaPcs, 12);
        $pcs = $sisaPcs % 12;

        // Simpan detail PO
        $po->podetails()->create([
            'kode_barang' => $item['kode_barang'],
            'nama_barang' => $item['nama_barang'],
            'harga' => $item['harga'],
            'quantity' => $item['quantity'],
            'jumlah' => $item['jumlah'],
            'dus' => $dus,
            'lusin' => $lusin,
            'pcs' => $pcs,
        ]);
    }

    if ($request->action === 'save_and_print') {
        return redirect()->route('po.cetak', $po->id);
    }

    return redirect()->route('po.index')->with('success', 'SO berhasil disimpan dengan No SO: ' . $po->id);
}
    public function cetak($id)
        {
            // Ambil data po berdasarkan ID
            $po = po::with('details')->findOrFail($id);
  
            // Kirim data ke view cetak
            return view('po.cetak', compact('po'));
        }
        public function cetakPdf($id)
        {
            $po = po::with('details')->findOrFail($id);
        
            // Generate PDF dari view 'po.cetak'
            $pdf = PDF::loadView('po.cetak', compact('po'))
            ->setPaper('a4', 'portrait'); // Pilih ukuran dan orientasi kertas;
        
            // Unduh file PDF
            return $pdf->download('nota_pembelian.pdf');
        }
        
    
        public function daftar(Request $request)
        {
             $userLevel = (int) session('user_level');
             $userSales = session('username'); // kode_sales si sales
            
        
           // Default hari ini
    if (! $request->filled('tanggal_dari') && ! $request->filled('tanggal_sampai')) {
        $today = now()->format('Y-m-d');
        $request->merge([
            'tanggal_dari'   => $today,
            'tanggal_sampai' => $today,
        ]);
    }
        $query = po::query();
         // Filter tgl
    if ($request->filled('tanggal_dari') && $request->filled('tanggal_sampai')) {
        $query->whereBetween('created_at', [
            $request->tanggal_dari . ' 00:00:00',
            $request->tanggal_sampai . ' 23:59:59',
        ]);
    }
       // Jika admin/spv (level ≥2), boleh filter manual kode_sales
    if ($userLevel >= 2) {
        if ($request->filled('kode_sales')) {
            $query->where('kode_sales', $request->kode_sales);
        }
    }
    // Jika sales (level 1), paksa hanya miliknya sendiri
    else {
        $query->where('kode_sales', $userSales);
        // agar form menunjukkan si sales-nya
        $request->merge(['kode_sales' => $userSales]);
    }
            // Filter berdasarkan kode pelanggan
            if ($request->filled('kode_pelanggan')) {
                $query->where('kode_pelanggan', $request->kode_pelanggan);
            }
                  
        
            $po = $query->orderBy('created_at', 'desc')->get();
        
           return view('penjualan.daftarso', [
                'po'        => $po,
                'userLevel' => $userLevel,   // <-- tambahkan ini
]);
        }

        // public function show($poId)
        // {
        //     // Mengambil PO beserta detailnya
        //     $po = Po::with('poDetails')->find($poId);
        
        //     if (!$po) {
        //         return response()->json(['message' => 'PO not found'], 404);
        //     }
        
        //     return response()->json($po);
        // }
        public function show($poId)
{
    $po = Po::with('poDetails')->where('id', $poId)->first();
    $pelangganList = Pelanggan::all(); // Ambil daftar supplier

    Log::info('Fetching PO Data', ['id' => $poId]);
   
    if (!$po) {
        return response()->json(['message' => 'PO tidak ditemukan'], 404);
    }

    return response()->json([
        'kode_pelanggan' => $po->kode_pelanggan,
        'nama_pelanggan' => $po->nama_pelanggan,
        'alamat' => $po->pelanggan->alamat,
        'telepon' => $po->pelanggan->telepon,
        'status' => $po->status,
        'po_details' => $po->poDetails->map(function ($detail) {
            return [
                'id' => $detail->id,
                'po_id' => $detail->po_id,
                'kode_barang' => $detail->kode_barang,
                'nama_barang' => $detail->nama_barang,
                'harga' => $detail->harga,
                'isidus' => $detail->barang->isidus ?? 1, // Ambil dari relasi barang
                'stok' => $detail->barang->stok,
                'dus' => $detail->dus,
                'lusin' => $detail->lusin,
                'pcs' => $detail->pcs,
                'quantity' => $detail->quantity,
                'jumlah' => $detail->jumlah,
            ];
        }),
    ]);
}

public function destroy($id)
{
    $po = Po::findOrFail($id);

    if ($po->status == 1) {
        return response()->json(['error' => 'Data tidak bisa dihapus karena sudah diproses PO.'], 403);
    }

    $po->delete();
    return response()->json(['success' => 'Data berhasil dihapus.']);
}

// public function edit($id)
// {
//     $po = Po::with('poDetails')->findOrFail($id); // Ambil PO beserta itemnya
//     $pelangganList = Pelanggan::all();
    
//     $salesmanList       = Salesman::all(); 
//     // Cek apakah PO memiliki detail barang
//     $barangItem = null;
//     if ($po->poDetails->isNotEmpty()) {
//         $barangItem = $po->poDetails->first()->barang;
//     }

//     // Pastikan barangItem adalah objek yang valid
//     if ($barangItem) {
//         // Lakukan sesuatu dengan barangItem
//     }

//     return view('penjualan.po-edit', compact('po', 'barangItem', 'pelangganList', 'salesmanList'));
// }




// public function update(Request $request, $id)
// {
//     // Validasi data yang diterima
//     $validatedData = $request->validate([
//         'kode_pelanggan' => 'required',
//         'nama_pelanggan' => 'required',
//         'items' => 'required|array',
//         'items.*.kode_barang' => 'required|exists:barang,kode_barang',
//         'items.*.nama_barang' => 'required',
//         'items.*.harga' => 'required|numeric',
//         'items.*.quantity' => 'required|integer|min:1',
//         'items.*.jumlah' => 'required|numeric',
//         // Validasi lainnya jika diperlukan
//     ]);

//     // Update data PO di tabel
   
//     $po = Po::findOrFail($id);
//     $po->kode_pelanggan = $request->kode_pelanggan;
//     $po->nama_pelanggan = $request->nama_pelanggan;
//     $po->total = $request->total;
//     // Update data PO lainnya sesuai kebutuhan

//     $po->save();

//     // Update data items yang terkait dengan PO
//     foreach ($data['items'] as $item) {
//         $barang = Barang::where('kode_barang', $item['kode_barang'])->first();

//         if (!$barang) {
//             return redirect()->back()->withErrors(['error' => 'Barang tidak ditemukan.'])->withInput();
//         }

//         // Hitung dus, lusin, pcs berdasarkan quantity dan isi dus
//         $isidus = $barang->isidus; // Ambil isi dus dari barang
//         $quantity = $item['quantity'];

//         $dus = intdiv($quantity, $isidus);
//         $sisaPcs = $quantity % $isidus;

//         $lusin = intdiv($sisaPcs, 12);
//         $pcs = $sisaPcs % 12;

//         // Simpan detail PO
//         $po->podetails()->update([
//             'kode_barang' => $item['kode_barang'],
//             'nama_barang' => $item['nama_barang'],
//             'harga' => $item['harga'],
//             'quantity' => $item['quantity'],
//             'jumlah' => $item['jumlah'],
//             'dus' => $dus,
//             'lusin' => $lusin,
//             'pcs' => $pcs,
//         ]);
//     }

//     // Redirect atau return response sesuai kebutuhan
//     return redirect()->route('po.index')->with('success', 'PO berhasil diperbarui!');
// }
public function processStep2(Request $request)
{
    $selectedPoIds = $request->input('selected_po');
    if(empty($selectedPoIds)) {
        return redirect()->back()->with('error', 'Harap pilih setidaknya satu PO.');
    }
    
    // Ambil PO beserta detail item-nya (misal dengan relasi poDetails)
    $poDetails = Po::with('poDetails')->whereIn('id', $selectedPoIds)->get();
    
    // Aggregasi item berdasarkan kode_barang
    $aggregatedItems = [];
    foreach($poDetails as $po) {
        foreach($po->poDetails as $item) {
            $kode = $item->kode_barang;
            if(!isset($aggregatedItems[$kode])) {
                $aggregatedItems[$kode] = [
                    'kode_barang' => $kode,
                    'nama_barang' => $item->nama_barang,
                    'total_dus'   => 0,
                    'total_lsn'   => 0,
                    'total_pcs'   => 0,
                    // Ambil stok dari data barang (misal via model Item) atau simpan di detail
                    'stok'        => $item->stok,
                ];
            }
            $aggregatedItems[$kode]['total_dus'] += $item->dus;
            $aggregatedItems[$kode]['total_lsn'] += $item->lsn;
            $aggregatedItems[$kode]['total_pcs'] += $item->pcs;
        }
    }
    
    // Kirim data aggregated beserta PO yang dipilih ke view step 2
    return view('po_process_step2', [
        'aggregatedItems' => $aggregatedItems,
        'selectedPoIds'   => $selectedPoIds,
        'poDetails'       => $poDetails, // jika diperlukan untuk referensi
    ]);
}
public function search(Request $r)
{
    $sales = $r->get('salesman', '');
    $query = Pelanggan::query();
    if ($sales) {
        $query->where('kode_sales', $sales);
    }
    // Jika ada parameter `q` untuk live‐search, bisa ditambahkan:
    // if ($r->filled('q')) { … }
    return response()->json($query->limit(20)->get());
}

public function selectSales()
{
$salesmanList = Salesman::all();
return view('penjualan.select-sales', compact('salesmanList'));
}
// Handle selection and redirect to PO form
public function handleSelectSales(Request $request)
{
    $request->validate(['kode_sales' => 'required']);
    return redirect()->route('po.create', ['sales' => $request->kode_sales]);
}

// Show PO creation form with selected sales
public function create(Request $request)
{
    $selectedSales = $request->get('sales');
    $salesmanList  = Salesman::all();
    // pass selectedSales to filter pelanggan
    return view('penjualan.po', compact('salesmanList', 'selectedSales'));
}

// store() as before...
}

