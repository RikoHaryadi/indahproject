<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;


    protected $table = 'barang';

    public $timestamps = false; // Nonaktifkan timestamps

    protected $primaryKey = 'kode_barang'; // Tentukan primary key

    public $incrementing = false; // Karena Kode_barang bukan auto-increment
    protected $keyType = 'string'; // Karena Kode_barang adalah string



    protected $fillable = ['kode_barang', 'nama_barang', 'isidus', 'harga', 'stok', 'stok_dus', 'stok_lsn', 'stok_pcs', 'nilairp'];
}
