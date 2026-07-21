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
    public function index(Request $request): Response
    {
        $perPage = min($request->integer('per_page', 10), 100);
        $search = trim((string) $request->query('search'));
        $status = (string) $request->query('status');

        $entries = EventEntry::query()
            ->with([
                'members:id,event_entry_id,name',
                'regionalCommittee:id,name',
                'tournamentEvent:id,code,name,status',
            ])
            ->where('verification_status', 'pending')
            ->when($status, fn ($query) => $query->whereHas('tournamentEvent', fn ($query) => $query->where('status', $status)))
            ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                $query->whereLike('display_name', "%{$search}%", caseSensitive: false)
                    ->orWhereHas('regionalCommittee', fn ($query) => $query->whereLike('name', "%{$search}%", caseSensitive: false))
                    ->orWhereHas('tournamentEvent', fn ($query) => $query->whereLike('name', "%{$search}%", caseSensitive: false))
                    ->orWhereHas('members', fn ($query) => $query->whereLike('name', "%{$search}%", caseSensitive: false));
            }))
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn ($entry) => [
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
            'filters' => ['search' => $search, 'status' => $status, 'per_page' => $perPage],
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
