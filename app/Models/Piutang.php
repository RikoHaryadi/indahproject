<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Piutang extends Model
{
    use HasFactory;
    protected $table = 'piutang';
    public $timestamps = false; // Nonaktifkan timestamps
    protected $fillable = [
        'id_faktur',
        'kode_pelanggan',
        'nama_pelanggan',
        'total',
        'pembayaran',
        'sisapiutang',
        'created_at'
    ];
    public function pelanggan()
{
    return $this->belongsTo(Pelanggan::class, 'kode_pelanggan', 'kode_pelanggan');
}

}
