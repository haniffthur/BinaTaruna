<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
class Ticket extends Model {
    use HasFactory;
    protected $fillable = ['name', 'description', 'price'];
    public function transactionDetails(): MorphMany {
        return $this->morphMany(TransactionDetail::class, 'purchasable');
    }
}