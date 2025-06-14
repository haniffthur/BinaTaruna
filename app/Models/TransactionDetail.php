<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
class TransactionDetail extends Model {
    use HasFactory;
    protected $fillable = [
        'purchasable_id', 'purchasable_type', 'detailable_id', 
        'detailable_type', 'quantity', 'price',
    ];
    // Relasi ke produk yang dibeli (bisa SchoolClass atau Ticket)
    public function purchasable(): MorphTo { return $this->morphTo(); }

    // Relasi ke induk transaksi (bisa MemberTransaction atau NonMemberTransaction)
    public function detailable(): MorphTo { return $this->morphTo(); }
}