<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    protected $fillable = [
        'total_amount',
        'payment_amount',
    ];

    // A transaction has many transaction details (products sold)
    public function details(): HasMany
    {
        return $this->hasMany(TransactionDetail::class);
    }
}
