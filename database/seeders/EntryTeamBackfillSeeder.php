<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EntryTeamBackfillSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('event_entries')->orderBy('id')->each(function ($entry): void {
            $teamId = DB::table('entry_teams')->where('event_entry_id', $entry->id)->where('team_no', 1)->value('id')
                ?: DB::table('entry_teams')->insertGetId(['public_id' => (string) Str::uuid(), 'event_entry_id' => $entry->id, 'team_no' => 1, 'label' => $entry->display_name.' #1', 'created_at' => $entry->created_at, 'updated_at' => $entry->updated_at]);
            DB::table('entry_members')->where('event_entry_id', $entry->id)->whereNull('entry_team_id')->update(['entry_team_id' => $teamId]);
            DB::table('matches')->where('entry_a_id', $entry->id)->whereNull('team_a_id')->update(['team_a_id' => $teamId]);
            DB::table('matches')->where('entry_b_id', $entry->id)->whereNull('team_b_id')->update(['team_b_id' => $teamId]);
            DB::table('matches')->where('winner_entry_id', $entry->id)->whereNull('winner_team_id')->update(['winner_team_id' => $teamId]);
        });

        DB::table('entry_members')->whereNull('identity_hash')->orderBy('id')->each(fn ($member) => DB::table('entry_members')->where('id', $member->id)->update(['identity_hash' => hash('sha256', mb_strtolower(trim($member->name)))]));
    }
}
