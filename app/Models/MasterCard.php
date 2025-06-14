<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
class MasterCard extends Model {
    use HasFactory;
    protected $fillable = ['card_uid', 'card_type', 'assignment_status'];
    public function member(): HasOne { return $this->hasOne(Member::class); }
    public function coach(): HasOne { return $this->hasOne(Coach::class); }
    public function staff(): HasOne { return $this->hasOne(Staff::class); }
    public function tapLogs(): HasMany { return $this->hasMany(TapLog::class); }
}