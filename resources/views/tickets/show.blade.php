@extends('layouts.app')

@section('title', 'E-Ticket: ' . $ticket->ticket_code . ' - EventTix')

@section('content')
<div class="max-w-lg mx-auto">
    <div class="mb-6">
        <a href="{{ route('user.orders.index') }}" class="text-sm text-indigo-400 hover:text-indigo-300 inline-flex items-center gap-1 font-bold">
            ← Kembali ke Daftar Tiket Saya
        </a>
    </div>

    <div class="bg-slate-800/60 border border-slate-700 rounded-2xl overflow-hidden">

        {{-- Ticket Header --}}
        <div class="p-6 text-center border-b border-dashed border-slate-600" style="background: linear-gradient(135deg, {{ $ticket->ticketTier->color }}20, transparent);">
            <div class="text-4xl mb-2">🎫</div>
            <h1 class="text-2xl font-extrabold text-white">E-TICKET</h1>
            <p class="text-sm text-slate-400 font-mono mt-1">{{ $ticket->ticket_code }}</p>
        </div>

        {{-- Event Info --}}
        <div class="p-6 space-y-4">
            <div>
                <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Event</p>
                <p class="text-lg font-bold text-white">{{ $ticket->event->title }}</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Tanggal</p>
                    <p class="text-sm font-semibold text-white">{{ $ticket->event->start_time->translatedFormat('d F Y') }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Waktu</p>
                    <p class="text-sm font-semibold text-white">{{ $ticket->event->start_time->format('H:i') }} WIB</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Venue</p>
                    <p class="text-sm font-semibold text-white">{{ $ticket->event->venue->name }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Kota</p>
                    <p class="text-sm font-semibold text-white">{{ $ticket->event->venue->city }}</p>
                </div>
            </div>

            {{-- Ticket Tier Info --}}
            <div class="bg-slate-700/50 rounded-xl p-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-sm" style="background-color: {{ $ticket->ticketTier->color }}">
                        {{ substr($ticket->ticketTier->name, 0, 2) }}
                    </div>
                    <div>
                        <p class="font-bold text-white">Kelas {{ $ticket->ticketTier->name }}</p>
                        <p class="text-xs text-slate-400">{{ $ticket->ticketTier->zone_label }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-xs font-bold px-3 py-1 rounded-full text-white" style="background-color: {{ $ticket->ticketTier->color }}">
                        Gelang {{ $ticket->ticketTier->wristband_color }}
                    </span>
                </div>
            </div>

            {{-- Ticket Holder --}}
            <div>
                <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Nama Pemegang Tiket</p>
                <p class="text-sm font-semibold text-white">{{ $ticket->user->name }}</p>
            </div>
        </div>

        {{-- QR Code --}}
        <div class="p-6 border-t border-dashed border-slate-600 text-center relative">
            @if($ticket->status === 'checked_in')
                <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm flex flex-col items-center justify-center z-10 border-t border-dashed border-slate-600">
                    <span class="text-5xl mb-3 shadow-emerald-500">✅</span>
                    <h2 class="text-2xl font-extrabold text-emerald-400 tracking-widest uppercase">TERPAKAI</h2>
                    <p class="text-sm text-slate-300 mt-2 font-medium bg-slate-800/80 px-4 py-1.5 rounded-full">Telah ditukar: {{ $ticket->checked_in_at->format('d M Y, H:i') }}</p>
                </div>
            @endif

            <p class="text-xs text-slate-400 mb-3 uppercase tracking-wider">Scan QR Code untuk Check-in & Tukar Gelang</p>
            <div class="inline-block bg-white p-4 rounded-xl {{ $ticket->status === 'checked_in' ? 'opacity-20 grayscale' : '' }}">
                {!! $qrCodeSvg !!}
            </div>
            <p class="mt-3 text-xs text-slate-500 font-mono font-bold">{{ $ticket->ticket_code }}</p>
        </div>

        {{-- Status --}}
        <div class="p-4 text-center border-t border-slate-700">
            @if($ticket->status === 'checked_in')
                <span class="px-4 py-2 rounded-full text-sm font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                    ✅ Sudah Check-in — {{ $ticket->checked_in_at->format('d M Y H:i') }}
                </span>
            @elseif($ticket->status === 'active')
                <span class="px-4 py-2 rounded-full text-sm font-bold bg-blue-500/20 text-blue-300 border border-blue-500/30">
                    🎫 Tiket Aktif — Tunjukkan QR saat masuk venue
                </span>
            @else
                <span class="px-4 py-2 rounded-full text-sm font-bold bg-red-500/20 text-red-300 border border-red-500/30">
                    ❌ Tiket Dibatalkan
                </span>
            @endif
        </div>

        {{-- Instructions --}}
        <div class="p-5 bg-slate-900/50 border-t border-slate-700">
            <p class="text-xs text-slate-400 leading-relaxed">
                <strong class="text-indigo-400">📌 Cara Penukaran Tiket:</strong><br>
                1. Datang ke venue pada hari H event<br>
                2. Tunjukkan QR Code di atas ke petugas (organizer)<br>
                3. Petugas akan scan QR Code Anda<br>
                4. Setelah terverifikasi, Anda akan menerima <strong class="text-white">gelang warna {{ $ticket->ticketTier->wristband_color ?? $ticket->ticketTier->name }}</strong><br>
                5. Gunakan gelang tersebut untuk memasuki zona <strong class="text-white">{{ $ticket->ticketTier->zone_label ?? $ticket->ticketTier->name }}</strong>
            </p>
        </div>
    </div>
</div>
@endsection
