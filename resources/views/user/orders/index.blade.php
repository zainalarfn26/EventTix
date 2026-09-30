@extends('layouts.app')

@section('title', 'Tiket Saya - EventTix')

@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-extrabold text-white mb-6">🎫 Tiket Saya & Riwayat Pembelian</h1>

    <div class="space-y-6">
        @forelse($orders as $order)
            <div class="bg-slate-800/60 border border-slate-700 rounded-2xl overflow-hidden">
                {{-- Order Header --}}
                <div class="p-5 border-b border-slate-700 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <span class="text-xs font-mono text-indigo-400">#{{ $order->order_code }}</span>
                            <span class="text-xs px-2 py-0.5 rounded-full font-bold
                                @if($order->status === 'paid') bg-emerald-500/20 text-emerald-300
                                @elseif($order->status === 'pending') bg-amber-500/20 text-amber-300
                                @else bg-red-500/20 text-red-300 @endif">
                                {{ strtoupper($order->status) }}
                            </span>
                        </div>
                        <h2 class="text-lg font-bold text-white">{{ $order->event->title }}</h2>
                        <p class="text-sm text-slate-400">{{ $order->created_at->translatedFormat('d M Y, H:i') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-slate-400">Total Pembayaran</p>
                        <p class="text-xl font-extrabold text-white">Rp{{ number_format($order->total_amount, 0, ',', '.') }}</p>
                        
                        @if($order->status === 'pending')
                            <div x-data="countdown('{{ $order->expires_at->toIso8601String() }}')" x-init="start()" class="mt-1">
                                <p class="text-xs text-amber-400">Sisa waktu: <strong x-text="timeLeft" class="font-mono text-white text-sm"></strong></p>
                                
                                @if($order->transaction && $order->transaction->snap_token)
                                <button onclick="payOrder('{{ $order->transaction->snap_token }}', '{{ $order->order_code }}')" class="mt-2 px-4 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-lg transition shadow-lg shadow-indigo-500/30">
                                    💳 Bayar Sekarang
                                </button>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Order Items (Tiers bought) --}}
                <div class="bg-slate-900/40 p-5">
                    <p class="text-sm font-semibold text-white mb-3">Detail Pesanan:</p>
                    <div class="space-y-2 mb-4">
                        @foreach($order->items as $item)
                            <div class="flex items-center justify-between text-sm">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full" style="background-color: {{ $item->ticketTier->color }}"></div>
                                    <span class="text-slate-300">{{ $item->ticketTier->name }} <span class="text-slate-500">x{{ $item->quantity }}</span></span>
                                </div>
                                <span class="text-white font-semibold">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>

                    {{-- E-Tickets Links if Paid --}}
                    @if($order->status === 'paid' && $order->tickets->isNotEmpty())
                        <div class="mt-4 pt-4 border-t border-slate-700">
                            <p class="text-sm font-semibold text-emerald-400 mb-3">✅ E-Tickets Anda:</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                                @foreach($order->tickets as $ticket)
                                    <a href="{{ route('tickets.show', $ticket->ticket_code) }}"
                                       class="flex items-center justify-between p-3 rounded-xl border border-slate-600 bg-slate-800 hover:border-indigo-500 hover:bg-slate-700 transition">
                                        <div>
                                            <p class="text-xs font-bold text-white">{{ $ticket->ticketTier->name }}</p>
                                            <p class="text-[10px] font-mono text-slate-400">{{ $ticket->ticket_code }}</p>
                                        </div>
                                        <div>
                                            @if($ticket->status === 'checked_in')
                                                <span class="text-[10px] px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 font-bold border border-emerald-500/30">Terpakai</span>
                                            @else
                                                <span class="text-xl">👁️</span>
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
            <div class="text-center py-12 bg-slate-800/40 rounded-2xl border border-slate-700">
                <div class="text-4xl mb-3">🎫</div>
                <h3 class="text-lg font-bold text-white">Belum Ada Tiket</h3>
                <p class="text-slate-400 mt-1 mb-4">Anda belum melakukan pembelian tiket apapun.</p>
                <a href="{{ route('events.index') }}" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-lg transition">Cari Event</a>
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
            alert("✅ [SANDBOX] Order " + orderCode + " berhasil dibayar!");
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
