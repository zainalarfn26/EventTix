<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\SeatReservationController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{event:slug}', [EventController::class, 'show'])->name('events.show');

// Seat Reservation API Endpoints
Route::post('/api/seats/lock', [SeatReservationController::class, 'lockSeat'])->name('seats.lock');
Route::post('/api/seats/release', [SeatReservationController::class, 'releaseSeat'])->name('seats.release');

// Midtrans Webhook (exclude CSRF in bootstrap/app.php or route group)
Route::post('/webhooks/midtrans', [WebhookController::class, 'handleMidtransWebhook'])->name('webhooks.midtrans');

// E-Ticket & Gate Scanner
Route::get('/tickets/{ticket:ticket_code}', [TicketController::class, 'show'])->name('tickets.show');
Route::get('/scanner', [TicketController::class, 'scanner'])->name('scanner.index');
Route::post('/api/tickets/scan', [TicketController::class, 'scan'])->name('tickets.scan');
