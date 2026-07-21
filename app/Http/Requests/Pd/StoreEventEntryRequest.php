<?php

namespace App\Http\Requests\Pd;

use App\Models\TournamentEvent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreEventEntryRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['intent' => $this->input('intent', 'submit')]);
    }

    public function authorize(): bool
    {
        return $this->user()?->isPdAdmin() && (bool) $this->user()->regional_committee_id;
    }

    public function rules(): array
    {
        $rules = $this->rulesSnapshot();

        return [
            'intent' => ['required', 'in:draft,submit'],
            'members' => ['required', 'array', 'min:'.($this->input('intent') === 'draft' ? 1 : ($rules['min_members'] ?? 1)), 'max:'.($rules['max_members'] ?? 1)],
            'members.*.name' => ['required', 'string', 'max:120'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $user = $this->user();
            $event = $this->route('event');
            $names = collect($this->input('members', []))
                ->pluck('name')
                ->map(fn ($name) => mb_strtolower(trim((string) $name)))
                ->filter();

            if ($names->duplicates()->isNotEmpty()) {
                $v->errors()->add('members', 'Nama pemain tidak boleh sama dalam satu pendaftaran.');
            }

            if ($event instanceof TournamentEvent && $event->entries()->where('regional_committee_id', $user->regional_committee_id)->whereIn('verification_status', ['pending', 'verified'])->exists()) {
                $v->errors()->add('members', 'Pendaftaran sedang diproses atau sudah terverifikasi.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'members.required' => 'Daftar pemain wajib diisi.',
            'members.min' => 'Jumlah pemain belum memenuhi batas minimum.',
            'members.max' => 'Jumlah pemain melebihi batas maksimum.',
            'members.*.name.required' => 'Nama pemain wajib diisi.',
        ];
    }

    private function rulesSnapshot(): array
    {
        $event = $this->route('event');

        return $event instanceof TournamentEvent ? ($event->registration_rules ?? []) : [];
    }
}
