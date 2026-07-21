<?php

namespace App\Http\Requests\Pd;

use App\Models\TournamentEvent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreEventEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isPdAdmin() && (bool) $this->user()->regional_committee_id;
    }

    public function rules(): array
    {
        $category = $this->category();

        return [
            'members' => ['required', 'array', 'min:'.($category?->min_members ?? 1), 'max:'.($category?->max_members ?? 1)],
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

            if ($event instanceof TournamentEvent) {
                $exists = $event->entries()
                    ->where('regional_committee_id', $user->regional_committee_id)
                    ->where('verification_status', '!=', 'rejected')
                    ->exists();

                if ($exists) {
                    $v->errors()->add('members', 'PD Anda sudah terdaftar pada cabor ini.');
                }
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

    private function category(): mixed
    {
        $event = $this->route('event');

        return $event instanceof TournamentEvent
            ? $event->loadMissing('category')->category
            : null;
    }
}
