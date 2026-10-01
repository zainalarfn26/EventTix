@extends('layouts.app')

@section('title', 'Tiket Saya - EventTix')

@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Tiket Saya & Riwayat Pembelian</h1>

    <div class="space-y-6">
        @forelse($orders as $order)
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm">
                {{-- Order Header --}}
                <div class="p-5 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <span class="text-xs font-mono text-indigo-600 font-semibold">#{{ $order->order_code }}</span>
                            <span class="text-xs px-2.5 py-0.5 rounded-full font-semibold
                                @if($order->status === 'paid') bg-emerald-100 text-emerald-700
                                @elseif($order->status === 'pending') bg-amber-100 text-amber-700
                                @else bg-red-100 text-red-700 @endif">
                                {{ strtoupper($order->status) }}
                            </span>
                        </div>
                        <h2 class="text-lg font-bold text-gray-900">{{ $order->event->title }}</h2>
                        <p class="text-sm text-gray-500">{{ $order->created_at->translatedFormat('d M Y, H:i') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Total Pembayaran</p>
                        <p class="text-xl font-extrabold text-gray-900">Rp{{ number_format($order->total_amount, 0, ',', '.') }}</p>
                        
                        @if($order->status === 'pending')
                            <div x-data="countdown('{{ $order->expires_at->toIso8601String() }}')" x-init="start()" class="mt-1">
                                <p class="text-xs text-amber-600">Sisa waktu: <strong x-text="timeLeft" class="font-mono text-gray-900 text-sm"></strong></p>
                                
                                @if($order->transaction && $order->transaction->snap_token)
                                <button onclick="payOrder('{{ $order->transaction->snap_token }}', '{{ $order->order_code }}')" class="mt-2 px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg transition shadow-sm">
                                    Bayar Sekarang
                                </button>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Order Items (Tiers bought) --}}
                <div class="bg-gray-50 p-5">
                    <p class="text-sm font-semibold text-gray-700 mb-3">Detail Pesanan:</p>
                    <div class="space-y-2 mb-4">
                        @foreach($order->items as $item)
                            <div class="flex items-center justify-between text-sm">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full shadow-sm" style="background-color: {{ $item->ticketTier->color }}"></div>
                                    <span class="text-gray-700">{{ $item->ticketTier->name }} <span class="text-gray-400">x{{ $item->quantity }}</span></span>
                                </div>
                                <span class="text-gray-900 font-semibold">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>

                    {{-- E-Tickets Links if Paid --}}
                    @if($order->status === 'paid' && $order->tickets->isNotEmpty())
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <p class="text-sm font-semibold text-emerald-600 mb-3">E-Tickets Anda:</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                                @foreach($order->tickets as $ticket)
                                    <a href="{{ route('tickets.show', $ticket->ticket_code) }}"
                                       class="flex items-center justify-between p-3 rounded-xl border border-gray-200 bg-white hover:border-indigo-300 hover:shadow-sm transition">
                                        <div>
                                            <p class="text-xs font-bold text-gray-900">{{ $ticket->ticketTier->name }}</p>
                                            <p class="text-[10px] font-mono text-gray-500">{{ $ticket->ticket_code }}</p>
                                        </div>
                                        <div>
                                            @if($ticket->status === 'checked_in')
                                                <span class="text-[10px] px-2 py-0.5 rounded bg-emerald-100 text-emerald-700 font-semibold">Terpakai</span>
                                            @else
                                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                            @endif
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-12 bg-white rounded-2xl border border-gray-200 shadow-sm">
                <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900">Belum Ada Tiket</h3>
                <p class="text-gray-500 mt-1 mb-4">Anda belum melakukan pembelian tiket apapun.</p>
                <a href="{{ route('events.index') }}" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition">Cari Event</a>
            </div>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script>
function payOrder(snapToken, orderCode) {
    if (typeof snap !== 'undefined') {
        if (!snapToken.startsWith('SANDBOX-TOKEN')) {
            snap.pay(snapToken, {
                onSuccess: async function(result) {
                    try {
                        await fetch('{{ route("orders.verify") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ order_code: orderCode }),
                        });
                    } catch (e) {}
                    window.location.href = '/orders/' + orderCode + '/ticket';
                },
                onPending: function(result) {
                    alert("Menunggu pembayaran Anda diselesaikan.");
                },
                onError: function(result) {
                    alert("Pembayaran gagal! Silakan coba metode pembayaran lain.");
                },
                onClose: function() {
                    // Stay on the same page
                }
            });
        } else {
            // Local sandbox fallback simulation
            alert("[SANDBOX] Order " + orderCode + " berhasil dibayar!");
            window.location.href = '/orders/' + orderCode + '/ticket';
        }
    } else {
        alert("Sistem pembayaran Midtrans belum siap dimuat. Coba refresh halaman.");
    }
}

document.addEventListener('alpine:init', () => {
    Alpine.data('countdown', (expiresAt) => ({
        timeLeft: 'Menghitung...',
        timer: null,
        start() {
            const countDownDate = new Date(expiresAt).getTime();
            this.updateTimer(countDownDate);
            this.timer = setInterval(() => this.updateTimer(countDownDate), 1000);
        },
        updateTimer(countDownDate) {
            const now = new Date().getTime();
            const distance = countDownDate - now;

            if (distance <= 0) {
                clearInterval(this.timer);
                this.timeLeft = '00:00';
                setTimeout(() => window.location.reload(), 1500);
                return;
            }

            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            this.timeLeft = minutes.toString().padStart(2, '0') + ':' + seconds.toString().padStart(2, '0');
        }
    }));
});
</script>
@endpush
