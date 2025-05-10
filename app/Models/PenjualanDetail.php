<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenjualanDetail extends Model
{
    use HasFactory;
    protected $table = 'penjualan_detail'; // Pastikan sesuai dengan tabel di database
    public $timestamps = false; // Nonaktifkan timestamps
    protected $fillable = ['penjualan_id', 'kode_barang', 'nama_barang', 'harga', 'dus', 'lusin', 'pcs', 'quantity', 'disc1', 'disc2', 'disc3', 'disc4', 'jumlah', 'created_at'];

   

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class);
    }
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'kode_barang', 'kode_barang');
    }
}
