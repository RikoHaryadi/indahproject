<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Barang;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; 

class PenjualanImportController extends Controller
{
    public function showForm()
    {
        return view('penjualan.import');
    }

        private function parseNumber(string $s): float
    {
        // Hilangkan titik ribuan, ganti koma desimal dengan titik
        $clean = str_replace(['.', ','], ['', '.'], $s);
        return floatval($clean);
    }
     public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt'
        ]);

        $path = $request->file('csv_file')->getRealPath();
        $lines = file($path);

        // deteksi delimiter
        $first = $lines[0];
        $delimiter = substr_count($first, ';') > substr_count($first, ',') ? ';' : ',';

        $handle = fopen($path, 'r');
        $header = fgetcsv($handle, 0, $delimiter);

        // baca semua baris
        $rows = [];
        while ($row = fgetcsv($handle, 0, $delimiter)) {
            if (count($row) === count($header)) {
                $rows[] = array_combine($header, $row);
            }
        }
        fclose($handle);

        // group by INVNumber
        $grouped = collect($rows)->groupBy('INVNumber');

        DB::transaction(function() use ($grouped) {
            foreach ($grouped as $inv => $items) {
                // ambil baris pertama sebagai sumber header
                $first = $items->first();
                $tgl = Carbon::createFromFormat('d/m/Y', $first['INVDate'])->format('Y-m-d');

                // hitung total per faktur
                $sumGross = $items->sum(fn($i)=> $this->parseNumber($i['GROSS']));
                $sumDisc  = $items->sum(fn($i)=> $this->parseNumber($i['Discount']));
                $sumPpn   = $items->sum(fn($i)=> $this->parseNumber($i['PPN']));
                $sumNet   = $items->sum(fn($i)=> $this->parseNumber($i['Net']));

                // simpan penjualan (header)
                $pj = Penjualan::create([
                    'id_faktur'      => $inv,
                    'kode_sales'     => $first['Salesman'],
                    'nama_sales'     => $first['SalesmaneNama'],
                    'kode_pelanggan' => $first['Outlet'],
                    'nama_pelanggan' => $first['Outlet Name'],
                    'total_discount' => $sumDisc,
                    'total'          => $sumNet,
                    'created_at'     => $tgl,
                ]);

                // simpan detail
                foreach ($items as $i) {
                    $qty   = intval($i['DUS']) * intval($i['LSN']) * intval($i['PCS']); // atau sesuaikan
                    // parse harga per unit, net per row
                    $disc1 = $this->parseNumber($i['Discount']); 
                    $net   = $this->parseNumber($i['Net']);

                    PenjualanDetail::create([
                        'penjualan_id' => $pj->id,
                        'kode_barang'  => $i['SKUCode'],
                        'nama_barang'  => $i['ProductName'],
                        'harga'        => $this->parseNumber($i['HARGA/DUS']),
                        'dus'          => intval($i['DUS']),
                        'lusin'        => intval($i['LSN']),
                        'pcs'          => intval($i['PCS']),
                        'quantity'     => intval($i['TotalQuantity(PCS)']),
                        'disc1'        => $disc1,
                        'disc2'        => 0,
                        'disc3'        => 0,
                        'disc4'        => 0,
                        'jumlah'       => $net,
                        'created_at'   => now(),
                    ]);

                    // Kurangi stok (boleh minus)
                    Barang::where('kode_barang', $i['SKUCode'])
                        ->decrement('stok', intval($i['TotalQuantity(PCS)']));
                }
            }
        });

        return back()->with('success', 'Import CSV berhasil!');
    }

}
