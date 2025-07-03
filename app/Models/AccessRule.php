<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class AccessRule extends Model {
    use HasFactory;

    protected $table = 'access_rules';
    protected $fillable = [
        'name', 'description', 'max_taps_per_day', 'max_taps_per_month',
        'allowed_days', 'start_time', 'end_time'
    ];
    protected $casts = ['allowed_days' => 'array'];
}