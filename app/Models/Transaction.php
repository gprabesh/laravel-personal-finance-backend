<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function transactionType()
    {
        return $this->belongsTo(TransactionType::class, 'transaction_type_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function parent()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    public function people()
    {
        return $this->belongsToMany(People::class, 'pivot_transactions_people', 'transaction_id', 'people_id');
    }
}
