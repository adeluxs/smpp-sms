<?php

use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\RouteController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\SmppCredentialController;
use App\Http\Controllers\ReportController;

Route::get('/', fn() => redirect('/login'));

Route::group(['prefix' => 'admin', 'middleware' => 'auth'], function () {
    Route::get('/', fn() => view('dashboard.index'));
    Route::resource('clients', ClientController::class)->except(['show']);
    Route::resource('routes', RouteController::class)->except(['show']);
    Route::get('/messages', [ReportController::class, 'messages'])->name('admin.messages');
});

Route::group(['middleware' => 'auth'], function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/my/messages', [ReportController::class, 'messages'])->name('user.messages');
    Route::get('/my/smpp-credentials', [SmppCredentialController::class, 'show'])->name('smpp.credentials');
    Route::post('/my/smpp-credentials/reset', [SmppCredentialController::class, 'reset'])->name('smpp.credentials.reset');
});