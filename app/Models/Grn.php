<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grn extends Model
{
    use HasFactory;
    protected $table = 'grn';
    protected $fillable = ['kode_suplier', 'nama_suplier', 'total', 'created_at'];

    public function details()
    {
        return $this->hasMany(GrnDetail::class, 'grn_id');
    }
}
