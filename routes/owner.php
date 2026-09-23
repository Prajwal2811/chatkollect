<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OwnerController;


Route::fallback(function () {
    return response()->view('owner.errors.404', [], 404);
});

Route::view('/', 'owner.tally.login')->name('owner.login');


Route::prefix('owner')->middleware('owner.guest')->group(function () {

    Route::view('/register', 'owner.register')->name('owner.register');

    Route::view('/forgot-password', 'owner.forgot-password')
        ->name('owner.forgot-password');

    Route::post('/login', [OwnerController::class, 'authenticate'])
        ->name('owner.auth');

    Route::post('/register', [OwnerController::class, 'registerOwner'])
        ->name('owner.register.submit');
});


Route::prefix('owner')->middleware('auth:owner')->group(function () {

    // Subscription Page
    Route::get('/subscription', [OwnerController::class, 'subscription'])->name('owner.subscription');
    Route::get('/subscribe', [OwnerController::class, 'subscribe'])->name('owner.subscribe');

    // Logout
    Route::get('/logout', [OwnerController::class, 'signOut'])
        ->name('owner.signOut');
});



Route::prefix('owner')->middleware(['auth:owner', 'owner.subscription'])->group(function () {


        Route::post('/tally/connect', [OwnerController::class, 'connect'])->name('owner.tally.connect');

        // Dashboard
        Route::get('/dashboard', [OwnerController::class, 'dashboard'])->name('owner.dashboard');


        // Accountants 
        Route::get('/accountants-list', [OwnerController::class, 'accountants'])->name('owner.accountants.index');
        Route::get('/edit/{id}/accountant', [OwnerController::class, 'editAccountant'])->name('owner.accountants.edit');
        Route::get('/accountant-create', [OwnerController::class, 'createAccountant'])->name('owner.accountants.create');
        Route::post('/accountant-store', [OwnerController::class, 'storeAccountant'])->name('owner.accountants.store');
        Route::post('/accountants/change-status', [OwnerController::class, 'changeStatus'])->name('owner.accountants.changeStatus');
        Route::post('/accountants/{id}',[OwnerController::class, 'updateAccountant'])->name('owner.accountants.updateAccountant');

        
        // Collectors
        Route::get('/collectors-list', [OwnerController::class, 'collectors'])->name('owner.collectors.index');
        Route::get('/edit/{id}/collectors', [OwnerController::class, 'editCollector'])->name('owner.collectors.edit');
        Route::get('/collectors-create', [OwnerController::class, 'createCollector'])->name('owner.collectors.create');
        Route::post('/collectors-store', [OwnerController::class, 'storeCollector'])->name('owner.collectors.store');
        Route::post('/collectors/change-status', [OwnerController::class, 'changeStatusCollector'])->name('owner.collectors.changeStatus');
        Route::post('/collectors/assign-accountant', [OwnerController::class, 'assignAccountant'])->name('owner.collectors.assignAccountant');
        Route::post('/collectors/{id}',[OwnerController::class, 'updateCollector'])->name('owner.collectors.update');


        Route::post('/owner/bank-details/save', [OwnerController::class, 'saveBankDetails'])->name('owner.bank.save');

        Route::post('/owner/tally/apply-sync-defaults', [OwnerController::class, 'applySyncDefaults'])->name('owner.tally.apply-sync-defaults');

        // Tally Dashboard
        Route::get('/tally/dashboard', [OwnerController::class, 'company'])->name('owner.tally.dashboard');

        // Sync all data from tally prime
        Route::post('/tally/sync-all', [OwnerController::class, 'syncAll'])->name('owner.tally.sync-all');


        Route::get('/tally/company/{company}',[OwnerController::class, 'companyDetails'])->name('owner.tally.company.details');

        Route::get(
            '/tally/company/{company}/ledgers',
            [OwnerController::class, 'companyLedgers']
        )->name('owner.tally.company.ledgers');

        Route::post('/tally/ledger/update-credit-period', [OwnerController::class, 'updateCreditPeriod'])->name('owner.tally.ledger.update-credit-period');

        Route::get('owner/tally/{company}/ledger/{ledger}/{under?}/field-vouchers', [OwnerController::class, 'ledgerFieldVouchers'])->name('owner.tally.ledger.field-vouchers');
        Route::get(
            
            '/tally/company/{company}/ledger/{ledger}/invoices',
            [OwnerController::class, 'ledgerInvoices']
        )->name('owner.tally.ledger.invoices');

        Route::get(
            '/tally/company/{company}/ledger/{ledger}/receipts',
            [OwnerController::class, 'ledgerReceipts']
        )->name('owner.tally.ledger.receipts');

        Route::get(
            '/tally/company/{company}/ledger/{ledger}/vouchers/{under?}',
            [OwnerController::class, 'ledgerVouchers']
        )->name('owner.tally.ledger.vouchers');

        Route::get(
            '/tally/company/{company}/ledger/{ledger}/followup/{under?}',
            [OwnerController::class, 'ledgerFollowUp']
        )->name('owner.tally.ledger.followup');

        Route::get(
            '/tally/voucher-mappings/{company}',
            [OwnerController::class, 'voucherMappings']
        )->name('owner.tally.voucher.mappings');

        Route::post(
            '/voucher-mappings/save', 
            [OwnerController::class, 'saveVoucherMappings']
        )->name('owner.voucher-mappings.save');

        Route::get(
            '/tally/company/{company}/followup/hub/', 
            [OwnerController::class, 'followUpsHub']
        )->name('owner.tally.followup.hub');

        Route::get(
            '/tally/company/{company}/ledgers-assign',
            [OwnerController::class, 'asignLedgers']
        )->name('owner.tally.company.ledgers-assign');

        Route::post(
            '/owner/assign-collectors', 
            [OwnerController::class, 'assignLedgers']
        )->name('owner.assign.collectors');


        // Followup Actions
        Route::get( 
            '/tally/company/{company}/followup-action/telecaller', 
            [OwnerController::class, 'telecaller']
        )->name('owner.tally.company.followup-action.telecaller');

        Route::get(
            '/tally/company/{company}/followup-action/call', 
            [OwnerController::class, 'call']
        )->name('owner.tally.company.followup-action.call');

        Route::get(
            '/tally/company/{company}/followup-action/whatsapp-message', 
            [OwnerController::class, 'whatsappMessage']
        )->name('owner.tally.company.followup-action.whatsapp-message');

        Route::get(
            '/tally/company/{company}/followup-action/physical-visit', 
            [OwnerController::class, 'physicalVisit']
        )->name('owner.tally.company.followup-action.physical-visit');
        
        Route::get(
            '/tally/company/{company}/followup-action/escalation', 
            [OwnerController::class, 'escalation']
        )->name('owner.tally.company.followup-action.escalation');

        Route::get(
            '/tally/company/{company}/followup-history', 
            [OwnerController::class, 'followupHistory']
        )->name('owner.tally.company.followup_history');


        // Other
        Route::get('/company/{company}/templates', [OwnerController::class, 'templates'])->name('owner.other.templates');
        Route::post('/company/{company}/save-templates', [OwnerController::class, 'storeTemplates'])->name('owner.other.templates.store');
        Route::post('/company/{company}/save-parameters', [OwnerController::class, 'storeParameter'])->name('owner.other.template.parameters.store');
        Route::get('/company/{company}/ai-templates', [OwnerController::class, 'aiTemplates'])->name('owner.other.ai-templates');
        Route::get('/company/{company}/responses', [OwnerController::class, 'responses'])->name('owner.other.responses');
        Route::get('/company/{company}/overdue-target', [OwnerController::class, 'overdueTarget'])->name('owner.other.overdue-target');
        Route::get('/company/{company}/default-settings', [OwnerController::class, 'defaultSettings'])->name('owner.other.default-settings');
        Route::post('owner/tally/ledger/set-common-setting', [OwnerController::class, 'setCommonSetting'])->name('owner.tally.ledger.set-common-setting');
        Route::get('/company/{company}/master-settings', [OwnerController::class, 'masterSettings'])->name('owner.other.master-settings');
       Route::post('/company/{company}/save-row', [OwnerController::class, 'saveRow'])->name('owner.other.saveRow');
        Route::post('/tally/{company}/bad-debts/assign', [OwnerController::class, 'assignBadDebts'])->name('owner.tally.baddebts.assign'); 


        // Reports
        Route::get('/company/{company}/charts', [OwnerController::class, 'charts'])->name('owner.reports.charts');
        Route::get('/company/{company}/sales-analysis', [OwnerController::class, 'salesAnalysis'])->name('owner.reports.sales-analysis');


        // EMI
        Route::get('/company/{company}/set-debtor-emi', [OwnerController::class, 'setDebtorEMI'])->name('owner.set-debtor-emi');


        // Ledgers
        Route::get('/ledgers-list', [OwnerController::class, 'ledgers'])->name('owner.ledgers.index');


        // Manual 
        Route::get('/manual/dashboard', [OwnerController::class, 'manualDashboard'])->name('owner.manual.dashboard');
        Route::get('/manual/students-list', [OwnerController::class, 'students'])->name('owner.manual.students.index');
        Route::get('/manual/edit/{id}/students', [OwnerController::class, 'editStudent'])->name('owner.manual.students.edit');
        Route::get('/manual/students-create', [OwnerController::class, 'createStudent'])->name('owner.manual.students.create');
        Route::post('/manual/students-store', [OwnerController::class, 'storeStudent'])->name('owner.manual.students.store');
        Route::post('/manual/students/change-status', [OwnerController::class, 'changeStatusStudent'])->name('owner.manual.students.changeStatus');

        // Accountants 
        Route::get('/manual/accountants-list', [OwnerController::class, 'manualAccountants'])->name('owner.manual.accountants.index');
        Route::get('/manual/edit/{id}/accountant', [OwnerController::class, 'editmanualAccountant'])->name('owner.manual.accountants.edit');
        Route::get('/manual/accountant-create', [OwnerController::class, 'createmanualAccountant'])->name('owner.manual.accountants.create');
        Route::post('/manual/accountant-store', [OwnerController::class, 'storemanualAccountant'])->name('owner.manual.accountants.store');
        Route::post('/manual/accountants/change-status', [OwnerController::class, 'changeStatusmanualAccountant'])->name('owner.manual.accountants.changeStatus');
        Route::post('/manual/accountants/{id}',[OwnerController::class, 'updatemanualAccountant'])->name('owner.manual.accountants.updateAccountant');

         // Collectors
        Route::get('/manual/collectors-list', [OwnerController::class, 'manualCollectors'])->name('owner.manual.collectors.index');
        Route::get('/manual/edit/{id}/collectors', [OwnerController::class, 'editmanualCollector'])->name('owner.manual.collectors.edit');
        Route::get('/manual/collectors-create', [OwnerController::class, 'createmanualCollector'])->name('owner.manual.collectors.create');
        Route::post('/manual/collectors-store', [OwnerController::class, 'storemanualCollector'])->name('owner.manual.collectors.store');
        Route::post('/manual/collectors/change-status', [OwnerController::class, 'changeStatusmanualCollector'])->name('owner.manual.collectors.changeStatus');
        Route::post('/manual/collectors/assign-accountant', [OwnerController::class, 'assignmanualAccountant'])->name('owner.manual.collectors.assignAccountant');
        Route::post('/manual/collectors/{id}',[OwnerController::class, 'updatemanualCollector'])->name('owner.manual.collectors.update');

        // Sales Receipts
        Route::get('/manual/sales-receipts', [OwnerController::class, 'manualSalesReceipts'])->name('owner.manual.sales-receipts');
        Route::get('/manual/students/{id}/sales-receipt', [OwnerController::class, 'salesReceipt'])->name('owner.manual.students.salesReceipt');
        Route::post('/manual/students/{student}/sales', [OwnerController::class, 'storemanualSale'])->name('owner.manual.students.sales.store');
        Route::put('/manual/students/{student}/sales/{sale}', [OwnerController::class, 'updatemanualSale'])->name('owner.manual.students.sales.update');
        Route::delete('/manual/students/{student}/sales/{sale}', [OwnerController::class, 'destroymanualSale'])->name('owner.manual.students.sales.destroy');
        Route::post('/manual/students/{student}/receipts', [OwnerController::class, 'storemanualReceipt'])->name('owner.manual.students.receipts.store');
        Route::put('/manual/students/{student}/receipts/{receipt}', [OwnerController::class, 'updatemanualReceipt'])->name('owner.manual.students.receipts.update');
        Route::delete('/manual/students/{student}/receipts/{receipt}', [OwnerController::class, 'destroymanualReceipt'])->name('owner.manual.students.receipts.destroy');
        

        // Follow Ups
        Route::get(
            '/manual/followup/hub/', 
            [OwnerController::class, 'manualFollowUpsHub']
        )->name('owner.manual.followup.hub');

        Route::get( 
            '/manual/followup-action/telecaller', 
            [OwnerController::class, 'manualTelecaller']
        )->name('owner.manual.followup-action.telecaller');

        Route::get(
            '/manual/followup-action/call', 
            [OwnerController::class, 'manualCall']
        )->name('owner.manual.followup-action.call');

        Route::get(
            '/manual/followup-action/whatsapp-message', 
            [OwnerController::class, 'manualWhatsappMessage']
        )->name('owner.manual.followup-action.whatsapp-message');

        Route::get(
            '/manual/followup-action/physical-visit', 
            [OwnerController::class, 'manualPhysicalVisit']
        )->name('owner.manual.followup-action.physical-visit');
        
        Route::get(
            '/manual/followup-action/escalation', 
            [OwnerController::class, 'manualEscalation']
        )->name('owner.manual.followup-action.escalation');

        Route::get(
            '/manual/followup-history', 
            [OwnerController::class, 'manualFollowupHistory']
        )->name('owner.manual.followup_history');

        // Other
        Route::get('/manual/templates', [OwnerController::class, 'manualTemplates'])->name('owner.manual.other.templates');
        Route::get('/manual/responses', [OwnerController::class, 'manualResponses'])->name('owner.manual.other.responses');
        Route::get('/manual/overdue-target', [OwnerController::class, 'manualOverdueTarget'])->name('owner.manual.other.overdue-target');
        Route::get('/manual/default-settings', [OwnerController::class, 'manualDefaultSettings'])->name('owner.manual.other.default-settings');
        Route::get('/manual/master-settings', [OwnerController::class, 'manualMasterSettings'])->name('owner.manual.other.master-settings');

        // Track
        Route::get('/manual/track-collectors', [OwnerController::class, 'manualTrackCollectors'])->name('owner.manual.track-collectors');


        // Reports
        Route::get('/manual/charts', [OwnerController::class, 'manualCharts'])->name('owner.manual.reports.charts');
        Route::get('/manual/sales-analysis', [OwnerController::class, 'manaulSalesAnalysis'])->name('owner.manual.reports.sales-analysis');


        Route::get('/tally/ledger-due-vouchers', [OwnerController::class, 'ledgerDueVouchers'])->name('owner.tally.ledger.due-vouchers');

        // EMI
        Route::get('/manual/set-debtor-emi', [OwnerController::class, 'manaualSetDebtorEMI'])->name('owner.manual.set-debtor-emi');

    });



