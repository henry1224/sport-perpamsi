<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Province extends Model
{
    protected $guarded = [];

    public function regencies(): HasMany
    {
        return $this->hasMany(Regency::class);
    }

    public function pdams(): HasMany
    {
        return $this->hasMany(Pdam::class);
    }

    public function committee(): HasOne
    {
        return $this->hasOne(RegionalCommittee::class);
    }
}
