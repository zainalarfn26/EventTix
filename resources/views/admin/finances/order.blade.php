@extends('layouts.admin')

@section('title', 'Order ' . $order->order_code . ' - Admin EventTix')

@section('content')
@php
    $statusStyle = [
        'paid' => 'bg-emerald-100 text-emerald-700', 'pending' => 'bg-amber-100 text-amber-700',
        'expired' => 'bg-gray-100 text-gray-600', 'cancelled' => 'bg-red-100 text-red-700',
    ][$order->status] ?? 'bg-gray-100 text-gray-600';
@endphp
<div class="max-w-5xl mx-auto">
    <a href="{{ route('admin.finances.index') }}" class="text-sm text-indigo-600 hover:text-indigo-500 inline-flex items-center gap-1 font-medium">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
        Kembali ke Finances
    </a>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mt-2 mb-6">
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold text-gray-900 font-mono">{{ $order->order_code }}</h1>
            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusStyle }}">{{ strtoupper($order->status) }}</span>
        </div>
        @if($order->status === 'pending')
            <form action="{{ route('admin.orders.cancel', $order) }}" method="POST" data-confirm="Order dibatalkan dan kuota tiket dikembalikan." data-confirm-title="Batalkan order {{ $order->order_code }}?" data-confirm-button="Ya, batalkan order">
                @csrf
                <button class="px-4 py-2 bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 text-sm font-semibold rounded-lg transition">Batalkan Order</button>
            </form>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100"><h2 class="text-base font-bold text-gray-900">Item Pesanan</h2></div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500"><tr><th class="px-6 py-3 text-left font-medium">Kelas</th><th class="px-6 py-3 text-right font-medium">Harga</th><th class="px-6 py-3 text-right font-medium">Qty</th><th class="px-6 py-3 text-right font-medium">Subtotal</th></tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($order->items as $item)
                            <tr>
                                <td class="px-6 py-3"><span class="inline-flex items-center gap-2 font-medium text-gray-900"><span class="w-2.5 h-2.5 rounded-full" style="background: {{ $item->ticketTier->color ?? '#94a3b8' }}"></span>{{ $item->ticketTier->name ?? '-' }}</span></td>
                                <td class="px-6 py-3 text-right text-gray-600">Rp{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                <td class="px-6 py-3 text-right text-gray-600">{{ $item->quantity }}</td>
                                <td class="px-6 py-3 text-right font-semibold text-gray-900">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 text-sm">
                        @if($order->discount_amount > 0)
                            <tr><td colspan="3" class="px-6 py-2 text-right text-gray-500">Diskon @if($order->promo_code)(<span class="font-mono">{{ $order->promo_code }}</span>)@endif</td><td class="px-6 py-2 text-right text-emerald-600 font-semibold">-Rp{{ number_format($order->discount_amount, 0, ',', '.') }}</td></tr>
                        @endif
                        <tr><td colspan="3" class="px-6 py-3 text-right font-bold text-gray-900">Total</td><td class="px-6 py-3 text-right font-bold text-gray-900">Rp{{ number_format($order->total_amount, 0, ',', '.') }}</td></tr>
                    </tfoot>
                </table>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100"><h2 class="text-base font-bold text-gray-900">Tiket Diterbitkan</h2></div>
                <div class="divide-y divide-gray-100">
                    @forelse($order->tickets as $ticket)
                        <div class="px-6 py-3 flex items-center justify-between">
                            <div>
                                <a href="{{ route('tickets.show', $ticket->ticket_code) }}" class="font-mono font-bold text-indigo-600 hover:text-indigo-500 text-sm">{{ $ticket->ticket_code }}</a>
                                <p class="text-xs text-gray-500">{{ $ticket->ticketTier->name ?? '-' }}</p>
                            </div>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ticket->status === 'checked_in' ? 'bg-emerald-100 text-emerald-700' : ($ticket->status === 'cancelled' ? 'bg-red-100 text-red-700' : 'bg-indigo-100 text-indigo-700') }}">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span>
                        </div>
                    @empty
                        <p class="px-6 py-8 text-center text-sm text-gray-500">Belum ada tiket (order belum dibayar).</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6 space-y-3 text-sm">
                <h2 class="text-base font-bold text-gray-900">Pembeli</h2>
                <div class="flex items-center gap-3">
                    <img class="h-10 w-10 rounded-full" src="https://ui-avatars.com/api/?name={{ urlencode($order->user->name ?? 'U') }}&background=6366f1&color=fff&size=64" alt="">
                    <div><p class="font-semibold text-gray-900">{{ $order->user->name ?? '-' }}</p><p class="text-xs text-gray-500">{{ $order->user->email ?? '' }}</p></div>
                </div>
            </div>
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6 space-y-2 text-sm">
                <h2 class="text-base font-bold text-gray-900 mb-2">Detail</h2>
                <div class="flex justify-between"><span class="text-gray-500">Event</span><span class="font-medium text-gray-900 text-right max-w-[60%]">{{ $order->event->title ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Dibuat</span><span class="text-gray-800">{{ $order->created_at?->translatedFormat('d M Y H:i') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Dibayar</span><span class="text-gray-800">{{ $order->paid_at?->translatedFormat('d M Y H:i') ?? '-' }}</span></div>
                @if($order->status === 'pending')<div class="flex justify-between"><span class="text-gray-500">Kedaluwarsa</span><span class="text-gray-800">{{ $order->expires_at?->translatedFormat('d M Y H:i') }}</span></div>@endif
                <div class="flex justify-between"><span class="text-gray-500">Metode</span><span class="text-gray-800 uppercase text-xs font-semibold">{{ str_replace('_', ' ', $order->transaction->payment_type ?? '-') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Status Midtrans</span><span class="text-gray-800">{{ ucfirst($order->transaction->transaction_status ?? '-') }}</span></div>
            </div>
        </div>
    </div>
</div>
@endsection
