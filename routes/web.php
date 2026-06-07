<?php

use App\Http\Controllers\AccountContactController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\PortalSetupController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductUnitController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\WorkspaceSettings\GeneralController as WorkspaceGeneralSettingsController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::get('/portal/setup/{token}', [PortalSetupController::class, 'show'])->name('portal.setup');
Route::post('/portal/setup/{token}', [PortalSetupController::class, 'complete'])->name('portal.setup.complete');

Route::middleware(['auth', 'verified', 'set.current.workspace'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::post('/workspaces', [WorkspaceController::class, 'store'])->name('workspaces.store');
    Route::post('/workspaces/{workspace}/switch', [WorkspaceController::class, 'switch'])->name('workspaces.switch');

    Route::resource('accounts', AccountController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
    Route::post('/accounts/{account}/contacts', [AccountContactController::class, 'store'])
        ->name('accounts.contacts.store');
    Route::put('/accounts/{account}/contacts/{contact}', [AccountContactController::class, 'update'])
        ->name('accounts.contacts.update');
    Route::delete('/accounts/{account}/contacts/{contact}', [AccountContactController::class, 'destroy'])
        ->name('accounts.contacts.destroy');

    Route::resource('product-units', ProductUnitController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('products', ProductController::class)->only(['index', 'store', 'show', 'update', 'destroy']);

    Route::resource('proposals', ProposalController::class);

    Route::post('uploads', [UploadController::class, 'store'])->name('uploads.store');
    Route::delete('uploads/{key}', [UploadController::class, 'destroy'])->name('uploads.destroy');

    Route::redirect('workspace/settings', '/workspace/settings/general')
        ->name('workspace-settings.index');
    Route::get('workspace/settings/general', [WorkspaceGeneralSettingsController::class, 'edit'])
        ->name('workspace-settings.general');
    Route::patch('workspace/settings/general', [WorkspaceGeneralSettingsController::class, 'update'])
        ->name('workspace-settings.general.update');
});

require __DIR__.'/settings.php';
