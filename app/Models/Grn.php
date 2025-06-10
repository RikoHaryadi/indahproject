<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grn extends Model
{
    use HasFactory;
    protected $table = 'grn';
    protected $fillable = ['noinvoice', 'kode_suplier', 'nama_suplier', 'total', 'date'];
    public $timestamps = false; // Nonaktifkan timestamps
    public function details()
    {
        return $this->hasMany(GrnDetail::class, 'grn_id');
    }
}
