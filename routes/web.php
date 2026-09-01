<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SiPintuOauthController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/tentang', fn () => view('public.about'))->name('about');
Route::get('/panduan', fn () => view('public.guide'))->name('guide');
Route::get('/pemilihan/{election}', [HomeController::class, 'showElection'])->name('election.show');
Route::get('/kandidat/{candidate}', [HomeController::class, 'showCandidate'])->name('candidate.show');
Route::get('/hasil', [HomeController::class, 'results'])->name('results');
Route::get('/sipintu-data', [HomeController::class, 'sipintuData'])->name('sipintu.data');
Route::post('/sipintu-sync', [HomeController::class, 'syncSipintuData'])->name('sipintu.sync');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/oauth/redirect', [SiPintuOauthController::class, 'redirect'])->name('sipintu.oauth.redirect');
    Route::get('/oauth/callback', [SiPintuOauthController::class, 'callback'])->name('sipintu.oauth.callback');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/vote', [DashboardController::class, 'store'])->name('vote.store');

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('dashboard');
        // Admin resource routes for management
        Route::resource('cards', \App\Http\Controllers\Admin\BannerController::class);
        Route::post('candidates/{candidate}/photos', [\App\Http\Controllers\Admin\CandidateController::class, 'destroyPhoto'])
            ->name('candidates.photos.destroy');
        Route::resource('candidates', \App\Http\Controllers\Admin\CandidateController::class);
        Route::resource('schedules', \App\Http\Controllers\Admin\ScheduleController::class)->except(['show']);
        Route::get('voters', [\App\Http\Controllers\Admin\VoterController::class, 'index'])->name('voters.index');
        Route::post('voters/sync', [\App\Http\Controllers\Admin\VoterController::class, 'syncFromSiPintu'])->name('voters.sync');
        Route::get('voters/print', [\App\Http\Controllers\Admin\VoterController::class, 'print'])->name('voters.print');
        Route::get('voters/export', [\App\Http\Controllers\Admin\VoterController::class, 'export'])->name('voters.export');
        Route::post('voters/promote', [\App\Http\Controllers\Admin\VoterController::class, 'promote'])->name('voters.promote');
        Route::post('voters/archive-xii', [\App\Http\Controllers\Admin\VoterController::class, 'archiveXii'])->name('voters.archive_xii');
        Route::get('statistics', [DashboardController::class, 'adminStatistics'])->name('statistics.index');
        Route::get('settings', [\App\Http\Controllers\Admin\Settings\AdminSettingsController::class, 'index'])->name('settings.index');
        Route::post('settings', [\App\Http\Controllers\Admin\Settings\AdminSettingsController::class, 'store'])->name('settings.store');
        Route::post('settings/guide', [\App\Http\Controllers\Admin\Settings\AdminSettingsController::class, 'storeGuide'])->name('settings.guide.store');
        Route::delete('settings/guide/{guideItem}', [\App\Http\Controllers\Admin\Settings\AdminSettingsController::class, 'destroyGuide'])->name('settings.guide.destroy');
        Route::post('settings/about', [\App\Http\Controllers\Admin\Settings\AdminSettingsController::class, 'storeAbout'])->name('settings.about.store');

        // Import / Export stubs
        Route::get('import', [\App\Http\Controllers\Admin\ImportController::class, 'index'])->name('import.index');
        Route::post('import', [\App\Http\Controllers\Admin\ImportController::class, 'store'])->name('import.store');
        Route::post('voters/generate-tokens', [\App\Http\Controllers\Admin\VoterController::class, 'generateTokens'])->name('voters.generate_tokens');
        Route::get('export', [\App\Http\Controllers\Admin\ExportController::class, 'index'])->name('export.index');
        Route::post('export', [\App\Http\Controllers\Admin\ExportController::class, 'run'])->name('export.run');
    });

    Route::middleware('role:guru')->prefix('guru')->name('guru.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'guruDashboard'])->name('dashboard');
    });

    Route::middleware('role:siswa')->prefix('siswa')->name('siswa.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'siswaDashboard'])->name('dashboard');
    });
});

require __DIR__.'/settings.php';
