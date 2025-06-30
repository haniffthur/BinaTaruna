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
        Schema::table('tap_logs', function (Blueprint $table) {
            // Mengubah kolom master_card_id agar bisa menerima nilai NULL.
            // Ini penting untuk mencatat log dari kartu yang tidak terdaftar.
            $table->unsignedBigInteger('master_card_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tap_logs', function (Blueprint $table) {
            // Mengembalikan aturan seperti semula jika migrasi di-rollback.
            // Peringatan: Ini bisa gagal jika sudah ada data NULL di kolom.
            $table->unsignedBigInteger('master_card_id')->nullable(false)->change();
        });
    }
};
