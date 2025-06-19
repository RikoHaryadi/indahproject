<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dt extends Model
{
    use HasFactory;
    protected $table = 'dt';
    // public $timestamps = false; // Nonaktifkan timestamps
    protected $fillable = [
        'id_colector',
        'colector',
        'totaldt',
        'is_updated',
        
    ];

    // Relasi dengan Biayaresume
   public function ddt()
{
    return $this->hasMany(Dtt::class, 'dt_id', 'id');
}
}
