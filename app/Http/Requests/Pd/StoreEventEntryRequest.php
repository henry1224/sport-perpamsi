<?php

namespace App\Http\Requests\Pd;

use App\Models\TournamentEvent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreEventEntryRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $teams = $this->input('teams');
        if (! is_array($teams) && is_array($this->input('members'))) $teams = [['members' => $this->input('members')]];
        $this->merge(['intent' => $this->input('intent', 'submit'), 'teams' => $teams]);
    }

    public function authorize(): bool { return $this->user()?->isPdAdmin() && (bool) $this->user()->regional_committee_id; }

    public function rules(): array
    {
        $snapshot = $this->snapshot();
        $minimum = $this->input('intent') === 'draft' ? 1 : ($snapshot['min_members_per_team'] ?? $snapshot['min_members'] ?? 1);
        $memberRules = ['required', 'array', 'min:'.$minimum];
        $maximum = $snapshot['max_members_per_team'] ?? $snapshot['max_members'] ?? null;
        if ($maximum !== null) $memberRules[] = 'max:'.$maximum;
        $officialRoles = $snapshot['official_roles'] ?? [];

        return [
            'intent' => ['required', 'in:draft,submit'],
            'teams' => ['required', 'array', 'min:1', 'max:'.($snapshot['max_teams_per_pd'] ?? 1)],
            'teams.*.members' => $memberRules,
            'teams.*.members.*.name' => ['required', 'string', 'max:120'],
            'officials' => ['nullable', 'array', 'max:'.($snapshot['max_officials_per_pd'] ?? 0)],
            'officials.*.name' => ['required', 'string', 'max:120'],
            'officials.*.role' => ['required', 'string', Rule::in($officialRoles)],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->has('teams.0.members')) $validator->errors()->add('members', 'Daftar pemain wajib diisi sesuai kuota.');
            $names = collect($this->input('teams', []))->flatMap(fn ($team) => $team['members'] ?? [])->pluck('name')->map(fn ($name) => mb_strtolower(trim((string) $name)))->filter();
            if ($names->duplicates()->isNotEmpty()) { $validator->errors()->add('teams', 'Pemain tidak boleh terdaftar pada dua tim dalam kompetisi yang sama.'); $validator->errors()->add('members', 'Nama pemain tidak boleh sama dalam satu pendaftaran.'); }
            $event = $this->route('event');
            if ($event instanceof TournamentEvent && $event->entries()->where('regional_committee_id', $this->user()->regional_committee_id)->whereIn('verification_status', ['pending', 'verified'])->exists()) { $validator->errors()->add('teams', 'Pendaftaran sedang diproses atau sudah terverifikasi.'); $validator->errors()->add('members', 'Pendaftaran sedang diproses atau sudah terverifikasi.'); }
            if (! $event instanceof TournamentEvent) return;

            $officialNames = collect($this->input('officials', []))->pluck('name')->map(fn ($name) => mb_strtolower(trim((string) $name)))->filter();
            if ($officialNames->duplicates()->isNotEmpty()) $validator->errors()->add('officials', 'Nama official tidak boleh didaftarkan lebih dari satu kali.');

            $sameRoster = $officialNames->intersect($names)->values();
            if ($sameRoster->isNotEmpty() && ! ($this->snapshot()['official_can_compete'] ?? false)) {
                $validator->errors()->add('officials', 'Official tidak boleh terdaftar sebagai pemain pada cabor ini sesuai regulasi.');
            }

            $committeeId = $this->user()->regional_committee_id;
            $activeStatuses = ['draft', 'pending', 'verified', 'revision_required'];
            // ponytail: identitas sementara memakai normalized_name; ganti ke player_id/NIK saat master pemain tersedia.
            $playerConflicts = DB::table('entry_members')->join('event_entries', 'entry_members.event_entry_id', '=', 'event_entries.id')
                ->where('entry_members.member_type', 'player')->where('event_entries.regional_committee_id', $committeeId)
                ->where('event_entries.tournament_event_id', '!=', $event->id)->whereIn('event_entries.verification_status', $activeStatuses)
                ->whereIn('entry_members.normalized_name', $officialNames)->pluck('entry_members.normalized_name');
            if ($playerConflicts->isNotEmpty() && ! ($this->snapshot()['official_can_compete'] ?? false)) {
                $validator->errors()->add('officials', 'Official sudah terdaftar sebagai pemain pada cabor lain dan regulasi ini tidak mengizinkan official bertanding.');
            }

            $officialConflicts = DB::table('entry_members')->join('event_entries', 'entry_members.event_entry_id', '=', 'event_entries.id')->join('tournament_events', 'event_entries.tournament_event_id', '=', 'tournament_events.id')
                ->where('entry_members.member_type', 'official')->where('event_entries.regional_committee_id', $committeeId)
                ->where('event_entries.tournament_event_id', '!=', $event->id)->whereIn('event_entries.verification_status', $activeStatuses)
                ->whereIn('entry_members.normalized_name', $names)->get(['entry_members.normalized_name', 'tournament_events.registration_rules'])
                ->contains(fn ($row) => ! (json_decode($row->registration_rules, true)['official_can_compete'] ?? false));
            if ($officialConflicts) $validator->errors()->add('teams', 'Pemain terdaftar sebagai official pada cabor lain yang tidak mengizinkan official bertanding.');
        });
    }

    public function messages(): array { return ['teams.required' => 'Daftar tim wajib diisi.', 'teams.max' => 'Jumlah tim melebihi kuota kompetisi.', 'teams.*.members.min' => 'Jumlah pemain tim belum memenuhi batas minimum.', 'teams.*.members.max' => 'Jumlah pemain tim melebihi batas maksimum.', 'teams.*.members.*.name.required' => 'Nama pemain wajib diisi.', 'officials.max' => 'Jumlah official melebihi kuota kompetisi.', 'officials.*.name.required' => 'Nama official wajib diisi.', 'officials.*.role.in' => 'Peran official tidak sesuai regulasi kompetisi.']; }

    private function snapshot(): array { $event = $this->route('event'); return $event instanceof TournamentEvent ? ($event->registration_rules ?? []) : []; }
}
