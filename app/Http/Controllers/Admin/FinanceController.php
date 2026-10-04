<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Order;
use App\Models\TicketTier;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        $base = $this->filteredQuery($request);

        $transactions = (clone $base)
            ->with(['order.event', 'order.user'])
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'revenue' => (clone $base)->where('transaction_status', 'settlement')->sum('gross_amount'),
            'pending' => (clone $base)->where('transaction_status', 'pending')->sum('gross_amount'),
            'settled_count' => (clone $base)->where('transaction_status', 'settlement')->count(),
            'failed_count' => (clone $base)->whereIn('transaction_status', ['deny', 'expire', 'cancel'])->count(),
        ];

        $discounts = Order::where('status', 'paid')
            ->when($request->filled('event_id'), fn ($q) => $q->where('event_id', $request->event_id))
            ->sum('discount_amount');

        $eventRevenue = Order::select('event_id', DB::raw('SUM(total_amount) as revenue'), DB::raw('COUNT(*) as orders'))
            ->where('status', 'paid')
            ->groupBy('event_id')
            ->with('event:id,title')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        $events = Event::orderBy('title')->get(['id', 'title']);

        return view('admin.finances.index', compact('transactions', 'summary', 'discounts', 'eventRevenue', 'events'));
    }

    public function export(Request $request)
    {
        $rows = $this->filteredQuery($request)
            ->with(['order.event', 'order.user'])
            ->orderByDesc('created_at')
            ->get();

        $filename = 'transaksi-eventtix-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
            fputcsv($out, ['Tanggal', 'Kode Order', 'Event', 'Pembeli', 'Email', 'Metode', 'Promo', 'Jumlah', 'Status']);
            foreach ($rows as $t) {
                fputcsv($out, [
                    $t->created_at?->format('Y-m-d H:i:s'),
                    $t->order_code,
                    $t->order->event->title ?? '-',
                    $t->order->user->name ?? '-',
                    $t->order->user->email ?? '-',
                    $t->payment_type ?? '-',
                    $t->order->promo_code ?? '-',
                    (int) $t->gross_amount,
                    $t->transaction_status,
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function showOrder(Order $order)
    {
        $order->load(['user', 'event.venue', 'items.ticketTier', 'transaction', 'tickets.ticketTier']);

        return view('admin.finances.order', compact('order'));
    }

    public function cancelOrder(Order $order)
    {
        if ($order->status !== 'pending') {
            return back()->with('error', 'Hanya order yang masih menunggu pembayaran yang bisa dibatalkan.');
        }

        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $tier = TicketTier::where('id', $item->ticket_tier_id)->lockForUpdate()->first();
                if ($tier) {
                    $tier->update(['sold_count' => max(0, $tier->sold_count - $item->quantity)]);
                }
            }

            if ($order->promo_code) {
                \App\Models\Promo::where('code', $order->promo_code)->where('current_usages', '>', 0)->decrement('current_usages');
            }

            $order->update(['status' => 'cancelled']);
            Transaction::where('order_id', $order->id)->where('transaction_status', 'pending')->update(['transaction_status' => 'cancel']);
        });

        return back()->with('success', 'Order ' . $order->order_code . ' dibatalkan dan kuota tiket dikembalikan.');
    }

    protected function filteredQuery(Request $request)
    {
        return Transaction::query()
            ->when($request->filled('status'), fn ($q) => $q->where('transaction_status', $request->status))
            ->when($request->filled('event_id'), fn ($q) => $q->whereHas('order', fn ($o) => $o->where('event_id', $request->event_id)))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->to))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%' . $request->q . '%';
                $q->where(function ($w) use ($term) {
                    $w->where('order_code', 'like', $term)
                        ->orWhereHas('order.user', fn ($u) => $u->where('name', 'like', $term)->orWhere('email', 'like', $term));
                });
            });
    }
}
