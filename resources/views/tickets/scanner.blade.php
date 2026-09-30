@extends('layouts.app')

@section('title', 'Gate Scanner - EventTix')

@section('content')
<div class="max-w-lg mx-auto" x-data="ticketScanner()">
    <div class="text-center mb-6">
        <h1 class="text-2xl font-extrabold text-white">📱 Gate Scanner</h1>
        <p class="text-sm text-slate-400 mt-1">Scan QR Code tiket dengan kamera</p>
    </div>

    {{-- Camera Scanner --}}
    <div class="bg-slate-800/60 border border-slate-700 rounded-2xl p-4 mb-6">
        <div id="reader" class="rounded-xl overflow-hidden w-full bg-black mb-4"></div>
        
        <div class="flex items-center gap-2">
            <div class="relative flex-1">
                <input type="text" x-model="qrInput"
                       @keydown.enter="scanTicket(qrInput)"
                       class="w-full bg-slate-900/60 border border-slate-600 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none"
                       placeholder="Atau ketik/paste kode secara manual...">
            </div>
            <button @click="scanTicket(qrInput)"
                    :disabled="loading || !qrInput"
                    class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl transition disabled:opacity-50 flex-shrink-0">
                <span x-show="!loading">Submit</span>
                <span x-show="loading">⏳</span>
            </button>
        </div>
    </div>

    {{-- Scan Result --}}
    <div x-show="result" x-transition class="mb-6">
        {{-- Success Result --}}
        <template x-if="result && result.success">
            <div class="bg-emerald-500/10 border-2 border-emerald-500/50 rounded-2xl p-6 text-center shadow-lg shadow-emerald-500/10">
                <div class="text-5xl mb-3">✅</div>
                <h2 class="text-xl font-extrabold text-emerald-300 mb-2" x-text="result.message"></h2>

                <div class="bg-slate-800/60 rounded-xl p-4 mt-4 space-y-3 text-left border border-slate-700">
                    <div class="flex justify-between">
                        <span class="text-sm text-slate-400">Nama</span>
                        <span class="text-sm font-bold text-white" x-text="result.ticket?.customer"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-slate-400">Event</span>
                        <span class="text-sm font-bold text-white" x-text="result.ticket?.event"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-slate-400">Kelas Tiket</span>
                        <span class="text-sm font-bold text-white" x-text="result.ticket?.tier"></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-400">Kode Tiket</span>
                        <span class="text-sm font-mono font-bold text-indigo-300" x-text="result.ticket?.code"></span>
                    </div>
                </div>

                {{-- Wristband Instruction --}}
                <div class="mt-4 p-4 rounded-xl border-2 border-dashed text-center transform transition hover:scale-105"
                     :style="`border-color: ${result.ticket?.tier_color || '#6366f1'}; background-color: ${result.ticket?.tier_color || '#6366f1'}15`">
                    <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Berikan Gelang Warna</p>
                    <p class="text-3xl font-extrabold text-white tracking-widest" x-text="(result.ticket?.wristband_color || '').toUpperCase()"></p>
                </div>
                
                <button @click="result = null; qrInput = ''; html5QrcodeScanner.resume()" class="mt-4 px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white text-sm font-bold rounded-lg w-full transition">
                    Scan Tiket Selanjutnya
                </button>
            </div>
        </template>

        {{-- Error Result --}}
        <template x-if="result && !result.success">
            <div class="bg-red-500/10 border-2 border-red-500/50 rounded-2xl p-6 text-center shadow-lg shadow-red-500/10">
                <div class="text-5xl mb-3">❌</div>
                <h2 class="text-lg font-extrabold text-red-300 mb-4" x-text="result.message"></h2>
                
                <button @click="result = null; qrInput = ''; html5QrcodeScanner.resume()" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white text-sm font-bold rounded-lg w-full transition">
                    Coba Lagi
                </button>
            </div>
        </template>
    </div>

    {{-- Instructions --}}
    <div class="bg-slate-800/40 border border-slate-700 rounded-2xl p-5 mb-10">
        <h3 class="text-sm font-bold text-indigo-400 mb-2">📌 Panduan Petugas (Organizer)</h3>
        <ol class="text-xs text-slate-400 space-y-1.5 list-decimal list-inside leading-relaxed">
            <li>Arahkan kamera ke QR Code di HP customer</li>
            <li>Jika <strong class="text-emerald-400">VALID</strong>: Berikan <strong class="text-white">gelang sesuai warna</strong></li>
            <li>Jika <strong class="text-red-400">GAGAL</strong>: Tiket tidak valid atau sudah dibatalkan</li>
        </ol>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
let html5QrcodeScanner;

function ticketScanner() {
    return {
        qrInput: '',
        loading: false,
        result: null,

        init() {
            // Initialize camera scanner
            html5QrcodeScanner = new Html5QrcodeScanner(
                "reader",
                { fps: 10, qrbox: {width: 250, height: 250}, aspectRatio: 1.0 },
                /* verbose= */ false
            );
            html5QrcodeScanner.render(this.onScanSuccess.bind(this), this.onScanFailure.bind(this));
        },

        onScanSuccess(decodedText, decodedResult) {
            // Pause scanner to prevent multiple requests
            if (html5QrcodeScanner.getState() === Html5QrcodeScannerState.SCANNING) {
                html5QrcodeScanner.pause();
            }
            this.scanTicket(decodedText);
        },

        onScanFailure(error) {
            // handle scan failure, usually better to ignore and keep scanning
        },

        async scanTicket(hash) {
            if (!hash || !hash.trim()) return;

            this.loading = true;
            this.result = null;

            try {
                const response = await fetch('{{ route("tickets.scan") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ qr_code_hash: hash.trim() }),
                });

                this.result = await response.json();
            } catch (err) {
                this.result = { success: false, message: 'Kesalahan jaringan. Coba lagi.' };
            } finally {
                this.loading = false;
                if (!this.result?.success && html5QrcodeScanner.getState() === Html5QrcodeScannerState.PAUSED) {
                     // Auto resume on network error, keep paused on API response so user can read it
                }
            }
        }
    };
}
</script>

<style>
/* Custom styling for html5-qrcode elements */
#reader { border: none !important; }
#reader button { 
    background-color: #4f46e5; color: white; border: none; padding: 6px 12px; border-radius: 6px; 
    font-size: 12px; font-weight: bold; cursor: pointer; margin: 4px;
}
#reader select {
    background-color: #1e293b; color: white; border: 1px solid #475569; padding: 6px; border-radius: 6px; margin: 4px;
}
#reader__dashboard_section_csr span { color: #94a3b8 !important; }
#reader__dashboard_section_swaplink { color: #818cf8 !important; text-decoration: none; }
</style>
@endpush
