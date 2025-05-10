<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterBarang extends Model
{
    use HasFactory;

    protected $table = 'masterbarang';

    public $timestamps = false; // Nonaktifkan timestamps

    protected $primaryKey = 'kode_barang'; // Tentukan primary key

    public $incrementing = false; // Karena Kode_barang bukan auto-increment
    protected $keyType = 'string'; // Karena Kode_barang adalah string



    protected $fillable = ['kode_barang', 'nama_barang', 'hargapcs', 'hargapcsjual', 'isidus', 'kategori'];

    public function kategori()
{
    return $this->belongsTo(Kategori::class, 'kategori', 'kode_kategori');
}

}
