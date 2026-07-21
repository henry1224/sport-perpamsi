<?php

namespace Tests\Feature;

use App\Models\EventEntry;
use App\Models\TournamentEvent;
use App\Models\User;
use App\Support\Porpamnas\PublicDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RegionalCommitteeRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_entries_from_one_province_share_one_regional_committee(): void
    {
        $this->seed();

        $provinceId = DB::table('pdams')
            ->whereNotNull('province_id')
            ->groupBy('province_id')
            ->havingRaw('count(*) > 1')
            ->value('province_id');

        $committeeIds = DB::table('event_entries')
            ->join('pdams', 'event_entries.pdam_id', '=', 'pdams.id')
            ->where('pdams.province_id', $provinceId)
            ->pluck('event_entries.regional_committee_id')
            ->filter()
            ->unique();

        $this->assertNotNull($provinceId);
        $this->assertCount(1, $committeeIds);
        $committee = DB::table('regional_committees')->where('id', $committeeIds->first())->first();
        $province = DB::table('provinces')->find($committee->province_id);

        $this->assertSame('PD PERPAMSI '.$province->name, $committee->name);
    }

    public function test_pd_admin_can_register_only_their_province_and_entry_starts_pending(): void
    {
        $this->seed();

        $admin = User::query()->where('role', 'pd_admin')->with('committee')->firstOrFail();
        $event = TournamentEvent::query()->where('status', 'registration_open')->with('category')->firstOrFail();
        $existingPdamIds = EventEntry::query()->where('tournament_event_id', $event->id)->pluck('pdam_id');
        $pdam = DB::table('pdams')
            ->where('province_id', $admin->committee->province_id)
            ->whereNotIn('id', $existingPdamIds)
            ->first();

        if (! $pdam) {
            EventEntry::query()->where('tournament_event_id', $event->id)->where('regional_committee_id', $admin->regional_committee_id)->firstOrFail()->delete();
            $pdam = DB::table('pdams')->where('province_id', $admin->committee->province_id)->firstOrFail();
        }

        $payload = ['pdam_id' => $pdam->id];
        if (in_array($event->category?->competition_type, ['individual', 'doubles'], true)) $payload['athlete_1'] = 'Atlet Uji';
        if ($event->category?->competition_type === 'doubles') $payload['athlete_2'] = 'Atlet Uji Dua';
        if ($event->category?->competition_type === 'team') $payload['team_name'] = 'Tim Uji';

        $this->actingAs($admin)
            ->post(route('pd.events.entries.store', $event), $payload)
            ->assertRedirect();

        $this->assertDatabaseHas('event_entries', [
            'tournament_event_id' => $event->id,
            'pdam_id' => $pdam->id,
            'regional_committee_id' => $admin->regional_committee_id,
            'display_name' => $admin->committee->name,
            'verification_status' => 'pending',
        ]);

        $this->assertSame(
            'PD PERPAMSI '.DB::table('provinces')->find($admin->committee->province_id)->name,
            $admin->committee->name
        );

        $foreignPdam = DB::table('pdams')->where('province_id', '!=', $admin->committee->province_id)->firstOrFail();
        $this->actingAs($admin)
            ->post(route('pd.events.entries.store', $event), [...$payload, 'pdam_id' => $foreignPdam->id])
            ->assertSessionHasErrors('pdam_id');
    }

    public function test_seeded_semifinal_losers_are_counted_as_bronze(): void
    {
        $this->seed();

        $rankings = collect(app(PublicDataService::class)->pageProps()['provinceRankings']);

        $this->assertGreaterThan(0, $rankings->sum('gold'));
        $this->assertGreaterThan(0, $rankings->sum('silver'));
        $this->assertGreaterThan(0, $rankings->sum('bronze'));
        $this->assertSame($rankings->sum('gold') * 2, $rankings->sum('bronze'));
    }
}
