<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\GoogleController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/available-slots', [BookingController::class, 'getAvailableSlots']);
Route::post('/bookings', [BookingController::class, 'store']);
Route::get('/bookings', [BookingController::class, 'index']);

// Google Calendar routes
Route::get('auth/google/connect', [GoogleController::class, 'connectToGoogle'])->name('google.connect');
Route::get('auth/google/callback', [GoogleController::class, 'callback']);
