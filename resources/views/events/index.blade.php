@extends('layouts.app')

@section('title', 'EventTix - Event Ticketing Platform')

@section('content')
<div class="text-center mb-12">
    <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-4">
        Event & Konser Terbaru
    </h1>
    <p class="text-lg text-gray-500 max-w-2xl mx-auto">
        Temukan event konser dan festival terbaik. Beli tiket secara online, dapatkan QR code, dan tukarkan dengan gelang di lokasi!
    </p>
</div>

@if($events->isEmpty())
    <div class="text-center py-20">
        <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
        </div>
        <p class="text-gray-500 text-lg">Belum ada event yang tersedia saat ini.</p>
    </div>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @foreach($events as $event)
        <a href="{{ route('events.show', $event->slug) }}"
           class="group bg-white border border-gray-200 rounded-2xl overflow-hidden hover:shadow-lg hover:border-indigo-200 transition-all duration-300 transform hover:-translate-y-1">

            {{-- Banner Image --}}
            <div class="relative h-48 bg-gradient-to-br from-indigo-100 via-purple-50 to-gray-100 flex items-center justify-center overflow-hidden">
                @if($event->banner_image)
                    <img src="{{ Storage::url($event->banner_image) }}" alt="{{ $event->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                @else
                    <svg class="h-16 w-16 text-gray-300 group-hover:text-indigo-300 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" /></svg>
                @endif

                {{-- Status Badge --}}
                <div class="absolute top-3 right-3">
                    <span class="px-3 py-1 text-xs font-semibold rounded-full shadow-sm
                        @if($event->status === 'published') bg-emerald-100 text-emerald-700
                        @elseif($event->status === 'draft') bg-amber-100 text-amber-700
                        @else bg-gray-100 text-gray-600 @endif">
                        {{ strtoupper($event->status) }}
                    </span>
                </div>
            </div>

            {{-- Event Info --}}
            <div class="p-5">
                <h2 class="text-lg font-bold text-gray-900 group-hover:text-indigo-600 transition-colors line-clamp-2 mb-3">
                    {{ $event->title }}
                </h2>

                <div class="space-y-2 text-sm text-gray-500">
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        <span>{{ $event->venue->name }}, {{ $event->venue->city }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        <span>{{ $event->start_time->translatedFormat('d F Y') }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>{{ $event->start_time->format('H:i') }} - {{ $event->end_time->format('H:i') }} WIB</span>
                    </div>
                </div>

                {{-- Ticket Tier Preview --}}
                @if($event->ticketTiers->isNotEmpty())
                <div class="mt-4 pt-3 border-t border-gray-100">
                    <div class="flex items-center justify-between">
                        <div class="flex gap-1.5">
                            @foreach($event->ticketTiers->take(4) as $tier)
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full text-white" style="background-color: {{ $tier->color }}">
                                    {{ $tier->name }}
                                </span>
                            @endforeach
                            @if($event->ticketTiers->count() > 4)
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-gray-200 text-gray-600">
                                    +{{ $event->ticketTiers->count() - 4 }}
                                </span>
                            @endif
                        </div>
                        <span class="text-sm font-bold text-indigo-600">
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
