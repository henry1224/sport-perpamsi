<?php

namespace Database\Seeders;

use App\Models\EntryMember;
use App\Models\EntryTeam;
use App\Models\EventEntry;
use App\Models\TournamentEvent;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PdVerificationDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) return;

        $event = TournamentEvent::query()->where('code', 'badminton-mixed-double')->first();
        foreach (['aceh' => 'aceh@gmail.com', 'kalimantan-timur' => 'pd-kalimantan-timur@perpamsi.local'] as $provinceSlug => $email) {
            $committeeId = DB::table('regional_committees')->join('provinces', 'regional_committees.province_id', '=', 'provinces.id')->where('provinces.slug', $provinceSlug)->value('regional_committees.id');
            User::query()->updateOrCreate(['email' => $email], ['name' => 'Admin PD PERPAMSI '.Str::headline($provinceSlug), 'password' => Hash::make('password'), 'role' => 'pd_admin', 'account_status' => 'verified', 'regional_committee_id' => $committeeId, 'email_verified_at' => now()]);
        }
        $users = User::query()->whereIn('email', ['aceh@gmail.com', 'pd-kalimantan-timur@perpamsi.local'])->orderBy('email')->get();

        if (! $event || $users->count() !== 2) {
            return;
        }

        $event->update([
            'status' => 'registration_closed',
            'registration_published_at' => '2026-07-01 08:00:00',
            'registration_open_at' => '2026-07-01 08:00:00',
            'registration_close_at' => '2026-07-22 23:59:00',
        ]);
        $event->entries()->whereNotIn('regional_committee_id', $users->pluck('regional_committee_id'))->delete();

        DB::transaction(function () use ($event, $users) {
            foreach ($users->values() as $userIndex => $user) {
                $pdamId = DB::table('pdams')->where('province_id', $user->committee?->province_id)->orderBy('id')->value('id');
                $entry = EventEntry::query()->updateOrCreate(
                    ['tournament_event_id' => $event->id, 'regional_committee_id' => $user->regional_committee_id],
                    ['public_id' => (string) Str::uuid(), 'pdam_id' => $pdamId, 'registration_key' => $event->id.':'.$user->regional_committee_id, 'province_id' => $user->committee?->province_id, 'display_name' => $user->committee?->name, 'verification_status' => 'pending', 'submitted_at' => now(), 'verified_at' => null]
                );
                $entry->members()->delete();
                $entry->teams()->delete();
                $team = EntryTeam::query()->create(['public_id' => (string) Str::uuid(), 'event_entry_id' => $entry->id, 'team_no' => 1, 'label' => $entry->display_name.' #1']);

                foreach (range(1, 2) as $playerNo) {
                    $verified = $userIndex === 0 && $playerNo === 1;
                    $documents = collect(['photo', 'registration_form', 'identity_card', 'pension_card', 'employee_decree'])->mapWithKeys(function ($key) use ($user, $playerNo) {
                        $path = "demo/{$user->id}/{$key}-{$playerNo}.png";
                        Storage::disk('local')->put($path, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='));
                        return [$key => $path];
                    })->all();
                    EntryMember::query()->create([
                        'event_entry_id' => $entry->id,
                        'entry_team_id' => $team->id,
                        'pdam_id' => $pdamId,
                        'name' => ($userIndex === 0 ? 'Pemain Aceh ' : 'Pemain Kalimantan Timur ').$playerNo,
                        'normalized_name' => Str::lower(($userIndex === 0 ? 'pemain aceh ' : 'pemain kalimantan timur ').$playerNo),
                        'member_type' => 'player',
                        'identity_hash' => hash('sha256', $user->email.'-'.$playerNo),
                        'identity_type' => 'nik',
                        'identity_number' => ($userIndex === 0 ? '1100' : '6400').str_pad((string) $playerNo, 12, '0', STR_PAD_LEFT),
                        'documents' => $documents,
                        'verification_status' => $verified ? 'verified' : 'pending',
                        'verified_by' => $verified ? User::query()->where('role', 'super_admin')->value('id') : null,
                        'verified_at' => $verified ? now() : null,
                    ]);
                }
            }
        });
    }
}
