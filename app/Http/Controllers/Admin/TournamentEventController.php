<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TournamentEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class TournamentEventController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Events', [
            'events' => TournamentEvent::query()
                ->with(['sport:id,name', 'category:id,sport_id,name,competition_type,scoring_type,min_members,max_members,is_active'])
                ->withCount('entries')
                ->orderBy('name')
                ->get()
                ->map(fn (TournamentEvent $event) => [
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
                ]),
        ]);
    }

    public function publish(Request $request, TournamentEvent $event): RedirectResponse
    {
        $data = $request->validate([
            'registration_open_at' => ['required', 'date'],
            'registration_close_at' => ['required', 'date', 'after:registration_open_at'],
        ]);

        $event->loadMissing('category');
        $category = $event->category;

        if (! $category || ! $category->is_active || $category->sport_id !== $event->sport_id) {
            throw ValidationException::withMessages(['event' => 'Kategori aktif dan cabor kompetisi harus sesuai sebelum dipublikasikan.']);
        }

        if ($event->registration_published_at && $event->entries()->exists()) {
            throw ValidationException::withMessages(['event' => 'Regulasi tidak dapat dipublikasikan ulang setelah pendaftaran masuk.']);
        }

        $event->update([
            'status' => 'registration_open',
            'registration_rules' => [
                'category_name' => $category->name,
                'competition_type' => $category->competition_type,
                'scoring_type' => $category->scoring_type,
                'format' => $event->format,
                'min_members' => $category->min_members,
                'max_members' => $category->max_members,
            ],
            'registration_published_at' => now(),
            'registration_published_by' => $request->user()->id,
            'registration_open_at' => $data['registration_open_at'],
            'registration_close_at' => $data['registration_close_at'],
            'seed_locked_at' => null,
        ]);

        return back()->with('success', 'Registrasi kompetisi dipublikasikan.');
    }

    public function close(TournamentEvent $event): RedirectResponse
    {
        abort_unless($event->registration_published_at, 422, 'Kompetisi belum dipublikasikan.');
        $event->update(['status' => 'registration_closed']);

        return back()->with('success', 'Registrasi kompetisi ditutup.');
    }
}
