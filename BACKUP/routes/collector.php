<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CollectorController;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    return response()->view('collector.errors.404', [], 404);
});

Route::view('/collector/login', 'collector.login')->name('collector.login');


Route::prefix('collector')->middleware('collector.guest')->group(function () {

    Route::view('/register', 'collector.register')->name('collector.register');

    Route::view('/forgot-password', 'collector.forgot-password')
        ->name('collector.forgot-password');

    Route::post('/login', [CollectorController::class, 'authenticate'])->name('collector.auth');

    
});

/*
|--------------------------------------------------------------------------
| AUTH OWNER ROUTES (NO SUBSCRIPTION REQUIRED)
|--------------------------------------------------------------------------
*/

Route::prefix('collector')->middleware('auth:collector')->group(function () {

    // Subscription Page
    Route::get('/subscription', [CollectorController::class, 'subscription'])->name('collector.subscription');
    Route::get('/subscribe', [CollectorController::class, 'subscribe'])->name('collector.subscribe');

    // Logout
    Route::get('/logout', [CollectorController::class, 'signOut'])->name('collector.signOut');
});

/*
|--------------------------------------------------------------------------
| AUTH OWNER + SUBSCRIPTION REQUIRED
|--------------------------------------------------------------------------
*/

Route::prefix('collector')->middleware(['auth:collector', 'collector.subscription'])->group(function () {

        Route::get('/tally/dashboard', [CollectorController::class, 'dashboard'])->name('collector.tally.dashboard');
        Route::post('/tally/sync-all', [CollectorController::class, 'syncAll'])->name('collector.tally.sync-all');
        
        Route::get(
            '/tally/company/{company}/ledgers',
            [CollectorController::class, 'companyLedgers']
        )->name('collector.tally.company.ledgers');


        Route::get(
            '/tally/company/{company}/ledgers-assign',
            [CollectorController::class, 'asignLedgers']
        )->name('collector.tally.company.ledgers-assign');


         Route::get(
            '/tally/company/{company}/ledger/{ledger}/vouchers/{under?}',
            [CollectorController::class, 'ledgerVouchers']
        )->name('collector.tally.ledger.vouchers');

        Route::get(
            '/collector/tally/ledger/{company}/{ledger}/{under}/{metric}',
            [CollectorController::class, 'ledgerMetric']
        )->name('collector.tally.ledger.metric');

        Route::get(
            '/collector/tally/ledger/{company}/{ledger}/{under}/pdf',
            [CollectorController::class, 'ledgerPdf']
        )->name('collector.tally.ledger.pdf');



        Route::get('/tally/ledger/detail', function () {
            return view('collector.tally.ledger-detail');
        })->name('collector.tally.ledger.detail');


        Route::get('/tally/ledger/collect', function () {
            return view('collector.tally.collect');
        })->name('collector.tally.ledger.collect');


        Route::get('/tally/ledger/track', function () {
            return view('collector.tally.track');
        })->name('collector.tally.ledger.track');


        Route::get('/tally/ledger/followup', function () {
            return view('collector.tally.followup');
        })->name('collector.tally.ledger.followup');

        Route::post('/collector/assign-collectors', [CollectorController::class, 'assignLedgers'])->name('collector.assign.collectors');


        Route::get('/manual/dashboard', [CollectorController::class, 'manualDashboard'])->name('collector.manual.dashboard');
    });








