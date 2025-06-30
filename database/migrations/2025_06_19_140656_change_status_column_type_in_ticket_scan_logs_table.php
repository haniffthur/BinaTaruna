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
        Schema::table('ticket_scan_logs', function (Blueprint $table) {
             // Mengubah kolom status menjadi tipe TINYINT (cocok untuk 0/1)
            $table->tinyInteger('status')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_scan_logs', function (Blueprint $table) {
            $table->string('status')->change();
        });
    }
};
