<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SportCategory extends Model
{
    protected $guarded = [];

    protected $casts = [
        'bracket_enabled' => 'bool',
        'is_active' => 'bool',
    ];

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }
}
