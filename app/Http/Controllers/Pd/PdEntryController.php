<?php

namespace App\Http\Controllers\Pd;

use App\Actions\Entries\RegisterEventEntry;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pd\StoreEventEntryRequest;
use App\Models\EventEntry;
use App\Models\TournamentEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

class PdEntryController extends Controller
{
    public function show(Request $request, TournamentEvent $event): Response
    {
        abort_unless($event->registration_published_at, 404);

        $user = $request->user()->loadMissing('committee');
        $event->loadMissing(['sport:id,code,name', 'category:id,name,competition_type,scoring_type,bracket_enabled,min_members,max_members']);

        $entries = EventEntry::query()
            ->with('members:id,event_entry_id,name')
            ->where('tournament_event_id', $event->id)
            ->where('regional_committee_id', $user->regional_committee_id)
            ->latest('id')
            ->get()
            ->map(fn ($entry) => [
                'id' => $entry->id,
                'display_name' => $entry->display_name,
                'members' => $entry->members->pluck('name'),
                'verification_status' => $entry->verification_status,
                'verification_note' => $entry->verification_note,
            ]);

        $rules = $event->registration_rules ?? [];

        return Inertia::render('Pd/EventEntries', [
            'event' => [
                'code' => $event->code,
                'name' => $event->name,
                'status' => $event->status,
                'registration_open' => $event->registrationIsOpen(),
                'format' => $rules['format'] ?? $event->format,
                'sport' => $event->sport?->name,
            ],
            'category' => $event->category ? [
                'name' => $rules['category_name'] ?? $event->category->name,
                'competition_type' => $rules['competition_type'] ?? $event->category->competition_type,
                'scoring_type' => $rules['scoring_type'] ?? $event->category->scoring_type,
                'bracket_enabled' => (bool) $event->category->bracket_enabled,
                'min_members' => $rules['min_members'] ?? $event->category->min_members,
                'max_members' => $rules['max_members'] ?? $event->category->max_members,
            ] : null,
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
