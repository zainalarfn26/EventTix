@extends('layouts.app')

@section('title', 'Pilih Metode Pembayaran - EventTix')

@section('content')
<div class="max-w-3xl mx-auto" x-data="paymentSelection()">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Pilih Pembayaran</h1>
        <p class="text-gray-500 mt-2">Pilih metode pembayaran yang paling nyaman untuk Anda.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        {{-- Payment Methods --}}
        <div class="md:col-span-2 space-y-6">
            {{-- QRIS --}}
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm hover:border-indigo-300 transition cursor-pointer"
                 :class="selectedMethod === 'qris' ? 'ring-2 ring-indigo-500 border-indigo-500' : ''"
                 @click="selectMethod('qris')">
                <div class="p-5 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-paper border border-gray-200 rounded-xl flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-ink" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900">QRIS</h3>
                            <p class="text-sm text-gray-500">Gopay, OVO, Dana, LinkAja, ShopeePay</p>
                        </div>
                    </div>
                    <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition"
                         :class="selectedMethod === 'qris' ? 'border-indigo-600' : 'border-gray-300'">
                        <div class="w-3 h-3 rounded-full bg-indigo-600 transition-transform scale-0"
                             :class="selectedMethod === 'qris' ? 'scale-100' : ''"></div>
                    </div>
                </div>
            </div>

            {{-- Virtual Accounts --}}
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
                <div class="p-5 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="font-bold text-gray-900">Transfer Virtual Account</h3>
                    <p class="text-sm text-gray-500">Transfer praktis dari bank pilihan Anda</p>
                </div>
                <div class="divide-y divide-gray-100">
                    <template x-for="bank in banks" :key="bank.code">
                        <div class="p-5 flex items-center justify-between hover:bg-indigo-50/30 transition cursor-pointer"
                             @click="selectMethod('va', bank.code)">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center font-bold text-gray-700 uppercase" x-text="bank.code"></div>
                                <div>
                                    <h3 class="font-bold text-gray-900" x-text="bank.name"></h3>
                                </div>
                            </div>
                            <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition"
                                 :class="selectedMethod === 'va' && selectedBank === bank.code ? 'border-indigo-600' : 'border-gray-300'">
                                <div class="w-3 h-3 rounded-full bg-indigo-600 transition-transform scale-0"
                                     :class="selectedMethod === 'va' && selectedBank === bank.code ? 'scale-100' : ''"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- Order Summary --}}
        <div class="md:col-span-1">
            <div class="sticky top-24 bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                <h3 class="font-bold text-gray-900 mb-4">Ringkasan Pesanan</h3>
                <div class="space-y-4 mb-6">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Order ID</span>
                        <span class="font-semibold text-gray-900">{{ $order->order_code }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Acara</span>
                        <span class="font-semibold text-gray-900 truncate ml-4">{{ $order->event->title }}</span>
                    </div>
                    <div class="border-t border-dashed border-gray-200 my-4"></div>
                    <div class="flex justify-between items-end">
                        <span class="text-gray-500 text-sm">Total Bayar</span>
                        <span class="text-2xl font-mono font-bold text-ink">Rp{{ number_format($order->total_amount, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div x-show="errorMessage" x-cloak class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-600 text-sm font-medium" x-text="errorMessage"></div>

                <button @click="processPayment()"
                        :disabled="!selectedMethod || loading"
                        class="w-full py-3.5 px-4 bg-gray-900 hover:bg-black text-white font-bold rounded-xl transition shadow-md disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                    <span x-show="!loading">Bayar Sekarang</span>
                    <span x-show="loading" class="flex items-center gap-2">
                        <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                        Memproses...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function paymentSelection() {
    return {
        selectedMethod: null,
        selectedBank: null,
        loading: false,
        errorMessage: '',
        banks: [
            { code: 'bca', name: 'BCA Virtual Account' },
            { code: 'mandiri', name: 'Mandiri Virtual Account' },
            { code: 'bni', name: 'BNI Virtual Account' },
            { code: 'bri', name: 'BRI Virtual Account' },
        ],

        selectMethod(method, bank = null) {
            this.selectedMethod = method;
            this.selectedBank = bank;
            this.errorMessage = '';
        },

        async processPayment() {
            if (!this.selectedMethod) return;

            this.loading = true;
            this.errorMessage = '';

            try {
                const response = await fetch('{{ route("orders.charge", $order->order_code) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        payment_type: this.selectedMethod,
                        bank: this.selectedBank,
                    }),
                });

                const data = await response.json();

                if (data.success && data.data.redirect_url) {
                    window.location.href = data.data.redirect_url;
                } else {
                    this.errorMessage = data.message || 'Gagal memproses pembayaran.';
                    this.loading = false;
                }
            } catch (err) {
                this.errorMessage = 'Terjadi kesalahan jaringan.';
                this.loading = false;
            }
        }
    }
}
</script>
@endpush
