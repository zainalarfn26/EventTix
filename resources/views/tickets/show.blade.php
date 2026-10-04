@extends('layouts.app')

@section('title', 'E-Ticket: ' . $ticket->ticket_code . ' - EventTix')

@section('content')
@php $tier = $ticket->ticketTier; $color = $tier->color ?: '#FF5A1F'; @endphp
<div class="max-w-md mx-auto">
    <a href="{{ route('user.orders.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-ink mb-6 transition">
        <x-icon name="arrow-left" class="h-4 w-4" /> Tiket saya
    </a>

    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">

        {{-- Header --}}
        <div class="bg-ink text-white px-6 pt-6 pb-5">
            <div class="flex items-center justify-between">
                <p class="eyebrow !text-white/50">E-Ticket</p>
                <p class="font-mono text-xs text-white/60">{{ $ticket->ticket_code }}</p>
            </div>
            <h1 class="font-display text-2xl font-bold mt-4 leading-tight">{{ $ticket->event->title }}</h1>
            <p class="text-sm text-white/60 mt-1">{{ $ticket->event->venue->name }}, {{ $ticket->event->venue->city }}</p>
        </div>
        <div class="h-2" style="background-color: {{ $color }}"></div>

        {{-- Details --}}
        <div class="p-6 grid grid-cols-2 gap-x-4 gap-y-5">
            <div>
                <p class="eyebrow mb-1.5">Tanggal</p>
                <p class="text-sm font-semibold">{{ $ticket->event->start_time->translatedFormat('d F Y') }}</p>
            </div>
            <div>
                <p class="eyebrow mb-1.5">Waktu</p>
                <p class="text-sm font-semibold font-mono">{{ $ticket->event->start_time->format('H:i') }} WIB</p>
            </div>
            <div>
                <p class="eyebrow mb-1.5">Kelas</p>
                <p class="text-sm font-semibold flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full" style="background-color: {{ $color }}"></span>{{ $tier->name }}</p>
            </div>
            <div>
                <p class="eyebrow mb-1.5">Gelang</p>
                <p class="text-sm font-semibold">{{ $tier->wristband_color ?? '-' }}</p>
            </div>
            <div>
                <p class="eyebrow mb-1.5">Zona</p>
                <p class="text-sm font-semibold">{{ $tier->zone_label ?? '-' }}</p>
            </div>
            <div>
                <p class="eyebrow mb-1.5">Pemegang</p>
                <p class="text-sm font-semibold truncate">{{ $ticket->user->name }}</p>
            </div>
        </div>

        <div class="perf"></div>

        {{-- QR --}}
        <div class="p-6 text-center relative">
            @if($ticket->status === 'checked_in')
                <div class="absolute inset-0 bg-white/90 backdrop-blur-sm flex flex-col items-center justify-center z-10">
                    <span class="h-12 w-12 rounded-full bg-emerald-600 text-white flex items-center justify-center mb-3"><x-icon name="check" class="h-6 w-6" /></span>
                    <h2 class="font-display text-2xl font-bold tracking-wide uppercase text-emerald-700">Terpakai</h2>
                    <p class="text-sm text-gray-600 mt-2">Ditukar {{ $ticket->checked_in_at->format('d M Y, H:i') }}</p>
                </div>
            @endif

            <div class="inline-block bg-white p-3 rounded-xl border border-gray-200 {{ $ticket->status === 'checked_in' ? 'opacity-20 grayscale' : '' }}">
                {!! $qrCodeSvg !!}
            </div>
            <p class="mt-3 text-xs text-gray-500">Tunjukkan QR ini ke petugas di gerbang</p>
        </div>

        {{-- Status --}}
        <div class="px-6 py-3.5 border-t border-gray-200 bg-paper flex items-center justify-between text-sm">
            <span class="eyebrow">Status</span>
            @if($ticket->status === 'checked_in')
                <span class="font-semibold text-emerald-700">Sudah check-in &middot; {{ $ticket->checked_in_at->format('d M, H:i') }}</span>
            @elseif($ticket->status === 'active')
                <span class="font-semibold text-ink flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-emerald-500"></span>Aktif</span>
            @else
                <span class="font-semibold text-red-600">Dibatalkan</span>
            @endif
        </div>
    </div>

    <div class="mt-6 rounded-xl border border-gray-200 bg-white p-5">
        <p class="eyebrow mb-3">Cara penukaran</p>
        <ol class="space-y-2 text-sm text-gray-600 list-decimal list-inside">
            <li>Datang ke venue pada hari event.</li>
            <li>Tunjukkan QR ke petugas, lalu QR akan discan.</li>
            <li>Terima gelang <strong class="text-ink">{{ $tier->wristband_color ?? $tier->name }}</strong>.</li>
            <li>Masuk ke zona <strong class="text-ink">{{ $tier->zone_label ?? $tier->name }}</strong>.</li>
        </ol>
    </div>
</div>
@endsection
