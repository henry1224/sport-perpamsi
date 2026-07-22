<?php

namespace App\Http\Requests\Pd;

use App\Models\TournamentEvent;
use Illuminate\Foundation\Http\FormRequest;
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
        return ['intent' => ['required', 'in:draft,submit'], 'teams' => ['required', 'array', 'min:1', 'max:'.($snapshot['max_teams_per_pd'] ?? 1)], 'teams.*.members' => $memberRules, 'teams.*.members.*.name' => ['required', 'string', 'max:120']];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->has('teams.0.members')) $validator->errors()->add('members', 'Daftar pemain wajib diisi sesuai kuota.');
            $names = collect($this->input('teams', []))->flatMap(fn ($team) => $team['members'] ?? [])->pluck('name')->map(fn ($name) => mb_strtolower(trim((string) $name)))->filter();
            if ($names->duplicates()->isNotEmpty()) { $validator->errors()->add('teams', 'Pemain tidak boleh terdaftar pada dua tim dalam kompetisi yang sama.'); $validator->errors()->add('members', 'Nama pemain tidak boleh sama dalam satu pendaftaran.'); }
            $event = $this->route('event');
            if ($event instanceof TournamentEvent && $event->entries()->where('regional_committee_id', $this->user()->regional_committee_id)->whereIn('verification_status', ['pending', 'verified'])->exists()) { $validator->errors()->add('teams', 'Pendaftaran sedang diproses atau sudah terverifikasi.'); $validator->errors()->add('members', 'Pendaftaran sedang diproses atau sudah terverifikasi.'); }
        });
    }

    public function messages(): array { return ['teams.required' => 'Daftar tim wajib diisi.', 'teams.max' => 'Jumlah tim melebihi kuota kompetisi.', 'teams.*.members.min' => 'Jumlah pemain tim belum memenuhi batas minimum.', 'teams.*.members.max' => 'Jumlah pemain tim melebihi batas maksimum.', 'teams.*.members.*.name.required' => 'Nama pemain wajib diisi.']; }

    private function snapshot(): array { $event = $this->route('event'); return $event instanceof TournamentEvent ? ($event->registration_rules ?? []) : []; }
}
