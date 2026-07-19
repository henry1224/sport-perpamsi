<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatchScore extends Model
{
    protected $guarded = [];
    protected $casts = ['score_payload' => 'array', 'verified_at' => 'datetime'];
}
