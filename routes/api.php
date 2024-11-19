<?php
// routes/api.php

use App\Http\Controllers\TelegramBotController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::post('/telegram/webhook', [TelegramBotController::class, 'webhook']);

// For debugging, add a GET route
Route::get('/telegram/webhook', [TelegramBotController::class, 'webhook']);

// Debug route
Route::get('/telegram/debug', [TelegramBotController::class, 'debug']);
