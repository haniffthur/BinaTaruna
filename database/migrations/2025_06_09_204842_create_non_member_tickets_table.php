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
        // Tabel ini akan menyimpan setiap tiket yang dibeli dalam satu transaksi.
        Schema::create('non_member_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('non_member_transaction_id')->constrained('non_member_transactions')->onDelete('cascade');
            $table->foreignId('ticket_id')->constrained('tickets')->onDelete('cascade'); // Mengacu ke produk tiket
            $table->string('qr_code_token')->unique();
            $table->enum('status', ['valid', 'used', 'expired'])->default('valid');
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('non_member_tickets');
    }
};
