<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class TransactionDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'detailable_id',
        'detailable_type',
        'purchasable_id',
        'purchasable_type',
        'quantity',
        'price',
    ];

    /**
     * Relasi polymorphic ke induk transaksi (bisa MemberTransaction atau NonMemberTransaction).
     */
    public function detailable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Relasi polymorphic ke produk yang dibeli (bisa SchoolClass atau Ticket).
     */
    public function purchasable(): MorphTo
    {
        return $this->morphTo();
    }
}
