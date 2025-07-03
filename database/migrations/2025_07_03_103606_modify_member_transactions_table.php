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
        Schema::table('member_transactions', function (Blueprint $table) {
            // 1. Tambahkan kolom baru untuk menyimpan nama member saat transaksi
            // Ditempatkan setelah kolom 'id' untuk kerapian
            if (!Schema::hasColumn('member_transactions', 'customer_name')) {
                $table->string('customer_name')->after('id')->nullable();
            }

            // 2. Ubah kolom member_id agar bisa null (jika belum)
            $table->unsignedBigInteger('member_id')->nullable()->change();

            // 3. Hapus foreign key yang lama (restrict)
            // Nama constraint default biasanya adalah 'nama_tabel_nama_kolom_foreign'
            $table->dropForeign('member_transactions_member_id_foreign');

            // 4. Tambahkan kembali foreign key dengan perilaku onDelete('set null')
            // Ini akan secara otomatis mengubah member_id menjadi NULL jika member dihapus
            $table->foreign('member_id')
                  ->references('id')
                  ->on('members')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('member_transactions', function (Blueprint $table) {
            // Kembalikan seperti semula jika migrasi di-rollback
            $table->dropForeign(['member_id']);
            $table->foreign('member_id')->references('id')->on('members')->onDelete('restrict');
            $table->dropColumn('customer_name');
        });
    }
};
