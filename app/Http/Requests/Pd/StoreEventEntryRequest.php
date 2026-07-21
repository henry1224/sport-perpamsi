<?php

namespace App\Http\Requests\Pd;

use App\Models\Pdam;
use App\Models\TournamentEvent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreEventEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isPdAdmin() && (bool) $this->user()->regional_committee_id;
    }

    public function rules(): array
    {
        $type = $this->competitionType();

        return [
            'pdam_id' => ['required', 'integer', Rule::exists('pdams', 'id')],
            'athlete_1' => [Rule::requiredIf(in_array($type, ['individual', 'doubles'], true)), 'nullable', 'string', 'max:120'],
            'athlete_2' => [Rule::requiredIf($type === 'doubles'), 'nullable', 'string', 'max:120'],
            'team_name' => [Rule::requiredIf($type === 'team'), 'nullable', 'string', 'max:160'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $user = $this->user();
            $pdam = Pdam::find($this->input('pdam_id'));
            $event = $this->route('event');

            if (! $pdam || $pdam->province_id !== $user->committee?->province_id) {
                $v->errors()->add('pdam_id', 'PDAM harus berasal dari provinsi PD Anda.');

                return;
            }

            if ($event instanceof TournamentEvent) {
                $exists = $event->entries()
                    ->where('pdam_id', $pdam->id)
                    ->where('verification_status', '!=', 'rejected')
                    ->exists();

                if ($exists) {
                    $v->errors()->add('pdam_id', 'PDAM ini sudah terdaftar pada event tersebut.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'athlete_1.required' => 'Nama atlet wajib diisi.',
            'athlete_2.required' => 'Nama atlet kedua wajib diisi untuk kategori ganda.',
            'team_name.required' => 'Nama tim wajib diisi untuk kategori beregu.',
            'pdam_id.required' => 'Pilih PDAM peserta.',
        ];
    }

    private function competitionType(): ?string
    {
        $event = $this->route('event');

        return $event instanceof TournamentEvent
            ? $event->loadMissing('category')->category?->competition_type
            : null;
    }
}
