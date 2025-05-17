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
        Schema::table('penjualan', function (Blueprint $table) {
            // Tambah kolom kode_sales sebelum kode_pelanggan
            $table->string('kode_sales')->after('id_faktur');
            // Tambah kolom nama_sales sebelum kode_pelanggan
            $table->string('nama_sales')->after('kode_sales');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('penjualan', function (Blueprint $table) {
              $table->dropColumn(['kode_sales', 'nama_sales']);
        });
    }
};
