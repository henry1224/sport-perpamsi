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

        $counts = EventEntry::query()
            ->where('regional_committee_id', $committee->id)
            ->selectRaw('tournament_event_id, verification_status, count(*) as total')
            ->groupBy('tournament_event_id', 'verification_status')
            ->get()
            ->groupBy('tournament_event_id');

        $events = TournamentEvent::query()
            ->with(['sport:id,code,name', 'category:id,name,competition_type,min_members,max_members'])
            ->whereNotNull('registration_published_at')
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'sport_id', 'sport_category_id', 'status', 'format', 'registration_rules', 'registration_published_at', 'registration_open_at', 'registration_close_at'])
            ->map(function ($event) use ($counts) {
                $eventCounts = collect($counts->get($event->id, []))->pluck('total', 'verification_status');
                $rules = $event->registration_rules ?? [];

                return [
                    'code' => $event->code,
                    'name' => $event->name,
                    'sport' => $event->sport?->name,
                    'category' => $rules['category_name'] ?? $event->category?->name,
                    'competition_type' => $rules['competition_type'] ?? $event->category?->competition_type,
                    'member_limit' => ($rules['min_members'] ?? null) !== null
                        ? $rules['min_members'].'–'.$rules['max_members'].' pemain'
                        : null,
                    'format' => $rules['format'] ?? $event->format,
                    'status' => $event->status,
                    'registration_open' => $event->registrationIsOpen(),
                    'entries' => [
                        'verified' => (int) $eventCounts->get('verified', 0),
                        'pending' => (int) $eventCounts->get('pending', 0),
                        'rejected' => (int) $eventCounts->get('rejected', 0),
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
        ]);
    }
}
