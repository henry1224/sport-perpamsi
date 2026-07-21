<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminEntryVerificationController extends Controller
{
    public function index(): Response
    {
        $entries = EventEntry::query()
            ->with([
                'pdam:id,name,city',
                'regionalCommittee:id,name',
                'tournamentEvent:id,code,name,status',
            ])
            ->where('verification_status', 'pending')
            ->latest('id')
            ->limit(200)
            ->get()
            ->map(fn ($entry) => [
                'id' => $entry->id,
                'display_name' => $entry->display_name,
                'athlete_1' => $entry->athlete_1,
                'athlete_2' => $entry->athlete_2,
                'team_name' => $entry->team_name,
                'pdam' => $entry->pdam?->name,
                'city' => $entry->pdam?->city,
                'committee' => $entry->regionalCommittee?->name,
                'event' => $entry->tournamentEvent?->name,
                'event_code' => $entry->tournamentEvent?->code,
                'event_status' => $entry->tournamentEvent?->status,
                'created_at' => (string) $entry->created_at,
            ]);

        return Inertia::render('Admin/Entries', [
            'entries' => $entries,
        ]);
    }

    public function verify(EventEntry $entry): RedirectResponse
    {
        // ponytail: bracket tidak di-rebuild otomatis walau event sudah bracket_locked; tambahkan RebuildBracket action jika late-registration jadi kebutuhan.
        $entry->update([
            'verification_status' => 'verified',
            'verification_note' => null,
        ]);

        return back()->with('success', 'Entry disetujui.');
    }

    public function reject(Request $request, EventEntry $entry): RedirectResponse
    {
        $data = $request->validate([
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $entry->update([
            'verification_status' => 'rejected',
            'verification_note' => $data['note'] ?? null,
        ]);

        return back()->with('success', 'Entry ditolak.');
    }
}
