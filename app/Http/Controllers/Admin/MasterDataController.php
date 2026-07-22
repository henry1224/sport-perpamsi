<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sport;
use App\Models\SportCategory;
use App\Models\SportRegulation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class MasterDataController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/MasterData', [
            'sports' => Sport::query()->withCount(['categories', 'events', 'regulations'])->orderBy('name')->get(),
            'categories' => SportCategory::query()->with('sport:id,name')->orderBy('sport_id')->orderBy('sort_order')->get(),
            'regulations' => SportRegulation::query()->with('sport:id,name')->latest('id')->get(),
            'audits' => DB::table('master_data_audits')->latest()->limit(20)->get(),
        ]);
    }

    public function storeSport(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:30', 'alpha_dash:ascii', 'unique:sports,code'],
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', Rule::in(['sport', 'seminar'])],
            'default_format' => ['nullable', 'string', 'max:60'],
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
            'type' => ['required', Rule::in(['sport', 'seminar'])],
            'default_format' => ['nullable', 'string', 'max:60'],
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

    public function storeRegulation(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'sport_id' => ['required', 'exists:sports,id'],
            'title' => ['required', 'string', 'max:150'],
            'content' => ['required', 'string'],
            'document_url' => ['nullable', 'url', 'max:2048'],
        ]);
        $data['version'] = (int) SportRegulation::query()->where('sport_id', $data['sport_id'])->max('version') + 1;
        $regulation = SportRegulation::query()->create($data + ['created_by' => $request->user()->id]);
        $this->audit($request, 'sport_regulation', $regulation->id, 'created', null, $regulation->toArray());

        return back()->with('success', 'Versi regulasi berhasil diterbitkan.');
    }

    private function categoryData(Request $request, ?SportCategory $category = null): array
    {
        return $request->validate([
            'sport_id' => ['required', 'exists:sports,id'],
            'code' => ['required', 'string', 'max:30', 'alpha_dash:ascii', Rule::unique('sport_categories')->where('sport_id', $request->input('sport_id'))->ignore($category)],
            'name' => ['required', 'string', 'max:100'],
            'competition_type' => ['required', Rule::in(['single', 'doubles', 'team'])],
            'min_members' => ['required', 'integer', 'min:1'],
            'max_members' => ['nullable', 'integer', 'gte:min_members', 'max:100'],
            'scoring_type' => ['required', 'string', 'max:50'],
            'bracket_enabled' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);
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
