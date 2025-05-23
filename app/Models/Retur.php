<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Retur extends Model
{
    use HasFactory;
     // Nama tabel
    protected $table = 'returs';

    // Kolom yang boleh diisi (mass assignable)
    protected $fillable = [
        'id_retur',
        'id_faktur',
        'kode_sales',
        'nama_sales',
        'kode_pelanggan',
        'nama_pelanggan',
        'total_discount',
        'total',
    ];

    /**
     * Relasi: satu Retur punya banyak ReturDetail
     */
    public function details()
    {
        return $this->hasMany(ReturDetail::class, 'retur_id');
    }
}
