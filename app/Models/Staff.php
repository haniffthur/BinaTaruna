<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Staff extends Model {
    use HasFactory;
       use HasFactory;
    
    protected $table = 'staffs';

    protected $fillable = [
        // 'user_id', // <-- HAPUS INI
        'master_card_id',
        'access_rule_id',
        'name',
        'position',
        'phone_number',
        'join_date',
        'max_taps_per_day', 'max_taps_per_month', 'allowed_days', 'start_time', 'end_time'
    ];
        protected $casts = [
        'allowed_days' => 'array', // Casting otomatis JSON ke array PHP
    ];

    public function masterCard(): BelongsTo {
        return $this->belongsTo(MasterCard::class);
    }

    public function accessRule(): BelongsTo {
        return $this->belongsTo(AccessRule::class);
    }
}