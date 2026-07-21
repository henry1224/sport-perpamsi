<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventAgenda;
use App\Models\Sport;
use App\Models\TournamentEvent;
use App\Models\Venue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class VenueAgendaController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search'));
        $perPage = min(max($request->integer('per_page', 10), 10), 100);

        return Inertia::render('Admin/VenueAgendas', [
            'venues' => Venue::query()->orderBy('name')->get(),
            'sports' => Sport::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'events' => TournamentEvent::query()->orderBy('name')->get(['id', 'name', 'sport_id']),
            'agendas' => EventAgenda::query()->join('venues', 'event_agendas.venue_id', '=', 'venues.id')
                ->leftJoin('sports', 'event_agendas.sport_id', '=', 'sports.id')
                ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                    $query->whereLike('event_agendas.title', "%{$search}%", caseSensitive: false)
                        ->orWhereLike('venues.name', "%{$search}%", caseSensitive: false)
                        ->orWhereLike('sports.name', "%{$search}%", caseSensitive: false);
                }))->orderBy('event_agendas.date')->orderBy('event_agendas.start_time')
                ->select('event_agendas.*', 'venues.name as venue_name', 'sports.name as sport_name')
                ->paginate($perPage)->withQueryString(),
            'filters' => ['search' => $search, 'per_page' => $perPage],
        ]);
    }

    public function storeVenue(Request $request): RedirectResponse
    {
        Venue::query()->create($this->venueData($request));

        return back()->with('success', 'Venue berhasil ditambahkan.');
    }

    public function updateVenue(Request $request, Venue $venue): RedirectResponse
    {
        $venue->update($this->venueData($request, $venue));

        return back()->with('success', 'Venue berhasil diperbarui.');
    }

    public function storeAgenda(Request $request): RedirectResponse
    {
        $data = $this->agendaData($request);
        $this->ensureNoConflict($data);
        EventAgenda::query()->create($data + ['day' => now()->parse($data['date'])->locale('id')->isoFormat('dddd')]);

        return back()->with('success', 'Agenda berhasil ditambahkan.');
    }

    public function updateAgenda(Request $request, EventAgenda $agenda): RedirectResponse
    {
        $data = $this->agendaData($request);
        $this->ensureNoConflict($data, $agenda);
        $agenda->update($data + ['day' => now()->parse($data['date'])->locale('id')->isoFormat('dddd')]);

        return back()->with('success', 'Agenda berhasil diperbarui.');
    }

    public function publish(EventAgenda $agenda): RedirectResponse
    {
        $agenda->update(['published_at' => now()]);

        return back()->with('success', 'Agenda berhasil dipublikasikan.');
    }

    private function venueData(Request $request, ?Venue $venue = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('venues')->ignore($venue)], 'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'], 'city' => ['nullable', 'string', 'max:100'], 'facilities' => ['nullable', 'string'],
            'map_url' => ['nullable', 'url', 'max:255'], 'contact_name' => ['nullable', 'string', 'max:100'],
            'contact_phone' => ['nullable', 'string', 'max:30'], 'is_active' => ['required', 'boolean'],
        ]);
    }

    private function agendaData(Request $request): array
    {
        return $request->validate([
            'date' => ['required', 'date'], 'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['sport', 'exhibition', 'official'])], 'sport_id' => ['nullable', 'exists:sports,id'],
            'tournament_event_id' => ['nullable', 'exists:tournament_events,id'],
            'venue_id' => ['required', Rule::exists('venues', 'id')->where('is_active', true)],
            'start_time' => ['required', 'date_format:H:i'], 'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'time_note' => ['nullable', 'string', 'max:100'], 'description' => ['nullable', 'string'],
        ]);
    }

    private function ensureNoConflict(array $data, ?EventAgenda $agenda = null): void
    {
        $conflict = EventAgenda::query()->where('venue_id', $data['venue_id'])->whereDate('date', $data['date'])
            ->when($agenda, fn ($query) => $query->whereKeyNot($agenda->id))
            ->where('start_time', '<', $data['end_time'])->where('end_time', '>', $data['start_time'])->exists();
        if ($conflict) {
            throw ValidationException::withMessages(['start_time' => 'Venue sudah dipakai pada rentang waktu tersebut.']);
        }
    }
}
