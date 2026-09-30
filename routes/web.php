<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventAdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\SeatReservationController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

// Public Event Catalog
Route::get('/', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{event:slug}', [EventController::class, 'show'])->name('events.show');

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Seat Reservation API Endpoints
Route::post('/api/seats/lock', [SeatReservationController::class, 'lockSeat'])->name('seats.lock');
Route::post('/api/seats/release', [SeatReservationController::class, 'releaseSeat'])->name('seats.release');

// Midtrans Webhook Notification Endpoint
Route::post('/webhooks/midtrans', [WebhookController::class, 'handleMidtransWebhook'])->name('webhooks.midtrans');

// E-Ticket Viewer & Gate Scanner
Route::get('/tickets/{ticket:ticket_code}', [TicketController::class, 'show'])->name('tickets.show');
Route::get('/scanner', [TicketController::class, 'scanner'])->name('scanner.index');
Route::post('/api/tickets/scan', [TicketController::class, 'scan'])->name('tickets.scan');

// Admin & Organizer Portal (Protected by Spatie Role Middleware)
Route::middleware(['auth', 'role:admin|organizer'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/events', [EventAdminController::class, 'index'])->name('events.index');
    Route::get('/events/create', [EventAdminController::class, 'create'])->name('events.create');
    Route::post('/events', [EventAdminController::class, 'store'])->name('events.store');
    Route::delete('/events/{event}', [EventAdminController::class, 'destroy'])->name('events.destroy');
});
