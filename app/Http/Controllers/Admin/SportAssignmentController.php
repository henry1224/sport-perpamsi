<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SportAssignment;
use App\Models\TournamentMatch;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SportAssignmentController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search'));
        $status = $request->query('status');
        $perPage = min(max((int) $request->query('per_page', 10), 10), 100);

        $assignments = SportAssignment::query()
            ->with(['user:id,name,email,account_status', 'sport:id,name', 'venue:id,name,city'])
            ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                $query->whereHas('user', fn ($query) => $query->whereLike('name', "%{$search}%", caseSensitive: false)->orWhereLike('email', "%{$search}%", caseSensitive: false))
                    ->orWhereHas('sport', fn ($query) => $query->whereLike('name', "%{$search}%", caseSensitive: false))
                    ->orWhereHas('venue', fn ($query) => $query->whereLike('name', "%{$search}%", caseSensitive: false));
            }))
            ->when($status !== null && $status !== '', fn ($query) => $query->where('is_active', $status === 'active'))
            ->latest('assigned_at')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn ($assignment) => [
                'id' => $assignment->id,
                'name' => $assignment->user->name,
                'email' => $assignment->user->email,
                'sport' => $assignment->sport->name,
                'venue' => $assignment->venue->name,
                'city' => $assignment->venue->city,
                'role' => $assignment->assignment_role,
                'is_active' => $assignment->is_active,
                'assigned_at' => $assignment->assigned_at?->format('d M Y H:i'),
            ]);

        $assignmentScopes = TournamentMatch::query()
            ->with(['tournamentEvent:id,sport_id', 'tournamentEvent.sport:id,name', 'venue:id,name,city,is_active'])
            ->whereNotNull('event_agenda_id')->whereNotNull('venue_id')->whereNotNull('scheduled_at')
            ->get(['id', 'tournament_event_id', 'venue_id'])
            ->filter(fn ($match) => $match->venue?->is_active && $match->tournamentEvent?->sport)
            ->unique(fn ($match) => $match->tournamentEvent->sport_id.'-'.$match->venue_id)
            ->map(fn ($match) => [
                'sport_id' => $match->tournamentEvent->sport_id,
                'sport' => $match->tournamentEvent->sport->name,
                'venue_id' => $match->venue_id,
                'venue' => $match->venue->name,
                'city' => $match->venue->city,
            ])->values();

        return Inertia::render('Admin/SportAssignments', [
            'assignments' => $assignments,
            'filters' => ['search' => $search, 'status' => $status, 'per_page' => $perPage],
            'staff' => User::query()->whereIn('role', ['scorekeeper', 'sport_coordinator'])->where('account_status', 'verified')->orderBy('name')->get(['id', 'name', 'email', 'role']),
            'assignmentScopes' => $assignmentScopes,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', Rule::exists('users', 'id')->where(fn ($query) => $query->whereIn('role', ['scorekeeper', 'sport_coordinator'])->where('account_status', 'verified'))],
            'sport_id' => ['required', Rule::exists('sports', 'id')->where('is_active', true)],
            'venue_id' => ['required', Rule::exists('venues', 'id')->where('is_active', true)],
        ]);
        abort_unless(TournamentMatch::query()->whereNotNull('event_agenda_id')->whereNotNull('scheduled_at')
            ->where('venue_id', $data['venue_id'])
            ->whereHas('tournamentEvent', fn ($query) => $query->where('sport_id', $data['sport_id']))->exists(), 422,
            'Assignment hanya dapat dibuat untuk pasangan cabor dan venue yang sudah memiliki jadwal pertandingan.');
        $data['assignment_role'] = User::query()->findOrFail($data['user_id'])->role;

        DB::transaction(function () use ($data, $request) {
            $assignment = SportAssignment::query()->firstOrNew(collect($data)->only(['user_id', 'sport_id', 'venue_id', 'assignment_role'])->all());
            abort_if($assignment->exists && $assignment->is_active, 422, 'Panitia sudah aktif pada cabor dan venue tersebut.');
            $action = $assignment->exists ? 'reactivated' : 'assigned';
            $assignment->fill(['is_active' => true, 'assigned_by' => $request->user()->id, 'assigned_at' => now(), 'revoked_at' => null])->save();

            DB::table('sport_assignment_audits')->insert([
                'sport_assignment_id' => $assignment->id,
                'action' => $action,
                'user_id' => $request->user()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return back()->with('success', 'Panitia berhasil ditetapkan ke venue.');
    }

    public function revoke(Request $request, SportAssignment $assignment): RedirectResponse
    {
        abort_unless($assignment->is_active, 422, 'Assignment sudah tidak aktif.');

        DB::transaction(function () use ($assignment, $request) {
            $assignment->update(['is_active' => false, 'revoked_at' => now()]);
            DB::table('sport_assignment_audits')->insert([
                'sport_assignment_id' => $assignment->id,
                'action' => 'revoked',
                'user_id' => $request->user()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return back()->with('success', 'Assignment panitia dinonaktifkan.');
    }
}
