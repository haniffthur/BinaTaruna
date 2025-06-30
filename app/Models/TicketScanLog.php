<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // <-- 1. Pastikan ini di-import

class TicketScanLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'non_member_ticket_id',
        'scanned_token',
        'status',
        'message',
        'scanned_at'
    ];

    /**
     * PERBAIKAN: Tambahkan method relasi ini.
     * Mendefinisikan bahwa satu log scan tiket 'milik' satu tiket non-member.
     */
    public function nonMemberTicket(): BelongsTo
    {
        return $this->belongsTo(NonMemberTicket::class, 'non_member_ticket_id');
    }
}
