<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'Insider Message Sender API',
        'version' => '1.0.0',
        'documentation' => url('/api/documentation'),
    ]);
});

Route::get('/api/documentation', function () {
    return response()->file(public_path('api-docs.json'));
});
