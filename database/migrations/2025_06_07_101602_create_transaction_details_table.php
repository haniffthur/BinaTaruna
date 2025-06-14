<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('transaction_details', function (Blueprint $table) {
            $table->id();
            $table->morphs('purchasable'); // purchasable_id & purchasable_type (SchoolClass atau Ticket)
            $table->morphs('detailable');   // detailable_id & detailable_type (MemberTransaction atau NonMemberTransaction)
            $table->integer('quantity')->default(1);
            $table->decimal('price', 10, 2);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('transaction_details');
    }
};