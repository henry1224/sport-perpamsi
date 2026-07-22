<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SportRegulation extends Model
{
    protected $guarded = [];

    protected $casts = ['technical_guide' => 'array', 'is_active' => 'boolean'];

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(TournamentEvent::class);
    }
}
