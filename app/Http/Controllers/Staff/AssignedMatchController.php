<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\SportAssignment;
use App\Models\TournamentMatch;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AssignedMatchController extends Controller
{
    public function index(Request $request): Response
    {
        $scopes = SportAssignment::query()->where('user_id', $request->user()->id)->where('is_active', true)
            ->get(['sport_id', 'venue_id']);

        $matches = TournamentMatch::query()->with(['entryA:id,display_name', 'entryB:id,display_name', 'venue:id,name', 'tournamentEvent:id,name,sport_id'])
            ->whereNotNull('venue_id')->whereNotNull('scheduled_at')
            ->when($scopes->isEmpty(), fn ($query) => $query->whereRaw('1 = 0'))
            ->when($scopes->isNotEmpty(), fn ($query) => $query->where(function ($query) use ($scopes) {
                foreach ($scopes as $scope) {
                    $query->orWhere(fn ($query) => $query->where('venue_id', $scope->venue_id)
                        ->whereHas('tournamentEvent', fn ($query) => $query->where('sport_id', $scope->sport_id)));
                }
            }))->orderBy('scheduled_at')->get()->map(fn ($match) => [
                'id' => $match->id, 'code' => $match->code, 'event' => $match->tournamentEvent->name,
                'venue' => $match->venue->name, 'scheduled_at' => $match->scheduled_at->toISOString(),
                'team_a' => $match->entryA?->display_name ?? 'TBD', 'team_b' => $match->entryB?->display_name ?? 'TBD', 'status' => $match->status,
            ]);

        return Inertia::render('Staff/Matches', ['matches' => $matches]);
    }

    public function show(Request $request, TournamentMatch $match): Response
    {
        abort_unless($this->assigned($request, $match), 403);

        return Inertia::render('Staff/Match', ['match' => $match->load(['entryA:id,display_name', 'entryB:id,display_name', 'venue:id,name', 'tournamentEvent:id,name'])]);
    }

    private function assigned(Request $request, TournamentMatch $match): bool
    {
        return SportAssignment::query()->where('user_id', $request->user()->id)->where('is_active', true)
            ->where('venue_id', $match->venue_id)->where('sport_id', $match->tournamentEvent()->value('sport_id'))->exists();
    }
}
