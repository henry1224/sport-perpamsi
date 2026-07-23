<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntryMember extends Model
{
    protected $guarded = [];

    protected $casts = ['documents' => 'array'];

    public function eventEntry(): BelongsTo
    {
        return $this->belongsTo(EventEntry::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(EntryTeam::class, 'entry_team_id');
    }

    public function pdam(): BelongsTo
    {
        return $this->belongsTo(Pdam::class);
    }
}
