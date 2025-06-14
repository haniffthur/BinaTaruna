<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Cek terlebih dahulu apakah kolom 'photo' sudah ada atau belum
        if (!Schema::hasColumn('members', 'photo')) {
            Schema::table('members', function (Blueprint $table) {
                // Jika BELUM ADA, baru tambahkan kolomnya.
                // Ini akan mencegah error jika migrasi dijalankan lebih dari sekali.
                $table->string('photo')->nullable()->after('parent_name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cek terlebih dahulu apakah kolom 'photo' ada sebelum mencoba menghapusnya
        if (Schema::hasColumn('members', 'photo')) {
            Schema::table('members', function (Blueprint $table) {
                $table->dropColumn('photo');
            });
        }
    }
};
