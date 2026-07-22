<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sport;
use App\Models\SportCategory;
use App\Models\SportRegulation;
use App\Models\Venue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class MasterDataController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Admin/MasterData', [
            'sports' => Sport::query()->withCount(['categories', 'events', 'regulations'])->orderBy('name')->get(),
            'categories' => SportCategory::query()->with('sport:id,name')->withCount('events')->orderBy('sport_id')->orderBy('sort_order')->get(),
            'regulations' => SportRegulation::query()->with('sport:id,name')->withCount('events')->latest('id')->get(),
            'venues' => Venue::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'address', 'city']),
            'audits' => DB::table('master_data_audits')->latest()->limit(20)->get(),
            'sportFormats' => Sport::FORMAT_LABELS,
            'initialTab' => in_array($request->query('tab'), ['sports', 'categories', 'regulations', 'audit'], true) ? $request->query('tab') : 'sports',
        ]);
    }

    public function storeSport(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:30', 'alpha_dash:ascii', 'unique:sports,code'],
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', Rule::in(['sport', 'exhibition'])],
            'default_format' => ['required', Rule::in(array_keys(Sport::FORMAT_LABELS))],
            'score_template' => ['nullable', 'string', 'max:100'],
        ]);

        $sport = Sport::query()->create($data + ['is_active' => true]);
        $this->audit($request, 'sport', $sport->id, 'created', null, $sport->toArray());

        return back()->with('success', 'Cabor berhasil ditambahkan.');
    }

    public function updateSport(Request $request, Sport $sport): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:30', 'alpha_dash:ascii', Rule::unique('sports', 'code')->ignore($sport)],
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', Rule::in(['sport', 'exhibition'])],
            'default_format' => ['required', Rule::in(array_keys(Sport::FORMAT_LABELS))],
            'score_template' => ['nullable', 'string', 'max:100'],
            'is_active' => ['required', 'boolean'],
        ]);
        $before = $sport->toArray();
        $sport->update($data);
        $this->audit($request, 'sport', $sport->id, 'updated', $before, $sport->fresh()->toArray());

        return back()->with('success', 'Cabor berhasil diperbarui.');
    }

    public function destroySport(Request $request, Sport $sport): RedirectResponse
    {
        if ($sport->is_active) {
            return back()->with('error', 'Cabor aktif tidak dapat dihapus. Nonaktifkan terlebih dahulu.');
        }
        if ($sport->categories()->exists() || $sport->regulations()->exists() || $sport->events()->exists()) {
            return back()->with('error', 'Cabor tidak dapat dihapus karena sudah memiliki kategori, regulasi, atau kompetisi.');
        }

        DB::transaction(function () use ($request, $sport) {
            $before = $sport->toArray();
            $this->audit($request, 'sport', $sport->id, 'deleted', $before, []);
            $sport->delete();
        });

        return back()->with('success', 'Cabor berhasil dihapus.');
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $data = $this->categoryData($request);
        $category = SportCategory::query()->create($data + ['public_id' => (string) Str::uuid()]);
        $this->audit($request, 'sport_category', $category->id, 'created', null, $category->toArray());

        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function updateCategory(Request $request, SportCategory $category): RedirectResponse
    {
        $before = $category->toArray();
        $category->update($this->categoryData($request, $category));
        $this->audit($request, 'sport_category', $category->id, 'updated', $before, $category->fresh()->toArray());

        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroyCategory(Request $request, SportCategory $category): RedirectResponse
    {
        if ($category->is_active || DB::table('tournament_events')->where('sport_category_id', $category->id)->exists()) {
            return back()->with('error', 'Kategori hanya dapat dihapus saat nonaktif dan belum dipakai kompetisi.');
        }
        $before = $category->toArray();
        $this->audit($request, 'sport_category', $category->id, 'deleted', $before, []);
        $category->delete();

        return back()->with('success', 'Kategori berhasil dihapus.');
    }

    public function storeRegulation(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'sport_id' => ['required', 'exists:sports,id'],
            'title' => ['required', 'string', 'max:150'],
            'content' => ['required', 'string'],
            'document_url' => ['nullable', 'url', 'max:2048'],
            'schedule' => ['nullable', 'string', 'max:255'], 'venue_id' => ['nullable', Rule::exists('venues', 'id')->where('is_active', true)],
            'system_text' => ['nullable', 'string'], 'eligibility_text' => ['nullable', 'string'], 'official_note' => ['nullable', 'string'],
            'fee_note' => ['nullable', 'string'], 'source_slides' => ['nullable', 'string', 'max:30'],
        ]);
        $data['technical_guide'] = $this->technicalGuide($data);
        $data['version'] = (int) SportRegulation::query()->where('sport_id', $data['sport_id'])->max('version') + 1;
        $regulation = SportRegulation::query()->create($data + ['created_by' => $request->user()->id]);
        $this->audit($request, 'sport_regulation', $regulation->id, 'created', null, $regulation->toArray());

        return back()->with('success', 'Versi regulasi berhasil diterbitkan.');
    }

    public function updateRegulation(Request $request, SportRegulation $regulation): RedirectResponse
    {
        $data = $request->validate(['title' => ['required', 'string', 'max:150'], 'content' => ['required', 'string'], 'document_url' => ['nullable', 'url', 'max:2048'], 'is_active' => ['required', 'boolean'],
            'schedule' => ['nullable', 'string', 'max:255'], 'venue_id' => ['nullable', Rule::exists('venues', 'id')->where('is_active', true)],
            'system_text' => ['nullable', 'string'], 'eligibility_text' => ['nullable', 'string'], 'official_note' => ['nullable', 'string'],
            'fee_note' => ['nullable', 'string'], 'source_slides' => ['nullable', 'string', 'max:30'],
        ]);
        $data['technical_guide'] = $this->technicalGuide($data);
        $before = $regulation->toArray();
        $regulation->update($data);
        $this->audit($request, 'sport_regulation', $regulation->id, 'updated', $before, $regulation->fresh()->toArray());

        return back()->with('success', 'Regulasi berhasil diperbarui.');
    }

    private function technicalGuide(array &$data): array
    {
        $venue = empty($data['venue_id']) ? null : Venue::query()->find($data['venue_id']);
        $guide = collect(['schedule', 'official_note', 'fee_note', 'source_slides'])->mapWithKeys(fn ($key) => [$key => $data[$key] ?? null])->all();
        $guide += ['venue_id' => $venue?->id, 'venue' => $venue?->name, 'address' => $venue?->address];
        $guide['system'] = preg_split('/\r\n|\r|\n/', $data['system_text'] ?? '', -1, PREG_SPLIT_NO_EMPTY);
        $guide['eligibility'] = preg_split('/\r\n|\r|\n/', $data['eligibility_text'] ?? '', -1, PREG_SPLIT_NO_EMPTY);
        foreach (['schedule', 'venue_id', 'system_text', 'eligibility_text', 'official_note', 'fee_note', 'source_slides'] as $key) {
            unset($data[$key]);
        }

        return array_filter($guide, fn ($value) => $value !== null && $value !== [] && $value !== '');
    }

    public function destroyRegulation(Request $request, SportRegulation $regulation): RedirectResponse
    {
        if ($regulation->is_active || DB::table('tournament_events')->where('sport_regulation_id', $regulation->id)->exists()) {
            return back()->with('error', 'Regulasi hanya dapat dihapus saat nonaktif dan belum dipakai kompetisi.');
        }
        $before = $regulation->toArray();
        $this->audit($request, 'sport_regulation', $regulation->id, 'deleted', $before, []);
        $regulation->delete();

        return back()->with('success', 'Regulasi berhasil dihapus.');
    }

    private function categoryData(Request $request, ?SportCategory $category = null): array
    {
        $data = $request->validate([
            'sport_id' => ['required', 'exists:sports,id'],
            'code' => ['required', 'string', 'max:30', 'alpha_dash:ascii', Rule::unique('sport_categories')->where('sport_id', $request->input('sport_id'))->ignore($category)],
            'name' => ['required', 'string', 'max:100'],
            'competition_type' => ['required', Rule::in(['single', 'doubles', 'team'])],
            'min_members' => ['required', 'integer', 'min:1'],
            'max_members' => ['nullable', 'integer', 'gte:min_members', 'max:100'],
            'bracket_enabled' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        return $data + ['scoring_type' => Sport::query()->findOrFail($data['sport_id'])->score_template ?? 'points'];
    }

    private function audit(Request $request, string $type, int $id, string $action, ?array $before, array $after): void
    {
        DB::table('master_data_audits')->insert([
            'entity_type' => $type,
            'entity_id' => $id,
            'action' => $action,
            'before_json' => $before ? json_encode($before) : null,
            'after_json' => json_encode($after),
            'user_id' => $request->user()->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
