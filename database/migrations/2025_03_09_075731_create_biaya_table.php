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
        Schema::create('biaya', function (Blueprint $table) {
            $table->id();
            $table->string('kode_transaksi', 10)->index();
            $table->string('kode_akun', 10);
            $table->string('nama_akun', 50);
            $table->integer('jumlah');
            $table->string('keterangan', 50);
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
        Schema::dropIfExists('biaya');
    }
};
