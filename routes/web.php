<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminEntryVerificationController;
use App\Http\Controllers\Admin\CommitteeApplicationController;
use App\Http\Controllers\Admin\MasterDataController;
use App\Http\Controllers\Admin\ScoreController;
use App\Http\Controllers\Admin\SportAssignmentController;
use App\Http\Controllers\Admin\TournamentEventController;
use App\Http\Controllers\Admin\VenueAgendaController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\CommitteeRegistrationController;
use App\Http\Controllers\Pd\PdDashboardController;
use App\Http\Controllers\Pd\PdEntryController;
use App\Http\Controllers\Public\PublicPageController;
use App\Http\Controllers\Staff\AssignedMatchController;
use Illuminate\Support\Facades\Route;

Route::controller(PublicPageController::class)->group(function () {
    Route::get('/', 'home')->name('home');
    Route::get('/agenda', 'agenda')->name('agenda');
    Route::get('/seminar', 'seminar')->name('seminar');
    Route::get('/hasil', 'hasil')->name('hasil');
    Route::get('/cabor', 'cabor')->name('cabor');
    Route::get('/bracket', 'bracket')->name('bracket');
    Route::get('/ranking', 'ranking')->name('ranking');
    Route::get('/venue', 'venue')->name('venue');
    Route::get('/peserta', 'peserta')->name('peserta');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store']);
    Route::get('/register', [CommitteeRegistrationController::class, 'create'])->name('register');
    Route::post('/register', [CommitteeRegistrationController::class, 'store']);
});
Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');
Route::get('/registration-status', [CommitteeRegistrationController::class, 'status'])->middleware('auth')->name('registration.status');
Route::put('/registration-status', [CommitteeRegistrationController::class, 'update'])->middleware('auth')->name('registration.update');

Route::middleware(['auth', 'pd.admin'])->prefix('pd')->name('pd.')->group(function () {
    Route::get('/dashboard', [PdDashboardController::class, 'index'])->name('dashboard');
    Route::get('/events/{event:code}', [PdEntryController::class, 'show'])->name('events.show');
    Route::post('/events/{event:code}/entries', [PdEntryController::class, 'store'])->name('events.entries.store');
    Route::delete('/entries/{entry}', [PdEntryController::class, 'destroy'])->name('entries.destroy');
});

Route::middleware(['auth', 'committee.staff'])->prefix('panitia')->name('staff.')->group(function () {
    Route::get('/pertandingan', [AssignedMatchController::class, 'index'])->name('matches.index');
    Route::get('/pertandingan/{match}', [AssignedMatchController::class, 'show'])->name('matches.show');
});

Route::middleware(['auth', 'super.admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
    Route::get('/skor', [ScoreController::class, 'index'])->name('scores.index');
    Route::post('/skor', [ScoreController::class, 'store'])->name('scores.store');
    Route::get('/entries', [AdminEntryVerificationController::class, 'index'])->name('entries.index');
    Route::post('/entries/{entry}/verify', [AdminEntryVerificationController::class, 'verify'])->name('entries.verify');
    Route::post('/entries/{entry}/reject', [AdminEntryVerificationController::class, 'reject'])->name('entries.reject');
    Route::post('/entries/{entry}/revision', [AdminEntryVerificationController::class, 'revision'])->name('entries.revision');
    Route::post('/entry-teams/{team}/override', [AdminEntryVerificationController::class, 'overrideTeam'])->name('entry-teams.override');
    Route::delete('/entry-teams/{team}/override', [AdminEntryVerificationController::class, 'resetTeamOverride'])->name('entry-teams.override.reset');
    Route::get('/committee-applications', [CommitteeApplicationController::class, 'index'])->name('committee-applications.index');
    Route::post('/committee-applications/{application}/verify', [CommitteeApplicationController::class, 'verify'])->name('committee-applications.verify');
    Route::post('/committee-applications/{application}/revision', [CommitteeApplicationController::class, 'revision'])->name('committee-applications.revision');
    Route::post('/committee-applications/{application}/reject', [CommitteeApplicationController::class, 'reject'])->name('committee-applications.reject');
    Route::get('/events', [TournamentEventController::class, 'index'])->name('events.index');
    Route::put('/events/{event:code}/format', [TournamentEventController::class, 'updateFormat'])->name('events.format.update');
    Route::post('/events/{event:code}/publish', [TournamentEventController::class, 'publish'])->name('events.publish');
    Route::post('/events/{event:code}/close', [TournamentEventController::class, 'close'])->name('events.close');
    Route::post('/events/{event:code}/unpublish', [TournamentEventController::class, 'unpublish'])->name('events.unpublish');
    Route::get('/assignments', [SportAssignmentController::class, 'index'])->name('assignments.index');
    Route::post('/assignments/users', [SportAssignmentController::class, 'storeUser'])->name('assignments.users.store');
    Route::post('/assignments', [SportAssignmentController::class, 'store'])->name('assignments.store');
    Route::post('/assignments/{assignment}/revoke', [SportAssignmentController::class, 'revoke'])->name('assignments.revoke');
    Route::redirect('/venue-agenda', '/admin/venues');
    Route::get('/venues', [VenueAgendaController::class, 'venues'])->name('venues.index');
    Route::get('/agenda', [VenueAgendaController::class, 'agendas'])->name('agendas.index');
    Route::post('/venues', [VenueAgendaController::class, 'storeVenue'])->name('venues.store');
    Route::put('/venues/{venue}', [VenueAgendaController::class, 'updateVenue'])->name('venues.update');
    Route::delete('/venues/{venue}', [VenueAgendaController::class, 'destroyVenue'])->name('venues.destroy');
    Route::post('/agendas', [VenueAgendaController::class, 'storeAgenda'])->name('agendas.store');
    Route::put('/agendas/{agenda}', [VenueAgendaController::class, 'updateAgenda'])->name('agendas.update');
    Route::post('/agendas/{agenda}/publish', [VenueAgendaController::class, 'publish'])->name('agendas.publish');
    Route::post('/matches/{match}/schedule', [VenueAgendaController::class, 'scheduleMatch'])->name('matches.schedule');
    Route::get('/master-data', [MasterDataController::class, 'index'])->name('master-data.index');
    Route::post('/master-data/sports', [MasterDataController::class, 'storeSport'])->name('master-data.sports.store');
    Route::put('/master-data/sports/{sport}', [MasterDataController::class, 'updateSport'])->name('master-data.sports.update');
    Route::delete('/master-data/sports/{sport}', [MasterDataController::class, 'destroySport'])->name('master-data.sports.destroy');
    Route::post('/master-data/categories', [MasterDataController::class, 'storeCategory'])->name('master-data.categories.store');
    Route::put('/master-data/categories/{category}', [MasterDataController::class, 'updateCategory'])->name('master-data.categories.update');
    Route::delete('/master-data/categories/{category}', [MasterDataController::class, 'destroyCategory'])->name('master-data.categories.destroy');
    Route::post('/master-data/regulations', [MasterDataController::class, 'storeRegulation'])->name('master-data.regulations.store');
    Route::put('/master-data/regulations/{regulation}', [MasterDataController::class, 'updateRegulation'])->name('master-data.regulations.update');
    Route::delete('/master-data/regulations/{regulation}', [MasterDataController::class, 'destroyRegulation'])->name('master-data.regulations.destroy');
});
