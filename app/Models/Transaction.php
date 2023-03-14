<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'desc',
        'total_amount',
        'payment_method',
        'courier_method',
        'status',
    ];

    public function transaction_details() {
        return $this->hasMany(TransactionDetail::class, 'transaction_id', 'id');
    }
}
