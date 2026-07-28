<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventAgenda extends Model
{
    protected $guarded = [];

    protected $casts = ['date' => 'date', 'published_at' => 'datetime'];

    public function matches(): HasMany
    {
        return $this->hasMany(TournamentMatch::class);
    }
}
