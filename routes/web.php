<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\GrnController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\MasterBarangController;
use App\Http\Controllers\AkunController;
use App\Http\Controllers\BukubesarController;
use App\Http\Controllers\BiayaController;
use App\Http\Controllers\SalesmanController;
use App\Http\Controllers\RekapController;
use App\Http\Controllers\PoController;
use App\Http\Controllers\MultiplepoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PiutangController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('home');
// });

Route::middleware('no.cache')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])
         ->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])
         ->name('logout');
});

/*
|--------------------------------------------------------------------------
| Protected Routes — session.auth + no-cache
|--------------------------------------------------------------------------
*/
Route::middleware(['session.auth', 'no.cache'])->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])
         ->name('home');

    // Penjualan
    Route::get('/penjualan', [PenjualanController::class, 'index'])
         ->name('penjualan.index');
    Route::post('/penjualan', [PenjualanController::class, 'store'])
         ->name('penjualan.store');
    Route::get('/penjualan/daftarso', [PoController::class, 'daftar'])
         ->name('penjualan.daftarso');
    Route::get('/penjualan/daftar', [PenjualanController::class, 'daftar'])
         ->name('penjualan.daftarjual');
    Route::get('/penjualan/{id}/cetak', [PenjualanController::class, 'cetak'])
         ->name('penjualan.cetak');
    Route::get('/penjualan/cetak-pdf/{id}', [PenjualanController::class, 'cetakPdf'])
         ->name('penjualan.cetak-pdf');

    // Sales Order (PO)
    Route::get('/po', [PoController::class, 'index'])
         ->name('po.index');
    Route::post('/po', [PoController::class, 'store'])
         ->name('po.store');
    Route::get('/api/po/{id}', [PoController::class, 'getPOData']);
    Route::get('/po/{id}', [PoController::class, 'show'])
         ->name('po.show');

    // Multiple PO Processing
    Route::get('/so/selection', [MultiplepoController::class, 'index'])
         ->name('po.selection');
    Route::post('/so/process/step2', [MultiplepoController::class, 'processStep2'])
         ->name('so.process.step2');
    Route::post('/so/process/final', [MultiplepoController::class, 'processFinal'])
         ->name('so.process.final');
    Route::get('/so/list', [MultiplepoController::class, 'list'])
         ->name('so.list');

    // Barang & Search
    Route::get('/barang', [BarangController::class, 'index'])
         ->name('barang.index');
    Route::post('/barang', [BarangController::class, 'store'])
         ->name('barang.store');
    Route::get('/barang/{kode}/edit', [BarangController::class, 'edit'])
         ->name('barang.edit');
    Route::put('/barang/{kode}', [BarangController::class, 'update'])
         ->name('barang.update');
    Route::delete('/barang/{kode}', [BarangController::class, 'destroy'])
         ->name('barang.destroy');
    Route::get('/barang/search', [BarangController::class, 'search'])
         ->name('barang.search');

    // Pelanggan & Import
    Route::get('/pelanggan', [PelangganController::class, 'index'])
         ->name('pelanggan.index');
    Route::post('/pelanggan', [PelangganController::class, 'store'])
         ->name('pelanggan.store');
    Route::get('/pelanggan/{Kode_pelanggan}/edit', [PelangganController::class, 'edit'])
         ->name('pelanggan.edit');
    Route::put('/pelanggan/{Kode_pelanggan}', [PelangganController::class, 'update'])
         ->name('pelanggan.update');
    Route::delete('/pelanggan/{Kode_pelanggan}', [PelangganController::class, 'destroy'])
         ->name('pelanggan.destroy');
    Route::post('/pelanggan/import', [PelangganController::class, 'import'])
         ->name('pelanggan.import');
    Route::get('/pelanggan/search', [PelangganController::class, 'search']);

    // Salesman
    Route::resource('salesman', SalesmanController::class)
         ->except(['show']);

    // Supplier
    Route::resource('supplier', SupplierController::class)
         ->parameters(['supplier' => 'Kode_suplier'])
         ->except(['show']);

    // Kategori
    Route::resource('kategori', KategoriController::class)
         ->except(['show']);

    // Akun & Buku Besar & Biaya
    Route::get('/kodeakun', [AkunController::class, 'index'])
         ->name('akuntan.kodeakun');
    Route::post('/kodeakun', [AkunController::class, 'store'])
         ->name('kodeakun.store');
    Route::delete('/kodeakun/{kode_akun}', [AkunController::class, 'destroy'])
         ->name('kodeakun.destroy');

    Route::get('/bukubesar', [BukubesarController::class, 'index'])
         ->name('akuntan.bukubesar');

    Route::get('/inputbiaya', [BiayaController::class, 'index'])
         ->name('akuntan.biaya');
    Route::post('/inputbiaya', [BiayaController::class, 'store'])
         ->name('biaya.store');
    Route::get('/akuntan/cetakbiaya/{kode_transaksi}', [BiayaController::class, 'cetak'])
         ->name('akuntan.cetakbiaya');
    Route::get('/biaya/cetak-pdf/{kode_transaksi}', [BiayaController::class, 'cetakPdf'])
         ->name('biaya.cetak-pdf');

    // Master Barang & Import
    Route::resource('masterbarang', MasterBarangController::class)
         ->parameters(['masterbarang' => 'kode']);
    Route::post('/masterbarang/import', [MasterBarangController::class, 'importCSV'])
         ->name('masterbarang.import');

    // GRN
    Route::resource('grn', GrnController::class)->except(['edit','update','destroy']);
    Route::get('/grn/daftar', [GrnController::class, 'daftar'])
         ->name('grn.daftargrn');
    Route::get('/grn/cetak/{id}', [GrnController::class, 'cetak'])
         ->name('grn.cetak');
    Route::get('/grn/cetak-pdf/{id}', [GrnController::class, 'cetakPdf'])
         ->name('grn.cetak-pdf');

    // Pembayaran
    Route::get('/pembayaran', [PembayaranController::class, 'index'])
         ->name('pembayaran.index');
    Route::post('/pembayaran', [PembayaranController::class, 'store'])
         ->name('pembayaran.store');
    Route::get('/pembayaran/daftarpiutang', [PembayaranController::class, 'daftar'])
         ->name('pembayaran.daftarpiutang');

    // Rekap
    Route::get('/rekap-barang', [RekapController::class, 'rekapBarang'])
         ->name('rekap.barang');
    Route::get('/rekap/pilih-faktur', [RekapController::class, 'pilihFaktur'])
         ->name('rekap.pilih-faktur');
    Route::post('/rekap/hasil-rekap', [RekapController::class, 'prosesRekap'])
         ->name('rekap.hasil-rekap');
    Route::get('/rekap-faktur', [RekapController::class, 'rekapFaktur'])
         ->name('rekap.faktur');

    // Piutang Testing
    Route::get('/dt', [PiutangController::class, 'index'])->name('dt.index');
    Route::post('/dt', [PiutangController::class, 'store'])->name('dt.store');
    Route::get('/dt/edit/{id}', [PiutangController::class, 'edit'])->name('dt.edit');
    Route::put('/dt/update/{id}', [PiutangController::class, 'update'])->name('dt.update');
    Route::get('/dt/cari-edit', [PiutangController::class, 'showCariEdit'])->name('dt.cari_edit');
});

