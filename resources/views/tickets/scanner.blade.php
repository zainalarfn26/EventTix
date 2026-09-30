@extends('layouts.app')

@section('title', 'Gate Scanner Panitia - SeatPulse')

@section('content')
<div x-data="scannerApp()" class="max-w-xl mx-auto space-y-6">
    <div class="bg-slate-800 border border-slate-700 rounded-3xl p-6 md:p-8 shadow-2xl space-y-6">
        <div class="text-center">
            <span class="text-3xl">📱</span>
            <h1 class="text-2xl font-black text-white mt-2">Gate Scanner Panitia</h1>
            <p class="text-xs text-slate-400 mt-1">Masukkan Hash / Code Tiket untuk verifikasi keabsahan penonton.</p>
        </div>

        <form @submit.prevent="submitScan()" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">QR Code Hash / String Code Tiket</label>
                <input
                    type="text"
                    x-model="qrCodeHash"
                    placeholder="Contoh: TKT-SP-XXXXXXXX atau QR Hash SHA256"
                    class="w-full bg-slate-900 border border-slate-700 focus:border-indigo-500 rounded-xl px-4 py-3 text-white text-sm focus:outline-none"
                    required
                />
            </div>

            <button
                type="submit"
                :disabled="isLoading"
                class="w-full bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white font-bold py-3 rounded-xl shadow-lg transition flex items-center justify-center gap-2">
                <span x-show="!isLoading">🔍 Verifikasi & Check-in Tiket</span>
                <span x-show="isLoading" x-cloak>Checking Database...</span>
            </button>
        </form>

        <!-- Success Result Alert -->
        <div x-show="scanResult && scanResult.success" x-cloak class="p-4 bg-emerald-500/20 border border-emerald-500/50 rounded-2xl space-y-2">
            <div class="flex items-center gap-2 text-emerald-400 font-bold text-sm">
                <span>✓</span>
                <span x-text="scanResult?.message"></span>
            </div>
            <div class="text-xs text-slate-300 space-y-1 font-mono border-t border-emerald-500/30 pt-2" x-show="scanResult?.ticket">
                <p x-text="'Kode: ' + scanResult?.ticket?.code"></p>
                <p x-text="'Kursi: ' + scanResult?.ticket?.seat"></p>
                <p x-text="'Penonton: ' + scanResult?.ticket?.customer"></p>
            </div>
        </div>

        <!-- Error / Warning Result Alert -->
        <div x-show="scanResult && !scanResult.success" x-cloak class="p-4 bg-rose-500/20 border border-rose-500/50 rounded-2xl text-rose-300 text-xs font-semibold space-y-1">
            <div class="flex items-center gap-2 text-sm font-bold">
                <span>⚠️</span>
                <span x-text="scanResult?.message"></span>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function scannerApp() {
        return {
            qrCodeHash: '',
            isLoading: false,
            scanResult: null,

            async submitScan() {
                if (!this.qrCodeHash.trim()) return;
                this.isLoading = true;
                this.scanResult = null;

                try {
                    const response = await fetch('{{ route("tickets.scan") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            qr_code_hash: this.qrCodeHash.trim()
                        })
                    });

                    const result = await response.json();
                    this.scanResult = result;
                    if (result.success) {
                        this.qrCodeHash = '';
                    }
                } catch (err) {
                    this.scanResult = {
                        success: false,
                        message: 'Kesalahan jaringan / server saat memverifikasi tiket.'
                    };
                } finally {
                    this.isLoading = false;
                }
            }
        }
    }
</script>
@endpush
