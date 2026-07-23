<?php

namespace Tests\Feature;

use App\Models\EventEntry;
use App\Models\Pdam;
use App\Models\TournamentEvent;
use App\Models\User;
use App\Support\Porpamnas\PublicDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RegionalCommitteeRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_entries_from_one_province_share_one_regional_committee(): void
    {
        $this->seed();

        $entry = DB::table('event_entries')->whereNull('pdam_id')->firstOrFail();
        $committee = DB::table('regional_committees')->find($entry->regional_committee_id);
        $province = DB::table('provinces')->find($committee->province_id);

        $this->assertSame($committee->province_id, $entry->province_id);
        $this->assertSame('PD PERPAMSI '.$province->name, $committee->name);
    }

    public function test_pd_admin_registers_members_without_pdam_and_entry_starts_pending(): void
    {
        $this->seed();

        $admin = User::query()->where('role', 'pd_admin')->with('committee')->firstOrFail();
        $event = TournamentEvent::query()->with('category')->firstOrFail();
        $event->update([
            'status' => 'registration_open',
            'registration_published_at' => now(),
            'registration_rules' => [
                'category_name' => $event->category->name,
                'competition_type' => $event->category->competition_type,
                'scoring_type' => $event->category->scoring_type,
                'format' => $event->format,
                'min_members' => $event->category->min_members,
                'max_members' => $event->category->max_members,
            ],
        ]);
        EventEntry::query()->where('tournament_event_id', $event->id)->where('regional_committee_id', $admin->regional_committee_id)->delete();
        $payload = ['teams' => [['members' => $this->submissionMembers($event->category->min_members, 'Pemain Uji')]]];

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
        $event->update([
            'status' => 'registration_open',
            'registration_published_at' => now(),
            'registration_rules' => [
                'category_name' => $event->category->name,
                'competition_type' => $event->category->competition_type,
                'scoring_type' => $event->category->scoring_type,
                'format' => $event->format,
                'min_members' => $event->category->min_members,
                'max_members' => $event->category->max_members,
            ],
        ]);
        EventEntry::query()->where('tournament_event_id', $event->id)->where('regional_committee_id', $admin->regional_committee_id)->delete();

        $this->actingAs($admin)
            ->post(route('pd.events.entries.store', $event), ['members' => [['name' => 'Pemain Sama'], ['name' => ' pemain sama ']]])
            ->assertSessionHasErrors('members');

        $this->actingAs($admin)
            ->post(route('pd.events.entries.store', $event), ['members' => []])
            ->assertSessionHasErrors('members');

        $tooMany = collect(range(1, $event->category->max_members + 1))->map(fn ($number) => ['name' => 'Pemain Batas '.$number])->all();
        $this->actingAs($admin)
            ->post(route('pd.events.entries.store', $event), ['members' => $tooMany])
            ->assertSessionHasErrors('members');
    }

    public function test_category_without_maximum_accepts_larger_roster_and_keeps_snapshot(): void
    {
        $this->seed();

        $admin = User::query()->where('role', 'pd_admin')->firstOrFail();
        $event = TournamentEvent::query()->whereHas('category', fn ($query) => $query->whereNull('max_members'))->with('category')->firstOrFail();
        $event->update([
            'status' => 'registration_open',
            'registration_published_at' => now(),
            'registration_rules' => [
                'category_name' => $event->category->name,
                'competition_type' => $event->category->competition_type,
                'scoring_type' => $event->category->scoring_type,
                'format' => $event->format,
                'min_members' => $event->category->min_members,
                'max_members' => null,
            ],
        ]);
        $event->category->update(['max_members' => 1]);
        EventEntry::query()->where('tournament_event_id', $event->id)->where('regional_committee_id', $admin->regional_committee_id)->delete();
        $members = $this->submissionMembers(5, 'Pegolf');

        $this->actingAs($admin)
            ->get(route('pd.events.show', $event))
            ->assertInertia(fn ($page) => $page->where('category.max_members', null));

        $this->actingAs($admin)
            ->post(route('pd.events.entries.store', $event), ['teams' => [['members' => $members]]])
            ->assertSessionHasNoErrors();

        $entry = EventEntry::query()->where('tournament_event_id', $event->id)->where('regional_committee_id', $admin->regional_committee_id)->firstOrFail();
        $this->assertCount(5, $entry->members);
    }

    public function test_pd_only_sees_registration_published_by_admin(): void
    {
        $this->seed();

        $admin = User::query()->where('role', 'pd_admin')->firstOrFail();
        $published = TournamentEvent::query()->whereNotNull('registration_published_at')->firstOrFail();
        $hidden = TournamentEvent::query()->whereNull('registration_published_at')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('pd.dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('events.data', fn ($events) => collect($events)->pluck('code')->contains($published->code)
                    && ! collect($events)->pluck('code')->contains($hidden->code)));

        $this->actingAs($admin)->get(route('pd.events.show', $hidden))->assertNotFound();
    }

    public function test_published_registration_uses_locked_rule_snapshot(): void
    {
        $this->seed();

        $admin = User::query()->where('role', 'pd_admin')->firstOrFail();
        $event = TournamentEvent::query()->whereNotNull('registration_published_at')->with('category')->firstOrFail();
        $lockedMinimum = $event->registration_rules['min_members'];
        $event->category->update(['min_members' => $lockedMinimum + 5, 'max_members' => $lockedMinimum + 5]);
        EventEntry::query()->where('tournament_event_id', $event->id)->where('regional_committee_id', $admin->regional_committee_id)->delete();
        $payload = ['teams' => [['members' => $this->submissionMembers($lockedMinimum, 'Pemain Snapshot')]]];

        $this->actingAs($admin)
            ->post(route('pd.events.entries.store', $event), $payload)
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('event_entries', [
            'tournament_event_id' => $event->id,
            'regional_committee_id' => $admin->regional_committee_id,
        ]);
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

    private function submissionMembers(int $count, string $prefix): array
    {
        return collect(range(1, $count))->map(function ($number) use ($prefix) {
            $member = ['name' => $prefix.' '.$number, 'pdam_id' => Pdam::query()->value('id'), 'identity_type' => 'nik', 'identity_number' => '3173'.str_pad((string) $number, 12, '0', STR_PAD_LEFT)];
            foreach (['photo', 'registration_form', 'identity_card', 'pension_card', 'employee_decree'] as $key) $member['documents'][$key] = UploadedFile::fake()->create($key.'.pdf', 100, 'application/pdf');
            return $member;
        })->all();
    }
}
