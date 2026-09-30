@extends('layouts.app')

@section('title', 'EventTix - Event Ticketing Platform')

@section('content')
<div class="text-center mb-12">
    <h1 class="text-4xl md:text-5xl font-extrabold bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-400 bg-clip-text text-transparent mb-4">
        🎶 Event & Konser Terbaru
    </h1>
    <p class="text-lg text-slate-400 max-w-2xl mx-auto">
        Temukan event konser dan festival terbaik. Beli tiket secara online, dapatkan QR code, dan tukarkan dengan gelang di lokasi!
    </p>
</div>

@if($events->isEmpty())
    <div class="text-center py-20">
        <p class="text-slate-500 text-lg">Belum ada event yang tersedia saat ini.</p>
    </div>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @foreach($events as $event)
        <a href="{{ route('events.show', $event->slug) }}"
           class="group bg-slate-800/60 border border-slate-700 rounded-2xl overflow-hidden hover:border-indigo-500/50 hover:shadow-2xl hover:shadow-indigo-500/10 transition-all duration-300 transform hover:-translate-y-1">

            {{-- Banner Image --}}
            <div class="relative h-48 bg-gradient-to-br from-indigo-900/60 via-purple-900/40 to-slate-800 flex items-center justify-center overflow-hidden">
                @if($event->banner_image)
                    <img src="{{ Storage::url($event->banner_image) }}" alt="{{ $event->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                @else
                    <div class="text-6xl opacity-30 group-hover:opacity-50 transition-opacity">🎤</div>
                @endif

                {{-- Status Badge --}}
                <div class="absolute top-3 right-3">
                    <span class="px-3 py-1 text-xs font-bold rounded-full
                        @if($event->status === 'published') bg-emerald-500/90 text-white
                        @elseif($event->status === 'draft') bg-amber-500/90 text-white
                        @else bg-slate-600 text-slate-300 @endif">
                        {{ strtoupper($event->status) }}
                    </span>
                </div>
            </div>

            {{-- Event Info --}}
            <div class="p-5">
                <h2 class="text-lg font-bold text-white group-hover:text-indigo-300 transition-colors line-clamp-2 mb-2">
                    {{ $event->title }}
                </h2>

                <div class="space-y-2 text-sm text-slate-400">
                    <div class="flex items-center gap-2">
                        <span>📍</span>
                        <span>{{ $event->venue->name }}, {{ $event->venue->city }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span>📅</span>
                        <span>{{ $event->start_time->translatedFormat('d F Y') }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span>🕐</span>
                        <span>{{ $event->start_time->format('H:i') }} - {{ $event->end_time->format('H:i') }} WIB</span>
                    </div>
                </div>

                {{-- Ticket Tier Preview --}}
                @if($event->ticketTiers->isNotEmpty())
                <div class="mt-4 pt-3 border-t border-slate-700">
                    <div class="flex items-center justify-between">
                        <div class="flex gap-1.5">
                            @foreach($event->ticketTiers->take(4) as $tier)
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full text-white" style="background-color: {{ $tier->color }}">
                                    {{ $tier->name }}
                                </span>
                            @endforeach
                            @if($event->ticketTiers->count() > 4)
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-slate-600 text-slate-300">
                                    +{{ $event->ticketTiers->count() - 4 }}
                                </span>
                            @endif
                        </div>
                        <span class="text-sm font-bold text-indigo-400">
                            Rp{{ $event->lowest_price }}
                        </span>
                    </div>
                </div>
                @endif
            </div>
        </a>
        @endforeach
    </div>
@endif
@endsection
