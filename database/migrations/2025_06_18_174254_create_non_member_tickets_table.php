<?php
// =========================================================================================
// 1. File Baru: database/migrations/YYYY_MM_DD_HHMMSS_create_non_member_tickets_table.php
// (Buat file ini dengan perintah: php artisan make:migration create_non_member_tickets_table)
// =========================================================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel ini akan menyimpan SETIAP tiket yang dibeli dalam satu transaksi.
        Schema::create('non_member_tickets', function (Blueprint $table) {
            $table->id();
            // Menghubungkan setiap tiket ke transaksi induknya
            $table->foreignId('non_member_transaction_id')->constrained('non_member_transactions')->onDelete('cascade');
            // Menghubungkan ke jenis tiket yang dibeli
            $table->foreignId('ticket_id')->constrained('tickets')->onDelete('cascade');
            
            $table->string('qrcode')->unique();
            $table->boolean('status')->default(1); 
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('non_member_tickets');
    }
};