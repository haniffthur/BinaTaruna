<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Enrollment extends Model {
    use HasFactory;
    protected $fillable = ['member_id', 'class_id', 'enrollment_date', 'status'];

protected $casts = [
        'enrollment_date' => 'date',
    ];

    public function member(): BelongsTo { return $this->belongsTo(Member::class); }
    public function schoolClass(): BelongsTo { return $this->belongsTo(SchoolClass::class, 'class_id'); }
}