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
                'members:id,event_entry_id,name',
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
                'members' => $entry->members->pluck('name'),
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

    public function verify(Request $request, EventEntry $entry): RedirectResponse
    {
        // ponytail: bracket tidak di-rebuild otomatis walau event sudah bracket_locked; tambahkan RebuildBracket action jika late-registration jadi kebutuhan.
        $entry->update([
            'verification_status' => 'verified',
            'verification_note' => null,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
        ]);

        return back()->with('success', 'Entry disetujui.');
    }

    public function reject(Request $request, EventEntry $entry): RedirectResponse
    {
        $data = $request->validate([
            'note' => ['required', 'string', 'max:255'],
        ]);

        $entry->update([
            'verification_status' => 'rejected',
            'verification_note' => $data['note'],
            'verified_by' => null,
            'verified_at' => null,
        ]);

        return back()->with('success', 'Entry ditolak.');
    }
}
