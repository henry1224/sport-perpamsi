<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TournamentEvent extends Model
{
    protected $guarded = [];

    protected $casts = [
        'seed_locked_at' => 'datetime',
        'registration_rules' => 'array',
        'registration_published_at' => 'datetime',
        'registration_open_at' => 'datetime',
        'registration_close_at' => 'datetime',
    ];

    public function registrationIsOpen(): bool
    {
        return $this->registration_published_at
            && $this->status === 'registration_open'
            && (! $this->registration_open_at || $this->registration_open_at->isPast())
            && (! $this->registration_close_at || $this->registration_close_at->isFuture());
    }

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(SportCategory::class, 'sport_category_id');
    }

    public function regulation(): BelongsTo
    {
        return $this->belongsTo(SportRegulation::class, 'sport_regulation_id');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(EventEntry::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(TournamentMatch::class);
    }
}
