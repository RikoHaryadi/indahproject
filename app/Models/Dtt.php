<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dtt extends Model
{
    use HasFactory;
    protected $table = 'ddt';
    // public $timestamps = false; // Nonaktifkan timestamps
    protected $fillable = [
         'dt_id', // WAJIB ditambahkan
        'id_faktur',
        'kode_pelanggan',
        'nama_pelanggan',
        'top',
        'total',
        'bayar',
        'sisapiutang',
    ];

    // Relasi dengan Biayaresume
    public function details()
    {
        return $this->belongsTo(Dt::class, 'dt_id', 'id');
    }
}
