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
        Schema::create('returdetails', function (Blueprint $table) {
           $table->id();
            $table->foreignId('retur_id')->constrained('returs')->onDelete('cascade');
            $table->string('kode_barang');
            $table->string('nama_barang');
            $table->decimal('harga', 15, 2);
            $table->integer('dus');
            $table->integer('lusin');
            $table->integer('pcs');
            $table->integer('quantity');
            $table->integer('dusretur');
            $table->integer('lusinretur');
            $table->integer('pcsretur');
            $table->integer('quantityretur');
            $table->decimal('jumlah', 15, 2);
            $table->date('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('returdetails');
    }
};
