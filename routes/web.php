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
               Route::post('/po',         [PoController::class,'store'])->name('po.store');
               Route::get('/api/po/{id}', [PoController::class,'getPOData']);
               Route::get('/po/{id}',     [PoController::class,'show'])->name('po.show');
               Route::get('/penjualan/daftarso',      [PoController::class,'daftar'])->name('penjualan.daftarso');

               // (Optional) jika masih pakai wizard select‐sales
               Route::get('/po/select-sales',       [PoController::class,'selectSales'])->name('po.select-sales');
               Route::post('/po/select-sales',      [PoController::class,'handleSelectSales'])->name('po.handle-select-sales');
               Route::get('/po/create',             [PoController::class,'create'])->name('po.create');
               Route::get('/pelanggan/search', [PelangganController::class,'search'])->name('pelanggan.search');
               Route::get('/masterbarang/search', [MasterBarangController::class,'search'])->name('masterbarang.search');
               Route::resource('pelanggan', PelangganController::class)
               ->parameters(['pelanggan'=>'Kode_pelanggan'])
               ->except('show');
               Route::get('/barang',    [BarangController::class,'index'])->name('barang.index');
     
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
     Route::resource('grn', GrnController::class)
          ->except(['edit','update','destroy']);
     Route::get('/grn/daftar',               [GrnController::class,'daftar'])->name('grn.daftargrn');
     Route::get('/grn/cetak/{id}',           [GrnController::class,'cetak'])->name('grn.cetak');
     Route::get('/grn/cetak-pdf/{id}',       [GrnController::class,'cetakPdf'])->name('grn.cetak-pdf');

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
     Route::get('/dt',                       [PiutangController::class,'index'])->name('dt.index');
     Route::post('/dt',                      [PiutangController::class,'store'])->name('dt.store');
     Route::get('/dt/edit/{id}',             [PiutangController::class,'edit'])->name('dt.edit');
     Route::put('/dt/update/{id}',           [PiutangController::class,'update'])->name('dt.update');
     Route::get('/dt/cari-edit',             [PiutangController::class,'showCariEdit'])->name('dt.cari_edit');
// });