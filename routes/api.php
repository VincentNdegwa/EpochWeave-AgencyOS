<?php

use App\Http\Controllers\ProposalKanbanController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'set.current.workspace'])->group(function () {
    Route::patch('/proposals/{proposal}/move', [ProposalKanbanController::class, 'move']);
});
