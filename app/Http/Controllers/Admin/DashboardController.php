<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\Transaction;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $totalRevenue = Transaction::where('transaction_status', 'settlement')->sum('gross_amount');
        $totalTicketsSold = Ticket::where('status', '!=', 'cancelled')->count();
        $totalEventsCount = Event::count();
        $totalCheckedInCount = Ticket::where('status', 'checked_in')->count();
        $pendingOrdersCount = Order::where('status', 'pending')->where('expires_at', '>', now())->count();

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

        // Recent events with per-event revenue and ticket numbers
        $recentEvents = Event::with(['venue', 'ticketTiers'])
            ->withCount(['tickets as active_tickets_count' => fn ($q) => $q->where('status', '!=', 'cancelled')])
            ->withSum(['orders as paid_revenue' => fn ($q) => $q->where('status', 'paid')], 'total_amount')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        // Ticket sales trend for the last 12 months (real data)
        $start = Carbon::now()->startOfMonth()->subMonths(11);
        $salesByMonth = Ticket::where('status', '!=', 'cancelled')
            ->where('created_at', '>=', $start)
            ->get(['created_at'])
            ->groupBy(fn ($t) => $t->created_at->format('Y-m'))
            ->map->count();

        $chartLabels = [];
        $chartData = [];
        for ($i = 0; $i < 12; $i++) {
            $month = $start->copy()->addMonths($i);
            $chartLabels[] = $month->translatedFormat('M Y');
            $chartData[] = $salesByMonth[$month->format('Y-m')] ?? 0;
        }

        return view('admin.dashboard', compact(
            'totalRevenue',
            'totalTicketsSold',
            'totalEventsCount',
            'totalCheckedInCount',
            'pendingOrdersCount',
            'recentTransactions',
            'recentCheckIns',
            'recentEvents',
            'chartLabels',
            'chartData'
        ));
    }
}
