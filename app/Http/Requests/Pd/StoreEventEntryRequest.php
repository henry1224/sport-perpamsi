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
            'teams.*.members.*.id' => ['nullable', 'integer'],
            'teams.*.members.*.identity_type' => [$this->input('intent') === 'submit' ? 'required' : 'nullable', Rule::in(['nik', 'kta'])],
            'teams.*.members.*.identity_number' => [$this->input('intent') === 'submit' ? 'required' : 'nullable', 'string', 'max:50'],
            'teams.*.members.*.pdam_id' => [$this->input('intent') === 'submit' ? 'required' : 'nullable', Rule::exists('pdams', 'id')],
            'officials' => ['nullable', 'array', 'max:'.($snapshot['max_officials_per_pd'] ?? 0)],
            'officials.*.name' => ['required', 'string', 'max:120'],
            'officials.*.id' => ['nullable', 'integer'],
            'officials.*.identity_type' => [$this->input('intent') === 'submit' ? 'required' : 'nullable', Rule::in(['nik', 'kta'])],
            'officials.*.identity_number' => [$this->input('intent') === 'submit' ? 'required' : 'nullable', 'string', 'max:50'],
            'officials.*.role' => ['required', 'string', Rule::in($officialRoles)],
            ...$this->documentRules(),
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->has('teams.0.members')) $validator->errors()->add('members', 'Daftar pemain wajib diisi sesuai kuota.');
            $players = collect($this->input('teams', []))->flatMap(fn ($team) => $team['members'] ?? []);
            $names = $players->pluck('name')->map(fn ($name) => mb_strtolower(trim((string) $name)))->filter();
            $playerIdentities = $players->map(fn ($member) => $this->identityHash($member))->filter();
            if ($names->duplicates()->isNotEmpty()) { $validator->errors()->add('teams', 'Pemain tidak boleh terdaftar pada dua tim dalam kompetisi yang sama.'); $validator->errors()->add('members', 'Nama pemain tidak boleh sama dalam satu pendaftaran.'); }
            if ($playerIdentities->duplicates()->isNotEmpty()) $validator->errors()->add('teams', 'NIK/KTA pemain tidak boleh digunakan lebih dari satu kali.');
            $event = $this->route('event');
            if ($event instanceof TournamentEvent && $event->entries()->where('regional_committee_id', $this->user()->regional_committee_id)->whereIn('verification_status', ['pending', 'verified'])->exists()) { $validator->errors()->add('teams', 'Pendaftaran sedang diproses atau sudah terverifikasi.'); $validator->errors()->add('members', 'Pendaftaran sedang diproses atau sudah terverifikasi.'); }
            if (! $event instanceof TournamentEvent) return;

            $officials = collect($this->input('officials', []));
            $officialNames = $officials->pluck('name')->map(fn ($name) => mb_strtolower(trim((string) $name)))->filter();
            $officialIdentities = $officials->map(fn ($member) => $this->identityHash($member))->filter();
            if ($officialNames->duplicates()->isNotEmpty()) $validator->errors()->add('officials', 'Nama official tidak boleh didaftarkan lebih dari satu kali.');
            if ($officialIdentities->duplicates()->isNotEmpty()) $validator->errors()->add('officials', 'NIK/KTA official tidak boleh digunakan lebih dari satu kali.');

            $sameRoster = $officialIdentities->intersect($playerIdentities)->values();
            if ($sameRoster->isNotEmpty() && ! ($this->snapshot()['official_can_compete'] ?? false)) {
                $validator->errors()->add('officials', 'Official tidak boleh terdaftar sebagai pemain pada cabor ini sesuai regulasi.');
            }

            $committeeId = $this->user()->regional_committee_id;
            $activeStatuses = ['draft', 'pending', 'verified', 'revision_required'];
            $playerConflicts = DB::table('entry_members')->join('event_entries', 'entry_members.event_entry_id', '=', 'event_entries.id')
                ->where('entry_members.member_type', 'player')->where('event_entries.regional_committee_id', $committeeId)
                ->where('event_entries.tournament_event_id', '!=', $event->id)->whereIn('event_entries.verification_status', $activeStatuses)
                ->whereIn('entry_members.identity_hash', $officialIdentities)->pluck('entry_members.identity_hash');
            if ($playerConflicts->isNotEmpty() && ! ($this->snapshot()['official_can_compete'] ?? false)) {
                $validator->errors()->add('officials', 'Official sudah terdaftar sebagai pemain pada cabor lain dan regulasi ini tidak mengizinkan official bertanding.');
            }

            $officialConflicts = DB::table('entry_members')->join('event_entries', 'entry_members.event_entry_id', '=', 'event_entries.id')->join('tournament_events', 'event_entries.tournament_event_id', '=', 'tournament_events.id')
                ->where('entry_members.member_type', 'official')->where('event_entries.regional_committee_id', $committeeId)
                ->where('event_entries.tournament_event_id', '!=', $event->id)->whereIn('event_entries.verification_status', $activeStatuses)
                ->whereIn('entry_members.identity_hash', $playerIdentities)->get(['entry_members.identity_hash', 'tournament_events.registration_rules'])
                ->contains(fn ($row) => ! (json_decode($row->registration_rules, true)['official_can_compete'] ?? false));
            if ($officialConflicts) $validator->errors()->add('teams', 'Pemain terdaftar sebagai official pada cabor lain yang tidak mengizinkan official bertanding.');

            if ($this->input('intent') === 'submit') $this->validateDocuments($validator, $officials);
        });
    }

    public function messages(): array { return ['teams.required' => 'Daftar tim wajib diisi.', 'teams.max' => 'Jumlah tim melebihi kuota kompetisi.', 'teams.*.members.min' => 'Jumlah pemain tim belum memenuhi batas minimum.', 'teams.*.members.max' => 'Jumlah pemain tim melebihi batas maksimum.', 'teams.*.members.*.name.required' => 'Nama pemain wajib diisi.', 'officials.max' => 'Jumlah official melebihi kuota kompetisi.', 'officials.*.name.required' => 'Nama official wajib diisi.', 'officials.*.role.in' => 'Peran official tidak sesuai regulasi kompetisi.']; }

    private function snapshot(): array { $event = $this->route('event'); return $event instanceof TournamentEvent ? ($event->registration_rules ?? []) : []; }

    private function identityHash(array $member): ?string
    {
        $type = $member['identity_type'] ?? null;
        $number = preg_replace('/[^a-zA-Z0-9]/', '', (string) ($member['identity_number'] ?? ''));
        return $type && $number ? hash('sha256', strtolower($type.':'.$number)) : null;
    }

    private function documentRules(): array
    {
        $rules = [];
        foreach (['photo', 'registration_form', 'identity_card', 'pension_card', 'employee_decree'] as $key) $rules["teams.*.members.*.documents.$key"] = ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'];
        foreach (['photo', 'identity_card'] as $key) $rules["officials.*.documents.$key"] = ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'];
        return $rules;
    }

    private function validateDocuments(Validator $validator, $officials): void
    {
        $allMembers = collect($this->input('teams', []))->flatMap(fn ($team) => $team['members'] ?? [])->merge($officials);
        $event = $this->route('event');
        $existing = DB::table('entry_members')->join('event_entries', 'entry_members.event_entry_id', '=', 'event_entries.id')
            ->whereIn('entry_members.id', $allMembers->pluck('id')->filter())
            ->where('event_entries.regional_committee_id', $this->user()->regional_committee_id)
            ->when($event instanceof TournamentEvent, fn ($query) => $query->where('event_entries.tournament_event_id', $event->id))
            ->pluck('entry_members.documents', 'entry_members.id')->map(fn ($value) => json_decode($value, true) ?: []);
        foreach ($this->input('teams', []) as $teamIndex => $team) foreach ($team['members'] ?? [] as $index => $member) foreach (['photo', 'registration_form', 'identity_card', 'pension_card', 'employee_decree'] as $key) if (! $this->hasFile("teams.$teamIndex.members.$index.documents.$key") && ! data_get($existing, ($member['id'] ?? 0).'.'.$key)) $validator->errors()->add("teams.$teamIndex.members.$index.documents.$key", 'Dokumen pemain wajib dilengkapi sebelum pengajuan.');
        foreach ($officials as $index => $member) foreach (['photo', 'identity_card'] as $key) if (! $this->hasFile("officials.$index.documents.$key") && ! data_get($existing, ($member['id'] ?? 0).'.'.$key)) $validator->errors()->add("officials.$index.documents.$key", 'Dokumen official wajib dilengkapi sebelum pengajuan.');
    }
}
