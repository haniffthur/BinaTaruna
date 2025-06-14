<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('coaches', function (Blueprint $table) {
            $table->id();
            $table->softDeletes();
            $table->foreignId('master_card_id')->nullable()->unique()->constrained('master_cards')->onDelete('set null');
            $table->foreignId('access_rule_id')->nullable()->constrained('access_rules')->onDelete('set null');
            $table->string('name');
            $table->text('address')->nullable();
            $table->string('phone_number', 20)->nullable();
            $table->string('specialization')->nullable();
            $table->date('join_date');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('coaches');
    }
};