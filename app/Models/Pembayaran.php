<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;
    protected $table = 'pembayaran';
    protected $fillable = ['id_faktur', 'kode_pelanggan', 'nama_pelanggan', 'total', 'bayar', 'sisapiutang'];
}
