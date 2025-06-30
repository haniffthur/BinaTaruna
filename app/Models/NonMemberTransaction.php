<?php
// File yang Diperbarui: app/Models/NonMemberTransaction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class NonMemberTransaction extends Model
{
    use HasFactory;

    /**
     * PERBAIKAN: Hapus kolom-kolom QR dari $fillable karena sudah dipindahkan.
     */
    protected $fillable = [
        'customer_name',
        'total_amount',
        'amount_paid',
        'change',
        'transaction_date'
    ];
    
    /**
     * Relasi lama ke TransactionDetail, ini bisa dipertahankan 
     * jika Anda masih menggunakannya untuk tujuan lain.
     */
    public function details(): MorphMany
    {
        return $this->morphMany(TransactionDetail::class, 'detailable');
    }

    /**
     * Relasi BARU: Satu transaksi sekarang memiliki banyak tiket.
     * Ini adalah relasi yang akan kita gunakan.
     */
    public function purchasedTickets(): HasMany
    {
        return $this->hasMany(NonMemberTicket::class);
    }
}