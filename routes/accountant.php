<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountantController;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    return response()->view('accountant.errors.404', [], 404);
});

Route::view('/accountant/login', 'accountant.login')->name('accountant.login');


Route::prefix('accountant')->middleware('accountant.guest')->group(function () {

    Route::view('/register', 'accountant.register')->name('accountant.register');

    Route::view('/forgot-password', 'accountant.forgot-password')
        ->name('accountant.forgot-password');

    Route::post('/login', [AccountantController::class, 'authenticate'])->name('accountant.auth');

    
});

/*
|--------------------------------------------------------------------------
| AUTH OWNER ROUTES (NO SUBSCRIPTION REQUIRED)
|--------------------------------------------------------------------------
*/

Route::prefix('accountant')->middleware('auth:accountant')->group(function () {

    // Subscription Page
    Route::get('/subscription', [AccountantController::class, 'subscription'])->name('accountant.subscription');
    Route::get('/subscribe', [AccountantController::class, 'subscribe'])->name('accountant.subscribe');

    // Logout
    Route::get('/logout', [AccountantController::class, 'signOut'])->name('accountant.signOut');
});

/*
|--------------------------------------------------------------------------
| AUTH OWNER + SUBSCRIPTION REQUIRED
|--------------------------------------------------------------------------
*/

Route::prefix('accountant')->middleware(['auth:accountant', 'accountant.subscription'])->group(function () {

        Route::get('/tally/dashboard', [AccountantController::class, 'dashboard'])->name('accountant.tally.dashboard');
        Route::post('/tally/sync-all', [AccountantController::class, 'syncAll'])->name('accountant.tally.sync-all');
        
        Route::get(
            '/tally/company/{company}/ledgers',
            [AccountantController::class, 'companyLedgers']
        )->name('accountant.tally.company.ledgers');


        Route::get(
            '/tally/company/{company}/ledgers-assign',
            [AccountantController::class, 'asignLedgers']
        )->name('accountant.tally.company.ledgers-assign');


         Route::get(
            '/tally/company/{company}/ledger/{ledger}/vouchers/{under?}',
            [AccountantController::class, 'ledgerVouchers']
        )->name('accountant.tally.ledger.vouchers');


        Route::get(
            '/tally/company/{company}/ledger/{ledger}/followup/{under?}',
            [AccountantController::class, 'ledgerFollowUp']
        )->name('accountant.tally.ledger.followup');



        Route::get(
            '/tally/company/{company}/followup/hub/', 
            [AccountantController::class, 'followUpsHub']
        )->name('accountant.tally.followup.hub');


        // Followup Actions
        Route::get( 
            '/tally/company/{company}/followup-action/telecaller', 
            [AccountantController::class, 'telecaller']
        )->name('accountant.tally.company.followup-action.telecaller');

        Route::get(
            '/tally/company/{company}/followup-action/call', 
            [AccountantController::class, 'call']
        )->name('accountant.tally.company.followup-action.call');

        Route::get(
            '/tally/company/{company}/followup-action/whatsapp-message', 
            [AccountantController::class, 'whatsappMessage']
        )->name('accountant.tally.company.followup-action.whatsapp-message');

        Route::get(
            '/tally/company/{company}/followup-action/physical-visit', 
            [AccountantController::class, 'physicalVisit']
        )->name('accountant.tally.company.followup-action.physical-visit');
        
        Route::get(
            '/tally/company/{company}/followup-action/escalation', 
            [AccountantController::class, 'escalation']
        )->name('accountant.tally.company.followup-action.escalation');

        Route::get(
            '/tally/company/{company}/followup-history', 
            [AccountantController::class, 'followupHistory']
        )->name('accountant.tally.company.followup_history');

        Route::post('/accountant/assign-collectors', [AccountantController::class, 'assignLedgers'])->name('accountant.assign.collectors');





        Route::get('/manual/dashboard', [AccountantController::class, 'manualDashboard'])->name('accountant.manual.dashboard');

         Route::get(
            '/manual/followup/hub/', 
            [AccountantController::class, 'manualFollowUpsHub']
        )->name('accountant.manual.followup.hub');

        Route::get( 
            '/manual/followup-action/telecaller', 
            [AccountantController::class, 'manualTelecaller']
        )->name('accountant.manual.followup-action.telecaller');

        Route::get(
            '/manual/followup-action/call', 
            [AccountantController::class, 'manualCall']
        )->name('accountant.manual.followup-action.call');

        Route::get(
            '/manual/followup-action/whatsapp-message', 
            [AccountantController::class, 'manualWhatsappMessage']
        )->name('accountant.manual.followup-action.whatsapp-message');

        Route::get(
            '/manual/followup-action/physical-visit', 
            [AccountantController::class, 'manualPhysicalVisit']
        )->name('accountant.manual.followup-action.physical-visit');
        
        Route::get(
            '/manual/followup-action/escalation', 
            [AccountantController::class, 'manualEscalation']
        )->name('accountant.manual.followup-action.escalation');


         Route::get(
            '/manual/followup-history', 
            [AccountantController::class, 'manualFollowupHistory']
        )->name('accountant.manual.followup_history');
    });








