@extends('layouts.app')

@section('title', $event->title . ' - EventTix')

@section('content')
<div x-data="ticketOrder()" x-cloak>

    {{-- Event Header --}}
    <div class="mb-8">
        <a href="{{ route('events.index') }}" class="text-sm text-indigo-600 hover:text-indigo-500 mb-4 inline-flex items-center gap-1 font-medium">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            Kembali ke Daftar Event
        </a>
        <div class="mt-4 flex flex-col lg:flex-row gap-8">
            {{-- Banner --}}
            <div class="lg:w-2/5">
                <div class="aspect-video rounded-2xl overflow-hidden bg-gradient-to-br from-indigo-100 via-purple-50 to-gray-100 flex items-center justify-center shadow-sm border border-gray-200">
                    @if($event->banner_image)
                        <img src="{{ Storage::url($event->banner_image) }}" alt="{{ $event->title }}" class="w-full h-full object-cover">
                    @else
                        <svg class="h-20 w-20 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" /></svg>
                    @endif
                </div>
            </div>

            {{-- Event Details --}}
            <div class="lg:w-3/5">
                <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-4">{{ $event->title }}</h1>
                <div class="space-y-3 text-gray-600">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">{{ $event->venue->name }}</p>
                            <p class="text-sm text-gray-500">{{ $event->venue->address }}, {{ $event->venue->city }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-teal-50 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="h-5 w-5 text-teal-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                        <p class="font-semibold text-gray-800">{{ $event->start_time->translatedFormat('l, d F Y') }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-amber-50 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <p class="text-gray-800">{{ $event->start_time->format('H:i') }} — {{ $event->end_time->format('H:i') }} WIB</p>
                    </div>
                </div>
                @if($event->description)
                    <p class="mt-4 text-gray-500 leading-relaxed">{{ $event->description }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Venue Zone Map --}}
    <div class="mb-8">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Denah Zona</h2>
        <div class="bg-white border border-gray-200 rounded-2xl p-6 relative overflow-hidden shadow-sm">
            {{-- Stage --}}
            <div class="w-3/4 mx-auto mb-6 bg-gradient-to-r from-gray-200 via-gray-300 to-gray-200 text-center py-3 rounded-xl">
                <span class="text-sm font-bold text-gray-600 tracking-widest uppercase">Panggung / Stage</span>
            </div>

            {{-- Zone Visualization --}}
            <div class="space-y-3">
                @foreach($event->ticketTiers as $tier)
                    <div class="rounded-xl px-4 py-3 flex items-center justify-between border-2 transition-all cursor-pointer hover:scale-[1.01]"
                         style="background-color: {{ $tier->color }}10; border-color: {{ $tier->color }}40;"
                         @click="selectTier({{ $tier->id }})">
                        <div class="flex items-center gap-3">
                            <div class="w-5 h-5 rounded-full flex-shrink-0 shadow-sm" style="background-color: {{ $tier->color }}"></div>
                            <div>
                                <span class="font-bold text-gray-900 text-sm">{{ $tier->zone_label ?? $tier->name }}</span>
                                <span class="text-xs text-gray-500 ml-2">{{ $tier->name }}</span>
                            </div>
                        </div>
                        <div class="text-right">
                            @if($tier->available_quota > 0)
                                <span class="text-xs px-2.5 py-1 rounded-full font-semibold text-emerald-700 bg-emerald-100">{{ $tier->available_quota }} tersisa</span>
                            @else
                                <span class="text-xs px-2.5 py-1 rounded-full font-semibold text-red-700 bg-red-100">SOLD OUT</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Ticket Tier Selection --}}
    <div class="mb-8">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Pilih Kelas Tiket</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($event->ticketTiers as $tier)
                <div class="bg-white border-2 rounded-2xl p-5 transition-all duration-200 cursor-pointer shadow-sm"
                     :class="selectedTiers[{{ $tier->id }}] ? 'border-indigo-500 shadow-md shadow-indigo-100' : 'border-gray-200 hover:border-gray-300'"
                     @click="selectTier({{ $tier->id }})">

                    {{-- Tier Header --}}
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 rounded-full shadow-sm" style="background-color: {{ $tier->color }}"></div>
                            <span class="text-lg font-bold text-gray-900">{{ $tier->name }}</span>
                        </div>
                        <span class="text-xs px-2.5 py-1 rounded-full font-semibold" style="background-color: {{ $tier->color }}15; color: {{ $tier->color }}">
                            Gelang {{ $tier->wristband_color }}
                        </span>
                    </div>

                    {{-- Price --}}
                    <div class="mb-3">
                        <span class="text-2xl font-extrabold text-gray-900">Rp{{ number_format($tier->price, 0, ',', '.') }}</span>
                        <span class="text-xs text-gray-400">/tiket</span>
                    </div>

                    {{-- Description --}}
                    @if($tier->description)
                        <p class="text-xs text-gray-500 mb-3 line-clamp-2">{{ $tier->description }}</p>
                    @endif

                    {{-- Zone & Availability --}}
                    <div class="flex items-center justify-between text-xs text-gray-500 mb-4">
                        <span class="flex items-center gap-1">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /></svg>
                            {{ $tier->zone_label ?? '-' }}
                        </span>
                        @if($tier->available_quota > 0)
                            <span class="text-emerald-600 font-semibold">{{ $tier->available_quota }}/{{ $tier->quota }} tersisa</span>
                        @else
                            <span class="text-red-500 font-semibold">SOLD OUT</span>
                        @endif
                    </div>

                    {{-- Quantity Selector --}}
                    @if($tier->available_quota > 0)
                        <div class="flex items-center gap-3 justify-center" @click.stop>
                            <button @click="decrementQty({{ $tier->id }})"
                                    class="w-9 h-9 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-lg flex items-center justify-center transition">
                                −
                            </button>
                            <span class="text-xl font-bold text-gray-900 w-8 text-center" x-text="selectedTiers[{{ $tier->id }}] || 0"></span>
                            <button @click="incrementQty({{ $tier->id }}, {{ $tier->available_quota }})"
                                    class="w-9 h-9 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-lg flex items-center justify-center transition">
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
         class="sticky bottom-4 z-40 bg-white/95 backdrop-blur border border-indigo-200 rounded-2xl p-5 shadow-xl">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="w-full md:w-auto">
                <p class="text-sm text-gray-500">Total Pembayaran</p>
                <div class="flex items-center gap-3">
                    <p class="text-2xl font-extrabold text-gray-900">Rp<span x-text="finalPrice.toLocaleString('id-ID')"></span></p>
                    <p x-show="discountAmount > 0" class="text-sm text-gray-400 line-through decoration-red-500">Rp<span x-text="totalPrice.toLocaleString('id-ID')"></span></p>
                </div>
                <p class="text-xs text-gray-400" x-text="totalItems + ' tiket dipilih'"></p>
                
                {{-- Promo Code Input --}}
                <div class="mt-3 flex items-center gap-2 max-w-xs relative z-50">
                    <input type="text" x-model="promoInput" :disabled="appliedPromo !== ''"
                           placeholder="Kode Promo (Opsional)"
                           class="w-full bg-gray-50 border border-gray-300 text-gray-900 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition uppercase disabled:opacity-50">
                    <button x-show="appliedPromo === ''" @click="applyPromo()" :disabled="promoInput === '' || loadingPromo"
                            class="px-3 py-2 bg-gray-900 hover:bg-gray-800 text-white text-sm font-semibold rounded-lg transition disabled:opacity-50">
                        <span x-show="!loadingPromo">Terapkan</span>
                        <span x-show="loadingPromo">⏳</span>
                    </button>
                    <button x-show="appliedPromo !== ''" @click="removePromo()"
                            class="px-3 py-2 bg-red-100 hover:bg-red-200 text-red-600 text-sm font-semibold rounded-lg transition">
                        Batal
                    </button>
                </div>
                <p x-show="promoMessage" class="text-xs font-semibold mt-1" :class="promoError ? 'text-red-500' : 'text-emerald-600'" x-text="promoMessage"></p>
            </div>
            <button @click="checkout()"
                    :disabled="loading"
                    class="w-full md:w-auto px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-lg transition-all shadow-sm disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                <span x-show="!loading">Checkout Sekarang</span>
                <span x-show="loading" class="flex items-center gap-2">
                    <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                    Memproses...
                </span>
            </button>
        </div>

        {{-- Alert Messages --}}
        <div x-show="alertMessage" x-transition class="mt-3 p-3 rounded-lg text-sm font-semibold"
             :class="alertType === 'error' ? 'bg-red-50 text-red-600 border border-red-200' : 'bg-emerald-50 text-emerald-600 border border-emerald-200'">
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
