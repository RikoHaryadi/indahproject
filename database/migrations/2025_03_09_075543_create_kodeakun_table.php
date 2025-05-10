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
        Schema::create('kodeakun', function (Blueprint $table) {
            $table->string('kode_akun', 10)->primary()->required;
            $table->string('nama_akun', 10)->required;
            $table->string('kelompok_akun', 10)->required;
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
        Schema::dropIfExists('kodeakun');
    }
};
