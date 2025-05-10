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
        Schema::create('piutang', function (Blueprint $table) {
            $table->id();
            $table->string('id_faktur');
            $table->string('kode_pelanggan');
            $table->string('nama_pelanggan');
            $table->decimal('total', 15, 2);
            $table->decimal('pembayaran', 15, 2);
            $table->decimal('sisapiutang', 15, 2);
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
        Schema::dropIfExists('piutang');
    }
};
