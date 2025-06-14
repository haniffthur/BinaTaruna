<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
class SchoolClass extends Model {
    use HasFactory;
    
    protected $table = 'classes'; // Penting: Menentukan nama tabel secara eksplisit
    protected $fillable = ['name', 'description', 'price'];
    public function transactionDetails(): MorphMany {
        return $this->morphMany(TransactionDetail::class, 'purchasable');
    }
}