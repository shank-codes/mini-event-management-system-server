<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\AttendeeController;

Route::prefix('v1')->group(function () {
    Route::post('/events', [EventController::class, 'store']);
    Route::get('/events', [EventController::class, 'index']);
    Route::post('/events/{event}/register', [AttendeeController::class, 'register']);
    Route::get('/events/{event}/attendees', [AttendeeController::class, 'index']);
});