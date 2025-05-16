<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Po extends Model
{
    use HasFactory;
    protected $table = 'po';
    public $timestamps = false; // Nonaktifkan timestamps
    protected $fillable = ['kode_sales', 'nama_sales', 'kode_pelanggan', 'status', 'nama_pelanggan', 'total', 'created_at'];

    public function poDetails()
    {
        return $this->hasMany(PoDetail::class, 'po_id', 'id');
    }
    public function pelanggan()
{
    return $this->belongsTo(Pelanggan::class, 'kode_pelanggan', 'kode_pelanggan');
}
}
