<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Barang;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; 
use App\Models\Pelanggan;
use App\Models\Piutang;

class PenjualanImportController extends Controller
{
    public function showForm()
    {
        return view('penjualan.import');
    }

    private function parseNumber(string $s): float
    {
        $s = trim($s);
        if (strpos($s, ',') !== false && strpos($s, '.') === false) {
            $s = str_replace('.', '', $s);
            $s = str_replace(',', '.', $s);
        }
        return floatval($s);
    }

    public function importCsv(Request $request)
    {
        ini_set('max_execution_time', 300);
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt'
        ]);

        $path = $request->file('csv_file')->getRealPath();
        $lines = file($path);
        $firstLine = $lines[0] ?? '';
        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';

        $handle = fopen($path, 'r');
        $header = fgetcsv($handle, 0, $delimiter);
        $rows = [];
        $barisKe = 2;
        $errors = [];

        $requiredFields = ['INVNumber', 'INVDate', 'SKUCode', 'ProductName', 'HARGA/DUS', 'DUS', 'LSN', 'PCS', 'TotalQuantity(PCS)', 'Discount', 'GROSS', 'DISCX', 'PPN', 'Net', 'Outlet', 'Outlet Name', 'Salesman', 'SalesmaneNama'];

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (count($row) !== count($header)) {
                $errors[] = "Baris ke-$barisKe: Jumlah kolom tidak sesuai.";
                $barisKe++;
                continue;
            }
            $assoc = array_combine($header, $row);
            $missingFields = [];
            foreach ($requiredFields as $field) {
                if (!isset($assoc[$field]) || trim($assoc[$field]) === '') {
                    $missingFields[] = $field;
                }
            }
            if (!empty($missingFields)) {
                $errors[] = "Baris ke-$barisKe: Kolom kosong: " . implode(', ', $missingFields);
                $barisKe++;
                continue;
            }
            $rows[] = $assoc;
            $barisKe++;
        }
        fclose($handle);

        if (!empty($errors)) {
            return redirect()->route('penjualan.import.form')->withErrors($errors);
        }

        $grouped = collect($rows)->groupBy('INVNumber');
        $missingPelanggan = [];
        $missingBarang = [];

        foreach ($grouped as $inv => $items) {
            $firstRow = $items->first();
            $kodePelangganCsv = trim($firstRow['Outlet'] ?? '');
            if (!Pelanggan::where('kode_pelanggan', $kodePelangganCsv)->exists()) {
                $missingPelanggan[] = $kodePelangganCsv;
            }
            foreach ($items as $i) {
                $kodeBarangCsv = trim($i['SKUCode'] ?? '');
                if (!Barang::where('kode_barang', $kodeBarangCsv)->exists()) {
                    $missingBarang[] = $kodeBarangCsv;
                }
            }
        }

        $missingPelanggan = array_filter(array_unique($missingPelanggan));
        $missingBarang = array_filter(array_unique($missingBarang));

        if (count($missingPelanggan) > 0 || count($missingBarang) > 0) {
            $messages = [];
            if (count($missingPelanggan) > 0) {
                $messages[] = 'Kode Pelanggan berikut tidak ditemukan: ' . implode(', ', $missingPelanggan);
            }
            if (count($missingBarang) > 0) {
                $messages[] = 'Kode Barang berikut tidak ditemukan: ' . implode(', ', $missingBarang);
            }
            return redirect()->back()->withErrors($messages)->withInput();
        }

        DB::transaction(function () use ($grouped) {
            foreach ($grouped as $inv => $items) {
                $first = $items->first();
                $tgl = Carbon::createFromFormat('d/m/Y', $first['INVDate'])->format('Y-m-d');
                $sumGross = $items->sum(fn ($i) => $this->parseNumber($i['GROSS']));
                $sumDisc = $items->sum(fn ($i) => $this->parseNumber($i['DISCX']));
                $sumPpn = $items->sum(fn ($i) => $this->parseNumber($i['PPN']));
                $sumNet = $items->sum(fn ($i) => $this->parseNumber($i['Net']));

                $pj = Penjualan::create([
                    'id_faktur' => $inv,
                    'kode_sales' => $first['Salesman'],
                    'nama_sales' => $first['SalesmaneNama'],
                    'kode_pelanggan' => $first['Outlet'],
                    'nama_pelanggan' => $first['Outlet Name'],
                    'total_discount' => $sumDisc,
                    'total' => $sumNet,
                    'created_at' => $tgl,
                    'updated_at' => now(),
                ]);

                Piutang::create([
                    'id_faktur' => $inv,
                    'kode_pelanggan' => $first['Outlet'],
                    'nama_pelanggan' => $first['Outlet Name'],
                    'total' => $sumNet,
                    'pembayaran' => 0,
                    'sisapiutang' => $sumNet,
                    'created_at' => $tgl,
                    'updated_at' => now(),
                ]);

                foreach ($items as $i) {
                    $barang = Barang::where('kode_barang', $i['SKUCode'])->first();
                    $masterIsidus = $barang ? intval($barang->isidus) : 1;
                    $qty = intval($i['TotalQuantity(PCS)']);
                    $disc1 = $this->parseNumber($i['Discount']);
                    $net = $this->parseNumber($i['Net']);

                    PenjualanDetail::create([
                        'penjualan_id' => $pj->id,
                        'kode_barang' => $i['SKUCode'],
                        'nama_barang' => $i['ProductName'],
                        'harga' => $this->parseNumber($i['HARGA/DUS']),
                        'dus' => intval($i['DUS']),
                        'lusin' => intval($i['LSN']),
                        'pcs' => intval($i['PCS']),
                        'isidus' => $masterIsidus,
                        'quantity' => $qty,
                        'disc1' => 0,
                        'disc2' => 0,
                        'disc3' => 0,
                        'disc4' => $disc1,
                        'jumlah' => $net,
                        'created_at' => $tgl,
                        'updated_at' => now(),
                    ]);

                    $barang->decrement('stok', $qty);
                }
            }
        });

        return back()->with('success', 'Import CSV berhasil!');
    }
}
