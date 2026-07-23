<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sport extends Model
{
    public const FORMAT_LABELS = [
        'knockout' => 'Knockout',
        'group' => 'Fase Grup',
        'group_or_knockout' => 'Fase Grup atau Knockout',
        'group_then_knockout' => 'Fase Grup lalu Knockout',
        'round_robin' => 'Round Robin',
        'swiss' => 'Swiss',
        'score_ranking' => 'Peringkat Skor',
        'single_performance_ranking' => 'Sekali Tampil lalu Peringkat',
        'fun_games' => 'Fun Games',
    ];

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'official_roles' => 'array',
        'allow_member_cross_category' => 'boolean',
        'official_can_compete' => 'boolean',
        'default_max_officials_per_pd' => 'integer',
        'max_categories_per_member' => 'integer',
    ];

    public function categories(): HasMany
    {
        return $this->hasMany(SportCategory::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(TournamentEvent::class);
    }

    public function regulations(): HasMany
    {
        return $this->hasMany(SportRegulation::class);
    }
}
