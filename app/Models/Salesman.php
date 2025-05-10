<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salesman extends Model
{
    use HasFactory;

    protected $table = 'salesman';
    protected $primaryKey = 'kode_sales'; // Primary key
    public $incrementing = false; // Non-incrementing primary key
    public $timestamps = false; // Nonaktifkan timestamps
   
    protected $fillable = ['kode_sales', 'nama_salesman', 'alamat', 'telepon', 'typesalesman'];
}
