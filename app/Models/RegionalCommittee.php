<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RegionalCommittee extends Model
{
    protected $guarded = [];

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(EventEntry::class);
    }

    public function application(): HasOne
    {
        return $this->hasOne(CommitteeApplication::class);
    }
}
