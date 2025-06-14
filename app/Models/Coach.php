<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Coach extends Model {

    use HasFactory;
    use HasFactory;
    protected $fillable = [
        'master_card_id', 'access_rule_id', 'name', 'address',
        'phone_number', 'specialization', 'join_date', 'max_taps_per_day', 'max_taps_per_month', 'allowed_days', 'start_time', 'end_time'
    ];


        protected $casts = [
        'allowed_days' => 'array', // Casting otomatis JSON ke array PHP
    ];
    public function masterCard(): BelongsTo { return $this->belongsTo(MasterCard::class); }
    public function accessRule(): BelongsTo { return $this->belongsTo(AccessRule::class); }
}