<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_card_id')->nullable()->unique()->constrained('master_cards')->onDelete('set null');
            $table->foreignId('access_rule_id')->nullable()->constrained('access_rules')->onDelete('set null');
            
            // Kolom Data Diri
          $table->string('name')->nullable();
            $table->text('address')->nullable();
            $table->string('phone_number', 20)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('parent_name')->nullable();
            $table->date('join_date');
            
            // === KOLOM BARU UNTUK ATURAN AKSES DITAMBAHKAN DI SINI ===
            $table->string('rule_type')->default('template');
            $table->integer('max_taps_per_day')->nullable();
            $table->integer('max_taps_per_month')->nullable();
            $table->json('allowed_days')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            // =========================================================
            
            $table->timestamps();
            $table->softDeletes(); // Jangan lupa tambahkan ini jika Anda pakai Soft Deletes
        });
    }
    public function down(): void {
        Schema::dropIfExists('members');
    }
};
