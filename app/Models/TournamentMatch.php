<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TournamentMatch extends Model
{
    protected $table = 'matches';
    protected $guarded = [];

    public function entryA(): BelongsTo
    {
        return $this->belongsTo(EventEntry::class, 'entry_a_id');
    }

    public function entryB(): BelongsTo
    {
        return $this->belongsTo(EventEntry::class, 'entry_b_id');
    }
}
