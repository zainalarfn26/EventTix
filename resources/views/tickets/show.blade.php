@extends('layouts.app')

@section('title', 'E-Ticket: ' . $ticket->ticket_code . ' - EventTix')

@section('content')
<div class="max-w-lg mx-auto">
    <div class="mb-6">
        <a href="{{ route('user.orders.index') }}" class="text-sm text-indigo-600 hover:text-indigo-500 inline-flex items-center gap-1 font-medium">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            Kembali ke Daftar Tiket Saya
        </a>
    </div>

    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm">

        {{-- Ticket Header --}}
        <div class="p-6 text-center border-b border-dashed border-gray-300" style="background: linear-gradient(135deg, {{ $ticket->ticketTier->color }}10, transparent);">
            <div class="w-14 h-14 bg-indigo-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                <svg class="h-7 w-7 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
            </div>
            <h1 class="text-2xl font-extrabold text-gray-900">E-TICKET</h1>
            <p class="text-sm text-gray-500 font-mono mt-1">{{ $ticket->ticket_code }}</p>
        </div>

        {{-- Event Info --}}
        <div class="p-6 space-y-4">
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Event</p>
                <p class="text-lg font-bold text-gray-900">{{ $ticket->event->title }}</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Tanggal</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $ticket->event->start_time->translatedFormat('d F Y') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Waktu</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $ticket->event->start_time->format('H:i') }} WIB</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Venue</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $ticket->event->venue->name }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Kota</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $ticket->event->venue->city }}</p>
                </div>
            </div>

            {{-- Ticket Tier Info --}}
            <div class="bg-gray-50 rounded-xl p-4 flex items-center justify-between border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-sm shadow-sm" style="background-color: {{ $ticket->ticketTier->color }}">
                        {{ substr($ticket->ticketTier->name, 0, 2) }}
                    </div>
                    <div>
                        <p class="font-bold text-gray-900">Kelas {{ $ticket->ticketTier->name }}</p>
                        <p class="text-xs text-gray-500">{{ $ticket->ticketTier->zone_label }}</p>
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
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Nama Pemegang Tiket</p>
                <p class="text-sm font-semibold text-gray-800">{{ $ticket->user->name }}</p>
            </div>
        </div>

        {{-- QR Code --}}
        <div class="p-6 border-t border-dashed border-gray-300 text-center relative">
            @if($ticket->status === 'checked_in')
                <div class="absolute inset-0 bg-white/90 backdrop-blur-sm flex flex-col items-center justify-center z-10 border-t border-dashed border-gray-300">
                    <div class="w-14 h-14 bg-emerald-100 rounded-full flex items-center justify-center mb-3">
                        <svg class="h-7 w-7 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    </div>
                    <h2 class="text-2xl font-extrabold text-emerald-600 tracking-widest uppercase">TERPAKAI</h2>
                    <p class="text-sm text-gray-600 mt-2 font-medium bg-gray-100 px-4 py-1.5 rounded-full">Telah ditukar: {{ $ticket->checked_in_at->format('d M Y, H:i') }}</p>
                </div>
            @endif

            <p class="text-xs text-gray-400 mb-3 uppercase tracking-wider">Scan QR Code untuk Check-in & Tukar Gelang</p>
            <div class="inline-block bg-white p-4 rounded-xl border border-gray-200 {{ $ticket->status === 'checked_in' ? 'opacity-20 grayscale' : '' }}">
                {!! $qrCodeSvg !!}
            </div>
            <p class="mt-3 text-xs text-gray-400 font-mono font-bold">{{ $ticket->ticket_code }}</p>
        </div>

        {{-- Status --}}
        <div class="p-4 text-center border-t border-gray-200">
            @if($ticket->status === 'checked_in')
                <span class="px-4 py-2 rounded-full text-sm font-semibold bg-emerald-100 text-emerald-700">
                    Sudah Check-in — {{ $ticket->checked_in_at->format('d M Y H:i') }}
                </span>
            @elseif($ticket->status === 'active')
                <span class="px-4 py-2 rounded-full text-sm font-semibold bg-blue-100 text-blue-700">
                    Tiket Aktif — Tunjukkan QR saat masuk venue
                </span>
            @else
                <span class="px-4 py-2 rounded-full text-sm font-semibold bg-red-100 text-red-700">
                    Tiket Dibatalkan
                </span>
            @endif
        </div>

        {{-- Instructions --}}
        <div class="p-5 bg-gray-50 border-t border-gray-200">
            <p class="text-xs text-gray-500 leading-relaxed">
                <strong class="text-indigo-600">Cara Penukaran Tiket:</strong><br>
                1. Datang ke venue pada hari H event<br>
                2. Tunjukkan QR Code di atas ke petugas (organizer)<br>
                3. Petugas akan scan QR Code Anda<br>
                4. Setelah terverifikasi, Anda akan menerima <strong class="text-gray-700">gelang warna {{ $ticket->ticketTier->wristband_color ?? $ticket->ticketTier->name }}</strong><br>
                5. Gunakan gelang tersebut untuk memasuki zona <strong class="text-gray-700">{{ $ticket->ticketTier->zone_label ?? $ticket->ticketTier->name }}</strong>
            </p>
        </div>
    </div>
</div>
@endsection
