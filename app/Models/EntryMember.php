<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntryMember extends Model
{
    protected $guarded = [];

    public function eventEntry(): BelongsTo
    {
        return $this->belongsTo(EventEntry::class);
    }
}
