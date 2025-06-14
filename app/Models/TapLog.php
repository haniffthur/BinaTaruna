<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class TapLog extends Model {
    use HasFactory;
    const UPDATED_AT = null;
    const CREATED_AT = 'tapped_at';
    protected $fillable = ['master_card_id', 'status', 'message', 'tapped_at'];
    public function masterCard(): BelongsTo {
        return $this->belongsTo(MasterCard::class);
    }
}