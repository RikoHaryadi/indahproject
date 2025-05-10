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
        Schema::create('masterbarang', function (Blueprint $table) {
            $table->string('kode_barang', 10)->required;
            $table->string('nama_barang', 50)->required;
            $table->integer('hargapcs')->required;
            $table->integer('hargapcsjual')->required;
            $table->integer('isidus')->required;
            $table->string('kategori', 50)->required;
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('masterbarang');
    }
};
