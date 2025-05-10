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
        Schema::create('ddt', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dt_id')->constrained('dt')->onDelete('cascade');
            $table->string('id_faktur');
            $table->string('kode_pelanggan');
            $table->string('nama_pelanggan');
            $table->integer('top');
            $table->integer('total');
            $table->integer('bayar');
            $table->integer('sisapiutang');
           
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
        Schema::dropIfExists('ddt');
    }
};
