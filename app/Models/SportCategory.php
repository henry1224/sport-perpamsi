<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SportCategory extends Model
{
    protected $guarded = [];

    protected $casts = [
        'bracket_enabled' => 'bool',
        'is_active' => 'bool',
        'min_members' => 'integer',
        'max_members' => 'integer',
        'default_max_teams_per_pd' => 'integer',
    ];

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(TournamentEvent::class);
    }
}
