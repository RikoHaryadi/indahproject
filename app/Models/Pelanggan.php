<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    use HasFactory;
    protected $table = 'pelanggan';
    protected $primaryKey = 'Kode_pelanggan'; // Primary key
    public $incrementing = false; // Non-incrementing primary key
    protected $keyType = 'string'; // Tipe primary key adalah string
    public $timestamps = false; // Nonaktifkan timestamps jika tidak digunakan
    protected $fillable = ['Kode_pelanggan', 'Nama_pelanggan', 'alamat', 'telepon', 'top', 'kredit_limit', 'kode_sales', 'nama_sales', 'hari_kunjungan'];
}
