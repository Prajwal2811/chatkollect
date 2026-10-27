<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TallyController;

// Admin Routes
Route::group(['prefix' => 'admin'], function() {
	Route::group(['middleware' => 'admin.guest'], function(){
		Route::view('/login', 'admin.login')->name('admin.login');
		Route::view('/forgot-password', 'admin.forgot-password')->name('admin.forgot-password');
		Route::post('/admin/login', [App\Http\Controllers\AdminController::class, 'authenticate'])->name('admin.auth');
		Route::post('/admin/send-reset-link', [App\Http\Controllers\AdminController::class, 'sendResetPassword'])->name('admin.send-reset-link');
	});
	
	Route::group(['middleware' => 'admin.auth'], function () {
		// Dashboard
		Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

		// Tally Integration
		Route::get('/tally/sync', [TallyController::class, 'syncTallyData'])->name('admin.tally.sync');
		
		Route::get('/organizations', [AdminController::class, 'organizations'])->name('admin.organizations.index');
		// Profile, Settings
		Route::get('/logout', [AdminController::class, 'signOut'])->name('admin.signOut');

		// Admin Management (Superadmin only)
		Route::middleware('admin.module:Admin')->group(function () {
			Route::get('/admins/create', [AdminController::class, 'create'])->name('admin.create');
		});
		
	});
});
