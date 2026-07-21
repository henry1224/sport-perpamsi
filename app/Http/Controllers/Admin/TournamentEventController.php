<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SportRegulation;
use App\Models\TournamentEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
                ->with(['sport:id,name', 'sport.regulations' => fn ($query) => $query->where('is_active', true)->latest('version'), 'category:id,sport_id,name,competition_type,scoring_type,min_members,max_members,is_active', 'regulation:id,sport_id,version,title'])
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
                    'sport' => $event->sport?->name,
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
        ]);
    }

    public function publish(Request $request, TournamentEvent $event): RedirectResponse
    {
        $data = $request->validate([
            'registration_open_at' => ['required', 'date'],
            'registration_close_at' => ['required', 'date', 'after:registration_open_at'],
            'sport_regulation_id' => ['required', Rule::exists('sport_regulations', 'id')->where('is_active', true)],
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
        $event->update(['status' => 'registration_draft', 'sport_regulation_id' => null, 'registration_rules' => null, 'registration_published_at' => null, 'registration_published_by' => null, 'registration_open_at' => null, 'registration_close_at' => null]);
        $this->audit($event, 'unpublished', $before, $this->publicationState($event), $request);

        return back()->with('success', 'Publikasi kompetisi ditarik.');
    }

    private function publicationState(TournamentEvent $event): array
    {
        return ['status' => $event->status, 'sport_regulation_id' => $event->sport_regulation_id, 'rules' => $event->registration_rules, 'published_at' => $event->registration_published_at?->toISOString(), 'open_at' => $event->registration_open_at?->toISOString(), 'close_at' => $event->registration_close_at?->toISOString()];
    }

    private function audit(TournamentEvent $event, string $action, array $before, array $after, Request $request): void
    {
        DB::table('event_publication_audits')->insert(['tournament_event_id' => $event->id, 'action' => $action, 'before_json' => json_encode($before), 'after_json' => json_encode($after), 'user_id' => $request->user()->id, 'created_at' => now(), 'updated_at' => now()]);
    }
}
