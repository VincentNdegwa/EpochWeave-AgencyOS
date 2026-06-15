<?php

use App\Http\Controllers\AccountContactController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\BuilderDataController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoiceStatusController;
use App\Http\Controllers\PortalSetupController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductUnitController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectStatusController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\ProposalStatusController;
use App\Http\Controllers\ProposalTemplateController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskStatusController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\UserPreferenceController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\WorkspaceSettings\AutomationController;
use App\Http\Controllers\WorkspaceSettings\GeneralController as WorkspaceGeneralSettingsController;
use App\Http\Controllers\WorkspaceSettings\InvoiceController as WorkspaceInvoiceSettingsController;
use App\Http\Controllers\WorkspaceSettings\NotificationController;
use App\Http\Controllers\WorkspaceSettings\ProposalController as WorkspaceProposalSettingsController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

// Public proposal routes
Route::get('/proposals/{token}/public', [ProposalController::class, 'publicShow'])->name('proposals.public.show');
Route::post('/proposals/{proposal}/accept', [ProposalController::class, 'accept'])->name('proposals.accept');
Route::post('/proposals/{proposal}/decline', [ProposalController::class, 'decline'])->name('proposals.decline');

// Public invoice routes
Route::get('/invoices/{token}/public', [InvoiceController::class, 'publicShow'])->name('invoices.public.show');

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
    Route::resource('proposal-status', ProposalStatusController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('project-status', ProjectStatusController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('tags', TagController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('task-status', TaskStatusController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('invoice-status', InvoiceStatusController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::post('/products/bulk-status', [ProductController::class, 'bulkUpdateStatus'])
        ->name('products.bulk-status');
    Route::post('/products/bulk', [ProductController::class, 'bulkDelete'])
        ->name('products.bulk-delete');

    Route::resource('projects', ProjectController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::resource('tasks', TaskController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.status.update');

    Route::resource('invoices', InvoiceController::class);
    Route::post('/invoices/{invoice}/send', [InvoiceController::class, 'send'])->name('invoices.send');
    Route::patch('/invoices/{invoice}/status', [InvoiceController::class, 'updateStatus'])->name('invoices.status.update');
    Route::post('/invoices/bulk', [InvoiceController::class, 'bulkDelete'])->name('invoices.bulk-delete');

    Route::resource('proposals', ProposalController::class);
    Route::patch('/proposals/{proposal}/move', [ProposalController::class, 'move'])->name('proposal.move');
    Route::post('/proposals/{proposal}/send', [ProposalController::class, 'send'])->name('proposals.send');

    Route::resource('proposal-templates', ProposalTemplateController::class);
    Route::post('proposal-templates/{template}/duplicate', [ProposalTemplateController::class, 'duplicate'])->name('proposal-templates.duplicate');
    Route::post('proposal-templates/{template}/set-default', [ProposalTemplateController::class, 'setDefault'])->name('proposal-templates.set-default');

    Route::prefix('builder-data')->name('builder-data.')->group(function () {
        Route::get('products', [BuilderDataController::class, 'products'])->name('products');
        Route::get('units', [BuilderDataController::class, 'units'])->name('units');
        Route::get('templates', [BuilderDataController::class, 'templates'])->name('templates');
        Route::get('templates/{template}', [BuilderDataController::class, 'template'])->name('templates.show');
        Route::get('accounts', [BuilderDataController::class, 'accounts'])->name('accounts');
        Route::get('users', [BuilderDataController::class, 'users'])->name('users');
        Route::get('account-contacts', [BuilderDataController::class, 'accountContacts'])->name('account-contacts');
        Route::get('projects', [BuilderDataController::class, 'projects'])->name('projects');
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
    Route::get('workspace/settings/invoices', [WorkspaceInvoiceSettingsController::class, 'edit'])
        ->name('workspace-settings.invoices');
    Route::patch('workspace/settings/invoices', [WorkspaceInvoiceSettingsController::class, 'update'])
        ->name('workspace-settings.invoices.update');
    Route::get('workspace/settings/notifications', [NotificationController::class, 'edit'])
        ->name('workspace-settings.notifications');
    Route::patch('workspace/settings/notifications', [NotificationController::class, 'update'])
        ->name('workspace-settings.notifications.update');
    Route::get('workspace/settings/automation', [AutomationController::class, 'edit'])
        ->name('workspace-settings.automation');
    Route::patch('workspace/settings/automation', [AutomationController::class, 'update'])
        ->name('workspace-settings.automation.update');

    Route::post('/user-preferences/display-mode', [UserPreferenceController::class, 'updateDisplayMode'])
        ->name('user-preferences.display-mode.update');
});

require __DIR__.'/settings.php';
