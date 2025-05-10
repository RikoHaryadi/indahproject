<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;
    protected $table = 'supplier';
    protected $primaryKey = 'Kode_suplier'; // Primary key
    public $incrementing = false; // Non-incrementing primary key
    protected $keyType = 'string'; // Tipe primary key adalah string
    public $timestamps = false; // Nonaktifkan timestamps jika tidak digunakan
    protected $fillable = ['Kode_suplier', 'Nama_suplier', 'alamat', 'telepon'];
}
