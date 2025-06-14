<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('non_member_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name')->nullable();
            $table->string('qr_code_token')->unique()->nullable();
            $table->enum('qr_code_status', ['valid', 'used', 'expired'])->default('valid');
            $table->timestamp('validated_at')->nullable();
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