<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    use HasFactory;
    protected $table = 'penjualan';
    public $timestamps = false; // Nonaktifkan timestamps
    protected $fillable = ['id_faktur', 'kode_pelanggan', 'nama_pelanggan', 'total_discount', 'total', 'created_at'];

    public function details()
    {
        return $this->hasMany(PenjualanDetail::class);
    }
    public function pelanggan()
{
    return $this->belongsTo(Pelanggan::class, 'kode_pelanggan', 'kode_pelanggan');
}
}
