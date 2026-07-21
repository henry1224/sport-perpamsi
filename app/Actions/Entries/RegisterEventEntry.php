<?php

namespace App\Actions\Entries;

use App\Models\EventEntry;
use App\Models\Pdam;
use App\Models\TournamentEvent;
use App\Models\User;
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

        $pdam = Pdam::query()->findOrFail($data['pdam_id']);

        $event->loadMissing('category');
        $user->loadMissing('committee');
        $type = $event->category?->competition_type;

        return EventEntry::query()->create([
            'public_id' => (string) Str::uuid(),
            'tournament_event_id' => $event->id,
            'pdam_id' => $pdam->id,
            'province_id' => $pdam->province_id,
            'regency_id' => $pdam->regency_id,
            'regional_committee_id' => $user->regional_committee_id,
            'display_name' => $user->committee->name,
            'athlete_1' => in_array($type, ['individual', 'doubles'], true) ? ($data['athlete_1'] ?? null) : null,
            'athlete_2' => $type === 'doubles' ? ($data['athlete_2'] ?? null) : null,
            'team_name' => $type === 'team' ? ($data['team_name'] ?? null) : null,
            'verification_status' => 'pending',
        ]);
    }

}
