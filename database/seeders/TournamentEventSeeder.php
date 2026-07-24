<?php

namespace Database\Seeders;

use App\Models\Sport;
use App\Models\TournamentEvent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TournamentEventSeeder extends Seeder
{
    public function run(): void
    {
        $sport = Sport::query()->where('code', 'badminton')->where('is_active', true)
            ->with(['categories' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order'), 'regulations' => fn ($query) => $query->where('is_active', true)->latest('version')])
            ->first();
        $regulation = $sport?->regulations->first();

        if (! $sport || ! $regulation) {
            return;
        }

        foreach ($sport->categories as $category) {
            $event = TournamentEvent::query()->firstOrNew(['sport_id' => $sport->id, 'sport_category_id' => $category->id]);

            $event->fill([
                'public_id' => $event->public_id ?: (string) Str::uuid(),
                'sport_regulation_id' => $regulation->id,
                'code' => $sport->code.'-'.$category->code,
                'name' => $sport->name.' - '.$category->name,
                'format' => $sport->default_format,
                'status' => 'registration_draft',
                'registration_rules' => [
                    'category_name' => $category->name,
                    'competition_type' => $category->competition_type,
                    'scoring_type' => $category->scoring_type,
                    'format' => $sport->default_format,
                    'min_members' => $category->min_members,
                    'max_members' => $category->max_members,
                    'max_teams_per_pd' => $category->default_max_teams_per_pd,
                    'min_members_per_team' => $category->min_members,
                    'max_members_per_team' => $category->max_members,
                    'max_officials_per_pd' => $sport->default_max_officials_per_pd,
                    'official_roles' => $sport->official_roles ?? [],
                    'allow_member_cross_category' => $sport->allow_member_cross_category,
                    'max_categories_per_member' => $sport->max_categories_per_member,
                    'official_can_compete' => $sport->official_can_compete,
                ],
                'registration_published_at' => null,
                'registration_published_by' => null,
                'registration_open_at' => null,
                'registration_close_at' => null,
                'bracket_size' => null,
                'seed_locked_at' => null,
            ])->save();
        }
    }
}
