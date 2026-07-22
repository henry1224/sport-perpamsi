<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venue extends Model
{
    protected $guarded = [];

    protected $casts = ['latitude' => 'float', 'longitude' => 'float'];

    public function agendas()
    {
        return $this->hasMany(EventAgenda::class);
    }
}
