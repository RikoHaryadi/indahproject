<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Barang;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; 
use App\Models\Pelanggan;

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
        $firstLine = $lines[0] ?? '';
        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';

        $handle = fopen($path, 'r');
        $header = fgetcsv($handle, 0, $delimiter);

        // baca semua baris ke dalam array asosiatif
        $rows = [];
        while ($row = fgetcsv($handle, 0, $delimiter)) {
            if (count($row) === count($header)) {
                $rows[] = array_combine($header, $row);
            }
        }
        fclose($handle);

        // group by INVNumber
        $grouped = collect($rows)->groupBy('INVNumber');

        // ----- VALIDASI MASTER PELANGGAN DAN MASTER BARANG -----
        $missingPelanggan = [];
        $missingBarang    = [];

        foreach ($grouped as $inv => $items) {
            // Cek master Pelanggan berdasarkan kolom 'Outlet' di baris pertama
            $firstRow = $items->first();
            $kodePelangganCsv = trim($firstRow['Outlet'] ?? '');

            if (! Pelanggan::where('kode_pelanggan', $kodePelangganCsv)->exists()) {
                $missingPelanggan[] = $kodePelangganCsv;
            }

            // Cek setiap baris item, kode_barang ("SKUCode")
            foreach ($items as $i) {
                $kodeBarangCsv = trim($i['SKUCode'] ?? '');

                if (! Barang::where('kode_barang', $kodeBarangCsv)->exists()) {
                    $missingBarang[] = $kodeBarangCsv;
                }
            }
        }

        // Hilangkan duplikasi dan kosongkan string
        $missingPelanggan = array_filter(array_unique($missingPelanggan));
        $missingBarang    = array_filter(array_unique($missingBarang));

        // Kalau ada yang tidak ketemu di master, hentikan dan kembalikan error
        if (count($missingPelanggan) > 0 || count($missingBarang) > 0) {
            $messages = [];

            if (count($missingPelanggan) > 0) {
                $messages[] = 'Kode Pelanggan berikut tidak ditemukan di master: ' 
                              . implode(', ', $missingPelanggan) . '.';
            }
            if (count($missingBarang) > 0) {
                $messages[] = 'Kode Barang berikut tidak ditemukan di master: ' 
                              . implode(', ', $missingBarang) . '.';
            }

            // Kembalikan ke halaman import dengan pesan error
            return redirect()
                    ->back()
                    ->withErrors($messages)
                    ->withInput();
        }

        // ----- PROSES IMPORT JIKA SEMUA VALID =====
        DB::transaction(function() use ($grouped) {
            foreach ($grouped as $inv => $items) {
                // ambil baris pertama sebagai sumber header
                $first = $items->first();
                $tgl = Carbon::createFromFormat('d/m/Y', $first['INVDate'])->format('Y-m-d');

                // hitung total per faktur
                $sumGross = $items->sum(fn($i) => $this->parseNumber($i['GROSS']));
                $sumDisc  = $items->sum(fn($i) => $this->parseNumber($i['Discount']));
                $sumPpn   = $items->sum(fn($i) => $this->parseNumber($i['PPN']));
                $sumNet   = $items->sum(fn($i) => $this->parseNumber($i['Net']));

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
                    // Ambil master Barang untuk mendapatkan isidus jika diperlukan
                    $barang = Barang::where('kode_barang', $i['SKUCode'])->first();
                    $masterIsidus = $barang ? intval($barang->isidus) : 1;

                    $qty   = intval($i['TotalQuantity(PCS)']); // total dalam PCS
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
                        'isidus'       => $masterIsidus,
                        'quantity'     => $qty,
                        'disc1'        => 0,
                        'disc2'        => 0,
                        'disc3'        => 0,
                        'disc4'        => $disc1,
                        'jumlah'       => $net,
                        'created_at'   => now(),
                    ]);

                    // Kurangi stok (boleh minus)
                    Barang::where('kode_barang', $i['SKUCode'])
                        ->decrement('stok', $qty);
                }
            }
        });

        return back()->with('success', 'Import CSV berhasil!');
    }

}
