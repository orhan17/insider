<?php

use App\Http\Controllers\Api\MessageController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/messages', [MessageController::class, 'index']);
    Route::get('/messages/pending', [MessageController::class, 'pending']);
    Route::post('/messages', [MessageController::class, 'store']);
});
