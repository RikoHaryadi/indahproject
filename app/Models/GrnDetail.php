<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrnDetail extends Model
{
    use HasFactory;
    protected $table = 'grndetail'; 
    protected $fillable = ['grn_id', 'kode_barang', 'nama_barang', 'harga', 'quantity', 'jumlah'];

    public function grn()
    {
        return $this->belongsTo(Grn::class, 'grn_id');
    }
    public function masterbarang()
{
    return $this->belongsTo(MasterBarang::class, 'masterbarang', 'kode_barang');
}
}
