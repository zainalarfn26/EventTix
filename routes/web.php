<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventAdminController;
use App\Http\Controllers\Admin\FinanceController;
use App\Http\Controllers\Admin\PromoAdminController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SearchController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\TicketAdminController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\Admin\VenueAdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\OrderController;
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

// Order/Checkout API Endpoints
Route::post('/api/orders/checkout', [OrderController::class, 'checkout'])->name('orders.checkout');
Route::post('/api/orders/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
Route::post('/api/orders/verify', [OrderController::class, 'verify'])->name('orders.verify');
Route::post('/api/orders/apply-promo', [OrderController::class, 'applyPromo'])->name('orders.apply_promo');

// Midtrans Webhook Notification Endpoint
Route::post('/webhooks/midtrans', [WebhookController::class, 'handleMidtransWebhook'])->name('webhooks.midtrans');
Route::post('/api/midtrans/callback', [WebhookController::class, 'handleMidtransWebhook']); // Alias for the user's Midtrans dashboard config

// E-Ticket Viewer
Route::get('/tickets/{ticket:ticket_code}', [TicketController::class, 'show'])->name('tickets.show');

// User Dashboard (Protected)
Route::middleware(['auth'])->group(function () {
    Route::get('/my-tickets', [App\Http\Controllers\UserOrderController::class, 'index'])->name('user.orders.index');
    Route::get('/orders/{order_code}/payment', [App\Http\Controllers\UserOrderController::class, 'paymentSelection'])->name('user.orders.payment');
    Route::get('/orders/{order_code}/instruction', [App\Http\Controllers\UserOrderController::class, 'paymentInstruction'])->name('user.orders.instruction');
    Route::get('/orders/{order_code}/ticket', [App\Http\Controllers\UserOrderController::class, 'redirectToTicket'])->name('orders.redirect_ticket');
});

Route::post('/api/orders/{order_code}/charge', [App\Http\Controllers\OrderController::class, 'chargePayment'])->name('orders.charge')->middleware('auth');
Route::get('/api/orders/{order_code}/status', [App\Http\Controllers\OrderController::class, 'checkStatus'])->name('orders.status')->middleware('auth');

// Admin & Organizer Portal (shared pages)
Route::middleware(['auth', 'role:admin|organizer'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::put('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile');
    Route::put('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');
});

// Admin-only management portal
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/search', [SearchController::class, 'index'])->name('search');

    // Events
    Route::get('/events', [EventAdminController::class, 'index'])->name('events.index');
    Route::get('/events/create', [EventAdminController::class, 'create'])->name('events.create');
    Route::post('/events', [EventAdminController::class, 'store'])->name('events.store');
    Route::get('/events/{event}/edit', [EventAdminController::class, 'edit'])->name('events.edit');
    Route::put('/events/{event}', [EventAdminController::class, 'update'])->name('events.update');
    Route::patch('/events/{event}/status', [EventAdminController::class, 'updateStatus'])->name('events.status');
    Route::delete('/events/{event}', [EventAdminController::class, 'destroy'])->name('events.destroy');

    // Venues
    Route::get('/venues', [VenueAdminController::class, 'index'])->name('venues.index');
    Route::post('/venues', [VenueAdminController::class, 'store'])->name('venues.store');
    Route::put('/venues/{venue}', [VenueAdminController::class, 'update'])->name('venues.update');
    Route::delete('/venues/{venue}', [VenueAdminController::class, 'destroy'])->name('venues.destroy');

    // Promo codes
    Route::get('/promos', [PromoAdminController::class, 'index'])->name('promos.index');
    Route::post('/promos', [PromoAdminController::class, 'store'])->name('promos.store');
    Route::put('/promos/{promo}', [PromoAdminController::class, 'update'])->name('promos.update');
    Route::patch('/promos/{promo}/toggle', [PromoAdminController::class, 'toggle'])->name('promos.toggle');
    Route::delete('/promos/{promo}', [PromoAdminController::class, 'destroy'])->name('promos.destroy');

    // Tickets
    Route::get('/tickets', [TicketAdminController::class, 'index'])->name('tickets.index');
    Route::post('/tickets/{ticket}/check-in', [TicketAdminController::class, 'checkIn'])->name('tickets.check_in');
    Route::post('/tickets/{ticket}/undo-check-in', [TicketAdminController::class, 'undoCheckIn'])->name('tickets.undo_check_in');
    Route::post('/tickets/{ticket}/cancel', [TicketAdminController::class, 'cancel'])->name('tickets.cancel');
    Route::post('/tickets/{ticket}/reactivate', [TicketAdminController::class, 'reactivate'])->name('tickets.reactivate');

    // Finances & orders
    Route::get('/finances', [FinanceController::class, 'index'])->name('finances.index');
    Route::get('/finances/export', [FinanceController::class, 'export'])->name('finances.export');
    Route::get('/orders/{order}', [FinanceController::class, 'showOrder'])->name('orders.show');
    Route::post('/orders/{order}/cancel', [FinanceController::class, 'cancelOrder'])->name('orders.cancel');

    // Accounts
    Route::get('/accounts', [UserAdminController::class, 'index'])->name('accounts.index');
    Route::post('/accounts', [UserAdminController::class, 'store'])->name('accounts.store');
    Route::put('/accounts/{user}', [UserAdminController::class, 'update'])->name('accounts.update');
    Route::delete('/accounts/{user}', [UserAdminController::class, 'destroy'])->name('accounts.destroy');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/{event}', [ReportController::class, 'show'])->name('reports.show');
    Route::get('/reports/{event}/export', [ReportController::class, 'export'])->name('reports.export');
});

// Gate Scanner (Protected by Admin/Organizer)
Route::middleware(['auth', 'role:admin|organizer'])->group(function () {
    Route::get('/scanner', [TicketController::class, 'scanner'])->name('scanner.index');
    Route::post('/api/tickets/scan', [TicketController::class, 'scan'])->name('tickets.scan');
    Route::get('/api/tickets/history', [TicketController::class, 'history'])->name('tickets.history');
});
