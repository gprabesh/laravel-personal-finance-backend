<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Account extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::addGlobalScope('forUser', function (Builder $builder) {
            $builder->where('user_id', auth()->id())->where('id', '<>', auth()->user()->opening_balance_account_id);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function accountGroup()
    {
        return $this->belongsTo(AccountGroup::class, 'account_group_id');
    }
}
