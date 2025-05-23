<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturDetail extends Model
{
    use HasFactory;
       // Tabel returdetails
    protected $table = 'returdetails';

    // Kita tidak menggunakan timestamps pada returdetails (hanya ada created_at manual)
    public $timestamps = false;

    // Kolom yang boleh diisi
    protected $fillable = [
        'retur_id',
        'kode_barang',
        'nama_barang',
        'harga',
        'dus',
        'lusin',
        'pcs',
        'quantity',
        'dusretur',
        'lusinretur',
        'pcsretur',
        'quantityretur',
        'jumlah',
        'created_at',
    ];

    /**
     * Relasi: satu ReturDetail milik satu Retur
     */
    public function retur()
    {
        return $this->belongsTo(Retur::class, 'retur_id');
    }
}
