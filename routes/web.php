<?php

use App\Http\Controllers\AccountContactController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\BuilderDataController;
use App\Http\Controllers\PortalSetupController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProductUnitController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\ProposalTemplateController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\WorkspaceSettings\GeneralController as WorkspaceGeneralSettingsController;
use App\Http\Controllers\WorkspaceSettings\ProposalController as WorkspaceProposalSettingsController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::get('/portal/setup/{token}', [PortalSetupController::class, 'show'])->name('portal.setup');
Route::post('/portal/setup/{token}', [PortalSetupController::class, 'complete'])->name('portal.setup.complete');

Route::middleware(['auth', 'verified', 'set.current.workspace'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::post('/workspaces', [WorkspaceController::class, 'store'])->name('workspaces.store');
    Route::post('/workspaces/{workspace}/switch', [WorkspaceController::class, 'switch'])->name('workspaces.switch');

    Route::resource('accounts', AccountController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
    Route::post('/accounts/bulk-status', [AccountController::class, 'bulkUpdateStatus'])
        ->name('accounts.bulk-status');
    Route::post('/accounts/bulk', [AccountController::class, 'bulkDelete'])
        ->name('accounts.bulk-delete');
    Route::post('/accounts/{account}/contacts', [AccountContactController::class, 'store'])
        ->name('accounts.contacts.store');
    Route::put('/accounts/{account}/contacts/{contact}', [AccountContactController::class, 'update'])
        ->name('accounts.contacts.update');
    Route::delete('/accounts/{account}/contacts/{contact}', [AccountContactController::class, 'destroy'])
        ->name('accounts.contacts.destroy');

    Route::resource('product-units', ProductUnitController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('products', ProductController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::post('/products/bulk-status', [ProductController::class, 'bulkUpdateStatus'])
        ->name('products.bulk-status');
    Route::post('/products/bulk', [ProductController::class, 'bulkDelete'])
        ->name('products.bulk-delete');

    Route::resource('projects', ProjectController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::resource('proposals', ProposalController::class);
    Route::resource('proposal-templates', ProposalTemplateController::class);
    Route::post('proposal-templates/{template}/duplicate', [ProposalTemplateController::class, 'duplicate'])->name('proposal-templates.duplicate');
    Route::post('proposal-templates/{template}/set-default', [ProposalTemplateController::class, 'setDefault'])->name('proposal-templates.set-default');

    Route::prefix('builder-data')->name('builder-data.')->group(function () {
        Route::get('products', [BuilderDataController::class, 'products'])->name('products');
        Route::get('units', [BuilderDataController::class, 'units'])->name('units');
        Route::get('templates', [BuilderDataController::class, 'templates'])->name('templates');
        Route::get('templates/{template}', [BuilderDataController::class, 'template'])->name('templates.show');
        Route::get('accounts', [BuilderDataController::class, 'accounts'])->name('accounts');
    });

    Route::post('uploads', [UploadController::class, 'store'])->name('uploads.store');
    Route::delete('uploads/{key}', [UploadController::class, 'destroy'])->name('uploads.destroy');

    Route::redirect('workspace/settings', '/workspace/settings/general')
        ->name('workspace-settings.index');
    Route::get('workspace/settings/general', [WorkspaceGeneralSettingsController::class, 'edit'])
        ->name('workspace-settings.general');
    Route::patch('workspace/settings/general', [WorkspaceGeneralSettingsController::class, 'update'])
        ->name('workspace-settings.general.update');
    Route::get('workspace/settings/proposals', [WorkspaceProposalSettingsController::class, 'edit'])
        ->name('workspace-settings.proposals');
    Route::patch('workspace/settings/proposals', [WorkspaceProposalSettingsController::class, 'update'])
        ->name('workspace-settings.proposals.update');
});

require __DIR__.'/settings.php';
