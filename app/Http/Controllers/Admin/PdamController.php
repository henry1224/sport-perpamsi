<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pdam;
use App\Models\Province;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PdamController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search'));
        $province = (string) $request->query('status');
        $perPage = min($request->integer('per_page', 10), 100);

        return Inertia::render('Admin/Pdams', [
            'pdams' => Pdam::query()->with('province:id,name')->withCount('members')
                ->when($search, fn ($query) => $query->where(fn ($query) => $query->whereLike('name', "%{$search}%", caseSensitive: false)->orWhereLike('code', "%{$search}%", caseSensitive: false)->orWhereLike('city', "%{$search}%", caseSensitive: false)))
                ->when($province, fn ($query) => $query->where('province_id', $province))->orderBy('name')->paginate($perPage)->withQueryString(),
            'provinces' => Province::query()->orderBy('name')->get(['id', 'name']),
            'filters' => ['search' => $search, 'status' => $province, 'per_page' => $perPage],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Pdam::query()->create($this->data($request) + ['public_id' => (string) Str::uuid()]);
        return back()->with('success', 'Master PDAM ditambahkan.');
    }

    public function update(Request $request, Pdam $pdam): RedirectResponse
    {
        $pdam->update($this->data($request, $pdam));
        return back()->with('success', 'Master PDAM diperbarui.');
    }

    private function data(Request $request, ?Pdam $pdam = null): array
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:30', Rule::unique('pdams')->ignore($pdam)],
            'name' => ['required', 'string', 'max:150'],
            'province_id' => ['required', Rule::exists('provinces', 'id')],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);
        $data['slug'] = Str::slug($data['name'].'-'.$data['code']);
        return $data;
    }
}
