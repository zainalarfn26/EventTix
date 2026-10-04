@extends('layouts.app')

@section('title', 'Tiket Saya - EventTix')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-8">
        <p class="eyebrow mb-3">Akun</p>
        <h1 class="font-display text-4xl font-bold">Tiket saya</h1>
        <p class="text-gray-500 mt-2">Riwayat pembelian dan e-ticket kamu.</p>
    </div>

    <div class="space-y-5">
        @forelse($orders as $order)
            @php
                $statusStyle = ['paid' => 'text-emerald-700 bg-emerald-50 border-emerald-200', 'pending' => 'text-amber-700 bg-amber-50 border-amber-200'][$order->status] ?? 'text-red-700 bg-red-50 border-red-200';
                $statusLabel = ['paid' => 'Lunas', 'pending' => 'Menunggu bayar', 'expired' => 'Kedaluwarsa', 'cancelled' => 'Dibatalkan', 'failed' => 'Gagal'][$order->status] ?? strtoupper($order->status);
            @endphp
            <article class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="p-5 md:p-6 flex flex-col md:flex-row md:items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2.5 mb-2">
                            <span class="text-xs font-mono font-bold text-gray-500">#{{ $order->order_code }}</span>
                            <span class="text-[11px] px-2 py-0.5 rounded-md border font-semibold {{ $statusStyle }}">{{ $statusLabel }}</span>
                        </div>
                        <h2 class="font-display text-xl font-bold leading-snug">{{ $order->event->title }}</h2>
                        <p class="text-sm text-gray-500 mt-1">Dipesan {{ $order->created_at->translatedFormat('d M Y, H:i') }}</p>
                    </div>
                    <div class="md:text-right shrink-0">
                        <p class="eyebrow mb-1.5">Total</p>
                        <p class="font-mono text-xl font-bold">Rp{{ number_format($order->total_amount, 0, ',', '.') }}</p>

                        @if($order->status === 'pending')
                            <div x-data="countdown('{{ $order->expires_at->toIso8601String() }}')" x-init="start()" class="mt-2">
                                <p class="text-xs text-amber-700">Sisa waktu <strong x-text="timeLeft" class="font-mono text-ink text-sm"></strong></p>
                                @if($order->transaction && $order->transaction->snap_token)
                                <button onclick="payOrder('{{ $order->transaction->snap_token }}', '{{ $order->order_code }}')" class="mt-2.5 px-4 py-2 bg-flame-500 hover:bg-flame-600 text-white text-xs font-bold rounded-lg transition">
                                    Bayar sekarang
                                </button>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <div class="perf"></div>

                <div class="p-5 md:p-6 bg-paper/60">
                    <div class="space-y-2">
                        @foreach($order->items as $item)
                            <div class="flex items-center justify-between text-sm">
                                <span class="flex items-center gap-2.5">
                                    <span class="h-2.5 w-2.5 rounded-full" style="background-color: {{ $item->ticketTier->color }}"></span>
                                    <span>{{ $item->ticketTier->name }} <span class="text-gray-400 font-mono">&times;{{ $item->quantity }}</span></span>
                                </span>
                                <span class="font-mono font-semibold">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>

                    @if($order->status === 'paid' && $order->tickets->isNotEmpty())
                        <div class="mt-5 pt-5 border-t border-gray-200">
                            <p class="eyebrow mb-3">E-ticket</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                                @foreach($order->tickets as $ticket)
                                    <a href="{{ route('tickets.show', $ticket->ticket_code) }}"
                                       class="flex rounded-lg border border-gray-200 bg-white hover:border-ink transition overflow-hidden">
                                        <span class="w-1.5 shrink-0" style="background-color: {{ $ticket->ticketTier->color }}"></span>
                                        <span class="flex-1 flex items-center justify-between gap-2 px-3 py-2.5">
                                            <span class="min-w-0">
                                                <span class="block text-sm font-bold truncate">{{ $ticket->ticketTier->name }}</span>
                                                <span class="block text-[10px] font-mono text-gray-500 truncate">{{ $ticket->ticket_code }}</span>
                                            </span>
                                            @if($ticket->status === 'checked_in')
                                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-gray-100 text-gray-600 font-semibold shrink-0">Terpakai</span>
                                            @else
                                                <x-icon name="qr" class="h-5 w-5 text-gray-400 shrink-0" />
                                            @endif
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </article>
        @empty
            <div class="text-center py-16 bg-white rounded-2xl border border-gray-200">
                <x-icon name="ticket" class="h-10 w-10 text-gray-300 mx-auto mb-4" />
                <h3 class="font-display text-xl font-bold">Belum ada tiket</h3>
                <p class="text-gray-500 mt-1 mb-6">Kamu belum membeli tiket apa pun.</p>
                <a href="{{ route('events.index') }}" class="inline-flex px-5 py-2.5 bg-ink hover:bg-gray-800 text-white text-sm font-semibold rounded-lg transition">Cari event</a>
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
                    Swal.fire({ ...swalBase, icon: 'info', title: 'Menunggu pembayaran', text: 'Selesaikan pembayaranmu untuk menerbitkan tiket.' });
                },
                onError: function(result) {
                    Swal.fire({ ...swalBase, icon: 'error', title: 'Pembayaran gagal', text: 'Silakan coba metode pembayaran lain.' });
                },
                onClose: function() {
                    // Stay on the same page
                }
            });
        } else {
            // Local sandbox fallback simulation
            Swal.fire({ ...swalBase, icon: 'success', title: 'Pembayaran berhasil', text: 'Order ' + orderCode + ' (sandbox) telah dibayar.', timer: 1800, showConfirmButton: false })
                .then(() => { window.location.href = '/orders/' + orderCode + '/ticket'; });
        }
    } else {
        Swal.fire({ ...swalBase, icon: 'warning', title: 'Belum siap', text: 'Sistem pembayaran belum selesai dimuat. Coba muat ulang halaman.' });
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
