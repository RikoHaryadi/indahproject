<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    use HasFactory;
    protected $table = 'kategori';

    public $timestamps = false; // Nonaktifkan timestamps

    protected $primaryKey = 'kode_kategori'; // Tentukan primary key

    public $incrementing = false; // Karena Kode_barang bukan auto-increment
    protected $keyType = 'string'; // Karena Kode_barang adalah string



    protected $fillable = ['kode_kategori', 'nama_kategori'];
}
