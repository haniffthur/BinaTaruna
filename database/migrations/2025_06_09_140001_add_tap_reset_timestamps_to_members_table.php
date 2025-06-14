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
        Schema::table('members', function (Blueprint $table) {
            // Kolom ini akan mencatat kapan terakhir aturan tap Harian diubah.
            $table->timestamp('daily_tap_reset_at')->nullable()->after('end_time');

            // Kolom ini akan mencatat kapan terakhir aturan tap Bulanan diubah.
            $table->timestamp('monthly_tap_reset_at')->nullable()->after('daily_tap_reset_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn(['daily_tap_reset_at', 'monthly_tap_reset_at']);
        });
    }
};
