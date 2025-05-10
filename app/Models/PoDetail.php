<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PoDetail extends Model
{
    use HasFactory;
    protected $table = 'po_detail'; // Pastikan sesuai dengan tabel di database
    protected $fillable = ['po_id', 'kode_barang', 'nama_barang', 'harga', 'dus', 'lusin', 'pcs', 'quantity', 'jumlah', 'created_at'];

   

    public function po()
    {
        return $this->belongsTo(Po::class, 'po_id', 'id');
    }
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'kode_barang', 'kode_barang');
    }
 

}
