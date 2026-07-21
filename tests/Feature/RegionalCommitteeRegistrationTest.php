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

    public function test_pd_admin_registers_members_without_pdam_and_entry_starts_pending(): void
    {
        $this->seed();

        $admin = User::query()->where('role', 'pd_admin')->with('committee')->firstOrFail();
        $event = TournamentEvent::query()->with('category')->firstOrFail();
        $event->update(['status' => 'registration_open']);
        EventEntry::query()->where('tournament_event_id', $event->id)->where('regional_committee_id', $admin->regional_committee_id)->delete();
        $payload = ['members' => collect(range(1, $event->category->min_members))->map(fn ($number) => ['name' => 'Pemain Uji '.$number])->all()];

        $this->actingAs($admin)
            ->post(route('pd.events.entries.store', $event), $payload)
            ->assertRedirect();

        $this->assertDatabaseHas('event_entries', [
            'tournament_event_id' => $event->id,
            'pdam_id' => null,
            'regional_committee_id' => $admin->regional_committee_id,
            'registration_key' => $event->id.':'.$admin->regional_committee_id,
            'display_name' => $admin->committee->name,
            'verification_status' => 'pending',
        ]);
        $this->assertDatabaseHas('entry_members', ['name' => 'Pemain Uji 1']);

        $this->assertSame(
            'PD PERPAMSI '.DB::table('provinces')->find($admin->committee->province_id)->name,
            $admin->committee->name
        );

        $this->actingAs($admin)
            ->post(route('pd.events.entries.store', $event), $payload)
            ->assertSessionHasErrors('members');
    }

    public function test_member_limits_and_duplicate_names_are_validated(): void
    {
        $this->seed();

        $admin = User::query()->where('role', 'pd_admin')->firstOrFail();
        $event = TournamentEvent::query()->whereHas('category', fn ($query) => $query->where('min_members', 2))->with('category')->firstOrFail();
        $event->update(['status' => 'registration_open']);
        EventEntry::query()->where('tournament_event_id', $event->id)->where('regional_committee_id', $admin->regional_committee_id)->delete();

        $this->actingAs($admin)
            ->post(route('pd.events.entries.store', $event), ['members' => [['name' => 'Pemain Sama'], ['name' => ' pemain sama ']]])
            ->assertSessionHasErrors('members');

        $this->actingAs($admin)
            ->post(route('pd.events.entries.store', $event), ['members' => []])
            ->assertSessionHasErrors('members');
    }

    public function test_admin_verification_records_actor_and_time(): void
    {
        $this->seed();

        $admin = User::query()->where('role', 'super_admin')->firstOrFail();
        $entry = EventEntry::query()->firstOrFail();
        $entry->update(['verification_status' => 'pending', 'verified_by' => null, 'verified_at' => null]);

        $this->actingAs($admin)->post(route('admin.entries.verify', $entry))->assertRedirect();

        $this->assertDatabaseHas('event_entries', [
            'id' => $entry->id,
            'verification_status' => 'verified',
            'verified_by' => $admin->id,
        ]);
        $this->assertNotNull($entry->fresh()->verified_at);
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
