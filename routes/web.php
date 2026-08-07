<?php

use App\Http\Controllers\Barber\BookingController as BarberBookingController;
use App\Http\Controllers\Barber\DashboardController as BarberDashboardController;
use App\Http\Controllers\Business\BookingController as BusinessBookingController;
use App\Http\Controllers\Business\ClientController;
use App\Http\Controllers\Business\DashboardController as BusinessDashboardController;
use App\Http\Controllers\Business\ServiceController;
use App\Http\Controllers\Business\StaffController;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicBookingController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::get('/book/{business:slug}', [PublicBookingController::class, 'show'])->name('public.booking.show');
Route::post('/book/{business:slug}', [PublicBookingController::class, 'store'])->name('public.booking.store');
Route::get('/book/{business:slug}/confirmed/{booking}', [PublicBookingController::class, 'confirmation'])->name('public.booking.confirmation');


Route::get('/dashboard', DashboardRedirectController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified', 'role:owner'])->prefix('business')->name('business.')->group(function () {
    Route::get('/', BusinessDashboardController::class)->name('dashboard');

    Route::resource('clients', ClientController::class)->except(['show']);
    Route::resource('services', ServiceController::class)->except(['show']);
    Route::resource('staff', StaffController::class)->only(['index', 'create', 'store', 'destroy']);
    Route::resource('bookings', BusinessBookingController::class)->only(['index', 'create', 'store', 'destroy']);
    Route::patch('bookings/{booking}/status', [BusinessBookingController::class, 'updateStatus'])->name('bookings.status');
});

Route::middleware(['auth', 'verified', 'role:barber'])->prefix('barber')->name('barber.')->group(function () {
    Route::get('/', BarberDashboardController::class)->name('dashboard');
    Route::get('/bookings', [BarberBookingController::class, 'index'])->name('bookings.index');
    Route::patch('/bookings/{booking}/status', [BarberBookingController::class, 'updateStatus'])->name('bookings.status');
});

require __DIR__.'/auth.php';
