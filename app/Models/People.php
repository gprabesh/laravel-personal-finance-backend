<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class People extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::addGlobalScope('forUser', function (Builder $builder) {
            $builder->where('user_id', auth()->id());
        });
        static::creating(function (People $people) {
            $people->user_id = auth()->id();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function transactions()
    {
        return $this->belongsToMany(Transaction::class, 'pivot_transactions_people', 'people_id', 'transaction_id');
    }
}
