<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SportRegulation extends Model
{
    protected $guarded = [];

    protected $casts = ['is_active' => 'boolean'];

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }
}
