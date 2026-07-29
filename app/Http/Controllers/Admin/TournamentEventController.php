<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sport;
use App\Models\SportCategory;
use App\Models\SportRegulation;
use App\Models\TournamentEvent;
use App\Models\TournamentMatch;
use App\Models\EntryTeam;
use App\Models\EntryMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class TournamentEventController extends Controller
{
    public function index(Request $request): Response
    {
        $perPage = min($request->integer('per_page', 10), 100);
        $search = trim((string) $request->query('search'));
        $status = (string) $request->query('status');

        return Inertia::render('Admin/Events', [
            'events' => TournamentEvent::query()
                ->addSelect([
                    'teams_count' => EntryTeam::query()->selectRaw('count(*)')->join('event_entries', 'entry_teams.event_entry_id', '=', 'event_entries.id')->whereColumn('event_entries.tournament_event_id', 'tournament_events.id')->whereNull('entry_teams.cancelled_at'),
                    'verified_teams_count' => EntryTeam::query()->selectRaw('count(*)')->join('event_entries', 'entry_teams.event_entry_id', '=', 'event_entries.id')->whereColumn('event_entries.tournament_event_id', 'tournament_events.id')->whereNull('entry_teams.cancelled_at')->whereRaw("COALESCE(entry_teams.verification_status_override, event_entries.verification_status) = 'verified'"),
                    'players_count' => EntryMember::query()->selectRaw('count(*)')->join('entry_teams', 'entry_members.entry_team_id', '=', 'entry_teams.id')->join('event_entries', 'entry_teams.event_entry_id', '=', 'event_entries.id')->whereColumn('event_entries.tournament_event_id', 'tournament_events.id')->whereNull('entry_teams.cancelled_at')->where('entry_members.member_type', 'player'),
                    'verified_players_count' => EntryMember::query()->selectRaw('count(*)')->join('entry_teams', 'entry_members.entry_team_id', '=', 'entry_teams.id')->join('event_entries', 'entry_teams.event_entry_id', '=', 'event_entries.id')->whereColumn('event_entries.tournament_event_id', 'tournament_events.id')->whereNull('entry_teams.cancelled_at')->where('entry_members.member_type', 'player')->where('entry_members.verification_status', 'verified'),
                ])
                ->with(['sport:id,name,default_format,default_max_officials_per_pd,official_roles,allow_member_cross_category,max_categories_per_member,official_can_compete', 'sport.regulations' => fn ($query) => $query->where('is_active', true)->latest('version'), 'category:id,sport_id,name,competition_type,scoring_type,min_members,max_members,default_max_teams_per_pd,is_active', 'regulation:id,sport_id,version,title'])
                ->withCount(['entries', 'matches'])
                ->when($status, fn ($query) => $query->where('status', $status))
                ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                    $query->whereLike('name', "%{$search}%", caseSensitive: false)
                        ->orWhereLike('code', "%{$search}%", caseSensitive: false)
                        ->orWhereHas('sport', fn ($query) => $query->whereLike('name', "%{$search}%", caseSensitive: false))
                        ->orWhereHas('category', fn ($query) => $query->whereLike('name', "%{$search}%", caseSensitive: false));
                }))
                ->orderBy('name')
                ->paginate($perPage)
                ->withQueryString()
                ->through(fn (TournamentEvent $event) => [
                    'code' => $event->code,
                    'name' => $event->name,
                    'sport_id' => $event->sport_id,
                    'category_id' => $event->sport_category_id,
                    'sport' => $event->sport?->name,
                    'default_format' => $event->sport?->default_format,
                    'category' => $event->category?->name,
                    'competition_type' => $event->category?->competition_type,
                    'min_members' => $event->category?->min_members,
                    'max_members' => $event->category?->max_members,
                    'format' => $event->format,
                    'uses_bracket' => $event->usesBracket(),
                    'status' => $event->status,
                    'published' => (bool) $event->registration_published_at,
                    'open_at' => $event->registration_open_at?->format('Y-m-d\TH:i'),
                    'close_at' => $event->registration_close_at?->format('Y-m-d\TH:i'),
                    'entries_count' => $event->entries_count,
                    'matches_count' => $event->matches_count,
                    'teams_count' => (int) $event->teams_count,
                    'verified_teams_count' => (int) $event->verified_teams_count,
                    'players_count' => (int) $event->players_count,
                    'verified_players_count' => (int) $event->verified_players_count,
                    'verification_complete' => $event->teams_count > 0 && $event->teams_count === $event->verified_teams_count && $event->players_count > 0 && $event->players_count === $event->verified_players_count,
                    'bracket_size' => $event->bracket_size,
                    'regulation_id' => $event->sport_regulation_id,
                    'regulation' => $event->regulation ? 'v'.$event->regulation->version.' · '.$event->regulation->title : null,
                    'regulations' => $event->sport?->regulations->map(fn ($regulation) => ['id' => $regulation->id, 'label' => 'v'.$regulation->version.' · '.$regulation->title, 'content' => $regulation->content, 'document_url' => $regulation->document_url])->values(),
                    'rules' => $event->registration_rules,
                ]),
            'audits' => DB::table('event_publication_audits')->join('tournament_events', 'event_publication_audits.tournament_event_id', '=', 'tournament_events.id')->latest('event_publication_audits.created_at')->limit(20)->get(['event_publication_audits.id', 'tournament_events.name as event', 'event_publication_audits.action', 'event_publication_audits.created_at']),
            'filters' => ['search' => $search, 'status' => $status, 'per_page' => $perPage],
            'sportFormats' => Sport::FORMAT_LABELS,
            'sports' => Sport::query()->where('is_active', true)
                ->with(['categories' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order'), 'regulations' => fn ($query) => $query->where('is_active', true)->latest('version')])
                ->orderBy('name')->get(['id', 'name', 'default_format', 'default_max_officials_per_pd', 'official_roles', 'allow_member_cross_category', 'max_categories_per_member', 'official_can_compete']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        TournamentEvent::query()->create($this->eventData($request) + [
            'public_id' => (string) Str::uuid(),
            'status' => 'registration_draft',
        ]);

        return back()->with('success', 'Data lomba berhasil ditambahkan.');
    }

    public function update(Request $request, TournamentEvent $event): RedirectResponse
    {
        abort_if($event->registration_published_at || $event->entries()->exists(), 422, 'Data lomba hanya dapat diubah saat draft dan belum memiliki peserta.');
        $event->update($this->eventData($request, $event));

        return back()->with('success', 'Data lomba berhasil diperbarui.');
    }

    public function destroy(TournamentEvent $event): RedirectResponse
    {
        abort_if($event->registration_published_at || $event->entries()->exists() || $event->matches()->exists() || DB::table('event_agendas')->where('tournament_event_id', $event->id)->exists(), 422, 'Data lomba hanya dapat dihapus saat draft dan belum dipakai.');
        $event->delete();

        return back()->with('success', 'Data lomba berhasil dihapus.');
    }

    public function publish(Request $request, TournamentEvent $event): RedirectResponse
    {
        $data = $request->validate([
            'registration_open_at' => ['required', 'date'],
            'registration_close_at' => ['required', 'date', 'after:registration_open_at'],
        ]);

        $event->loadMissing(['category', 'sport']);
        $category = $event->category;
        $regulation = SportRegulation::query()->where('sport_id', $event->sport_id)->where('is_active', true)->latest('version')->first();

        if (! $category || ! $category->is_active || $category->sport_id !== $event->sport_id) {
            throw ValidationException::withMessages(['event' => 'Kategori aktif dan cabor kompetisi harus sesuai sebelum dipublikasikan.']);
        }

        if (! $regulation) {
            throw ValidationException::withMessages(['event' => 'Cabor belum memiliki regulasi aktif. Lengkapi Master Regulasi sebelum publikasi.']);
        }

        if ($event->registration_published_at && $event->entries()->exists()) {
            throw ValidationException::withMessages(['event' => 'Regulasi tidak dapat dipublikasikan ulang setelah pendaftaran masuk.']);
        }

        DB::transaction(function () use ($event, $category, $regulation, $data, $request) {
            $before = $this->publicationState($event);
            $event->update([
                'status' => 'registration_open',
                'format' => $event->sport->default_format,
                'sport_regulation_id' => $regulation->id,
                'registration_rules' => [
                    'category_name' => $category->name,
                    'competition_type' => $category->competition_type,
                    'scoring_type' => $category->scoring_type,
                    'format' => $event->sport->default_format,
                    'uses_bracket' => in_array($event->sport->default_format, Sport::BRACKET_FORMATS, true),
                    'min_members' => $category->min_members,
                    'max_members' => $category->max_members,
                    'max_teams_per_pd' => $category->default_max_teams_per_pd,
                    'min_members_per_team' => $category->min_members,
                    'max_members_per_team' => $category->max_members,
                    'max_officials_per_pd' => $event->sport->default_max_officials_per_pd,
                    'official_roles' => $event->sport->official_roles ?? [],
                    'allow_member_cross_category' => $event->sport->allow_member_cross_category,
                    'max_categories_per_member' => $event->sport->max_categories_per_member,
                    'official_can_compete' => $event->sport->official_can_compete,
                    'avoid_same_pd_in_round' => true,
                    'regulation_id' => $regulation->id,
                    'regulation_version' => $regulation->version,
                    'regulation_title' => $regulation->title,
                ],
                'registration_published_at' => now(),
                'registration_published_by' => $request->user()->id,
                'registration_open_at' => $data['registration_open_at'],
                'registration_close_at' => $data['registration_close_at'],
                'seed_locked_at' => null,
            ]);
            $this->audit($event, $before['published_at'] ? 'republished' : 'published', $before, $this->publicationState($event), $request);
        });

        return back()->with('success', 'Registrasi kompetisi dipublikasikan.');
    }

    public function close(Request $request, TournamentEvent $event): RedirectResponse
    {
        abort_unless($event->registration_published_at, 422, 'Kompetisi belum dipublikasikan.');
        $before = $this->publicationState($event);
        $event->update(['status' => 'registration_closed']);
        $this->audit($event, 'closed', $before, $this->publicationState($event), $request);

        return back()->with('success', 'Registrasi kompetisi ditutup.');
    }

    public function lockParticipants(Request $request, TournamentEvent $event): RedirectResponse
    {
        abort_unless($event->registration_published_at && $event->status === 'registration_closed', 422, 'Peserta hanya dapat dikunci setelah pendaftaran ditutup.');

        $teams = EntryTeam::query()->with('eventEntry')->whereHas('eventEntry', fn ($query) => $query->where('tournament_event_id', $event->id))->whereNull('cancelled_at')->orderBy('id')->get();
        $verified = $teams->filter(fn ($team) => $team->effectiveStatus() === 'verified');
        $players = EntryMember::query()->whereIn('entry_team_id', $teams->pluck('id'))->where('member_type', 'player');
        $playersCount = (clone $players)->count();
        $verifiedPlayersCount = (clone $players)->where('verification_status', 'verified')->count();

        abort_if($teams->isEmpty(), 422, 'Minimal satu team diperlukan untuk mengunci peserta.');
        abort_if($event->usesBracket() && $teams->count() < 2, 422, 'Minimal dua team diperlukan untuk format bracket.');
        abort_if($teams->count() !== $verified->count(), 422, $teams->count() - $verified->count().' team belum diverifikasi. Selesaikan verifikasi sebelum mengunci peserta.');
        abort_if($playersCount === 0 || $playersCount !== $verifiedPlayersCount, 422, $playersCount - $verifiedPlayersCount.' pemain belum diverifikasi. Selesaikan verifikasi pemain sebelum mengunci peserta.');

        DB::transaction(function () use ($event, $verified, $request) {
            $before = $this->publicationState($event);
            $usesBracket = $event->usesBracket();
            $verified->values()->each(fn ($team, $index) => $team->update(['seed_no' => $usesBracket ? $index + 1 : null]));
            $event->update([
                'status' => $usesBracket ? 'bracket_locked' : 'participants_locked',
                'bracket_size' => $usesBracket ? 2 ** (int) ceil(log($verified->count(), 2)) : null,
                'seed_locked_at' => now(),
            ]);
            if ($usesBracket && in_array($verified->count(), [2, 4], true)) {
                $this->createKnockoutMatches($event, $verified->values());
            }
            $this->audit($event, $usesBracket ? 'bracket_locked' : 'participants_locked', $before, $this->publicationState($event), $request);
        });

        if (! $event->usesBracket()) {
            return back()->with('success', 'Peserta dikunci. Data siap diproses sesuai format kompetisi.');
        }

        return back()->with('success', in_array($verified->count(), [2, 4], true)
            ? 'Bracket dikunci dan pertandingan berhasil dibuat.'
            : 'Bracket dikunci. Seluruh team terverifikasi telah mendapat nomor seed.');
    }

    public function lockBracket(Request $request, TournamentEvent $event): RedirectResponse
    {
        return $this->lockParticipants($request, $event);
    }

    public function generateMatches(Request $request, TournamentEvent $event): RedirectResponse
    {
        abort_unless($event->status === 'bracket_locked', 422, 'Pertandingan hanya dapat dibuat setelah bracket dikunci.');
        abort_unless($event->usesBracket(), 422, 'Format kompetisi ini tidak menggunakan bracket.');
        abort_if($event->matches()->exists(), 422, 'Pertandingan untuk Data Lomba ini sudah tersedia.');
        $teams = $event->eligibleTeams()->with('eventEntry:id')->orderBy('seed_no')->get();
        abort_unless(in_array($teams->count(), [2, 4], true), 422, 'Generator pertandingan saat ini mendukung bracket dua atau empat team.');

        DB::transaction(function () use ($event, $teams, $request) {
            $this->createKnockoutMatches($event, $teams);
            $this->audit($event, 'matches_generated', $this->publicationState($event), $this->publicationState($event), $request);
        });

        return back()->with('success', 'Pertandingan berhasil dibuat dan siap dijadwalkan.');
    }

    public function unpublish(Request $request, TournamentEvent $event): RedirectResponse
    {
        abort_unless($event->registration_published_at, 422, 'Kompetisi belum dipublikasikan.');
        abort_if($event->entries()->exists(), 422, 'Publikasi tidak dapat ditarik setelah pendaftaran masuk.');

        $before = $this->publicationState($event);
        $event->update(['status' => 'registration_draft', 'registration_published_at' => null, 'registration_published_by' => null, 'registration_open_at' => null, 'registration_close_at' => null]);
        $this->audit($event, 'unpublished', $before, $this->publicationState($event), $request);

        return back()->with('success', 'Publikasi kompetisi ditarik.');
    }

    private function publicationState(TournamentEvent $event): array
    {
        return ['status' => $event->status, 'sport_regulation_id' => $event->sport_regulation_id, 'rules' => $event->registration_rules, 'published_at' => $event->registration_published_at?->toISOString(), 'open_at' => $event->registration_open_at?->toISOString(), 'close_at' => $event->registration_close_at?->toISOString()];
    }

    private function createKnockoutMatches(TournamentEvent $event, $teams): void
    {
        if ($teams->count() === 2) {
            $this->createMatch($event, 'final-01', 1, 'Final', 1, $teams[0], $teams[1]);

            return;
        }

        $final = $this->createMatch($event, 'final-01', 2, 'Final', 1);
        $this->createMatch($event, 'semifinal-01', 1, 'Semifinal', 1, $teams[0], $teams[3], $final->id, 'A');
        $this->createMatch($event, 'semifinal-02', 1, 'Semifinal', 2, $teams[1], $teams[2], $final->id, 'B');
    }

    private function createMatch(TournamentEvent $event, string $suffix, int $round, string $roundName, int $slot, $teamA = null, $teamB = null, ?int $nextMatchId = null, ?string $nextSlot = null): TournamentMatch
    {
        return TournamentMatch::query()->create([
            'public_id' => Str::uuid(),
            'tournament_event_id' => $event->id,
            'code' => $event->code.'-'.$suffix,
            'round_no' => $round,
            'round_name' => $roundName,
            'side' => $slot === 1 ? 'left' : 'right',
            'slot_no' => $slot,
            'entry_a_id' => $teamA?->event_entry_id,
            'entry_b_id' => $teamB?->event_entry_id,
            'team_a_id' => $teamA?->id,
            'team_b_id' => $teamB?->id,
            'next_match_id' => $nextMatchId,
            'next_slot' => $nextSlot,
            'status' => 'scheduled',
        ]);
    }

    private function eventData(Request $request, ?TournamentEvent $event = null): array
    {
        $data = $request->validate([
            'sport_id' => ['required', Rule::exists('sports', 'id')->where('is_active', true)],
            'sport_category_id' => ['required', Rule::exists('sport_categories', 'id')->where('is_active', true)],
        ]);
        $sport = Sport::query()->findOrFail($data['sport_id']);
        $category = SportCategory::query()->findOrFail($data['sport_category_id']);
        $regulation = SportRegulation::query()->where('sport_id', $sport->id)->where('is_active', true)->latest('version')->first();

        if (TournamentEvent::query()->where('sport_id', $data['sport_id'])->where('sport_category_id', $data['sport_category_id'])->when($event, fn ($query) => $query->whereKeyNot($event->id))->exists()) {
            throw ValidationException::withMessages(['sport_category_id' => 'Kategori ini sudah memiliki Data Lomba. Ubah data yang sudah ada.']);
        }

        if ($category->sport_id !== (int) $data['sport_id']) {
            throw ValidationException::withMessages(['sport_id' => 'Cabor dan kategori harus berasal dari master yang sama.']);
        }

        if (! $regulation) {
            throw ValidationException::withMessages(['sport_id' => 'Cabor belum memiliki regulasi aktif. Lengkapi Master Regulasi terlebih dahulu.']);
        }

        $data += [
            'sport_regulation_id' => $regulation->id,
            'code' => Str::slug($sport->code.'-'.$category->code),
            'name' => $sport->name.' - '.$category->name,
            'format' => $sport->default_format,
        ];
        $data['registration_rules'] = [
            'max_teams_per_pd' => $category->default_max_teams_per_pd,
            'min_members_per_team' => $category->min_members,
            'max_members_per_team' => $category->max_members,
            'max_officials_per_pd' => $sport->default_max_officials_per_pd,
            'official_roles' => $sport->official_roles ?? [],
            'allow_member_cross_category' => $sport->allow_member_cross_category,
            'max_categories_per_member' => $sport->max_categories_per_member,
            'official_can_compete' => $sport->official_can_compete,
            'uses_bracket' => in_array($sport->default_format, Sport::BRACKET_FORMATS, true),
        ];

        return $data;
    }

    private function audit(TournamentEvent $event, string $action, array $before, array $after, Request $request): void
    {
        DB::table('event_publication_audits')->insert(['tournament_event_id' => $event->id, 'action' => $action, 'before_json' => json_encode($before), 'after_json' => json_encode($after), 'user_id' => $request->user()->id, 'created_at' => now(), 'updated_at' => now()]);
    }
}
