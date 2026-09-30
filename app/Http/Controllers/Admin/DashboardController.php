<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Reservation;
use App\Models\Ticket;
use App\Models\Transaction;

class DashboardController extends Controller
{
    public function index()
    {
        $totalRevenue = Transaction::where('transaction_status', 'settlement')->sum('gross_amount');
        $totalTicketsSold = Ticket::count();
        $totalEventsCount = Event::count();
        $totalCheckedInCount = Ticket::where('is_checked_in', true)->count();

        // Recent 10 Transactions
        $recentTransactions = Transaction::with(['reservation.event', 'reservation.user', 'reservation.seat'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Recent 10 Check-ins
        $recentCheckIns = Ticket::with(['reservation.event', 'reservation.user', 'reservation.seat'])
            ->where('is_checked_in', true)
            ->orderBy('checked_in_at', 'desc')
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalRevenue',
            'totalTicketsSold',
            'totalEventsCount',
            'totalCheckedInCount',
            'recentTransactions',
            'recentCheckIns'
        ));
    }
}
