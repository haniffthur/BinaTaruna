<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class NonMemberTransaction extends Model {
    use HasFactory;
    protected $fillable = [
        'customer_name', 'qr_code_token', 'qr_code_status', 'validated_at',
        'total_amount', 'amount_paid', 'change', 'transaction_date'
    ];

     public function details(): MorphMany
    {
        // Pastikan Anda memiliki model 'TransactionDetail'
        return $this->morphMany(TransactionDetail::class, 'detailable');
    }
    
 
}