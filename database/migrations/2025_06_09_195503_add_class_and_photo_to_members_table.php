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
            // Cek dan tambahkan kolom school_class_id jika belum ada
            if (!Schema::hasColumn('members', 'school_class_id')) {
                // Definisikan kolom baru dengan tipe data yang benar
                $table->unsignedBigInteger('school_class_id')->nullable()->after('master_card_id');
                
                // Tambahkan foreign key constraint secara manual
                // Mengacu ke tabel 'classes' sesuai dengan migration Anda.
                $table->foreign('school_class_id')
                      ->references('id')->on('classes')
                      ->onDelete('set null');
            }

            // Cek dan tambahkan kolom photo jika belum ada
            if (!Schema::hasColumn('members', 'photo')) {
                // Tambahkan kolom untuk foto
                $table->string('photo')->nullable()->after('parent_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            // Cek sebelum menghapus untuk menghindari error
            if (Schema::hasColumn('members', 'school_class_id')) {
                $table->dropForeign(['school_class_id']);
                $table->dropColumn('school_class_id');
            }
            if (Schema::hasColumn('members', 'photo')) {
                $table->dropColumn('photo');
            }
        });
    }
};
