<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Account extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'current_balance' => 'double',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope('forUser', function (Builder $builder) {
            $builder->where('user_id', auth()->id())->whereNotIn('id', [auth()->user()->opening_balance_account_id, auth()->user()->transfer_charge_account_id]);
        });
        static::creating(function (Account $account) {
            $account->user_id = auth()->id();
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
