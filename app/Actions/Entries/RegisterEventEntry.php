<?php

namespace App\Actions\Entries;

use App\Models\EventEntry;
use App\Models\TournamentEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
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
            $before = $entry->exists ? $this->state($entry->loadMissing('members')) : null;
            $entry->fill([
                'public_id' => $entry->public_id ?: (string) Str::uuid(), 'tournament_event_id' => $event->id, 'pdam_id' => null,
                'province_id' => $user->committee->province_id, 'regency_id' => null, 'regional_committee_id' => $user->regional_committee_id,
                'display_name' => $user->committee->name, 'verification_status' => $data['intent'] === 'draft' ? 'draft' : 'pending',
                'verification_note' => null, 'submitted_at' => $data['intent'] === 'submit' ? now() : null, 'verified_by' => null, 'verified_at' => null,
            ])->save();

            $entry->members()->delete();
            $entry->members()->createMany(collect($data['members'])->map(fn ($member) => [
                'name' => trim($member['name']),
                'normalized_name' => mb_strtolower(trim($member['name'])),
            ])->all());

            $action = $data['intent'] === 'draft' ? 'draft_saved' : (in_array($before['status'] ?? null, ['revision_required', 'rejected', 'cancelled'], true) ? 'resubmitted' : 'submitted');
            DB::table('entry_registration_audits')->insert(['event_entry_id' => $entry->id, 'action' => $action, 'before_json' => $before ? json_encode($before) : null, 'after_json' => json_encode($this->state($entry->load('members'))), 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()]);

            return $entry;
        });
    }

    private function state(EventEntry $entry): array
    {
        return ['status' => $entry->verification_status, 'note' => $entry->verification_note, 'members' => $entry->members->pluck('name')->all()];
    }
}
