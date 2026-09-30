<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\Transaction;

class DashboardController extends Controller
{
    public function index()
    {
        $totalRevenue = Transaction::where('transaction_status', 'settlement')->sum('gross_amount');
        $totalTicketsSold = Ticket::where('status', '!=', 'cancelled')->count();
        $totalEventsCount = Event::count();
        $totalCheckedInCount = Ticket::where('status', 'checked_in')->count();

        // Recent 10 Transactions
        $recentTransactions = Transaction::with(['order.event', 'order.user'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Recent 10 Check-ins
        $recentCheckIns = Ticket::with(['event', 'ticketTier', 'user'])
            ->where('status', 'checked_in')
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
