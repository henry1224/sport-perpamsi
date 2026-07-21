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
        if ($event->status !== 'registration_open') {
            // ponytail: buka kembali event atau tambah alur late-registration + rebuild bracket jika dibutuhkan.
            throw new InvalidArgumentException('Pendaftaran event ini sudah ditutup.');
        }

        $user->loadMissing('committee');

        return DB::transaction(function () use ($user, $event, $data) {
            $entry = EventEntry::query()->create([
                'public_id' => (string) Str::uuid(),
                'tournament_event_id' => $event->id,
                'pdam_id' => null,
                'province_id' => $user->committee->province_id,
                'regency_id' => null,
                'regional_committee_id' => $user->regional_committee_id,
                'registration_key' => $event->id.':'.$user->regional_committee_id,
                'display_name' => $user->committee->name,
                'verification_status' => 'pending',
                'submitted_at' => now(),
            ]);

            $entry->members()->createMany(collect($data['members'])->map(fn ($member) => [
                'name' => trim($member['name']),
                'normalized_name' => mb_strtolower(trim($member['name'])),
            ])->all());

            return $entry;
        });
    }
}
