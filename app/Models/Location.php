<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Location extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::addGlobalScope('forUser', function (Builder $builder) {
            $builder->where('user_id', auth()->id());
        });
        static::creating(function (Location $location) {
            $location->user_id = auth()->id();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
