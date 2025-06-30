<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NonMemberTicket extends Model
{
    use HasFactory;
    protected $fillable = ['non_member_transaction_id', 'ticket_id', 'qrcode', 'status', 'validated_at'];
    
    public function transaction(): BelongsTo {
        return $this->belongsTo(NonMemberTransaction::class, 'non_member_transaction_id');
    }
    public function ticketProduct(): BelongsTo {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    
}
