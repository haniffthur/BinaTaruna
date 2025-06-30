<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany; // <-- 1. Pastikan ini di-import

class MemberTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id', 
        'total_amount', 
        'amount_paid', 
        'change', 
        'transaction_date'
    ];
    
    /**
     * Relasi ke member yang melakukan transaksi.
     */
    public function member(): BelongsTo 
    {
        return $this->belongsTo(Member::class);
    }
    
    /**
     * PERBAIKAN: Tambahkan method relasi 'details' yang hilang di sini.
     * Ini mendefinisikan bahwa satu transaksi member bisa memiliki banyak detail.
     */
    public function details(): MorphMany
    {
        // Pastikan Anda sudah memiliki model 'TransactionDetail'
        return $this->morphMany(TransactionDetail::class, 'detailable');
    }
}
