<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pelanggan', function (Blueprint $table) {
            $table->string('Kode_pelanggan', 50)->required;
            $table->string('Nama_pelanggan', 50)->required;
            $table->string('alamat', 100)->required;
            $table->string('telepon', 25);
            $table->integer('top');
            $table->integer('kredit_limit');
            $table->string('kode_sales', 3);
            $table->string('nama_sales', 15);
            $table->string('hari_kunjungan', 15);
        

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pelanggan');
    }
};
