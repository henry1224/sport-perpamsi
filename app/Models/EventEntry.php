<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
