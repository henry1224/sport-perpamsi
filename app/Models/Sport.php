<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sport extends Model
{
    protected $guarded = [];

    protected $casts = ['is_active' => 'boolean'];

    public function categories(): HasMany
    {
        return $this->hasMany(SportCategory::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(TournamentEvent::class);
    }

    public function regulations(): HasMany
    {
        return $this->hasMany(SportRegulation::class);
    }
}
