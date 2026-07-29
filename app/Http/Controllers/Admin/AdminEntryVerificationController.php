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
                'tournamentEvent:id,code,name,status,registration_rules',
            ])
            ->where('verification_status', 'pending')
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
                'team_addition_open' => (bool) $entry->team_addition_opened_at,
                'can_open_team_addition' => $entry->tournamentEvent?->status === 'registration_closed' && $entry->teams->count() < ($entry->tournamentEvent?->registration_rules['max_teams_per_pd'] ?? 1),
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
            'documents' => collect($member->documents ?? [])->map(fn ($path, $key) => ['key' => $key, 'url' => route('entry-members.documents.show', [$member, $key]), 'is_image' => in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png'], true)])->values(),
            'document_count' => count($member->documents ?? []),
            'verification_status' => $member->verification_status,
            'verification_note' => $member->verification_note,
            'verified_at' => $member->verified_at?->format('d M Y H:i'),
            'updated_at' => $member->updated_at?->format('d M Y H:i'),
            'audits' => DB::table('entry_member_audits')->where('entry_member_id', $member->id)->latest('id')->get(['action', 'reason', 'created_at']),
        ];
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
        if (in_array($status, ['revision_required', 'rejected'], true) && $member->team) {
            $team = $member->team;
            $teamBefore = $team->toArray();
            $team->update(['verification_status_override' => 'revision_required', 'verification_note' => $member->verification_note, 'verified_by' => null, 'verified_at' => null]);
            $this->auditTeam($team, $status === 'rejected' ? 'member_rejection_opened' : 'member_revision_opened', $teamBefore, $member->verification_note, $request);
        }

        return back()->with('success', 'Status pemain diperbarui.');
    }

    public function openTeamAddition(Request $request, EventEntry $entry): RedirectResponse
    {
        $data = $request->validate(['note' => ['required', 'string', 'max:255']]);
        abort_unless($entry->verification_status === 'pending', 422, 'Penambahan tim hanya dapat dibuka saat pendaftaran menunggu verifikasi.');
        abort_if($entry->team_addition_opened_at, 422, 'Penambahan tim sudah dibuka untuk PD.');
        abort_if(in_array($entry->tournamentEvent?->status, ['participants_locked', 'bracket_locked', 'ongoing', 'completed'], true), 422, 'Penambahan tim tidak dapat dibuka setelah peserta dikunci.');
        $maximum = $entry->tournamentEvent?->registration_rules['max_teams_per_pd'] ?? 1;
        abort_if($entry->teams()->whereNull('cancelled_at')->count() >= $maximum, 422, 'Kuota tim PD sudah terpenuhi.');
        $before = $this->state($entry);
        $entry->update(['team_addition_opened_at' => now(), 'verification_note' => $data['note']]);
        $this->audit($entry, 'team_addition_opened', $before, $request);

        return back()->with('success', 'Penambahan tim dibuka untuk PD tanpa membuka tim terverifikasi.');
    }

    public function overrideTeam(Request $request, EntryTeam $team): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:revision_required,verified,rejected,cancelled'], 'reason' => ['required', 'string', 'max:255']]);
        $finalized = DB::transaction(function () use ($request, $team, $data) {
            $team = EntryTeam::query()->lockForUpdate()->findOrFail($team->id);
            $entry = EventEntry::query()->lockForUpdate()->findOrFail($team->event_entry_id);
            abort_unless($entry->verification_status === 'pending', 422, 'Status tim hanya dapat diubah saat pendaftaran menunggu verifikasi.');
            $currentStatus = $team->effectiveStatus();
            $allowedFrom = $data['status'] === 'revision_required' ? ['pending', 'rejected'] : ['pending'];
            abort_unless(in_array($currentStatus, $allowedFrom, true), 422, 'Status tim harus dikembalikan ke menunggu verifikasi sebelum tindakan ini dilakukan.');
            if ($data['status'] === 'verified') {
                $players = $team->members()->where('member_type', 'player');
                if ((clone $players)->count() === 0 || (clone $players)->where('verification_status', 'verified')->count() !== (clone $players)->count()) return false;
            }
            $before = $team->toArray();
            $team->update(['verification_status_override' => $data['status'], 'verification_note' => $data['reason'], 'verified_by' => $data['status'] === 'verified' ? $request->user()->id : null, 'verified_at' => $data['status'] === 'verified' ? now() : null, 'cancelled_at' => $data['status'] === 'cancelled' ? now() : null]);
            $this->auditTeam($team, 'override_set', $before, $data['reason'], $request);

            if ($data['status'] !== 'verified' || $entry->team_addition_opened_at || $entry->teams()->whereNull('cancelled_at')->where(fn ($query) => $query->whereNull('verification_status_override')->orWhere('verification_status_override', '!=', 'verified'))->exists()) return null;
            $entryBefore = $this->state($entry->load('teams.members', 'members'));
            $entry->update(['verification_status' => 'verified', 'verification_note' => null, 'verified_by' => $request->user()->id, 'verified_at' => now()]);
            $this->audit($entry, 'verified_automatically', $entryBefore, $request);
            return true;
        });

        if ($finalized === false) return back()->with('error', 'Tim belum dapat disetujui. Verifikasi seluruh pemain di dalam tim terlebih dahulu.');
        return back()->with('success', $finalized ? 'Tim disetujui dan pendaftaran selesai otomatis.' : 'Status tim diperbarui.');
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
