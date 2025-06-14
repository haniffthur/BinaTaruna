<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'master_card_id',
        'access_rule_id',
        'name',
        'address',
        'phone_number',
        'date_of_birth',
        'parent_name',
        'photo',
        'school_class_id',
        'join_date',
        'rule_type',
        'max_taps_per_day',
        'max_taps_per_month',
        'allowed_days',
        'start_time',
        'end_time',
        'daily_tap_reset_at',
        'monthly_tap_reset_at',
    ];

    protected $casts = [
        'allowed_days' => 'array',
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
        'daily_tap_reset_at' => 'datetime',
        'monthly_tap_reset_at' => 'datetime',
        'join_date' => 'date',
        'date_of_birth' => 'date',
    ];

    // RELATIONS
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    public function masterCard(): BelongsTo 
    {
        return $this->belongsTo(MasterCard::class);
    }

    public function accessRule(): BelongsTo 
    {
        return $this->belongsTo(AccessRule::class);
    }

    public function enrollments(): HasMany 
    {
        return $this->hasMany(Enrollment::class);
    }

    public function transactions(): HasMany 
    {
        return $this->hasMany(MemberTransaction::class);
    }

    public function tapLogs()
    {
        return $this->hasManyThrough(TapLog::class, MasterCard::class, 'id', 'master_card_id', 'master_card_id', 'id');
    }
}
