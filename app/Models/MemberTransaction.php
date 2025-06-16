<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberTransaction extends Model {
    use HasFactory;
    protected $fillable = [
        'member_id', 'total_amount', 'amount_paid', 'change', 'transaction_date'
    ];
    public function member(): BelongsTo { return $this->belongsTo(Member::class); }
   
}