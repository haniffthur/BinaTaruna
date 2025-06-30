<?php
// File yang Diperbarui: database/migrations/YYYY_MM_DD_HHMMSS_create_non_member_transactions_table.php
// (Ganti seluruh isi file asli Anda dengan ini)

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::create('non_member_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name')->nullable();
            
            // PERBAIKAN: Kolom QR Code, Status, dan Validated_at DIHAPUS dari sini
            // karena akan dipindahkan ke tabel non_member_tickets.
            
            $table->decimal('total_amount', 10, 2);
            $table->decimal('amount_paid', 10, 2);
            $table->decimal('change', 10, 2);
            $table->timestamp('transaction_date')->useCurrent();
            $table->timestamps();
        });
    }
    
    public function down(): void {
        Schema::dropIfExists('non_member_transactions');
    }
};