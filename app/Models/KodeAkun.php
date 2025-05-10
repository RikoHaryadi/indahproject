<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KodeAkun extends Model
{
    use HasFactory;

    protected $table = 'kodeakun';

    public $timestamps = false; // Nonaktifkan timestamps

    protected $primaryKey = 'kode_akun'; // Tentukan primary key

    public $incrementing = false; // Karena Kode_barang bukan auto-increment
    protected $keyType = 'string'; // Karena Kode_barang adalah string



    protected $fillable = ['kode_akun', 'nama_akun', 'kelompok_akun'];
}
