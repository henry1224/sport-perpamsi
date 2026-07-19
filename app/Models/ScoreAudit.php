<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScoreAudit extends Model
{
    protected $guarded = [];
    protected $casts = ['before_json' => 'array', 'after_json' => 'array'];
}
