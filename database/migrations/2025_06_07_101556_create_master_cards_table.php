<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('master_cards', function (Blueprint $table) {
            $table->id();
            $table->string('cardno')->unique();
            $table->enum('card_type', ['member', 'staff', 'coach']);
            $table->enum('assignment_status', ['available', 'assigned'])->default('available');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('master_cards');
    }
};