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
        Schema::create('salesman', function (Blueprint $table) {
            $table->string('kode_sales', 10)->primary();
            $table->string('nama_salesman', 50)->required;
            $table->string('alamat', 100)->required;
            $table->string('telepon', 15);
            $table->string('typesalesman', 15);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('salesman');
    }
};
