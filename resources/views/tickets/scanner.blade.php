@extends('layouts.app')

@section('title', 'Gate Scanner - EventTix')

@section('content')
<div class="max-w-lg mx-auto" x-data="ticketScanner()">
    <div class="text-center mb-6">
        <div class="w-14 h-14 bg-indigo-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
            <svg class="h-7 w-7 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">Gate Scanner</h1>
        <p class="text-sm text-gray-500 mt-1">Scan QR Code tiket dengan kamera</p>
    </div>

    {{-- Camera Scanner --}}
    <div class="bg-white border border-gray-200 rounded-2xl p-4 mb-6 shadow-sm">
        <div id="reader" class="rounded-xl overflow-hidden w-full bg-gray-900 mb-4"></div>
        
        <div class="flex items-center gap-2">
            <div class="relative flex-1">
                <input type="text" x-model="qrInput"
                       @keydown.enter="scanTicket(qrInput)"
                       class="w-full bg-gray-50 border border-gray-300 text-gray-900 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition"
                       placeholder="Atau ketik/paste kode secara manual...">
            </div>
            <button @click="scanTicket(qrInput)"
                    :disabled="loading || !qrInput"
                    class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition disabled:opacity-50 flex-shrink-0">
                <span x-show="!loading">Submit</span>
                <span x-show="loading">
                    <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                </span>
            </button>
        </div>
    </div>

    {{-- Scan Result --}}
    <div x-show="result" x-transition class="mb-6">
        {{-- Success Result --}}
        <template x-if="result && result.success">
            <div class="bg-emerald-50 border-2 border-emerald-200 rounded-2xl p-6 text-center shadow-sm">
                <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="h-8 w-8 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                </div>
                <h2 class="text-xl font-bold text-emerald-700 mb-2" x-text="result.message"></h2>

                <div class="bg-white rounded-xl p-4 mt-4 space-y-3 text-left border border-gray-200">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Nama</span>
                        <span class="text-sm font-bold text-gray-900" x-text="result.ticket?.customer"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Event</span>
                        <span class="text-sm font-bold text-gray-900" x-text="result.ticket?.event"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Kelas Tiket</span>
                        <span class="text-sm font-bold text-gray-900" x-text="result.ticket?.tier"></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Kode Tiket</span>
                        <span class="text-sm font-mono font-bold text-indigo-600" x-text="result.ticket?.code"></span>
                    </div>
                </div>

                {{-- Wristband Instruction --}}
                <div class="mt-4 p-4 rounded-xl border-2 border-dashed text-center"
                     :style="`border-color: ${result.ticket?.tier_color || '#6366f1'}; background-color: ${result.ticket?.tier_color || '#6366f1'}10`">
                    <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Berikan Gelang Warna</p>
                    <p class="text-3xl font-extrabold text-gray-900 tracking-widest" x-text="(result.ticket?.wristband_color || '').toUpperCase()"></p>
                </div>
                
                <p class="mt-4 text-xs text-emerald-600 font-medium animate-pulse">Menyiapkan scanner berikutnya...</p>
            </div>
        </template>

        {{-- Error Result --}}
        <template x-if="result && !result.success">
            <div class="bg-red-50 border-2 border-red-200 rounded-2xl p-6 text-center shadow-sm">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </div>
                <h2 class="text-lg font-bold text-red-700 mb-4" x-text="result.message"></h2>
                
                <p class="mt-4 text-xs text-red-600 font-medium animate-pulse">Menyiapkan scanner berikutnya...</p>
            </div>
        </template>
    </div>

    {{-- Instructions --}}
    <div class="bg-white border border-gray-200 rounded-2xl p-5 mb-6 shadow-sm">
        <h3 class="text-sm font-bold text-indigo-600 mb-2">Panduan Petugas (Organizer)</h3>
        <ol class="text-xs text-gray-500 space-y-1.5 list-decimal list-inside leading-relaxed">
            <li>Arahkan kamera ke QR Code di HP customer</li>
            <li>Jika <strong class="text-emerald-600">VALID</strong>: Berikan <strong class="text-gray-700">gelang sesuai warna</strong></li>
            <li>Jika <strong class="text-red-600">GAGAL</strong>: Tiket tidak valid atau sudah dibatalkan</li>
        </ol>
    </div>

    {{-- Scan History --}}
    <div class="bg-white border border-gray-200 rounded-2xl p-5 mb-10 shadow-sm" x-init="fetchHistory()">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-gray-900">Riwayat Scan Terakhir</h3>
            <button @click="fetchHistory()" class="text-xs text-indigo-600 hover:text-indigo-800 flex items-center gap-1 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" :class="{'animate-spin': loadingHistory}"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                Refresh
            </button>
        </div>
        
        <div class="space-y-3">
            <template x-for="item in history" :key="item.id">
                <div class="flex items-center justify-between p-3 rounded-xl border border-gray-100 hover:bg-gray-50 transition">
                    <div>
                        <p class="text-sm font-bold text-gray-900" x-text="item.customer"></p>
                        <p class="text-xs text-gray-500" x-text="item.code + ' &bull; ' + item.scanned_at"></p>
                    </div>
                    <div class="text-right flex flex-col items-end gap-1">
                        <span class="px-2 py-1 text-[10px] font-bold uppercase rounded-full tracking-wide" 
                              :style="`color: ${item.tier_color || '#6366f1'}; background-color: ${item.tier_color || '#6366f1'}15`" 
                              x-text="item.tier"></span>
                        <span class="text-xs font-semibold text-gray-600">
                            Gelang: <span x-text="(item.wristband_color || '').toUpperCase()"></span>
                        </span>
                    </div>
                </div>
            </template>
            <div x-show="history.length === 0 && !loadingHistory" class="text-center py-4 text-xs text-gray-400">
                Belum ada tiket yang di-scan.
            </div>
        </div>
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
        loadingHistory: false,
        result: null,
        history: [],
        lastScannedHash: null,
        lastScanTime: 0,
        clearResultTimer: null,

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
            const now = new Date().getTime();
            // Prevent duplicate scans of the same ticket within 3 seconds
            if (this.lastScannedHash === decodedText && (now - this.lastScanTime) < 3000) {
                return;
            }
            
            this.lastScannedHash = decodedText;
            this.lastScanTime = now;

            // Pause scanner to prevent multiple requests flying at once
            if (html5QrcodeScanner.getState() === Html5QrcodeScannerState.SCANNING) {
                html5QrcodeScanner.pause();
            }
            this.scanTicket(decodedText);
        },

        onScanFailure(error) {
            // handle scan failure, usually better to ignore and keep scanning
        },

        playAudioFeedback(type) {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gainNode = ctx.createGain();
                
                osc.connect(gainNode);
                gainNode.connect(ctx.destination);
                
                if (type === 'success') {
                    // High-pitched double beep for success
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(880, ctx.currentTime);
                    osc.frequency.setValueAtTime(1200, ctx.currentTime + 0.1);
                    gainNode.gain.setValueAtTime(0.5, ctx.currentTime);
                    gainNode.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.2);
                    osc.start(ctx.currentTime);
                    osc.stop(ctx.currentTime + 0.2);
                } else {
                    // Low-pitched long buzz for error
                    osc.type = 'sawtooth';
                    osc.frequency.setValueAtTime(150, ctx.currentTime);
                    osc.frequency.exponentialRampToValueAtTime(100, ctx.currentTime + 0.4);
                    gainNode.gain.setValueAtTime(0.5, ctx.currentTime);
                    gainNode.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.4);
                    osc.start(ctx.currentTime);
                    osc.stop(ctx.currentTime + 0.4);
                }
            } catch (e) {
                console.warn('Audio Context not supported or blocked');
            }
        },

        async fetchHistory() {
            this.loadingHistory = true;
            try {
                const response = await fetch('{{ route("tickets.history") }}', {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();
                if (data.success) {
                    this.history = data.data;
                }
            } catch (err) {
                console.error("Gagal mengambil riwayat scan.");
            } finally {
                this.loadingHistory = false;
            }
        },

        async scanTicket(hash) {
            if (!hash || !hash.trim()) return;

            this.loading = true;
            
            // Clear existing timer if any
            if (this.clearResultTimer) clearTimeout(this.clearResultTimer);

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
                
                // Play audio feedback based on success status
                if (this.result.success) {
                    this.playAudioFeedback('success');
                    this.fetchHistory(); // Update history dynamically
                } else {
                    this.playAudioFeedback('error');
                }
                
            } catch (err) {
                this.result = { success: false, message: 'Kesalahan jaringan. Coba lagi.' };
                this.playAudioFeedback('error');
            } finally {
                this.loading = false;
                this.qrInput = '';
                
                // Resume camera IMMEDIATELY so they can scan the next person right away
                setTimeout(() => {
                    if (html5QrcodeScanner && html5QrcodeScanner.getState() === Html5QrcodeScannerState.PAUSED) {
                        html5QrcodeScanner.resume();
                    }
                }, 300); // 300ms small buffer

                // Keep UI result visible for 10 seconds before clearing it (so they have time to read)
                this.clearResultTimer = setTimeout(() => {
                    this.result = null;
                }, 10000);
            }
        }
    };
}
</script>

<style>
/* Custom styling for html5-qrcode elements */
#reader { border: none !important; }
#reader button { 
    background-color: #4f46e5; color: white; border: none; padding: 6px 12px; border-radius: 8px; 
    font-size: 12px; font-weight: 600; cursor: pointer; margin: 4px;
}
#reader select {
    background-color: #f9fafb; color: #111827; border: 1px solid #d1d5db; padding: 6px; border-radius: 8px; margin: 4px;
}
#reader__dashboard_section_csr span { color: #6b7280 !important; }
#reader__dashboard_section_swaplink { color: #4f46e5 !important; text-decoration: none; }
</style>
@endpush
