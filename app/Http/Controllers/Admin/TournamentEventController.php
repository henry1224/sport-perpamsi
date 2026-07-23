<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sport;
use App\Models\SportCategory;
use App\Models\SportRegulation;
use App\Models\TournamentEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class TournamentEventController extends Controller
{
    public function index(Request $request): Response
    {
        $perPage = min($request->integer('per_page', 10), 100);
        $search = trim((string) $request->query('search'));
        $status = (string) $request->query('status');

        return Inertia::render('Admin/Events', [
            'events' => TournamentEvent::query()
                ->with(['sport:id,name,default_format,default_max_officials_per_pd,official_roles,allow_member_cross_category,max_categories_per_member,official_can_compete', 'sport.regulations' => fn ($query) => $query->where('is_active', true)->latest('version'), 'category:id,sport_id,name,competition_type,scoring_type,min_members,max_members,default_max_teams_per_pd,is_active', 'regulation:id,sport_id,version,title'])
                ->withCount('entries')
                ->when($status, fn ($query) => $query->where('status', $status))
                ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                    $query->whereLike('name', "%{$search}%", caseSensitive: false)
                        ->orWhereLike('code', "%{$search}%", caseSensitive: false)
                        ->orWhereHas('sport', fn ($query) => $query->whereLike('name', "%{$search}%", caseSensitive: false))
                        ->orWhereHas('category', fn ($query) => $query->whereLike('name', "%{$search}%", caseSensitive: false));
                }))
                ->orderBy('name')
                ->paginate($perPage)
                ->withQueryString()
                ->through(fn (TournamentEvent $event) => [
                    'code' => $event->code,
                    'name' => $event->name,
                    'sport_id' => $event->sport_id,
                    'category_id' => $event->sport_category_id,
                    'sport' => $event->sport?->name,
                    'default_format' => $event->sport?->default_format,
                    'category' => $event->category?->name,
                    'format' => $event->format,
                    'status' => $event->status,
                    'published' => (bool) $event->registration_published_at,
                    'open_at' => $event->registration_open_at?->format('Y-m-d\TH:i'),
                    'close_at' => $event->registration_close_at?->format('Y-m-d\TH:i'),
                    'entries_count' => $event->entries_count,
                    'regulation_id' => $event->sport_regulation_id,
                    'regulation' => $event->regulation ? 'v'.$event->regulation->version.' · '.$event->regulation->title : null,
                    'regulations' => $event->sport?->regulations->map(fn ($regulation) => ['id' => $regulation->id, 'label' => 'v'.$regulation->version.' · '.$regulation->title, 'content' => $regulation->content, 'document_url' => $regulation->document_url])->values(),
                    'rules' => $event->registration_rules,
                ]),
            'audits' => DB::table('event_publication_audits')->join('tournament_events', 'event_publication_audits.tournament_event_id', '=', 'tournament_events.id')->latest('event_publication_audits.created_at')->limit(20)->get(['event_publication_audits.id', 'tournament_events.name as event', 'event_publication_audits.action', 'event_publication_audits.created_at']),
            'filters' => ['search' => $search, 'status' => $status, 'per_page' => $perPage],
            'sportFormats' => Sport::FORMAT_LABELS,
            'sports' => Sport::query()->where('is_active', true)
                ->with(['categories' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order'), 'regulations' => fn ($query) => $query->where('is_active', true)->latest('version')])
                ->orderBy('name')->get(['id', 'name', 'default_format', 'default_max_officials_per_pd', 'official_roles', 'allow_member_cross_category', 'max_categories_per_member', 'official_can_compete']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        TournamentEvent::query()->create($this->eventData($request) + [
            'public_id' => (string) Str::uuid(),
            'status' => 'registration_draft',
        ]);

        return back()->with('success', 'Data lomba berhasil ditambahkan.');
    }

    public function update(Request $request, TournamentEvent $event): RedirectResponse
    {
        abort_if($event->registration_published_at || $event->entries()->exists(), 422, 'Data lomba hanya dapat diubah saat draft dan belum memiliki peserta.');
        $event->update($this->eventData($request, $event));

        return back()->with('success', 'Data lomba berhasil diperbarui.');
    }

    public function destroy(TournamentEvent $event): RedirectResponse
    {
        abort_if($event->registration_published_at || $event->entries()->exists() || $event->matches()->exists() || DB::table('event_agendas')->where('tournament_event_id', $event->id)->exists(), 422, 'Data lomba hanya dapat dihapus saat draft dan belum dipakai.');
        $event->delete();

        return back()->with('success', 'Data lomba berhasil dihapus.');
    }

    public function publish(Request $request, TournamentEvent $event): RedirectResponse
    {
        $data = $request->validate([
            'registration_open_at' => ['required', 'date'],
            'registration_close_at' => ['required', 'date', 'after:registration_open_at'],
            'sport_regulation_id' => ['required', Rule::exists('sport_regulations', 'id')->where('is_active', true)],
            'max_teams_per_pd' => ['required', 'integer', 'min:1', 'max:16'],
        ]);

        $event->loadMissing('category');
        $category = $event->category;
        $regulation = SportRegulation::query()->findOrFail($data['sport_regulation_id']);

        if (! $category || ! $category->is_active || $category->sport_id !== $event->sport_id) {
            throw ValidationException::withMessages(['event' => 'Kategori aktif dan cabor kompetisi harus sesuai sebelum dipublikasikan.']);
        }

        if ($regulation->sport_id !== $event->sport_id) {
            throw ValidationException::withMessages(['sport_regulation_id' => 'Regulasi harus berasal dari cabor kompetisi yang sama.']);
        }

        if ($event->registration_published_at && $event->entries()->exists()) {
            throw ValidationException::withMessages(['event' => 'Regulasi tidak dapat dipublikasikan ulang setelah pendaftaran masuk.']);
        }

        DB::transaction(function () use ($event, $category, $regulation, $data, $request) {
            $before = $this->publicationState($event);
            $draftRules = $event->registration_rules ?? [];
            $event->update([
                'status' => 'registration_open',
                'sport_regulation_id' => $regulation->id,
                'registration_rules' => [
                    'category_name' => $category->name,
                    'competition_type' => $category->competition_type,
                    'scoring_type' => $category->scoring_type,
                    'format' => $event->format,
                    'min_members' => $category->min_members,
                    'max_members' => $category->max_members,
                    'max_teams_per_pd' => $data['max_teams_per_pd'],
                    'min_members_per_team' => $category->min_members,
                    'max_members_per_team' => $category->max_members,
                    'max_officials_per_pd' => $draftRules['max_officials_per_pd'] ?? 0,
                    'official_roles' => $draftRules['official_roles'] ?? [],
                    'allow_member_cross_category' => $draftRules['allow_member_cross_category'] ?? false,
                    'max_categories_per_member' => $draftRules['max_categories_per_member'] ?? null,
                    'official_can_compete' => $draftRules['official_can_compete'] ?? false,
                    'avoid_same_pd_in_round' => true,
                    'regulation_id' => $regulation->id,
                    'regulation_version' => $regulation->version,
                    'regulation_title' => $regulation->title,
                ],
                'registration_published_at' => now(),
                'registration_published_by' => $request->user()->id,
                'registration_open_at' => $data['registration_open_at'],
                'registration_close_at' => $data['registration_close_at'],
                'seed_locked_at' => null,
            ]);
            $this->audit($event, $before['published_at'] ? 'republished' : 'published', $before, $this->publicationState($event), $request);
        });

        return back()->with('success', 'Registrasi kompetisi dipublikasikan.');
    }

    public function updateFormat(Request $request, TournamentEvent $event): RedirectResponse
    {
        if ($event->registration_published_at || $event->entries()->exists()) {
            throw ValidationException::withMessages(['format' => 'Format kompetisi terkunci setelah dipublikasikan atau memiliki peserta.']);
        }

        $data = $request->validate(['format' => ['required', Rule::in(array_keys(Sport::FORMAT_LABELS))]]);
        $event->update($data);

        return back()->with('success', 'Format kompetisi diperbarui.');
    }

    public function close(Request $request, TournamentEvent $event): RedirectResponse
    {
        abort_unless($event->registration_published_at, 422, 'Kompetisi belum dipublikasikan.');
        $before = $this->publicationState($event);
        $event->update(['status' => 'registration_closed']);
        $this->audit($event, 'closed', $before, $this->publicationState($event), $request);

        return back()->with('success', 'Registrasi kompetisi ditutup.');
    }

    public function unpublish(Request $request, TournamentEvent $event): RedirectResponse
    {
        abort_unless($event->registration_published_at, 422, 'Kompetisi belum dipublikasikan.');
        abort_if($event->entries()->exists(), 422, 'Publikasi tidak dapat ditarik setelah pendaftaran masuk.');

        $before = $this->publicationState($event);
        $event->update(['status' => 'registration_draft', 'registration_published_at' => null, 'registration_published_by' => null, 'registration_open_at' => null, 'registration_close_at' => null]);
        $this->audit($event, 'unpublished', $before, $this->publicationState($event), $request);

        return back()->with('success', 'Publikasi kompetisi ditarik.');
    }

    private function publicationState(TournamentEvent $event): array
    {
        return ['status' => $event->status, 'sport_regulation_id' => $event->sport_regulation_id, 'rules' => $event->registration_rules, 'published_at' => $event->registration_published_at?->toISOString(), 'open_at' => $event->registration_open_at?->toISOString(), 'close_at' => $event->registration_close_at?->toISOString()];
    }

    private function eventData(Request $request, ?TournamentEvent $event = null): array
    {
        $data = $request->validate([
            'sport_id' => ['required', Rule::exists('sports', 'id')->where('is_active', true)],
            'sport_category_id' => ['required', Rule::exists('sport_categories', 'id')->where('is_active', true)],
            'sport_regulation_id' => ['required', Rule::exists('sport_regulations', 'id')->where('is_active', true)],
            'code' => ['required', 'string', 'max:100', 'alpha_dash:ascii', Rule::unique('tournament_events', 'code')->ignore($event)],
            'name' => ['required', 'string', 'max:150'],
            'format' => ['required', Rule::in(array_keys(Sport::FORMAT_LABELS))],
            'max_teams_per_pd' => ['nullable', 'integer', 'min:1', 'max:16'],
            'max_officials_per_pd' => ['nullable', 'integer', 'min:0', 'max:20'],
            'official_roles' => ['nullable', 'array', 'max:10'],
            'official_roles.*' => ['string', 'max:50'],
            'allow_member_cross_category' => ['nullable', 'boolean'],
            'max_categories_per_member' => ['nullable', 'integer', 'min:1', 'max:20'],
            'official_can_compete' => ['nullable', 'boolean'],
        ]);
        $sport = Sport::query()->findOrFail($data['sport_id']);
        $category = SportCategory::query()->findOrFail($data['sport_category_id']);
        $regulation = SportRegulation::query()->findOrFail($data['sport_regulation_id']);

        if (TournamentEvent::query()->where('sport_id', $data['sport_id'])->where('sport_category_id', $data['sport_category_id'])->when($event, fn ($query) => $query->whereKeyNot($event->id))->exists()) {
            throw ValidationException::withMessages(['sport_category_id' => 'Kategori ini sudah memiliki Data Lomba. Ubah data yang sudah ada.']);
        }

        if ($category->sport_id !== (int) $data['sport_id'] || $regulation->sport_id !== (int) $data['sport_id']) {
            throw ValidationException::withMessages(['sport_id' => 'Cabor, kategori, dan regulasi harus berasal dari master yang sama.']);
        }

        foreach (['max_teams_per_pd', 'max_officials_per_pd', 'official_roles', 'allow_member_cross_category', 'max_categories_per_member', 'official_can_compete'] as $key) unset($data[$key]);
        $data['registration_rules'] = [
            'max_teams_per_pd' => $request->integer('max_teams_per_pd') ?: $category->default_max_teams_per_pd,
            'min_members_per_team' => $category->min_members,
            'max_members_per_team' => $category->max_members,
            'max_officials_per_pd' => $request->input('max_officials_per_pd', $sport->default_max_officials_per_pd),
            'official_roles' => $request->input('official_roles', $sport->official_roles ?? []),
            'allow_member_cross_category' => $request->has('allow_member_cross_category') ? $request->boolean('allow_member_cross_category') : $sport->allow_member_cross_category,
            'max_categories_per_member' => $request->input('max_categories_per_member', $sport->max_categories_per_member),
            'official_can_compete' => $request->has('official_can_compete') ? $request->boolean('official_can_compete') : $sport->official_can_compete,
        ];

        return $data;
    }

    private function audit(TournamentEvent $event, string $action, array $before, array $after, Request $request): void
    {
        DB::table('event_publication_audits')->insert(['tournament_event_id' => $event->id, 'action' => $action, 'before_json' => json_encode($before), 'after_json' => json_encode($after), 'user_id' => $request->user()->id, 'created_at' => now(), 'updated_at' => now()]);
    }
}
