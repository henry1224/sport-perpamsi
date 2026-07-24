<?php

namespace App\Http\Controllers\Pd;

use App\Http\Controllers\Controller;
use App\Models\EventEntry;
use App\Models\TournamentEvent;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PdDashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user()->loadMissing('committee.province');
        $committee = $user->committee;
        $search = trim((string) $request->query('search'));
        $status = (string) $request->query('status');
        $perPage = min(max($request->integer('per_page', 10), 10), 100);

        $counts = EventEntry::query()
            ->where('regional_committee_id', $committee->id)
            ->selectRaw('tournament_event_id, verification_status, count(*) as total')
            ->groupBy('tournament_event_id', 'verification_status')
            ->get()
            ->groupBy('tournament_event_id');
        $playerCounts = EventEntry::query()
            ->join('entry_members', 'event_entries.id', '=', 'entry_members.event_entry_id')
            ->where('event_entries.regional_committee_id', $committee->id)
            ->where('entry_members.member_type', 'player')
            ->selectRaw('event_entries.tournament_event_id, entry_members.verification_status, count(*) as total')
            ->groupBy('event_entries.tournament_event_id', 'entry_members.verification_status')
            ->get()
            ->groupBy('tournament_event_id');

        $events = TournamentEvent::query()
            ->with(['sport:id,code,name', 'category:id,name,competition_type,min_members,max_members'])
            ->whereNotNull('registration_published_at')
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                $query->whereLike('name', "%{$search}%", caseSensitive: false)
                    ->orWhereLike('code', "%{$search}%", caseSensitive: false)
                    ->orWhereHas('sport', fn ($query) => $query->whereLike('name', "%{$search}%", caseSensitive: false))
                    ->orWhereHas('category', fn ($query) => $query->whereLike('name', "%{$search}%", caseSensitive: false));
            }))
            ->orderBy('name')
            ->paginate($perPage, ['id', 'code', 'name', 'sport_id', 'sport_category_id', 'status', 'format', 'registration_rules', 'registration_published_at', 'registration_open_at', 'registration_close_at'])
            ->withQueryString()
            ->through(function ($event) use ($counts, $playerCounts) {
                $eventCounts = collect($counts->get($event->id, []))->pluck('total', 'verification_status');
                $eventPlayerCounts = collect($playerCounts->get($event->id, []))->pluck('total', 'verification_status');
                $rules = $event->registration_rules ?? [];

                return [
                    'code' => $event->code,
                    'name' => $event->name,
                    'sport' => $event->sport?->name,
                    'category' => $rules['category_name'] ?? $event->category?->name,
                    'competition_type' => $rules['competition_type'] ?? $event->category?->competition_type,
                    'member_limit' => ($rules['min_members_per_team'] ?? $rules['min_members'] ?? null) === null
                        ? null
                        : (($rules['max_members_per_team'] ?? $rules['max_members'] ?? null) === null
                            ? 'Minimal '.($rules['min_members_per_team'] ?? $rules['min_members']).' pemain'
                            : (($rules['min_members_per_team'] ?? $rules['min_members']) === ($rules['max_members_per_team'] ?? $rules['max_members'])
                                ? ($rules['min_members_per_team'] ?? $rules['min_members']).' pemain'
                                : ($rules['min_members_per_team'] ?? $rules['min_members']).'–'.($rules['max_members_per_team'] ?? $rules['max_members']).' pemain')),
                    'format' => $rules['format'] ?? $event->format,
                    'status' => $event->status,
                    'registration_open' => $event->registrationIsOpen(),
                    'entries' => [
                        'verified' => (int) $eventCounts->get('verified', 0),
                        'pending' => (int) $eventCounts->get('pending', 0),
                        'rejected' => (int) $eventCounts->get('rejected', 0),
                    ],
                    'players' => [
                        'total' => (int) $eventPlayerCounts->sum(),
                        'verified' => (int) $eventPlayerCounts->get('verified', 0),
                        'pending' => (int) $eventPlayerCounts->get('pending', 0),
                        'revision_required' => (int) $eventPlayerCounts->get('revision_required', 0),
                        'rejected' => (int) $eventPlayerCounts->get('rejected', 0),
                    ],
                ];
            });

        return Inertia::render('Pd/Dashboard', [
            'committee' => [
                'id' => $committee->id,
                'name' => $committee->name,
                'province' => $committee->province?->name,
            ],
            'events' => $events,
            'filters' => ['search' => $search, 'status' => $status, 'per_page' => $perPage],
            'summary' => [
                'events' => TournamentEvent::query()->whereNotNull('registration_published_at')->count(),
                'entries' => EventEntry::query()->where('regional_committee_id', $committee->id)->whereIn('verification_status', ['pending', 'verified'])->count(),
            ],
        ]);
    }
}
