<?php

namespace App\Actions\Entries;

use App\Models\EventEntry;
use App\Models\EntryMember;
use App\Models\TournamentEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class RegisterEventEntry
{
    public function handle(User $user, TournamentEvent $event, array $data): EventEntry
    {
        $existing = EventEntry::query()->with('teams')->where('registration_key', $event->id.':'.$user->regional_committee_id)->first();
        $teamRevisionIds = $existing?->teams->where('verification_status_override', 'revision_required')->pluck('id') ?? collect();
        $canRevise = $existing?->verification_status === 'revision_required' || $teamRevisionIds->isNotEmpty();

        if (! $event->registrationIsOpen() && ! $canRevise) {
            // ponytail: buka kembali event atau tambah alur late-registration + rebuild bracket jika dibutuhkan.
            throw new InvalidArgumentException('Pendaftaran event ini sudah ditutup.');
        }

        $user->loadMissing('committee');

        return DB::transaction(function () use ($user, $event, $data, $teamRevisionIds) {
            $entry = EventEntry::query()->lockForUpdate()->firstOrNew(['registration_key' => $event->id.':'.$user->regional_committee_id]);
            $teamScopedRevision = $entry->exists && $entry->verification_status === 'pending' && $teamRevisionIds->isNotEmpty();
            abort_if($entry->exists && $entry->verification_status === 'verified', 422, 'Pendaftaran terverifikasi tidak dapat diubah.');
            abort_if($entry->exists && $entry->verification_status === 'pending' && ! $teamScopedRevision, 422, 'Pendaftaran yang sedang diproses tidak dapat diubah.');
            abort_if($entry->exists && ! $teamScopedRevision && $entry->teams()->where('verification_status_override', 'verified')->exists(), 422, 'Roster tim terverifikasi tidak dapat diubah.');
            abort_if($entry->exists && DB::table('matches')->whereIn('team_a_id', $entry->teams()->select('id'))->orWhereIn('team_b_id', $entry->teams()->select('id'))->exists(), 422, 'Roster yang sudah masuk pertandingan tidak dapat diubah.');
            $before = $entry->exists ? $this->state($entry->loadMissing('teams.members')) : null;
            $entry->fill([
                'public_id' => $entry->public_id ?: (string) Str::uuid(), 'tournament_event_id' => $event->id, 'pdam_id' => null,
                'province_id' => $user->committee->province_id, 'regency_id' => null, 'regional_committee_id' => $user->regional_committee_id,
                'display_name' => $user->committee->name, 'verification_status' => $data['intent'] === 'draft' ? 'draft' : 'pending',
                'verification_note' => null, 'submitted_at' => $data['intent'] === 'submit' ? now() : null, 'verified_by' => null, 'verified_at' => null,
            ])->save();

            $existingTeams = $entry->teams()->with('members')->get()->keyBy('team_no');
            $existingTeamsById = $existingTeams->keyBy('id');
            $nextTeamNo = ($existingTeams->max('team_no') ?? 0) + 1;
            $existingMembers = $entry->members()->get()->keyBy('id');
            $keptMemberIds = collect();
            $keptTeamIds = collect();
            foreach ($data['teams'] as $index => $teamData) {
                $team = isset($teamData['id']) ? $existingTeamsById->get($teamData['id']) : null;
                abort_if($teamScopedRevision && (! $team || ! $teamRevisionIds->contains($team->id)), 422, 'Hanya tim yang diminta perbaikan yang dapat diubah.');
                $team ??= $existingTeams->get($index + 1)?->cancelled_at ? null : $existingTeams->get($index + 1);
                $teamNo = $team?->team_no ?? $nextTeamNo++;
                $team ??= $entry->teams()->create(['public_id' => (string) Str::uuid(), 'team_no' => $teamNo, 'label' => $entry->display_name.' #'.$teamNo]);
                $teamBefore = $teamScopedRevision ? $team->toArray() : null;
                $team->update(['label' => $entry->display_name.' #'.$teamNo, 'cancelled_at' => null]);
                $keptTeamIds->push($team->id);
                foreach ($teamData['members'] as $memberData) {
                    $member = $this->saveMember($entry, $team->id, $memberData, 'player', null, $existingMembers, $teamScopedRevision);
                    $keptMemberIds->push($member->id);
                }
                if ($teamScopedRevision) {
                    $team->update(['verification_status_override' => null, 'verification_note' => null, 'verified_by' => null, 'verified_at' => null]);
                    DB::table('entry_team_audits')->insert(['entry_team_id' => $team->id, 'action' => 'resubmitted', 'before_json' => json_encode($teamBefore), 'after_json' => json_encode($team->fresh()->toArray()), 'reason' => 'Perbaikan tim dikirim ulang oleh PD.', 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()]);
                }
            }
            if (! $teamScopedRevision) $entry->teams()->whereNotIn('id', $keptTeamIds)->whereNull('cancelled_at')->update(['cancelled_at' => now()]);
            foreach ($teamScopedRevision ? [] : ($data['officials'] ?? []) as $officialData) {
                $official = $this->saveMember($entry, null, $officialData, 'official', $officialData['role'], $existingMembers);
                $keptMemberIds->push($official->id);
            }

            $removedMembers = $entry->members()->whereNotIn('id', $keptMemberIds)->where(fn ($query) => $query->when(! $teamScopedRevision, fn ($query) => $query->where('member_type', 'official')->orWhereIn('entry_team_id', $keptTeamIds), fn ($query) => $query->whereIn('entry_team_id', $keptTeamIds)))->get();
            abort_if(($teamScopedRevision || in_array($before['status'] ?? null, ['revision_required', 'rejected'], true)) && $removedMembers->isNotEmpty(), 422, 'Revisi wajib mempertahankan ID pemain dan official. Pergantian orang memerlukan alur substitusi resmi.');
            abort_if($removedMembers->contains(fn ($member) => $member->verification_status !== 'pending' || DB::table('entry_member_audits')->where('entry_member_id', $member->id)->exists()), 422, 'Pemain atau official yang sudah diperiksa tidak dapat dihapus. Gunakan alur koreksi resmi.');
            $entry->members()->whereKey($removedMembers->pluck('id'))->delete();

            $action = $teamScopedRevision ? 'team_resubmitted' : ($data['intent'] === 'draft' ? 'draft_saved' : (in_array($before['status'] ?? null, ['revision_required', 'rejected', 'cancelled'], true) ? 'resubmitted' : 'submitted'));
            DB::table('entry_registration_audits')->insert(['event_entry_id' => $entry->id, 'action' => $action, 'before_json' => $before ? json_encode($before) : null, 'after_json' => json_encode($this->state($entry->load('teams.members', 'members'))), 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()]);

            return $entry;
        });
    }

    private function state(EventEntry $entry): array
    {
        return ['status' => $entry->verification_status, 'note' => $entry->verification_note, 'teams' => $entry->teams->map(fn ($team) => ['team_no' => $team->team_no, 'label' => $team->label, 'members' => $team->members->pluck('name')->all()])->all(), 'officials' => $entry->members->where('member_type', 'official')->map(fn ($member) => ['name' => $member->name, 'role' => $member->position])->values()->all()];
    }

    private function memberData(EventEntry $entry, array $member, string $name, string $type, ?string $position, $existing): array
    {
        $identityType = $member['identity_type'] ?? null;
        $identityNumber = preg_replace('/[^a-zA-Z0-9]/', '', (string) ($member['identity_number'] ?? '')) ?: null;
        $documents = $existing?->documents ?? [];
        foreach (($member['documents'] ?? []) as $key => $file) {
            if (! $file) continue;
            // ponytail: file lama dipertahankan agar rollback DB tidak kehilangan dokumen; cleanup orphan ditambah saat volume storage membutuhkan.
            $documents[$key] = $file->store("registrations/{$entry->public_id}", 'local');
        }

        return [
            'event_entry_id' => $entry->id, 'entry_team_id' => null, 'pdam_id' => $type === 'player' ? ($member['pdam_id'] ?? null) : null, 'name' => $name, 'normalized_name' => mb_strtolower($name),
            'identity_type' => $identityType, 'identity_number' => $identityNumber,
            'identity_hash' => $identityType && $identityNumber ? hash('sha256', strtolower($identityType.':'.$identityNumber)) : null,
            'member_type' => $type, 'position' => $position, 'documents' => $documents ?: null,
        ];
    }

    private function saveMember(EventEntry $entry, ?int $teamId, array $data, string $type, ?string $position, $existingMembers, bool $resetVerification = false)
    {
        $normalizedName = mb_strtolower(trim($data['name']));
        $existing = $existingMembers->get($data['id'] ?? null) ?? $existingMembers->first(fn ($member) => $member->member_type === $type && $member->entry_team_id === $teamId && $member->normalized_name === $normalizedName);
        abort_if($existing && $existing->member_type !== $type, 422, 'Data anggota tidak sesuai jenis roster.');

        $values = array_merge($this->memberData($entry, $data, trim($data['name']), $type, $position, $existing), ['entry_team_id' => $teamId]);
        if (! $existing) return EntryMember::query()->create($values);

        abort_if($existing->member_type === 'player' && $existing->entry_team_id !== $teamId && $existing->verification_status === 'verified', 422, 'Pemain terverifikasi tidak dapat dipindahkan ke tim lain.');
        if ($resetVerification || in_array($existing->verification_status, ['revision_required', 'rejected'], true)) $values += ['verification_status' => 'pending', 'verification_note' => null, 'verified_by' => null, 'verified_at' => null];
        $existing->update($values);

        return $existing;
    }
}
