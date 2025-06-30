<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_scan_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('non_member_ticket_id')->nullable()->constrained('non_member_tickets')->onDelete('set null');
            $table->string('scanned_token');
            $table->enum('status', ['success', 'not_found', 'already_used', 'expired']);
            $table->string('message');
            $table->timestamp('scanned_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_scan_logs');
    }
};
