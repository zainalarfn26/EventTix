<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\OrderItem;
use App\Models\Ticket;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $events = Event::with(['venue', 'ticketTiers'])
            ->withCount([
                'tickets as active_tickets_count' => fn ($q) => $q->where('status', '!=', 'cancelled'),
                'tickets as checked_in_count' => fn ($q) => $q->where('status', 'checked_in'),
            ])
            ->withSum(['orders as paid_revenue' => fn ($q) => $q->where('status', 'paid')], 'total_amount')
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', '%' . $request->q . '%'))
            ->orderByDesc('start_time')
            ->get();

        $totals = [
            'revenue' => $events->sum('paid_revenue'),
            'tickets' => $events->sum('active_tickets_count'),
            'checked_in' => $events->sum('checked_in_count'),
            'quota' => $events->sum(fn ($e) => $e->ticketTiers->sum('quota')),
        ];

        return view('admin.reports.index', compact('events', 'totals'));
    }

    public function show(Event $event)
    {
        $event->load(['venue', 'ticketTiers']);

        $tierRevenue = OrderItem::selectRaw('ticket_tier_id, SUM(subtotal) as revenue, SUM(quantity) as qty')
            ->whereHas('order', fn ($q) => $q->where('event_id', $event->id)->where('status', 'paid'))
            ->groupBy('ticket_tier_id')
            ->get()
            ->keyBy('ticket_tier_id');

        $tierStats = $event->ticketTiers->map(function ($tier) use ($event, $tierRevenue) {
            $tickets = Ticket::where('event_id', $event->id)->where('ticket_tier_id', $tier->id);
            return [
                'tier' => $tier,
                'revenue' => (float) ($tierRevenue[$tier->id]->revenue ?? 0),
                'issued' => (clone $tickets)->where('status', '!=', 'cancelled')->count(),
                'checked_in' => (clone $tickets)->where('status', 'checked_in')->count(),
            ];
        });

        $summary = [
            'revenue' => (float) $event->orders()->where('status', 'paid')->sum('total_amount'),
            'discount' => (float) $event->orders()->where('status', 'paid')->sum('discount_amount'),
            'paid_orders' => $event->orders()->where('status', 'paid')->count(),
            'pending_orders' => $event->orders()->where('status', 'pending')->count(),
            'issued' => $tierStats->sum('issued'),
            'checked_in' => $tierStats->sum('checked_in'),
            'quota' => $event->ticketTiers->sum('quota'),
        ];

        $recentCheckIns = Ticket::with(['user', 'ticketTier', 'checkedInBy'])
            ->where('event_id', $event->id)
            ->where('status', 'checked_in')
            ->orderByDesc('checked_in_at')
            ->limit(10)
            ->get();

        return view('admin.reports.show', compact('event', 'tierStats', 'summary', 'recentCheckIns'));
    }

    public function export(Event $event)
    {
        $tickets = Ticket::with(['user', 'ticketTier', 'order', 'checkedInBy'])
            ->where('event_id', $event->id)
            ->orderBy('ticket_tier_id')
            ->orderBy('id')
            ->get();

        $filename = 'laporan-' . \Illuminate\Support\Str::slug($event->title) . '-' . now()->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($tickets) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Kode Tiket', 'Kelas', 'Warna Gelang', 'Pemilik', 'Email', 'Kode Order', 'Status', 'Waktu Check-in', 'Di-scan Oleh']);
            foreach ($tickets as $t) {
                fputcsv($out, [
                    $t->ticket_code,
                    $t->ticketTier->name ?? '-',
                    $t->ticketTier->wristband_color ?? '-',
                    $t->user->name ?? '-',
                    $t->user->email ?? '-',
                    $t->order->order_code ?? '-',
                    $t->status,
                    $t->checked_in_at?->format('Y-m-d H:i:s') ?? '',
                    $t->checkedInBy->name ?? '',
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
