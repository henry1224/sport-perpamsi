<?php

use App\Http\Controllers\Admin\ScoreController;
use App\Http\Controllers\Public\PublicPageController;
use Illuminate\Support\Facades\Route;

Route::controller(PublicPageController::class)->group(function () {
    Route::get('/', 'home')->name('home');
    Route::get('/agenda', 'agenda')->name('agenda');
    Route::get('/hasil', 'hasil')->name('hasil');
    Route::get('/cabor', 'cabor')->name('cabor');
    Route::get('/bracket', 'bracket')->name('bracket');
    Route::get('/ranking', 'ranking')->name('ranking');
    Route::get('/venue', 'venue')->name('venue');
    Route::get('/peserta', 'peserta')->name('peserta');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/skor', [ScoreController::class, 'index'])->name('scores.index');
    Route::post('/skor', [ScoreController::class, 'store'])->name('scores.store');
});
