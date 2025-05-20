<?php

use Inertia\Inertia;
use App\Models\Booking;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\BookingController;
use App\Http\Middleware\EnsureAdminHasGoogleCalendar;

Route::get('/', function () {
    return Inertia::render('Booking');
})->name('home');

Route::get('welcome', function () {
    return Inertia::render('Welcome');
})->name('home_page');

Route::get('admin', function () {
    return Inertia::render('Dashboard', [
        'bookings' => Booking::all() // Pass bookings data
    ]);
})->middleware(['auth', 'verified', EnsureAdminHasGoogleCalendar::class])->name('dashboard');

// Route::get('dashboard', function () {
//     return Inertia::render('Dashboard_old', [
//         'bookings' => Booking::all() // Pass bookings data
//     ]);
// })->middleware(['auth', 'verified'])->name('dashboard');



require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
