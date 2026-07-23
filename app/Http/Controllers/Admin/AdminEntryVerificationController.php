<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventEntry;
use App\Models\EntryTeam;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminEntryVerificationController extends Controller
{
    public function index(Request $request): Response
    {
        $perPage = min($request->integer('per_page', 10), 100);
        $search = trim((string) $request->query('search'));
        $status = (string) $request->query('status');

        $entries = EventEntry::query()
            ->with([
                'teams.members:id,event_entry_id,entry_team_id,name',
                'members' => fn ($query) => $query->where('member_type', 'official')->select('id', 'event_entry_id', 'name', 'position'),
                'regionalCommittee:id,name',
                'tournamentEvent:id,code,name,status',
            ])
            ->where('verification_status', 'pending')
            ->when($status, fn ($query) => $query->whereHas('tournamentEvent', fn ($query) => $query->where('status', $status)))
            ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                $query->whereLike('display_name', "%{$search}%", caseSensitive: false)
                    ->orWhereHas('regionalCommittee', fn ($query) => $query->whereLike('name', "%{$search}%", caseSensitive: false))
                    ->orWhereHas('tournamentEvent', fn ($query) => $query->whereLike('name', "%{$search}%", caseSensitive: false))
                    ->orWhereHas('members', fn ($query) => $query->whereLike('name', "%{$search}%", caseSensitive: false));
            }))
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn ($entry) => [
                'id' => $entry->id,
                'display_name' => $entry->display_name,
                'teams' => $entry->teams->map(fn ($team) => ['id' => $team->id, 'label' => $team->label, 'members' => $team->members->pluck('name'), 'override' => $team->verification_status_override, 'effective_status' => $team->effectiveStatus()]),
                'officials' => $entry->members->map(fn ($member) => ['name' => $member->name, 'role' => $member->position]),
                'committee' => $entry->regionalCommittee?->name,
                'event' => $entry->tournamentEvent?->name,
                'event_code' => $entry->tournamentEvent?->code,
                'event_status' => $entry->tournamentEvent?->status,
                'created_at' => (string) $entry->created_at,
            ]);

        return Inertia::render('Admin/Entries', [
            'entries' => $entries,
            'filters' => ['search' => $search, 'status' => $status, 'per_page' => $perPage],
        ]);
    }

    public function verify(Request $request, EventEntry $entry): RedirectResponse
    {
        abort_unless($entry->verification_status === 'pending', 422, 'Hanya entry menunggu yang dapat diverifikasi.');
        $before = $this->state($entry);
        // ponytail: bracket tidak di-rebuild otomatis walau event sudah bracket_locked; tambahkan RebuildBracket action jika late-registration jadi kebutuhan.
        $entry->update([
            'verification_status' => 'verified',
            'verification_note' => null,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
        ]);
        $this->audit($entry, 'verified', $before, $request);

        return back()->with('success', 'Entry disetujui.');
    }

    public function reject(Request $request, EventEntry $entry): RedirectResponse
    {
        abort_unless($entry->verification_status === 'pending', 422, 'Hanya entry menunggu yang dapat ditolak.');
        $data = $request->validate([
            'note' => ['required', 'string', 'max:255'],
        ]);
        $before = $this->state($entry);

        $entry->update([
            'verification_status' => 'rejected',
            'verification_note' => $data['note'],
            'verified_by' => null,
            'verified_at' => null,
        ]);
        $this->audit($entry, 'rejected', $before, $request);

        return back()->with('success', 'Entry ditolak.');
    }

    public function revision(Request $request, EventEntry $entry): RedirectResponse
    {
        abort_unless($entry->verification_status === 'pending', 422, 'Hanya entry menunggu yang dapat diminta perbaikan.');
        $data = $request->validate(['note' => ['required', 'string', 'max:255']]);
        $before = $this->state($entry);
        $entry->update(['verification_status' => 'revision_required', 'verification_note' => $data['note'], 'verified_by' => null, 'verified_at' => null]);
        $this->audit($entry, 'revision_required', $before, $request);

        return back()->with('success', 'Perbaikan roster diminta.');
    }

    public function overrideTeam(Request $request, EntryTeam $team): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:revision_required,verified,rejected,cancelled'], 'reason' => ['required', 'string', 'max:255']]);
        $before = $team->toArray();
        $team->update(['verification_status_override' => $data['status'], 'verification_note' => $data['reason'], 'verified_by' => $data['status'] === 'verified' ? $request->user()->id : null, 'verified_at' => $data['status'] === 'verified' ? now() : null, 'cancelled_at' => $data['status'] === 'cancelled' ? now() : null]);
        $this->auditTeam($team, 'override_set', $before, $data['reason'], $request);
        return back()->with('success', 'Status tim diperbarui.');
    }

    public function resetTeamOverride(Request $request, EntryTeam $team): RedirectResponse
    {
        $before = $team->toArray();
        $team->update(['verification_status_override' => null, 'verification_note' => null, 'verified_by' => null, 'verified_at' => null, 'cancelled_at' => null]);
        $this->auditTeam($team, 'override_reset', $before, 'Reset ke status parent', $request);
        return back()->with('success', 'Override tim direset.');
    }

    private function audit(EventEntry $entry, string $action, array $before, Request $request): void
    {
        DB::table('entry_registration_audits')->insert(['event_entry_id' => $entry->id, 'action' => $action, 'before_json' => json_encode($before), 'after_json' => json_encode($this->state($entry)), 'user_id' => $request->user()->id, 'created_at' => now(), 'updated_at' => now()]);
    }

    private function state(EventEntry $entry): array
    {
        return ['status' => $entry->verification_status, 'note' => $entry->verification_note, 'teams' => $entry->teams()->with('members')->get()->toArray(), 'officials' => $entry->members()->where('member_type', 'official')->get()->toArray()];
    }

    private function auditTeam(EntryTeam $team, string $action, array $before, string $reason, Request $request): void
    {
        DB::table('entry_team_audits')->insert(['entry_team_id' => $team->id, 'action' => $action, 'before_json' => json_encode($before), 'after_json' => json_encode($team->fresh()->toArray()), 'reason' => $reason, 'user_id' => $request->user()->id, 'created_at' => now(), 'updated_at' => now()]);
    }
}
