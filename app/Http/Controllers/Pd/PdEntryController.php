<?php

namespace App\Http\Controllers\Pd;

use App\Actions\Entries\RegisterEventEntry;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pd\StoreEventEntryRequest;
use App\Models\EventEntry;
use App\Models\TournamentEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

class PdEntryController extends Controller
{
    public function show(Request $request, TournamentEvent $event): Response
    {
        abort_unless($event->registration_published_at, 404);

        $user = $request->user()->loadMissing('committee');
        $event->loadMissing(['sport:id,code,name', 'category:id,name,competition_type,scoring_type,bracket_enabled,min_members,max_members']);

        $entries = EventEntry::query()
            ->with(['teams.members:id,event_entry_id,entry_team_id,pdam_id,name,identity_type,identity_number,identity_hash,documents', 'teams.members.pdam:id,name', 'members' => fn ($query) => $query->where('member_type', 'official')->select('id', 'event_entry_id', 'name', 'normalized_name', 'identity_type', 'identity_number', 'identity_hash', 'position', 'documents')])
            ->where('tournament_event_id', $event->id)
            ->where('regional_committee_id', $user->regional_committee_id)
            ->latest('id')
            ->get();
        $officialIdentities = $entries->flatMap->members->pluck('identity_hash')->filter()->unique();
        $playingSports = $officialIdentities->isEmpty() ? collect() : DB::table('entry_members')
            ->join('event_entries', 'entry_members.event_entry_id', '=', 'event_entries.id')
            ->join('tournament_events', 'event_entries.tournament_event_id', '=', 'tournament_events.id')
            ->join('sports', 'tournament_events.sport_id', '=', 'sports.id')
            ->where('entry_members.member_type', 'player')->where('event_entries.regional_committee_id', $user->regional_committee_id)
            ->whereIn('event_entries.verification_status', ['draft', 'pending', 'verified', 'revision_required'])
            ->whereIn('entry_members.identity_hash', $officialIdentities)->get(['entry_members.identity_hash', 'sports.name'])
            ->groupBy('identity_hash')->map(fn ($rows) => $rows->pluck('name')->unique()->values()->all());
        $entries = $entries
            ->map(fn ($entry) => [
                'id' => $entry->id,
                'display_name' => $entry->display_name,
                'teams' => $entry->teams->map(fn ($team) => ['id' => $team->id, 'team_no' => $team->team_no, 'label' => $team->label, 'members' => $team->members->map(fn ($member) => $this->memberPayload($member)), 'status' => $team->effectiveStatus(), 'override' => $team->verification_status_override]),
                'officials' => $entry->members->map(fn ($member) => $this->memberPayload($member) + ['role' => $member->position, 'playing_sports' => $playingSports->get($member->identity_hash, [])]),
                'verification_status' => $entry->verification_status,
                'verification_note' => $entry->verification_note,
            ]);

        $rules = $event->registration_rules ?? [];

        return Inertia::render('Pd/EventEntries', [
            'event' => [
                'code' => $event->code,
                'name' => $event->name,
                'status' => $event->status,
                'registration_open' => $event->registrationIsOpen(),
                'format' => $rules['format'] ?? $event->format,
                'sport' => $event->sport?->name,
            ],
            'category' => $event->category ? [
                'name' => $rules['category_name'] ?? $event->category->name,
                'competition_type' => $rules['competition_type'] ?? $event->category->competition_type,
                'scoring_type' => $rules['scoring_type'] ?? $event->category->scoring_type,
                'bracket_enabled' => (bool) $event->category->bracket_enabled,
                'min_members' => $rules['min_members'] ?? $event->category->min_members,
                'max_members' => array_key_exists('max_members', $rules) ? $rules['max_members'] : $event->category->max_members,
                'max_teams_per_pd' => $rules['max_teams_per_pd'] ?? 1,
                'max_officials_per_pd' => $rules['max_officials_per_pd'] ?? 0,
                'official_roles' => $rules['official_roles'] ?? [],
                'official_can_compete' => (bool) ($rules['official_can_compete'] ?? false),
            ] : null,
            'entries' => $entries,
            'pdams' => DB::table('pdams')->leftJoin('provinces', 'pdams.province_id', '=', 'provinces.id')->orderBy('pdams.name')->get(['pdams.id', 'pdams.name', 'pdams.city', 'provinces.name as province']),
        ]);
    }

    private function memberPayload($member): array
    {
        return ['id' => $member->id, 'name' => $member->name, 'pdam_id' => $member->pdam_id, 'pdam_name' => $member->pdam?->name, 'identity_type' => $member->identity_type, 'identity_number' => $member->identity_number, 'documents' => array_keys($member->documents ?? [])];
    }

    public function store(StoreEventEntryRequest $request, TournamentEvent $event, RegisterEventEntry $action): RedirectResponse
    {
        try {
            $action->handle($request->user(), $event, $request->validated());
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return back()->with('success', $request->validated('intent') === 'draft' ? 'Draft roster disimpan.' : 'Pendaftaran diajukan. Menunggu verifikasi.');
    }

    public function destroy(Request $request, EventEntry $entry): RedirectResponse
    {
        $user = $request->user();

        abort_unless(
            $entry->regional_committee_id === $user->regional_committee_id && in_array($entry->verification_status, ['draft', 'pending', 'revision_required', 'rejected'], true),
            403,
            'Pendaftaran ini tidak dapat dibatalkan.'
        );

        $before = ['status' => $entry->verification_status, 'note' => $entry->verification_note, 'teams' => $entry->teams()->with('members')->get()->toArray(), 'officials' => $entry->members()->where('member_type', 'official')->get()->toArray()];
        $entry->update(['verification_status' => 'cancelled', 'submitted_at' => null, 'verified_by' => null, 'verified_at' => null]);
        DB::table('entry_registration_audits')->insert(['event_entry_id' => $entry->id, 'action' => 'cancelled', 'before_json' => json_encode($before), 'after_json' => json_encode(['status' => 'cancelled', 'note' => $entry->verification_note, 'teams' => $before['teams'], 'officials' => $before['officials']]), 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()]);

        return back()->with('success', 'Pendaftaran dibatalkan.');
    }
}
