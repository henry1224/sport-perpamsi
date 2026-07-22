<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventEntry extends Model
{
    protected $guarded = [];

    public function tournamentEvent(): BelongsTo
    {
        return $this->belongsTo(TournamentEvent::class);
    }

    public function pdam(): BelongsTo
    {
        return $this->belongsTo(Pdam::class);
    }

    public function regionalCommittee(): BelongsTo
    {
        return $this->belongsTo(RegionalCommittee::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(EntryMember::class);
    }

    public function teams(): HasMany
    {
        return $this->hasMany(EntryTeam::class)->orderBy('team_no');
    }
}
