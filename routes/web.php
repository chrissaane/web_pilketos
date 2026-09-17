<?php

use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CandidateController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\Settings\AdminSettingsController;
use App\Http\Controllers\Admin\VoterController;
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
Route::get('/hasil/data', [HomeController::class, 'publicResultsData'])->name('results.data');
Route::middleware(['auth', 'active', 'role:admin'])->group(function () {
    Route::get('/sipintu-data', [HomeController::class, 'sipintuData'])->name('sipintu.data');
    Route::post('/sipintu-sync', [HomeController::class, 'syncSipintuData'])->name('sipintu.sync');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.post');
    Route::get('/oauth/redirect', [SiPintuOauthController::class, 'redirect'])->name('sipintu.oauth.redirect');
    Route::get('/oauth/callback', [SiPintuOauthController::class, 'callback'])->name('sipintu.oauth.callback');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/history', [DashboardController::class, 'history'])->name('history');
    Route::post('/vote', [DashboardController::class, 'store'])->name('vote.store');

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('dashboard');
        // Admin resource routes for management
        Route::resource('cards', BannerController::class);
        Route::post('cards/{card}/publish', [BannerController::class, 'publish'])
            ->name('cards.publish');
        Route::post('candidates/{candidate}/photos', [CandidateController::class, 'destroyPhoto'])
            ->name('candidates.photos.destroy');
        Route::resource('candidates', CandidateController::class);
        Route::resource('schedules', ScheduleController::class)->except(['show']);
        Route::get('voters', [VoterController::class, 'index'])->name('voters.index');
        Route::delete('voters/{identity}', [VoterController::class, 'destroy'])->name('voters.destroy');
        Route::delete('voters/user/{user}', [VoterController::class, 'destroyById'])->name('voters.destroy_by_id');
        Route::put('voters/{identity}/password', [VoterController::class, 'updatePassword'])->name('voters.password.update');
        Route::post('voters/{identity}/regenerate-password', [VoterController::class, 'regeneratePassword'])->name('voters.regenerate_password');
        Route::post('voters/regenerate-passwords', [VoterController::class, 'regeneratePasswordsForFilter'])->name('voters.regenerate_passwords');
        Route::post('voters/sync', [VoterController::class, 'syncFromSiPintu'])->name('voters.sync');
        Route::get('voters/print', [VoterController::class, 'print'])->name('voters.print');
        Route::get('voters/export', [VoterController::class, 'export'])->name('voters.export');
        Route::post('voters/promote', [VoterController::class, 'promote'])->name('voters.promote');
        Route::post('voters/archive-xii', [VoterController::class, 'archiveXii'])->name('voters.archive_xii');
        Route::get('statistics', [DashboardController::class, 'adminStatistics'])->name('statistics.index');
        Route::get('statistics/data', [DashboardController::class, 'adminStatisticsData'])->name('statistics.data');
        Route::post('statistics/results-visibility', [AdminSettingsController::class, 'storeResultsVisibility'])->name('statistics.results_visibility');
        Route::get('settings', [AdminSettingsController::class, 'index'])->name('settings.index');
        Route::post('settings', [AdminSettingsController::class, 'store'])->name('settings.store');
        Route::post('settings/guide', [AdminSettingsController::class, 'storeGuide'])->name('settings.guide.store');
        Route::delete('settings/guide/{guideItem}', [AdminSettingsController::class, 'destroyGuide'])->name('settings.guide.destroy');
        Route::post('settings/about', [AdminSettingsController::class, 'storeAbout'])->name('settings.about.store');

        // Import / Export stubs
        Route::get('import', [ImportController::class, 'index'])->name('import.index');
        Route::post('import', [ImportController::class, 'store'])->name('import.store');
        Route::post('voters/generate-tokens', [VoterController::class, 'generateTokens'])->name('voters.generate_tokens');
        Route::get('export', [ExportController::class, 'index'])->name('export.index');
        Route::post('export', [ExportController::class, 'run'])->name('export.run');
    });

    Route::middleware('role:guru,karyawan')->prefix('guru')->name('guru.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'guruDashboard'])->name('dashboard');
    });

    Route::middleware('role:siswa')->prefix('siswa')->name('siswa.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'siswaDashboard'])->name('dashboard');
    });
});

require __DIR__.'/settings.php';
