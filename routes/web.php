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
use App\Http\Controllers\SoController;
use App\Http\Controllers\MultiplepoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PiutangController;
use App\Http\Controllers\ReturController;
use App\Http\Controllers\PenjualanImportController;

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

// Public (no cache)
Route::middleware('no.cache')->group(function(){
    // login, register, logout
    Route::get('/login',  [AuthController::class,'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class,'login']);
    Route::post('/logout',[AuthController::class,'logout'])->name('logout');
    Route::get('/register', [AuthController::class,'showRegister'])->name('register');
    Route::post('/register',[AuthController::class,'register'])->name('register.post');
});

// // Protected (login + no cache)
// Route::middleware(['session.auth','no.cache'])->group(function () {
//     // Dashboard
    Route::get('/', [DashboardController::class,'index'])->name('home');
            
               // PO (Sales Order)
               Route::get('/po',          [PoController::class,'index'])->name('po.index');
                 Route::get('/so',          [SoController::class,'index'])->name('po.index');
               Route::post('/po',         [PoController::class,'store'])->name('po.store');
               Route::get('/api/po/{id}', [PoController::class,'getPOData']);
               Route::get('/po/{id}',     [PoController::class,'show'])->name('po.show');
               Route::delete('/so/{id}', [SoController::class, 'destroy'])->name('po.destroy');


               // (Optional) jika masih pakai wizard select‐sales
               Route::get('/po/select-sales',       [PoController::class,'selectSales'])->name('po.select-sales');
               Route::post('/po/select-sales',      [PoController::class,'handleSelectSales'])->name('po.handle-select-sales');
               Route::get('/pelanggan/export-excel', [PelangganController::class, 'exportExcel'])->name('pelanggan.export.excel');

               Route::get('/po/create',             [PoController::class,'create'])->name('po.create');
              Route::get('/api/pelanggan', [PelangganController::class, 'searchBySales'])
               ->name('api.pelanggan.search');
               Route::get('/pelanggan/search', [PelangganController::class,'search'])->name('pelanggan.search');
               Route::get('/masterbarang/search', [MasterBarangController::class,'search'])->name('masterbarang.search');
               Route::resource('pelanggan', PelangganController::class)
               ->parameters(['pelanggan'=>'Kode_pelanggan'])
               ->except('show');
               Route::get('/stok/export', [BarangController::class, 'exportExcel'])->name('stok.export');

               Route::get('/barang',    [BarangController::class,'index'])->name('barang.index');
     Route::delete('/penjualan/{id}', [PenjualanController::class, 'destroy'])->name('penjualan.destroy');

     Route::get('/penjualan',               [PenjualanController::class,'index'])->name('penjualan.index');
     Route::post('/penjualan',              [PenjualanController::class,'store'])->name('penjualan.store');
     Route::get('/penjualan/daftarso',      [PoController::class,'daftar'])->name('penjualan.daftarso');
     Route::get('/penjualan/daftar',        [PenjualanController::class,'daftar'])->name('penjualan.daftarjual');
     Route::get('/penjualan/{id}/cetak',    [PenjualanController::class,'cetak'])->name('penjualan.cetak');
     Route::get('/penjualan/cetak-pdf/{id}',[PenjualanController::class,'cetakPdf'])->name('penjualan.cetak-pdf');

     // Multiple PO
     Route::get('/so/selection',            [MultiplepoController::class,'index'])->name('po.selection');
     Route::post('/so/process/step2',       [MultiplepoController::class,'processStep2'])->name('so.process.step2');
     Route::post('/so/process/final',       [MultiplepoController::class,'processFinal'])->name('so.process.final');
     Route::get('/so/list',                 [MultiplepoController::class,'list'])->name('so.list');

     // Barang & MasterBarang
     Route::resource('supplier', SupplierController::class)
          ->parameters(['supplier'=>'Kode_suplier'])
          ->except('show');
     Route::resource('salesman', SalesmanController::class)->except('show');
     Route::resource('kategori', KategoriController::class)->except('show');
     Route::resource('masterbarang', MasterBarangController::class)
          ->parameters(['masterbarang'=>'kode']);
     Route::post('/masterbarang/import',    [MasterBarangController::class,'importCSV'])->name('masterbarang.import');
     Route::get('/barang/search',           [BarangController::class,'search'])->name('barang.search');
     Route::get('/masterbarang/search',           [MasterBarangController::class,'search'])->name('masterbarang.search');
     Route::get('/grn/daftar',               [GrnController::class,'daftar'])->name('grn.daftargrn');
     Route::get('/grn/import', [GrnController::class, 'formImport'])->name('grn.import.form');
   Route::post('/grn/import', [GrnController::class, 'import'])->name('grn.import');
     Route::get('/grn/download-template', [GrnController::class, 'uploadTemplateGRN'])->name('grn.template');

     Route::resource('grn', GrnController::class)
          ->except(['edit','update','destroy']);
    
     Route::get('/grn/cetak/{id}',           [GrnController::class,'cetak'])->name('grn.cetak');
     Route::get('/grn/cetak-pdf/{id}',       [GrnController::class,'cetakPdf'])->name('grn.cetak-pdf');
     Route::post('/grn',         [GrnController::class,'store'])->name('grn.store');

     // Pelanggan
     Route::resource('pelanggan', PelangganController::class)
          ->parameters(['pelanggan'=>'Kode_pelanggan']);
     Route::post('/pelanggan/import',        [PelangganController::class,'import'])->name('pelanggan.import');

     // Pembayaran
     Route::get('/pembayaran',               [PembayaranController::class,'index'])->name('pembayaran.index');
     Route::post('/pembayaran',              [PembayaranController::class,'store'])->name('pembayaran.store');
     Route::get('/pembayaran/daftarpiutang', [PembayaranController::class,'daftar'])->name('pembayaran.daftarpiutang');

     // Rekap
     Route::get('/rekap-barang',             [RekapController::class,'rekapBarang'])->name('rekap.barang');
     Route::get('/rekap/pilih-faktur',       [RekapController::class,'pilihFaktur'])->name('rekap.pilih-faktur');
     Route::post('/rekap/hasil-rekap',       [RekapController::class,'prosesRekap'])->name('rekap.hasil-rekap');
     Route::get('/rekap-faktur',             [RekapController::class,'rekapFaktur'])->name('rekap.faktur');

     // Kode Akun, Buku Besar, Biaya
     Route::get('/kodeakun',                 [AkunController::class,'index'])->name('akuntan.kodeakun');
     Route::post('/kodeakun',                [AkunController::class,'store'])->name('kodeakun.store');
     Route::delete('/kodeakun/{kode_akun}',  [AkunController::class,'destroy'])->name('kodeakun.destroy');
     


     Route::get('/bukubesar',                [BukubesarController::class,'index'])->name('akuntan.bukubesar');

     Route::get('/inputbiaya',               [BiayaController::class,'index'])->name('akuntan.biaya');
     Route::post('/inputbiaya',              [BiayaController::class,'store'])->name('biaya.store');
     Route::get('/akuntan/cetakbiaya/{kode}',[BiayaController::class,'cetak'])->name('akuntan.cetakbiaya');
     Route::get('/biaya/cetak-pdf/{kode}',   [BiayaController::class,'cetakPdf'])->name('biaya.cetak-pdf');

     // Piutang Testing
     Route::get('/dt/{id}/cetak', [PiutangController::class, 'cetak'])->name('dt.cetak');
     Route::get('/dt/{id}/edit', [PiutangController::class, 'edit'])->name('dt.edit');
     Route::put('/dt/{id}', [PiutangController::class, 'updatedt'])->name('dt.update');
     Route::delete('/dt/{id}', [PiutangController::class, 'destroy'])->name('dt.destroy');

      Route::get('/dt/daftar',        [PiutangController::class,'daftar'])->name('dt.daftardt');
     Route::get('/check-faktur-exists', [PiutangController::class, 'checkFakturExists'])->name('check.faktur.exists');
     Route::get('/dt',                       [PiutangController::class,'index'])->name('dt.index');
     Route::post('/dt',                      [PiutangController::class,'store'])->name('dt.store');
  
     Route::get('/dt/cari-edit',             [PiutangController::class,'showCariEdit'])->name('dt.cari_edit');
       Route::get('/datapiutang',             [PiutangController::class,'indexPiutang'])->name('piutang.index');
     // tampilkan form import
     Route::post('/penjualan/import-preview', [PenjualanImportController::class, 'preview'])->name('penjualan.import.preview');
     Route::post('/penjualan/import-do', [PenjualanImportController::class, 'importCsv'])->name('penjualan.import.do');

     Route::get('/penjualan/import', [App\Http\Controllers\PenjualanImportController::class, 'showForm'])
     ->name('penjualan.import.form');
// proses upload
     Route::post('/penjualan/import', [App\Http\Controllers\PenjualanImportController::class, 'importCsv'])
     ->name('penjualan.import');
// Edit form (GET)
Route::get('/penjualan/{id}/edit', [PenjualanController::class, 'edit'])
     ->name('penjualan.edit');

// Update (PUT)
Route::put('/penjualan/{id}', [PenjualanController::class, 'update'])
     ->name('penjualan.update');
// });
// Menampilkan Form Retur Penjualan
Route::get('/retur-penjualan', [ReturController::class, 'showForm'])->name('retur.form');
Route::get('/retur-bebas', [ReturController::class, 'showFormBebas'])->name('retur.bebas');
Route::post('/retur-bebas', [ReturController::class, 'simpanReturBebas'])->name('retur.bebas.simpan');

// AJAX: Ambil detail penjualan (header + detail item) berdasar ID penjualan
Route::get('/retur-penjualan/details-faktur/{id}', [ReturPenjualanController::class, 'getDetailFaktur']);

// Proses Submit Form Retur Penjualan
Route::post('/retur-penjualan', [ReturController::class, 'processRetur'])
     ->name('retur.submit');
    // Route untuk AJAX search faktur
Route::get('/penjualan/search-faktur', [PenjualanController::class, 'searchFaktur'])
     ->name('penjualan.search-faktur');
Route::get('/retur-penjualan/details/{id}', [ReturController::class, 'getPenjualanDetails']);
Route::get('/retur/daftar', [ReturController::class, 'daftarRetur'])->name('retur.daftar');

// Cetak dan Cancel (placeholder, bisa disesuaikan)
Route::get('/retur/cetak/{id}', [ReturController::class, 'cetak'])->name('retur.cetak');
Route::delete('/retur/cancel/{id}', [ReturController::class, 'cancel'])->name('retur.cancel');
Route::post('/retur/batalkan/{id}', [ReturController::class, 'batalkan'])->name('retur.batalkan');





    
