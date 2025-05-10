<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bukubesar extends Model
{
    use HasFactory;

    protected $table = 'bukubesar';
    public $timestamps = false; // Nonaktifkan timestamps

    protected $fillable = ['kode_akun', 'nama_akun', 'uraian', 'debet', 'kredit', 'created_at'];
}
