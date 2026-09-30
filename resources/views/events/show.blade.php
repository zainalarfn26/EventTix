@extends('layouts.app')

@section('title', $event->title . ' - EventTix')

@section('content')
<div x-data="ticketOrder()" x-cloak>

    {{-- Event Header --}}
    <div class="mb-8">
        <a href="{{ route('events.index') }}" class="text-sm text-indigo-400 hover:text-indigo-300 mb-4 inline-flex items-center gap-1">
            ← Kembali ke Daftar Event
        </a>
        <div class="mt-4 flex flex-col lg:flex-row gap-8">
            {{-- Banner --}}
            <div class="lg:w-2/5">
                <div class="aspect-video rounded-2xl overflow-hidden bg-gradient-to-br from-indigo-900/60 via-purple-900/40 to-slate-800 flex items-center justify-center">
                    @if($event->banner_image)
                        <img src="{{ Storage::url($event->banner_image) }}" alt="{{ $event->title }}" class="w-full h-full object-cover">
                    @else
                        <div class="text-8xl opacity-30">🎤</div>
                    @endif
                </div>
            </div>

            {{-- Event Details --}}
            <div class="lg:w-3/5">
                <h1 class="text-3xl md:text-4xl font-extrabold text-white mb-4">{{ $event->title }}</h1>
                <div class="space-y-3 text-slate-300">
                    <div class="flex items-center gap-3">
                        <span class="text-xl">📍</span>
                        <div>
                            <p class="font-semibold text-white">{{ $event->venue->name }}</p>
                            <p class="text-sm text-slate-400">{{ $event->venue->address }}, {{ $event->venue->city }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xl">📅</span>
                        <p class="font-semibold">{{ $event->start_time->translatedFormat('l, d F Y') }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xl">🕐</span>
                        <p>{{ $event->start_time->format('H:i') }} — {{ $event->end_time->format('H:i') }} WIB</p>
                    </div>
                </div>
                @if($event->description)
                    <p class="mt-4 text-slate-400 leading-relaxed">{{ $event->description }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Venue Zone Map --}}
    <div class="mb-8">
        <h2 class="text-xl font-bold text-white mb-4">🗺️ Denah Zona</h2>
        <div class="bg-slate-800/60 border border-slate-700 rounded-2xl p-6 relative overflow-hidden">
            {{-- Stage --}}
            <div class="w-3/4 mx-auto mb-6 bg-gradient-to-r from-slate-600 via-slate-500 to-slate-600 text-center py-3 rounded-xl">
                <span class="text-sm font-bold text-white tracking-widest uppercase">🎵 Panggung / Stage</span>
            </div>

            {{-- Zone Visualization --}}
            <div class="space-y-3">
                @foreach($event->ticketTiers as $tier)
                    <div class="rounded-xl px-4 py-3 flex items-center justify-between border-2 transition-all cursor-pointer hover:scale-[1.01]"
                         style="background-color: {{ $tier->color }}15; border-color: {{ $tier->color }}60;"
                         @click="selectTier({{ $tier->id }})">
                        <div class="flex items-center gap-3">
                            <div class="w-5 h-5 rounded-full flex-shrink-0" style="background-color: {{ $tier->color }}"></div>
                            <div>
                                <span class="font-bold text-white text-sm">{{ $tier->zone_label ?? $tier->name }}</span>
                                <span class="text-xs text-slate-400 ml-2">{{ $tier->name }}</span>
                            </div>
                        </div>
                        <div class="text-right">
                            @if($tier->available_quota > 0)
                                <span class="text-xs px-2 py-0.5 rounded-full font-bold text-emerald-300 bg-emerald-500/20">{{ $tier->available_quota }} tersisa</span>
                            @else
                                <span class="text-xs px-2 py-0.5 rounded-full font-bold text-red-300 bg-red-500/20">SOLD OUT</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Ticket Tier Selection --}}
    <div class="mb-8">
        <h2 class="text-xl font-bold text-white mb-4">🎫 Pilih Kelas Tiket</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($event->ticketTiers as $tier)
                <div class="bg-slate-800/60 border-2 rounded-2xl p-5 transition-all duration-200 cursor-pointer"
                     :class="selectedTiers[{{ $tier->id }}] ? 'border-indigo-500 shadow-lg shadow-indigo-500/20' : 'border-slate-700 hover:border-slate-500'"
                     @click="selectTier({{ $tier->id }})">

                    {{-- Tier Header --}}
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 rounded-full" style="background-color: {{ $tier->color }}"></div>
                            <span class="text-lg font-extrabold text-white">{{ $tier->name }}</span>
                        </div>
                        <span class="text-xs px-2 py-1 rounded-full font-bold" style="background-color: {{ $tier->color }}30; color: {{ $tier->color }}">
                            🏷️ Gelang {{ $tier->wristband_color }}
                        </span>
                    </div>

                    {{-- Price --}}
                    <div class="mb-3">
                        <span class="text-2xl font-extrabold text-white">Rp{{ number_format($tier->price, 0, ',', '.') }}</span>
                        <span class="text-xs text-slate-400">/tiket</span>
                    </div>

                    {{-- Description --}}
                    @if($tier->description)
                        <p class="text-xs text-slate-400 mb-3 line-clamp-2">{{ $tier->description }}</p>
                    @endif

                    {{-- Zone & Availability --}}
                    <div class="flex items-center justify-between text-xs text-slate-400 mb-4">
                        <span>📍 {{ $tier->zone_label ?? '-' }}</span>
                        @if($tier->available_quota > 0)
                            <span class="text-emerald-400 font-bold">{{ $tier->available_quota }}/{{ $tier->quota }} tersisa</span>
                        @else
                            <span class="text-red-400 font-bold">SOLD OUT</span>
                        @endif
                    </div>

                    {{-- Quantity Selector --}}
                    @if($tier->available_quota > 0)
                        <div class="flex items-center gap-3 justify-center" @click.stop>
                            <button @click="decrementQty({{ $tier->id }})"
                                    class="w-9 h-9 rounded-lg bg-slate-700 hover:bg-slate-600 text-white font-bold text-lg flex items-center justify-center transition">
                                −
                            </button>
                            <span class="text-xl font-bold text-white w-8 text-center" x-text="selectedTiers[{{ $tier->id }}] || 0"></span>
                            <button @click="incrementQty({{ $tier->id }}, {{ $tier->available_quota }})"
                                    class="w-9 h-9 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-lg flex items-center justify-center transition">
                                +
                            </button>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- Order Summary & Checkout --}}
    <div x-show="totalItems > 0" x-transition
         class="sticky bottom-4 z-40 bg-slate-800/95 backdrop-blur border border-indigo-500/50 rounded-2xl p-5 shadow-2xl shadow-indigo-500/20">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
            <div>
                <p class="text-sm text-slate-400">Total Pembayaran</p>
                <p class="text-2xl font-extrabold text-white">Rp<span x-text="totalPrice.toLocaleString('id-ID')"></span></p>
                <p class="text-xs text-slate-500" x-text="totalItems + ' tiket dipilih'"></p>
            </div>
            <button @click="checkout()"
                    :disabled="loading"
                    class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold rounded-xl text-lg transition-all shadow-lg disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                <span x-show="!loading">🎫 Checkout Sekarang</span>
                <span x-show="loading" class="flex items-center gap-2">
                    <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                    Memproses...
                </span>
            </button>
        </div>

        {{-- Alert Messages --}}
        <div x-show="alertMessage" x-transition class="mt-3 p-3 rounded-lg text-sm font-semibold"
             :class="alertType === 'error' ? 'bg-red-500/20 text-red-300 border border-red-500/30' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30'">
            <span x-text="alertMessage"></span>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function ticketOrder() {
    return {
        eventId: {{ $event->id }},
        selectedTiers: {},
        tierPrices: {
            @foreach($event->ticketTiers as $tier)
                {{ $tier->id }}: {{ (int) $tier->price }},
            @endforeach
        },
        loading: false,
        alertMessage: '',
        alertType: 'error',

        get totalItems() {
            return Object.values(this.selectedTiers).reduce((sum, qty) => sum + (qty || 0), 0);
        },

        get totalPrice() {
            let total = 0;
            for (const [tierId, qty] of Object.entries(this.selectedTiers)) {
                total += (this.tierPrices[tierId] || 0) * (qty || 0);
            }
            return total;
        },

        selectTier(tierId) {
            if (!this.selectedTiers[tierId]) {
                this.selectedTiers[tierId] = 1;
            }
        },

        incrementQty(tierId, maxQty) {
            const current = this.selectedTiers[tierId] || 0;
            const max = Math.min(maxQty, 5); // Max 5 per tier per order
            if (current < max) {
                this.selectedTiers[tierId] = current + 1;
            }
        },

        decrementQty(tierId) {
            const current = this.selectedTiers[tierId] || 0;
            if (current > 1) {
                this.selectedTiers[tierId] = current - 1;
            } else {
                delete this.selectedTiers[tierId];
            }
        },

        async checkout() {
            this.alertMessage = '';
            this.loading = true;

            const items = Object.entries(this.selectedTiers)
                .filter(([_, qty]) => qty > 0)
                .map(([tierId, qty]) => ({
                    ticket_tier_id: parseInt(tierId),
                    quantity: qty,
                }));

            if (items.length === 0) {
                this.alertMessage = 'Pilih minimal 1 tiket untuk checkout.';
                this.alertType = 'error';
                this.loading = false;
                return;
            }

            try {
                const response = await fetch('{{ route("orders.checkout") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        event_id: this.eventId,
                        items: items,
                    }),
                });

                const data = await response.json();

                if (data.success && data.data?.snap_token) {
                    // Try to open Midtrans Snap popup
                    if (typeof snap !== 'undefined' && !data.data.snap_token.startsWith('SANDBOX-TOKEN')) {
                        snap.pay(data.data.snap_token, {
                            onSuccess: async (result) => {
                                this.alertMessage = '🎉 Pembayaran berhasil! Memverifikasi tiket Anda...';
                                this.alertType = 'success';
                                
                                // Call verify endpoint manually since localhost cannot receive webhooks
                                try {
                                    await fetch('{{ route("orders.verify") }}', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                            'Accept': 'application/json',
                                        },
                                        body: JSON.stringify({ order_code: data.data.order_code }),
                                    });
                                } catch (e) {}

                                setTimeout(() => window.location.href = '/orders/' + data.data.order_code + '/ticket', 1000);
                            },
                            onPending: (result) => {
                                this.alertMessage = '⏳ Menunggu pembayaran... Order Code: ' + data.data.order_code;
                                this.alertType = 'success';
                            },
                            onError: (result) => {
                                this.alertMessage = '❌ Pembayaran gagal. Silakan coba lagi.';
                                this.alertType = 'error';
                            },
                            onClose: () => {
                                this.alertMessage = '⚠️ Jendela pembayaran ditutup. Order masih aktif selama 10 menit.';
                                this.alertType = 'error';
                                setTimeout(() => window.location.href = '/my-tickets', 2000);
                            },
                        });
                    } else {
                        // Sandbox mode — show success directly
                        this.alertMessage = '✅ [SANDBOX] Order berhasil dibuat! Mengalihkan ke tiket Anda...';
                        this.alertType = 'success';
                        setTimeout(() => window.location.href = '/orders/' + data.data.order_code + '/ticket', 2000);
                    }
                } else {
                    this.alertMessage = data.message || 'Gagal membuat order.';
                    this.alertType = 'error';
                }
            } catch (err) {
                this.alertMessage = 'Terjadi kesalahan jaringan. Silakan coba lagi.';
                this.alertType = 'error';
            } finally {
                this.loading = false;
            }
        }
    };
}
</script>
@endpush
