@extends('layouts.app')

@section('title', 'Instruksi Pembayaran - EventTix')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-3xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="bg-gray-900 px-6 py-8 text-center">
            <h1 class="text-2xl font-extrabold text-white">Menunggu Pembayaran</h1>
            <p class="text-gray-400 mt-2 text-sm">Selesaikan pembayaran sebelum waktu habis</p>
            
            <div class="mt-6 inline-block bg-white/10 backdrop-blur border border-white/20 rounded-xl px-6 py-3">
                <div class="text-3xl font-mono font-bold text-white tracking-widest"
                     x-data="countdown('{{ $order->expires_at->toIso8601String() }}', '{{ $order->order_code }}')"
                     x-text="timeLeft">
                    00:00:00
                </div>
            </div>
        </div>

        <div class="p-8">
            <div class="text-center mb-8">
                <p class="text-sm text-gray-500 font-medium mb-1">Total Pembayaran</p>
                <p class="text-4xl font-extrabold text-gray-900">Rp{{ number_format($order->total_amount, 0, ',', '.') }}</p>
            </div>

            @php
                $payload = $transaction->raw_payload ?? [];
            @endphp

            @if($transaction->payment_type === 'qris')
                {{-- QRIS Instruction --}}
                <div class="flex flex-col items-center">
                    <div class="w-full max-w-xs bg-gray-50 border border-gray-200 rounded-2xl p-6 text-center mb-6">
                        <p class="font-bold text-gray-900 mb-4">Scan QRIS</p>
                        @php
                            $qrUrl = '';
                            if (isset($payload['actions'])) {
                                foreach($payload['actions'] as $action) {
                                    if ($action['name'] === 'generate-qr-code') {
                                        $qrUrl = $action['url'];
                                        break;
                                    }
                                }
                            }
                        @endphp
                        
                        @if($qrUrl)
                            <img src="{{ $qrUrl }}" alt="QRIS" class="w-full aspect-square object-cover rounded-xl bg-white border border-gray-100 p-2">
                        @else
                            <div class="w-full aspect-square rounded-xl bg-gray-200 flex items-center justify-center text-gray-400">QR tidak tersedia</div>
                        @endif
                    </div>
                    
                    <div class="w-full space-y-4">
                        <div class="flex gap-4 items-start">
                            <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 font-bold flex items-center justify-center shrink-0">1</div>
                            <p class="text-gray-600 text-sm pt-1">Buka aplikasi pembayaran (Gopay, OVO, Dana, LinkAja, BCA mobile, dll).</p>
                        </div>
                        <div class="flex gap-4 items-start">
                            <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 font-bold flex items-center justify-center shrink-0">2</div>
                            <p class="text-gray-600 text-sm pt-1">Pilih menu Scan / Bayar lalu arahkan kamera ke kode QR di atas.</p>
                        </div>
                        <div class="flex gap-4 items-start">
                            <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 font-bold flex items-center justify-center shrink-0">3</div>
                            <p class="text-gray-600 text-sm pt-1">Periksa detail pembayaran dan masukkan PIN Anda.</p>
                        </div>
                    </div>
                </div>
            @else
                {{-- Virtual Account Instruction --}}
                <div class="flex flex-col items-center">
                    <div class="w-full bg-gray-50 border border-gray-200 rounded-2xl p-6 mb-6 text-center">
                        <p class="font-bold text-gray-900 uppercase mb-2">{{ $transaction->payment_type }} Virtual Account</p>
                        @php
                            $vaNumber = $payload['va_numbers'][0]['va_number'] ?? ($payload['permata_va_number'] ?? 'N/A');
                        @endphp
                        
                        <div class="flex items-center justify-center gap-3">
                            <code class="text-3xl font-extrabold text-indigo-600 tracking-wider" id="va-number">{{ $vaNumber }}</code>
                            <button onclick="navigator.clipboard.writeText('{{ $vaNumber }}'); Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Nomor VA Tersalin!', showConfirmButton: false, timer: 1500, background: '#f8fafc', color: '#1e293b' })" class="p-2 text-gray-400 hover:text-indigo-600 bg-white rounded-lg border border-gray-200 shadow-sm transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                            </button>
                        </div>
                    </div>

                    <div class="w-full space-y-4">
                        <div class="flex gap-4 items-start">
                            <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 font-bold flex items-center justify-center shrink-0">1</div>
                            <p class="text-gray-600 text-sm pt-1">Gunakan ATM, m-Banking, atau Internet Banking pilihan Anda.</p>
                        </div>
                        <div class="flex gap-4 items-start">
                            <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 font-bold flex items-center justify-center shrink-0">2</div>
                            <p class="text-gray-600 text-sm pt-1">Pilih menu Transfer > Ke Rekening Virtual Account.</p>
                        </div>
                        <div class="flex gap-4 items-start">
                            <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 font-bold flex items-center justify-center shrink-0">3</div>
                            <p class="text-gray-600 text-sm pt-1">Masukkan Nomor Virtual Account di atas dan selesaikan pembayaran.</p>
                        </div>
                    </div>
                </div>
            @endif

            @if(config('services.midtrans.server_key') === 'SB-Mid-server-dummy-key' || env('APP_ENV') === 'local')
                <div class="mt-8 pt-6 border-t border-gray-100">
                    <p class="text-xs text-gray-400 mb-3">🛠 Mode Development: Anda dapat mensimulasikan pembayaran berhasil</p>
                    <button onclick="simulatePayment()" class="px-4 py-2 bg-emerald-100 hover:bg-emerald-200 text-emerald-700 font-semibold text-sm rounded-lg transition">
                        Simulasikan Pembayaran Lunas
                    </button>
                </div>
            @endif

            <div class="mt-8 border-t border-gray-100 pt-6 text-center">
                <a href="{{ route('user.orders.index') }}" class="text-indigo-600 hover:text-indigo-800 font-semibold text-sm transition">
                    &larr; Kembali ke Daftar Tiket Saya
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
async function simulatePayment() {
    try {
        const response = await fetch('{{ route("orders.verify") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ order_code: '{{ $order->order_code }}' }),
        });
        const data = await response.json();
        if(data.success) {
            Swal.fire({ icon: 'success', title: 'Sukses', text: 'Simulasi berhasil! Sistem akan otomatis mengalihkan...', confirmButtonColor: '#4f46e5', background: '#ffffff', color: '#111827', customClass: { popup: 'rounded-2xl' }});
        } else {
            Swal.fire({ icon: 'error', title: 'Gagal', text: data.message, confirmButtonColor: '#4f46e5', background: '#ffffff', color: '#111827', customClass: { popup: 'rounded-2xl' }});
        }
    } catch(e) {
        Swal.fire({ icon: 'error', title: 'Oops...', text: 'Error jaringan saat simulasi.', confirmButtonColor: '#4f46e5', background: '#ffffff', color: '#111827', customClass: { popup: 'rounded-2xl' }});
    }
}
function countdown(expiryIso, orderCode) {
    return {
        timeLeft: '00:00:00',
        statusTimer: null,
        init() {
            const expiryTime = new Date(expiryIso).getTime();
            
            const timer = setInterval(() => {
                const now = new Date().getTime();
                const distance = expiryTime - now;
                
                if (distance < 0) {
                    clearInterval(timer);
                    this.timeLeft = 'KEDALUWARSA';
                    if (this.statusTimer) clearInterval(this.statusTimer);
                    return;
                }
                
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                
                this.timeLeft = 
                    String(hours).padStart(2, '0') + ':' + 
                    String(minutes).padStart(2, '0') + ':' + 
                    String(seconds).padStart(2, '0');
            }, 1000);

            // Polling status
            this.statusTimer = setInterval(async () => {
                try {
                    const response = await fetch(`/api/orders/${orderCode}/status`);
                    const data = await response.json();
                    if (data.status === 'paid' || data.status === 'settlement') {
                        clearInterval(this.statusTimer);
                        clearInterval(timer);
                        this.timeLeft = 'LUNAS';
                        Swal.fire({
                            icon: 'success',
                            title: 'Pembayaran Berhasil!',
                            text: 'Pembayaran telah dikonfirmasi. Mengalihkan ke tiket Anda...',
                            showConfirmButton: false,
                            timer: 2500,
                            background: '#ffffff',
                            color: '#111827',
                            customClass: { popup: 'rounded-2xl' }
                        }).then(() => {
                            window.location.href = `/orders/${orderCode}/ticket`;
                        });
                    } else if (data.status === 'expired' || data.status === 'cancelled') {
                        clearInterval(this.statusTimer);
                        clearInterval(timer);
                        this.timeLeft = 'DIBATALKAN';
                        Swal.fire({
                            icon: 'warning',
                            title: 'Waktu Habis',
                            text: 'Order ini telah dibatalkan atau kedaluwarsa.',
                            confirmButtonColor: '#4f46e5',
                            background: '#ffffff',
                            color: '#111827',
                            customClass: { popup: 'rounded-2xl' }
                        }).then(() => {
                            window.location.href = `/my-tickets`;
                        });
                    }
                } catch (e) {
                    // Ignore network errors on polling
                }
            }, 5000);
        }
    }
}
</script>
@endpush
