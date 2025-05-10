<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Biaya extends Model
{
    use HasFactory;
    protected $table = 'biaya';
    public $timestamps = false; // Nonaktifkan timestamps
    protected $fillable = [
        'kode_transaksi',
        'kode_akun',
        'nama_akun',
        'jumlah',
        'keterangan',
        'created_at',
    ];

    // Relasi dengan Biayaresume
    public function resume()
    {
        return $this->belongsTo(Biayaresume::class, 'kode_transaksi', 'kode_transaksi');
    }
}
