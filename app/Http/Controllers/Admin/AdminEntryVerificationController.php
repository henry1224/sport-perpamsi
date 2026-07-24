<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventEntry;
use App\Models\EntryTeam;
use App\Models\EntryMember;
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
        $event = (string) $request->query('event');

        $entries = EventEntry::query()
            ->with([
                'teams' => fn ($query) => $query->whereNull('cancelled_at'),
                'teams.members:id,event_entry_id,entry_team_id,pdam_id,name,identity_type,identity_number,documents,verification_status,verification_note,verified_by,verified_at,created_at,updated_at',
                'teams.members.pdam:id,name',
                'members' => fn ($query) => $query->where('member_type', 'official')->select('id', 'event_entry_id', 'name', 'identity_type', 'identity_number', 'position', 'documents', 'created_at', 'updated_at'),
                'regionalCommittee:id,name',
                'tournamentEvent:id,code,name,status',
            ])
            ->whereIn('verification_status', ['pending', 'rejected'])
            ->when($event, fn ($query) => $query->whereHas('tournamentEvent', fn ($query) => $query->where('code', $event)))
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
                'verification_status' => $entry->verification_status,
                'verification_note' => $entry->verification_note,
                'display_name' => $entry->display_name,
                'teams' => $entry->teams->map(fn ($team) => ['id' => $team->id, 'label' => $team->label, 'members' => $team->members->map(fn ($member) => $this->memberPayload($member)), 'players_count' => $team->members->count(), 'verified_players_count' => $team->members->where('verification_status', 'verified')->count(), 'override' => $team->verification_status_override, 'effective_status' => $team->effectiveStatus()]),
                'officials' => $entry->members->map(fn ($member) => $this->memberPayload($member) + ['role' => $member->position]),
                'players_count' => $entry->teams->sum(fn ($team) => $team->members->count()),
                'verified_players_count' => $entry->teams->sum(fn ($team) => $team->members->where('verification_status', 'verified')->count()),
                'teams_ready' => $entry->teams->isNotEmpty() && $entry->teams->every(fn ($team) => $team->effectiveStatus() === 'verified'),
                'committee' => $entry->regionalCommittee?->name,
                'event' => $entry->tournamentEvent?->name,
                'event_code' => $entry->tournamentEvent?->code,
                'event_status' => $entry->tournamentEvent?->status,
                'submitted_at' => $entry->submitted_at?->format('d M Y H:i'),
                'created_at' => (string) $entry->created_at,
            ]);

        return Inertia::render('Admin/Entries', [
            'entries' => $entries,
            'filters' => ['search' => $search, 'status' => $status, 'event' => $event, 'per_page' => $perPage],
        ]);
    }

    private function memberPayload($member): array
    {
        return [
            'id' => $member->id,
            'name' => $member->name,
            'pdam' => $member->pdam?->name ?: 'PDAM belum diisi',
            'identity_type' => $member->identity_type,
            'identity_number' => $member->identity_number,
            'identity' => $member->identity_number ? strtoupper($member->identity_type).' · '.$member->identity_number : 'Identitas belum diisi',
            'documents' => collect($member->documents ?? [])->keys()->map(fn ($key) => ['key' => $key, 'url' => route('entry-members.documents.show', [$member, $key])])->values(),
            'document_count' => count($member->documents ?? []),
            'verification_status' => $member->verification_status,
            'verification_note' => $member->verification_note,
            'verified_at' => $member->verified_at?->format('d M Y H:i'),
            'updated_at' => $member->updated_at?->format('d M Y H:i'),
            'audits' => DB::table('entry_member_audits')->where('entry_member_id', $member->id)->latest('id')->get(['action', 'reason', 'created_at']),
        ];
    }

    public function verify(Request $request, EventEntry $entry): RedirectResponse
    {
        abort_unless($entry->verification_status === 'pending', 422, 'Hanya entry menunggu yang dapat diverifikasi.');
        $activeTeams = $entry->teams()->whereNull('cancelled_at')->get();
        abort_if($activeTeams->isEmpty() || $activeTeams->contains(fn ($team) => $team->effectiveStatus() !== 'verified'), 422, 'Seluruh tim aktif harus disetujui sebelum entry disetujui.');
        $players = EntryMember::query()->where('event_entry_id', $entry->id)->where('member_type', 'player')->whereHas('team', fn ($query) => $query->whereNull('cancelled_at'));
        abort_if((clone $players)->count() !== (clone $players)->where('verification_status', 'verified')->count(), 422, 'Seluruh pemain harus diverifikasi sebelum entry disetujui.');
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

    public function verifyMember(Request $request, EntryMember $member): RedirectResponse
    {
        return $this->updateMemberStatus($request, $member, 'verified');
    }

    public function revisionMember(Request $request, EntryMember $member): RedirectResponse
    {
        $request->validate(['note' => ['required', 'string', 'max:255']]);
        return $this->updateMemberStatus($request, $member, 'revision_required');
    }

    public function rejectMember(Request $request, EntryMember $member): RedirectResponse
    {
        $request->validate(['note' => ['required', 'string', 'max:255']]);
        return $this->updateMemberStatus($request, $member, 'rejected');
    }

    private function updateMemberStatus(Request $request, EntryMember $member, string $status): RedirectResponse
    {
        abort_unless($member->member_type === 'player' && $member->eventEntry?->verification_status === 'pending', 422, 'Pemain hanya dapat diperiksa saat entry menunggu verifikasi.');
        abort_unless($member->team && $member->team->effectiveStatus() === 'pending', 422, 'Pemain hanya dapat diperiksa saat tim menunggu verifikasi.');
        $before = $member->only(['verification_status', 'verification_note', 'verified_by', 'verified_at']);
        $member->update([
            'verification_status' => $status,
            'verification_note' => $status === 'verified' ? null : $request->string('note')->toString(),
            'verified_by' => $status === 'verified' ? $request->user()->id : null,
            'verified_at' => $status === 'verified' ? now() : null,
        ]);
        DB::table('entry_member_audits')->insert(['entry_member_id' => $member->id, 'action' => $status, 'before_json' => json_encode($before), 'after_json' => json_encode($member->only(['verification_status', 'verification_note', 'verified_by', 'verified_at'])), 'reason' => $member->verification_note, 'user_id' => $request->user()->id, 'created_at' => now(), 'updated_at' => now()]);
        if ($status === 'revision_required' && $member->team) {
            $team = $member->team;
            $teamBefore = $team->toArray();
            $team->update(['verification_status_override' => 'revision_required', 'verification_note' => $member->verification_note, 'verified_by' => null, 'verified_at' => null]);
            $this->auditTeam($team, 'member_revision_opened', $teamBefore, $member->verification_note, $request);
        }

        return back()->with('success', 'Status pemain diperbarui.');
    }

    public function reject(Request $request, EventEntry $entry): RedirectResponse
    {
        abort_unless($entry->verification_status === 'pending', 422, 'Hanya entry menunggu yang dapat ditolak.');
        $data = $request->validate([
            'note' => ['required', 'string', 'max:255'],
        ]);
        $before = $this->state($entry);

        DB::transaction(function () use ($entry, $data, $before, $request) {
            $entry->teams()->whereNull('cancelled_at')->whereNotNull('verification_status_override')->get()->each(function ($team) use ($data, $request) {
                $teamBefore = $team->toArray();
                $team->update(['verification_status_override' => null, 'verification_note' => null, 'verified_by' => null, 'verified_at' => null]);
                $this->auditTeam($team, 'parent_rejected', $teamBefore, $data['note'], $request);
            });
            $entry->update(['verification_status' => 'rejected', 'verification_note' => $data['note'], 'verified_by' => null, 'verified_at' => null]);
            $this->audit($entry, 'rejected', $before, $request);
        });

        return back()->with('success', 'Entry ditolak.');
    }

    public function revision(Request $request, EventEntry $entry): RedirectResponse
    {
        abort_unless(in_array($entry->verification_status, ['pending', 'rejected'], true), 422, 'Hanya entry menunggu atau ditolak yang dapat dibuka untuk perbaikan.');
        abort_if($entry->teams()->where('verification_status_override', 'verified')->exists(), 422, 'Perbaikan roster penuh tidak dapat dibuka karena ada tim terverifikasi. Gunakan Perbaikan Tim untuk tim yang bermasalah.');
        $data = $request->validate(['note' => ['required', 'string', 'max:255']]);
        $before = $this->state($entry);
        $entry->update(['verification_status' => 'revision_required', 'verification_note' => $data['note'], 'verified_by' => null, 'verified_at' => null]);
        $this->audit($entry, 'revision_required', $before, $request);

        return back()->with('success', 'Perbaikan roster diminta.');
    }

    public function overrideTeam(Request $request, EntryTeam $team): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:revision_required,verified,rejected,cancelled'], 'reason' => ['required', 'string', 'max:255']]);
        abort_unless($team->eventEntry?->verification_status === 'pending', 422, 'Status tim hanya dapat diubah saat pendaftaran menunggu verifikasi.');
        $currentStatus = $team->effectiveStatus();
        $allowedFrom = $data['status'] === 'revision_required' ? ['pending', 'rejected'] : ['pending'];
        abort_unless(in_array($currentStatus, $allowedFrom, true), 422, 'Status tim harus dikembalikan ke menunggu verifikasi sebelum tindakan ini dilakukan.');
        if ($data['status'] === 'verified') {
            $players = $team->members()->where('member_type', 'player');
            if ((clone $players)->count() === 0 || (clone $players)->where('verification_status', 'verified')->count() !== (clone $players)->count()) return back()->with('error', 'Tim belum dapat disetujui. Verifikasi seluruh pemain di dalam tim terlebih dahulu.');
        }
        $before = $team->toArray();
        $team->update(['verification_status_override' => $data['status'], 'verification_note' => $data['reason'], 'verified_by' => $data['status'] === 'verified' ? $request->user()->id : null, 'verified_at' => $data['status'] === 'verified' ? now() : null, 'cancelled_at' => $data['status'] === 'cancelled' ? now() : null]);
        $this->auditTeam($team, 'override_set', $before, $data['reason'], $request);
        return back()->with('success', 'Status tim diperbarui.');
    }

    public function resetTeamOverride(Request $request, EntryTeam $team): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:255']]);
        $before = $team->toArray();
        $team->update(['verification_status_override' => null, 'verification_note' => null, 'verified_by' => null, 'verified_at' => null, 'cancelled_at' => null]);
        $this->auditTeam($team, 'override_reset', $before, $data['reason'], $request);
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
