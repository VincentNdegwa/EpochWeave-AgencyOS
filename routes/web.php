<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AccountContactController;
use App\Http\Controllers\PortalSetupController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductUnitController;
use App\Http\Controllers\WorkspaceController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::get('/portal/setup/{token}', [PortalSetupController::class, 'show'])->name('portal.setup');
Route::post('/portal/setup/{token}', [PortalSetupController::class, 'complete'])->name('portal.setup.complete');

Route::middleware(['auth', 'verified', 'set.current.workspace'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::post('/workspaces', [WorkspaceController::class, 'store'])->name('workspaces.store');
    Route::post('/workspaces/{workspace}/switch', [WorkspaceController::class, 'switch'])->name('workspaces.switch');

    Route::resource('accounts', AccountController::class);
    Route::post('/accounts/{account}/contacts', [AccountContactController::class, 'store'])
        ->name('accounts.contacts.store');
    Route::put('/accounts/{account}/contacts/{contact}', [AccountContactController::class, 'update'])
        ->name('accounts.contacts.update');
    Route::delete('/accounts/{account}/contacts/{contact}', [AccountContactController::class, 'destroy'])
        ->name('accounts.contacts.destroy');

    Route::resource('product-units', ProductUnitController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('products', ProductController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
});

require __DIR__.'/settings.php';
