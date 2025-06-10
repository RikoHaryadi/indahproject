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
        Schema::create('grn', function (Blueprint $table) {
            $table->id();
            $table->string('noinvoice');
            $table->string('kode_suplier');
            $table->string('nama_suplier');
            $table->decimal('total', 15, 2);
            $table->date('date');
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
        Schema::dropIfExists('grn');
    }
};
