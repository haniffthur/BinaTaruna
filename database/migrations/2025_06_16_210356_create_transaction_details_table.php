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
        // Tabel ini akan menyimpan setiap item dalam sebuah transaksi,
        // baik itu dari MemberTransaction maupun NonMemberTransaction.
        Schema::create('transaction_details', function (Blueprint $table) {
            $table->id();
            
            // Ini adalah bagian "polymorphic" yang cerdas.
            // Kolom ini bisa merujuk ke ID dari tabel manapun.
            $table->morphs('detailable'); // Akan membuat kolom 'detailable_id' dan 'detailable_type'

            // Menyimpan info produk yang dibeli (bisa berupa SchoolClass atau Ticket)
            $table->morphs('purchasable'); // Akan membuat kolom 'purchasable_id' dan 'purchasable_type'

            $table->integer('quantity');
            $table->decimal('price', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_details');
    }
};
