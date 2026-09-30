@extends('layouts.app')

@section('title', $event->title . ' - Real-Time Seat Reservation')

@section('content')
<div x-data="seatMapApp()" class="space-y-8">
    <!-- Event Details Header -->
    <div class="bg-slate-800/80 border border-slate-700 rounded-2xl p-6 shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <a href="{{ route('events.index') }}" class="text-xs text-indigo-400 font-semibold hover:underline">← Kembali ke Lineup Event</a>
            <h1 class="text-2xl md:text-3xl font-extrabold text-white mt-1">{{ $event->title }}</h1>
            <p class="text-slate-400 text-sm mt-1">📍 {{ $event->venue->name }} — {{ $event->start_time->format('d M Y, H:i') }} WIB</p>
        </div>

        <!-- Legend Bar -->
        <div class="flex items-center gap-4 bg-slate-900/60 p-3 rounded-xl border border-slate-700/60 text-xs font-medium">
            <div class="flex items-center gap-1.5">
                <span class="w-3.5 h-3.5 rounded-md bg-emerald-500 border border-emerald-400"></span>
                <span>Tersedia</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-3.5 h-3.5 rounded-md bg-amber-500 animate-pulse border border-amber-400"></span>
                <span>Locked (Pending)</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-3.5 h-3.5 rounded-md bg-rose-900/80 border border-rose-700 opacity-60"></span>
                <span>Terjual (Booked)</span>
            </div>
        </div>
    </div>

    <!-- Interactive Seat Arena Layout -->
    <div class="bg-slate-800 border border-slate-700 rounded-2xl p-6 md:p-8 shadow-2xl overflow-x-auto">
        <!-- Stage Visual -->
        <div class="w-full max-w-2xl mx-auto mb-10 text-center">
            <div class="w-full h-10 bg-gradient-to-b from-indigo-500/30 to-purple-600/10 border-t-2 border-indigo-400 rounded-b-3xl flex items-center justify-center shadow-lg shadow-indigo-500/10">
                <span class="text-xs font-black tracking-widest text-indigo-200 uppercase">✨ MAIN STAGE / PANGGUNG UTAMA ✨</span>
            </div>
        </div>

        <!-- Seat Grid -->
        <div class="max-w-3xl mx-auto space-y-4">
            @php
                $groupedSeats = $event->venue->seats->groupBy('row');
            @endphp

            @foreach($groupedSeats as $row => $seatsInRow)
                <div class="flex items-center justify-center gap-2">
                    <span class="w-8 text-center text-xs font-bold text-slate-400">{{ $row }}</span>

                    <div class="flex items-center gap-2">
                        @foreach($seatsInRow as $seat)
                            @php
                                $reservation = $activeReservations->get($seat->id);
                                $status = $reservation ? $reservation->status : 'available';
                            @endphp

                            <button
                                @click="selectSeat({{ json_encode(['id' => $seat->id, 'number' => $seat->seat_number, 'category' => $seat->category, 'price' => number_format($seat->base_price, 0, ',', '.'), 'raw_price' => $seat->base_price, 'status' => $status]) }})"
                                :class="getSeatClass({{ $seat->id }}, '{{ $status }}', '{{ $seat->category }}')"
                                class="w-9 h-9 md:w-11 md:h-11 rounded-lg border text-xs font-bold transition transform hover:scale-105 flex items-center justify-center focus:outline-none shadow-md">
                                {{ $seat->column }}
                            </button>
                        @endforeach
                    </div>

                    <span class="w-8 text-center text-xs font-bold text-slate-400">{{ $row }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Active Selection Modal / Checkout Drawer -->
    <div x-show="selectedSeat !== null" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div class="bg-slate-800 border border-slate-700 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-6 relative">
            <button @click="selectedSeat = null" class="absolute top-4 right-4 text-slate-400 hover:text-white">✕</button>

            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-indigo-400">Konfirmasi Pemesanan Kursi</span>
                <h3 class="text-2xl font-black text-white mt-1" x-text="'Kursi No. ' + selectedSeat?.number"></h3>
                <p class="text-xs text-slate-400 mt-1" x-text="'Kategori: ' + selectedSeat?.category"></p>
            </div>

            <div class="bg-slate-900/80 border border-slate-700 p-4 rounded-xl space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-400">Harga Tiket:</span>
                    <span class="font-bold text-emerald-400" x-text="'Rp ' + selectedSeat?.price"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Proteksi Lock:</span>
                    <span class="text-xs font-semibold text-amber-300">Redis Mutex 10-Min Lock</span>
                </div>
            </div>

            <!-- Error Banner -->
            <div x-show="errorMessage" x-cloak class="p-3 bg-rose-500/20 border border-rose-500/50 rounded-xl text-xs text-rose-300" x-text="errorMessage"></div>

            <button
                @click="processLockAndPay()"
                :disabled="isLoading"
                class="w-full bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 text-white font-bold py-3 rounded-xl shadow-lg transition flex items-center justify-center gap-2">
                <span x-show="!isLoading">🔒 Kunci Kursi & Bayar via Midtrans</span>
                <span x-show="isLoading" x-cloak>Processing...</span>
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function seatMapApp() {
        return {
            eventId: {{ $event->id }},
            selectedSeat: null,
            isLoading: false,
            errorMessage: null,

            selectSeat(seat) {
                if (seat.status === 'confirmed') {
                    alert('Kursi ini sudah terjual!');
                    return;
                }
                this.selectedSeat = seat;
                this.errorMessage = null;
            },

            getSeatClass(seatId, currentStatus, category) {
                if (currentStatus === 'confirmed') {
                    return 'bg-slate-900 border-slate-800 text-slate-600 cursor-not-allowed';
                }
                if (currentStatus === 'pending') {
                    return 'bg-amber-500 border-amber-400 text-slate-950 font-black animate-pulse';
                }
                if (category === 'VVIP') {
                    return 'bg-amber-600/30 border-amber-500 text-amber-300 hover:bg-amber-500 hover:text-slate-950';
                }
                if (category === 'VIP') {
                    return 'bg-purple-600/30 border-purple-500 text-purple-300 hover:bg-purple-500 hover:text-white';
                }
                return 'bg-emerald-600/30 border-emerald-500 text-emerald-300 hover:bg-emerald-500 hover:text-white';
            },

            async processLockAndPay() {
                if (!this.selectedSeat) return;
                this.isLoading = true;
                this.errorMessage = null;

                try {
                    const response = await fetch('{{ route("seats.lock") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            event_id: this.eventId,
                            seat_id: this.selectedSeat.id
                        })
                    });

                    const result = await response.json();

                    if (response.ok && result.success) {
                        const snapToken = result.data.snap_token;
                        const orderId = result.data.order_id;

                        // Trigger Midtrans Snap Popup
                        window.snap.pay(snapToken, {
                            onSuccess: (result) => {
                                alert('Pembayaran Berhasil! Mengalihkan ke E-Ticket...');
                                window.location.href = '/tickets/TKT-SP-SUCCESS';
                            },
                            onPending: (result) => {
                                alert('Pembayaran Pending. Harap lunasi sebelum kurun waktu 10 menit.');
                                window.location.reload();
                            },
                            onError: (result) => {
                                alert('Pembayaran Gagal!');
                                window.location.reload();
                            },
                            onClose: () => {
                                alert('Popup ditutup. Kursi tetap terkunci selama 10 menit.');
                                window.location.reload();
                            }
                        });
                    } else {
                        this.errorMessage = result.message || 'Gagal mengunci kursi.';
                    }
                } catch (err) {
                    this.errorMessage = 'Terjadi kesalahan sistem saat menghubungi server.';
                } finally {
                    this.isLoading = false;
                }
            }
        }
    }
</script>
@endpush
