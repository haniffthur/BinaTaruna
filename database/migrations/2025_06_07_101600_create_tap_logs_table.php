<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('tap_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_card_id')->constrained('master_cards')->onDelete('cascade');
         $table->tinyInteger('status')->comment('1: granted, 0: denied');
            $table->string('message')->nullable();
            $table->timestamp('tapped_at')->useCurrent();
        });
    }
    public function down(): void {
        Schema::dropIfExists('tap_logs');
    }
};