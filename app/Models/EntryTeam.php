<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EntryTeam extends Model
{
    protected $guarded = [];

    protected $casts = ['verified_at' => 'datetime', 'cancelled_at' => 'datetime'];

    public function eventEntry(): BelongsTo
    {
        return $this->belongsTo(EventEntry::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(EntryMember::class);
    }

    public function effectiveStatus(): string
    {
        return $this->verification_status_override ?? $this->eventEntry->verification_status;
    }
}
