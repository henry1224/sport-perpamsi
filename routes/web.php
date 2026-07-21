<?php

use App\Http\Controllers\Admin\AdminEntryVerificationController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\ScoreController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Pd\PdDashboardController;
use App\Http\Controllers\Pd\PdEntryController;
use App\Http\Controllers\Public\PublicPageController;
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
});
Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'pd.admin'])->prefix('pd')->name('pd.')->group(function () {
    Route::get('/dashboard', [PdDashboardController::class, 'index'])->name('dashboard');
    Route::get('/events/{event:code}', [PdEntryController::class, 'show'])->name('events.show');
    Route::post('/events/{event:code}/entries', [PdEntryController::class, 'store'])->name('events.entries.store');
    Route::delete('/entries/{entry}', [PdEntryController::class, 'destroy'])->name('entries.destroy');
});

Route::middleware(['auth', 'super.admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
    Route::get('/skor', [ScoreController::class, 'index'])->name('scores.index');
    Route::post('/skor', [ScoreController::class, 'store'])->name('scores.store');
    Route::get('/entries', [AdminEntryVerificationController::class, 'index'])->name('entries.index');
    Route::post('/entries/{entry}/verify', [AdminEntryVerificationController::class, 'verify'])->name('entries.verify');
    Route::post('/entries/{entry}/reject', [AdminEntryVerificationController::class, 'reject'])->name('entries.reject');
});
