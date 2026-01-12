<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BulkOnboardController;
use App\Http\Controllers\BatchStatusController;

// throttle:bulk-onboard for ratelimitting
Route::middleware('throttle:bulk-onboard')->group(function () {
    Route::post('/bulk-onboard', [BulkOnboardController::class, 'store']);
});

Route::get('/batch/{batchId}', [BatchStatusController::class, 'show']);