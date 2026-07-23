<?php

namespace App\Actions\Entries;

use App\Models\EventEntry;
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
        $existing = EventEntry::query()->where('registration_key', $event->id.':'.$user->regional_committee_id)->first();
        $canRevise = $existing?->verification_status === 'revision_required';

        if (! $event->registrationIsOpen() && ! $canRevise) {
            // ponytail: buka kembali event atau tambah alur late-registration + rebuild bracket jika dibutuhkan.
            throw new InvalidArgumentException('Pendaftaran event ini sudah ditutup.');
        }

        $user->loadMissing('committee');

        return DB::transaction(function () use ($user, $event, $data) {
            $entry = EventEntry::query()->lockForUpdate()->firstOrNew(['registration_key' => $event->id.':'.$user->regional_committee_id]);
            abort_if($entry->exists && in_array($entry->verification_status, ['pending', 'verified'], true), 422, 'Pendaftaran yang sedang diproses atau terverifikasi tidak dapat diubah.');
            abort_if($entry->exists && $entry->teams()->where('verification_status_override', 'verified')->exists(), 422, 'Roster tim terverifikasi tidak dapat diubah.');
            abort_if($entry->exists && DB::table('matches')->whereIn('team_a_id', $entry->teams()->select('id'))->orWhereIn('team_b_id', $entry->teams()->select('id'))->exists(), 422, 'Roster yang sudah masuk pertandingan tidak dapat diubah.');
            $before = $entry->exists ? $this->state($entry->loadMissing('teams.members')) : null;
            $entry->fill([
                'public_id' => $entry->public_id ?: (string) Str::uuid(), 'tournament_event_id' => $event->id, 'pdam_id' => null,
                'province_id' => $user->committee->province_id, 'regency_id' => null, 'regional_committee_id' => $user->regional_committee_id,
                'display_name' => $user->committee->name, 'verification_status' => $data['intent'] === 'draft' ? 'draft' : 'pending',
                'verification_note' => null, 'submitted_at' => $data['intent'] === 'submit' ? now() : null, 'verified_by' => null, 'verified_at' => null,
            ])->save();

            $existingTeams = $entry->teams()->with('members')->get()->keyBy('team_no');
            $existingMembers = $entry->members()->get()->keyBy('id');
            $incomingMemberIds = collect($data['teams'])->flatMap(fn ($team) => $team['members'])->merge($data['officials'] ?? [])->pluck('id')->filter();
            $existingMembers->reject(fn ($member) => $incomingMemberIds->contains($member->id))->each(fn ($member) => collect($member->documents)->filter()->each(fn ($path) => Storage::disk('local')->delete($path)));
            foreach ($data['teams'] as $index => $teamData) {
                $teamNo = $index + 1;
                $team = $existingTeams->get($teamNo) ?? $entry->teams()->create(['public_id' => (string) Str::uuid(), 'team_no' => $teamNo, 'label' => $entry->display_name.' #'.$teamNo]);
                $team->update(['label' => $entry->display_name.' #'.$teamNo, 'cancelled_at' => null]);
                $team->members()->delete();
                $team->members()->createMany(collect($teamData['members'])->map(function ($member) use ($entry, $existingMembers) {
                    $name = trim($member['name']);
                    return $this->memberData($entry, $member, $name, 'player', null, $existingMembers->get($member['id'] ?? null));
                })->all());
            }
            $entry->teams()->where('team_no', '>', count($data['teams']))->whereNull('cancelled_at')->update(['cancelled_at' => now()]);
            $entry->members()->where('member_type', 'official')->delete();
            $entry->members()->createMany(collect($data['officials'] ?? [])->map(function ($official) use ($entry, $existingMembers) {
                $name = trim($official['name']);
                return $this->memberData($entry, $official, $name, 'official', $official['role'], $existingMembers->get($official['id'] ?? null));
            })->all());

            $action = $data['intent'] === 'draft' ? 'draft_saved' : (in_array($before['status'] ?? null, ['revision_required', 'rejected', 'cancelled'], true) ? 'resubmitted' : 'submitted');
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
            if ($old = $documents[$key] ?? null) Storage::disk('local')->delete($old);
            $documents[$key] = $file->store("registrations/{$entry->public_id}", 'local');
        }

        return [
            'event_entry_id' => $entry->id, 'entry_team_id' => null, 'pdam_id' => $type === 'player' ? ($member['pdam_id'] ?? null) : null, 'name' => $name, 'normalized_name' => mb_strtolower($name),
            'identity_type' => $identityType, 'identity_number' => $identityNumber,
            'identity_hash' => $identityType && $identityNumber ? hash('sha256', strtolower($identityType.':'.$identityNumber)) : null,
            'member_type' => $type, 'position' => $position, 'documents' => $documents ?: null,
        ];
    }
}
