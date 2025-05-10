<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Biayaresume extends Model
{
    use HasFactory;
    use HasFactory;
    protected $table = 'biayaresume';
    public $timestamps = false; // Nonaktifkan timestamps
    protected $fillable = [
        'kode_transaksi',
        'total',
        'created_at',
    ];

     // Relasi dengan Biaya
     public function items()
     {
         return $this->hasMany(Biaya::class, 'kode_transaksi', 'kode_transaksi');
     }
}
