@extends('layouts.app')

@section('title', 'E-Ticket #' . $ticket->ticket_code . ' - SeatPulse')

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    <div class="bg-gradient-to-b from-indigo-900 to-slate-800 border border-slate-700 rounded-3xl overflow-hidden shadow-2xl">
        <!-- Ticket Header -->
        <div class="p-6 text-center border-b border-slate-700/80">
            <span class="inline-block px-3 py-1 bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 rounded-full text-xs font-bold uppercase tracking-wider mb-2">
                ✓ OFFICIAL E-TICKET CONFIRMED
            </span>
            <h1 class="text-2xl font-black text-white">{{ $ticket->reservation->event->title ?? 'Event Concert' }}</h1>
            <p class="text-xs text-indigo-300 mt-1">📍 {{ $ticket->reservation->event->venue->name ?? 'Venue' }}</p>
        </div>

        <!-- Ticket Body with QR Code -->
        <div class="p-8 flex flex-col items-center justify-center space-y-6 bg-slate-900/60">
            <div class="bg-white p-4 rounded-2xl shadow-xl flex items-center justify-center">
                {!! $qrCodeSvg !!}
            </div>

            <div class="text-center">
                <span class="text-xs font-mono text-slate-400 block uppercase tracking-widest">KODE TIKET UNIK</span>
                <span class="text-2xl font-black tracking-widest text-indigo-400 font-mono">{{ $ticket->ticket_code }}</span>
            </div>

            <div class="w-full grid grid-cols-2 gap-4 border-t border-b border-slate-800 py-4 text-sm">
                <div>
                    <span class="text-slate-400 text-xs block">NOMOR KURSI</span>
                    <span class="font-bold text-white text-lg">{{ $ticket->reservation->seat->seat_number ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 text-xs block">KATEGORI</span>
                    <span class="font-bold text-amber-400 text-lg">{{ $ticket->reservation->seat->category ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 text-xs block">PEMEGANG TIKET</span>
                    <span class="font-semibold text-slate-200">{{ $ticket->reservation->user->name ?? 'Guest' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 text-xs block">STATUS CHECK-IN</span>
                    <span class="{{ $ticket->is_checked_in ? 'text-emerald-400 font-bold' : 'text-slate-400 font-medium' }}">
                        {{ $ticket->is_checked_in ? '✓ Checked-in' : 'Belum Scan' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="p-6 bg-slate-800 text-center flex justify-center gap-4">
            <button onclick="window.print()" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-2.5 px-6 rounded-xl text-sm transition">
                🖨️ Cetak / Simpan PDF
            </button>
            <a href="{{ route('events.index') }}" class="bg-slate-700 hover:bg-slate-600 text-slate-200 font-semibold py-2.5 px-6 rounded-xl text-sm transition">
                Kembali ke Catalog
            </a>
        </div>
    </div>
</div>
@endsection
