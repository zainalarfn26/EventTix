<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketAdminController extends Controller
{
    public function index(Request $request)
    {
        $tickets = Ticket::with(['event', 'ticketTier', 'user', 'order', 'checkedInBy'])
            ->when($request->filled('event_id'), fn ($q) => $q->where('event_id', $request->event_id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%' . $request->q . '%';
                $q->where(function ($w) use ($term) {
                    $w->where('ticket_code', 'like', $term)
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $term)->orWhere('email', 'like', $term))
                        ->orWhereHas('order', fn ($o) => $o->where('order_code', 'like', $term));
                });
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $events = Event::orderBy('title')->get(['id', 'title']);

        $counts = [
            'total' => Ticket::count(),
            'active' => Ticket::where('status', 'active')->count(),
            'checked_in' => Ticket::where('status', 'checked_in')->count(),
            'cancelled' => Ticket::where('status', 'cancelled')->count(),
        ];

        return view('admin.tickets.index', compact('tickets', 'events', 'counts'));
    }

    public function checkIn(Request $request, Ticket $ticket)
    {
        if ($ticket->status !== 'active') {
            return back()->with('error', 'Hanya tiket berstatus aktif yang bisa di-check-in.');
        }

        $ticket->update([
            'status' => 'checked_in',
            'checked_in_at' => now(),
            'checked_in_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Tiket ' . $ticket->ticket_code . ' berhasil di-check-in manual.');
    }

    public function undoCheckIn(Ticket $ticket)
    {
        if ($ticket->status !== 'checked_in') {
            return back()->with('error', 'Tiket ini belum ber-status check-in.');
        }

        $ticket->update([
            'status' => 'active',
            'checked_in_at' => null,
            'checked_in_by' => null,
        ]);

        return back()->with('success', 'Check-in tiket ' . $ticket->ticket_code . ' dibatalkan. Tiket bisa dipindai ulang.');
    }

    public function cancel(Ticket $ticket)
    {
        if ($ticket->status === 'cancelled') {
            return back()->with('error', 'Tiket sudah dibatalkan.');
        }

        $ticket->update(['status' => 'cancelled']);

        return back()->with('success', 'Tiket ' . $ticket->ticket_code . ' dibatalkan dan tidak bisa dipakai masuk.');
    }

    public function reactivate(Ticket $ticket)
    {
        if ($ticket->status !== 'cancelled') {
            return back()->with('error', 'Hanya tiket yang dibatalkan yang bisa diaktifkan kembali.');
        }

        $ticket->update(['status' => 'active', 'checked_in_at' => null, 'checked_in_by' => null]);

        return back()->with('success', 'Tiket ' . $ticket->ticket_code . ' diaktifkan kembali.');
    }
}
