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
        Schema::create('grndetail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grn_id')->constrained('grn')->onDelete('cascade');
            $table->string('kode_barang');
            $table->string('nama_barang');
            $table->decimal('harga', 15, 2);
            $table->integer('quantity');
            $table->decimal('jumlah', 15, 2);
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
        Schema::dropIfExists('grndetail');
    }
};
