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
        Schema::table('members', function (Blueprint $table) {
            // Tambah kolom nama panggilan (nickname)
            // Bisa string dan nullable jika tidak wajib diisi
            $table->string('nickname')->nullable()->after('name');

            // Tambah kolom NIS (Nomor Induk Siswa)
            // Umumnya string karena bisa mengandung nol di depan, dan bisa unique
            $table->string('nis', 50)->nullable()->unique()->after('school_class_id');

            // Tambah kolom NISNAS (Nomor Induk Siswa Nasional)
            // Umumnya string karena bisa mengandung nol di depan, dan bisa unique
            $table->string('nisnas', 50)->nullable()->unique()->after('nis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            // Hapus kolom dalam urutan terbalik dari penambahannya
            if (Schema::hasColumn('members', 'nisnas')) {
                $table->dropUnique(['nisnas']); // Hapus indeks unique jika ada
                $table->dropColumn('nisnas');
            }
            if (Schema::hasColumn('members', 'nis')) {
                $table->dropUnique(['nis']); // Hapus indeks unique jika ada
                $table->dropColumn('nis');
            }
            if (Schema::hasColumn('members', 'nickname')) {
                $table->dropColumn('nickname');
            }
        });
    }
};