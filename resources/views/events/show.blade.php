@extends('layouts.app')

@section('title', $event->title . ' - EventTix')

@section('content')
@php
    $tiers = $event->ticketTiers;
    $colors = $tiers->pluck('color')->filter()->values();
    if ($colors->isEmpty()) { $colors = collect(['#14130F', '#FF5A1F']); }
    $stops = [];
    foreach ($colors as $i => $c) { $stops[] = "$c " . ($i * 22) . "px, $c " . (($i + 1) * 22) . "px"; }
    $stripe = 'background: repeating-linear-gradient(115deg, ' . implode(', ', $stops) . ');';
    $ended = $event->end_time < now();
@endphp
<div x-data="ticketOrder()" x-cloak>

    <a href="{{ route('events.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-ink mb-6 transition">
        <x-icon name="arrow-left" class="h-4 w-4" /> Semua event
    </a>

    {{-- Hero --}}
    <section class="grid lg:grid-cols-[1.1fr_1fr] gap-8 lg:gap-12 pb-10 border-b border-gray-200">
        <div class="rounded-2xl overflow-hidden border border-gray-200 bg-white self-start">
            <div class="aspect-[16/10] relative" @if(!$event->banner_image) style="{{ $stripe }}" @endif>
                @if($event->banner_image)
                    <img src="{{ Storage::url($event->banner_image) }}" alt="{{ $event->title }}" class="w-full h-full object-cover">
                @endif
            </div>
            <div class="h-2" style="{{ $stripe }}"></div>
        </div>

        <div class="flex flex-col">
            <p class="eyebrow mb-4">{{ $event->start_time->translatedFormat('l, d F Y') }}</p>
            <h1 class="font-display text-4xl md:text-5xl font-bold leading-[1.02]">{{ $event->title }}</h1>

            <dl class="mt-7 divide-y divide-gray-200 border-y border-gray-200 text-sm">
                <div class="flex gap-4 py-3.5">
                    <dt class="w-20 shrink-0 eyebrow pt-0.5">Venue</dt>
                    <dd><p class="font-semibold">{{ $event->venue->name }}</p><p class="text-gray-500">{{ $event->venue->address }}, {{ $event->venue->city }}</p></dd>
                </div>
                <div class="flex gap-4 py-3.5">
                    <dt class="w-20 shrink-0 eyebrow pt-0.5">Waktu</dt>
                    <dd class="font-semibold font-mono">{{ $event->start_time->format('H:i') }} &ndash; {{ $event->end_time->format('H:i') }} WIB</dd>
                </div>
                <div class="flex gap-4 py-3.5">
                    <dt class="w-20 shrink-0 eyebrow pt-0.5">Gelang</dt>
                    <dd class="text-gray-600">Tunjukkan QR di gerbang, lalu tukar dengan gelang sesuai kelas tiketmu.</dd>
                </div>
            </dl>

            @if($event->description)
                <p class="mt-6 text-gray-600 leading-relaxed">{{ $event->description }}</p>
            @endif
        </div>
    </section>

    {{-- Zone map --}}
    <section class="py-10 border-b border-gray-200">
        <div class="flex items-end justify-between mb-5">
            <h2 class="font-display text-2xl font-bold">Denah zona</h2>
            <p class="text-sm text-gray-500 hidden sm:block">Klik zona untuk memilih kelas</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-2xl p-5 md:p-7">
            <div class="w-3/4 mx-auto mb-6 bg-ink text-white text-center py-2.5 rounded-lg">
                <span class="text-[11px] font-semibold tracking-[0.3em] uppercase">Panggung</span>
            </div>
            <div class="grid sm:grid-cols-2 gap-2.5">
                @foreach($tiers as $tier)
                    <button type="button" @click="selectTier({{ $tier->id }})" {{ $tier->available_quota > 0 ? '' : 'disabled' }}
                            class="text-left flex items-center justify-between gap-3 rounded-lg border border-gray-200 bg-paper px-4 py-3 hover:border-ink transition disabled:opacity-50 disabled:hover:border-gray-200">
                        <span class="flex items-center gap-3 min-w-0">
                            <span class="h-8 w-1.5 rounded-full shrink-0" style="background-color: {{ $tier->color }}"></span>
                            <span class="min-w-0">
                                <span class="block font-semibold text-sm truncate">{{ $tier->zone_label ?? $tier->name }}</span>
                                <span class="block text-xs text-gray-500">{{ $tier->name }}</span>
                            </span>
                        </span>
                        <span class="text-xs font-semibold shrink-0 {{ $tier->available_quota > 0 ? 'text-gray-600' : 'text-flame-600' }}">
                            {{ $tier->available_quota > 0 ? $tier->available_quota . ' tersisa' : 'Sold out' }}
                        </span>
                    </button>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Tiers --}}
    <section class="py-10">
        <h2 class="font-display text-2xl font-bold mb-5">Pilih tiket</h2>
        @if($ended)
            <div class="rounded-xl border border-gray-200 bg-white p-6 text-gray-600">Event ini sudah selesai.</div>
        @endif
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($tiers as $tier)
                <article class="relative bg-white border rounded-2xl overflow-hidden transition-colors cursor-pointer flex"
                     :class="selectedTiers[{{ $tier->id }}] ? 'border-ink ring-1 ring-ink' : 'border-gray-200 hover:border-gray-400'"
                     @click="selectTier({{ $tier->id }})">
                    <div class="w-2.5 shrink-0" style="background-color: {{ $tier->color }}"></div>
                    <div class="flex-1 p-5">
                        <div class="flex items-start justify-between gap-3">
                            <h3 class="font-display text-lg font-bold leading-tight">{{ $tier->name }}</h3>
                            <span class="text-[11px] font-semibold text-gray-500 border border-gray-200 rounded-md px-2 py-0.5 shrink-0">Gelang {{ $tier->wristband_color }}</span>
                        </div>

                        <p class="mt-3 font-mono text-2xl font-bold">Rp{{ number_format($tier->price, 0, ',', '.') }}</p>

                        @if($tier->description)
                            <p class="mt-2 text-xs text-gray-500 line-clamp-2">{{ $tier->description }}</p>
                        @endif

                        <div class="mt-4 flex items-center justify-between text-xs text-gray-500">
                            <span class="flex items-center gap-1"><x-icon name="pin" class="h-3 w-3" /> {{ $tier->zone_label ?? '-' }}</span>
                            @if($tier->available_quota > 0)
                                <span class="font-medium">{{ $tier->available_quota }}/{{ $tier->quota }} tersisa</span>
                            @else
                                <span class="font-bold text-flame-600">Sold out</span>
                            @endif
                        </div>

                        @if($tier->available_quota > 0 && !$ended)
                            <div class="mt-4 pt-4 border-t border-dashed border-gray-300 flex items-center justify-between" @click.stop>
                                <span class="text-xs text-gray-500">Jumlah</span>
                                <div class="flex items-center gap-1 border border-gray-300 rounded-lg">
                                    <button type="button" @click="decrementQty({{ $tier->id }})" class="h-8 w-8 flex items-center justify-center text-gray-600 hover:text-ink transition" aria-label="Kurangi">
                                        <x-icon name="minus" class="h-4 w-4" />
                                    </button>
                                    <span class="w-6 text-center font-mono font-bold text-sm" x-text="selectedTiers[{{ $tier->id }}] || 0"></span>
                                    <button type="button" @click="incrementQty({{ $tier->id }}, {{ $tier->available_quota }})" class="h-8 w-8 flex items-center justify-center text-gray-600 hover:text-ink transition" aria-label="Tambah">
                                        <x-icon name="plus" class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    {{-- Checkout bar --}}
    <div x-show="totalItems > 0" x-transition
         class="sticky bottom-4 z-40 bg-ink text-white rounded-2xl p-5 shadow-2xl">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="w-full md:w-auto">
                <p class="eyebrow !text-white/50">Total</p>
                <div class="flex items-baseline gap-3 mt-1.5">
                    <p class="font-mono text-2xl font-bold">Rp<span x-text="finalPrice.toLocaleString('id-ID')"></span></p>
                    <p x-show="discountAmount > 0" class="text-sm text-white/40 line-through font-mono">Rp<span x-text="totalPrice.toLocaleString('id-ID')"></span></p>
                </div>
                <p class="text-xs text-white/50 mt-1" x-text="totalItems + ' tiket dipilih'"></p>

                <div class="mt-3 flex items-center gap-2 max-w-xs">
                    <input type="text" x-model="promoInput" :disabled="appliedPromo !== ''"
                           placeholder="Kode promo"
                           class="w-full bg-white/10 border border-white/15 text-white placeholder:text-white/40 rounded-lg px-3 py-2 text-sm focus:border-flame-500 focus:ring-0 outline-none transition uppercase disabled:opacity-50">
                    <button x-show="appliedPromo === ''" @click="applyPromo()" :disabled="promoInput === '' || loadingPromo"
                            class="px-3.5 py-2 bg-white text-ink text-sm font-semibold rounded-lg hover:bg-paper transition disabled:opacity-40">
                        <span x-show="!loadingPromo">Pakai</span>
                        <svg x-show="loadingPromo" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    </button>
                    <button x-show="appliedPromo !== ''" @click="removePromo()" class="px-3.5 py-2 bg-white/10 hover:bg-white/20 text-white text-sm font-semibold rounded-lg transition">Batal</button>
                </div>
                <p x-show="promoMessage" class="text-xs font-semibold mt-1.5" :class="promoError ? 'text-red-400' : 'text-emerald-400'" x-text="promoMessage"></p>
            </div>
            <button @click="checkout()" :disabled="loading"
                    class="w-full md:w-auto px-7 py-3.5 bg-flame-500 hover:bg-flame-600 text-white font-bold rounded-xl transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                <span x-show="!loading" class="flex items-center gap-2">Checkout <x-icon name="arrow-right" class="h-4 w-4" /></span>
                <span x-show="loading" class="flex items-center gap-2">
                    <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                    Memproses
                </span>
            </button>
        </div>

        <div x-show="alertMessage" x-transition class="mt-3 p-3 rounded-lg text-sm font-semibold"
             :class="alertType === 'error' ? 'bg-red-500/15 text-red-300' : 'bg-emerald-500/15 text-emerald-300'">
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

        promoInput: '',
        appliedPromo: '',
        discountAmount: 0,
        loadingPromo: false,
        promoMessage: '',
        promoError: false,

        get totalItems() {
            return Object.values(this.selectedTiers).reduce((sum, qty) => sum + (qty || 0), 0);
        },

        get totalPrice() {
            let total = 0;
            for (const [tierId, qty] of Object.entries(this.selectedTiers)) {
                total += (this.tierPrices[tierId] || 0) * (qty || 0);
            }
            // Reset promo if items change so total goes to 0 or changes significantly
            if (total === 0 && this.appliedPromo) {
                this.removePromo();
            }
            return total;
        },

        get finalPrice() {
            return Math.max(0, this.totalPrice - this.discountAmount);
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
                this.recalculatePromo();
            }
        },

        decrementQty(tierId) {
            const current = this.selectedTiers[tierId] || 0;
            if (current > 1) {
                this.selectedTiers[tierId] = current - 1;
                this.recalculatePromo();
            } else {
                delete this.selectedTiers[tierId];
                this.recalculatePromo();
            }
        },

        async applyPromo() {
            if (!this.promoInput || this.totalPrice === 0) return;
            
            this.loadingPromo = true;
            this.promoMessage = '';
            
            try {
                const response = await fetch('{{ route("orders.apply_promo") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        promo_code: this.promoInput.toUpperCase(),
                        subtotal: this.totalPrice
                    }),
                });

                const data = await response.json();

                if (data.success) {
                    this.appliedPromo = data.data.promo_code;
                    this.discountAmount = data.data.discount_amount;
                    this.promoError = false;
                    this.promoMessage = data.message;
                } else {
                    this.promoError = true;
                    this.promoMessage = data.message;
                }
            } catch (err) {
                this.promoError = true;
                this.promoMessage = 'Terjadi kesalahan jaringan.';
            } finally {
                this.loadingPromo = false;
            }
        },

        removePromo() {
            this.promoInput = '';
            this.appliedPromo = '';
            this.discountAmount = 0;
            this.promoMessage = '';
            this.promoError = false;
        },

        async recalculatePromo() {
            if (this.appliedPromo) {
                // If subtotal changes, re-apply to get new discount if it's percentage based
                this.promoInput = this.appliedPromo;
                await this.applyPromo();
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
                        promo_code: this.appliedPromo ? this.appliedPromo : null,
                    }),
                });

                const data = await response.json();

                if (data.success && data.data?.redirect_url) {
                    this.alertMessage = 'Mengalihkan ke halaman pembayaran...';
                    this.alertType = 'success';
                    setTimeout(() => window.location.href = data.data.redirect_url, 500);
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
