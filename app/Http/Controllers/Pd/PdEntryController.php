<?php

namespace App\Http\Controllers\Pd;

use App\Actions\Entries\RegisterEventEntry;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pd\StoreEventEntryRequest;
use App\Models\EventEntry;
use App\Models\Pdam;
use App\Models\TournamentEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Inertia\Inertia;
use Inertia\Response;

class PdEntryController extends Controller
{
    public function show(Request $request, TournamentEvent $event): Response
    {
        $user = $request->user()->loadMissing('committee');
        $event->loadMissing(['sport:id,code,name', 'category:id,name,competition_type,scoring_type,bracket_enabled']);

        $pdams = Pdam::query()
            ->where('province_id', $user->committee->province_id)
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'city']);

        $entries = EventEntry::query()
            ->with('pdam:id,name,city')
            ->where('tournament_event_id', $event->id)
            ->where('regional_committee_id', $user->regional_committee_id)
            ->latest('id')
            ->get()
            ->map(fn ($entry) => [
                'id' => $entry->id,
                'display_name' => $entry->display_name,
                'pdam' => $entry->pdam?->name,
                'athlete_1' => $entry->athlete_1,
                'athlete_2' => $entry->athlete_2,
                'team_name' => $entry->team_name,
                'verification_status' => $entry->verification_status,
                'verification_note' => $entry->verification_note,
            ]);

        return Inertia::render('Pd/EventEntries', [
            'event' => [
                'code' => $event->code,
                'name' => $event->name,
                'status' => $event->status,
                'format' => $event->format,
                'sport' => $event->sport?->name,
            ],
            'category' => $event->category ? [
                'name' => $event->category->name,
                'competition_type' => $event->category->competition_type,
                'scoring_type' => $event->category->scoring_type,
                'bracket_enabled' => (bool) $event->category->bracket_enabled,
            ] : null,
            'pdams' => $pdams,
            'entries' => $entries,
        ]);
    }

    public function store(StoreEventEntryRequest $request, TournamentEvent $event, RegisterEventEntry $action): RedirectResponse
    {
        try {
            $action->handle($request->user(), $event, $request->validated());
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return back()->with('success', 'Pendaftaran diajukan. Menunggu verifikasi.');
    }

    public function destroy(Request $request, EventEntry $entry): RedirectResponse
    {
        $user = $request->user();

        abort_unless(
            $entry->regional_committee_id === $user->regional_committee_id && $entry->verification_status === 'pending',
            403,
            'Hanya entry pending milik PD Anda yang bisa dihapus.'
        );

        $entry->delete();

        return back()->with('success', 'Pendaftaran dibatalkan.');
    }
}
