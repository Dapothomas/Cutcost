<?php

use App\Http\Controllers\Barber\BookingController as BarberBookingController;
use App\Http\Controllers\Barber\DashboardController as BarberDashboardController;
use App\Http\Controllers\Business\BookingController as BusinessBookingController;
use App\Http\Controllers\Business\ClientController;
use App\Http\Controllers\Business\DashboardController as BusinessDashboardController;
use App\Http\Controllers\Business\NotificationController;
use App\Http\Controllers\Business\PaymentsController;
use App\Http\Controllers\Business\ServiceController;
use App\Http\Controllers\Business\SettingsController;
use App\Http\Controllers\Business\StaffController;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\Auth\ResumeCheckoutController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicBookingController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\Admin\WaitlistController as AdminWaitlistController;
use App\Http\Controllers\WaitlistController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::get('/waitlist', [WaitlistController::class, 'show'])->name('waitlist');
Route::post('/waitlist', [WaitlistController::class, 'store'])->name('waitlist.store');

Route::middleware(['auth', 'verified', 'platform-admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('waitlist', [AdminWaitlistController::class, 'index'])->name('waitlist.index');
    Route::delete('waitlist/{waitlist}', [AdminWaitlistController::class, 'destroy'])->name('waitlist.destroy');
});

Route::get('/book/{business:slug}', [PublicBookingController::class, 'show'])->name('public.booking.show');
Route::post('/book/{business:slug}', [PublicBookingController::class, 'store'])->name('public.booking.store');
Route::get('/book/{business:slug}/checkout/success', [PublicBookingController::class, 'checkoutSuccess'])->name('public.booking.checkout.success');
Route::get('/book/{business:slug}/checkout/cancel/{booking}', [PublicBookingController::class, 'checkoutCancel'])->name('public.booking.checkout.cancel');
Route::get('/book/{business:slug}/confirmed/{booking}', [PublicBookingController::class, 'confirmation'])->name('public.booking.confirmation');


Route::post('/stripe/webhook', StripeWebhookController::class)->name('stripe.webhook');

Route::get('/dashboard', DashboardRedirectController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('register/checkout/resume', ResumeCheckoutController::class)
        ->name('register.checkout.resume');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified', 'role:owner', 'subscribed'])->prefix('business')->name('business.')->group(function () {
    Route::get('/', BusinessDashboardController::class)->name('dashboard');

    Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::patch('settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('settings/subscription/cancel', [SettingsController::class, 'cancelSubscription'])->name('settings.subscription.cancel');

    Route::get('payments', [PaymentsController::class, 'index'])->name('payments.index');
    Route::post('payments/connect', [PaymentsController::class, 'connect'])->name('payments.connect');
    Route::get('payments/return', [PaymentsController::class, 'return'])->name('payments.return');
    Route::get('payments/refresh', [PaymentsController::class, 'refresh'])->name('payments.refresh');

    Route::resource('clients', ClientController::class)->except(['show']);
    Route::resource('services', ServiceController::class)->except(['show']);
    Route::resource('staff', StaffController::class)->only(['index', 'create', 'store', 'destroy']);
    Route::resource('bookings', BusinessBookingController::class)->only(['index', 'create', 'store', 'destroy']);
    Route::patch('bookings/{booking}/status', [BusinessBookingController::class, 'updateStatus'])->name('bookings.status');

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::get('notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
});

Route::middleware(['auth', 'verified', 'role:barber'])->prefix('barber')->name('barber.')->group(function () {
    Route::get('/', BarberDashboardController::class)->name('dashboard');
    Route::get('/bookings', [BarberBookingController::class, 'index'])->name('bookings.index');
    Route::patch('/bookings/{booking}/status', [BarberBookingController::class, 'updateStatus'])->name('bookings.status');
});

require __DIR__.'/auth.php';
