<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventEntry;
use App\Models\RegionalCommittee;
use App\Models\TournamentEvent;
use App\Models\TournamentMatch;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'committees' => RegionalCommittee::count(),
                'events' => TournamentEvent::count(),
                'entries' => EventEntry::count(),
                'pending' => EventEntry::where('verification_status', 'pending')->count(),
                'matches' => TournamentMatch::count(),
            ],
            'recentEntries' => EventEntry::query()
                ->with(['regionalCommittee:id,name', 'tournamentEvent:id,name'])
                ->latest()
                ->limit(6)
                ->get()
                ->map(fn (EventEntry $entry) => [
                    'id' => $entry->id,
                    'name' => $entry->display_name,
                    'committee' => $entry->regionalCommittee?->name,
                    'event' => $entry->tournamentEvent?->name,
                    'status' => $entry->verification_status,
                ]),
        ]);
    }
}
