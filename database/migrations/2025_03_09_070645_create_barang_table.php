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
        Schema::create('barang', function (Blueprint $table) {
            $table->string('kode_barang', 10)->primary()->required;
            $table->string('nama_barang', 100)->required;
            $table->double('isidus', 8, 2)->nullable();
            $table->double('harga', 15, 2);
            $table->double('stok', 8, 2);
            $table->double('stok_dus', 8, 2)->nullable();
            $table->double('stok_lsn', 8, 2)->nullable();
            $table->double('stok_pcs', 8, 2)->nullable();
            $table->double('nilairp', 15, 2);
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
        Schema::dropIfExists('barang');
    }
};
