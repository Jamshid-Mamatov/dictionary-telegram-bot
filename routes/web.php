<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/telegram/test', function() {
    return response()->json([
        'status' => 'working',
        'message' => 'Telegram bot endpoint is accessible',
        'timestamp' => now()->toIso8601String()
    ]);
});
