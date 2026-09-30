<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserOrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = $request->user()->orders()
            ->with(['event', 'items.ticketTier', 'tickets', 'transaction'])
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($orders as $order) {
            if ($order->status === 'pending' && now()->greaterThanOrEqualTo($order->expires_at)) {
                app(\App\Services\OrderService::class)->cancelOrder($request->user(), $order->id);
                $order->status = 'cancelled';
            }
        }

        return view('user.orders.index', compact('orders'));
    }

    public function redirectToTicket(string $orderCode)
    {
        $order = \App\Models\Order::where('order_code', $orderCode)->firstOrFail();
        $ticket = $order->tickets()->first();

        if ($ticket) {
            return redirect()->route('tickets.show', $ticket->ticket_code);
        }

        // Fallback to order list if ticket is not generated yet
        return redirect()->route('user.orders.index')->with('success', 'Pesanan Anda berhasil diproses.');
    }
}
