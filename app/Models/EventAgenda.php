<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventAgenda extends Model
{
    protected $guarded = [];

    protected $casts = ['date' => 'date', 'published_at' => 'datetime'];
}
