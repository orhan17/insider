<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'Insider Message Sender API',
        'version' => '1.0.0',
        'status' => 'OK',
    ]);
});
