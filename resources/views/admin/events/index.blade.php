@extends('layouts.admin')

@section('title', 'Kelola Event - Admin EventTix')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Kelola Event</h1>
        <a href="{{ route('admin.events.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition shadow-sm">
            + Buat Event Baru
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    <div class="space-y-4">
        @forelse($events as $event)
            <div class="bg-white border border-gray-200 rounded-xl p-5 hover:shadow-sm transition shadow-sm">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <h2 class="text-lg font-bold text-gray-900">{{ $event->title }}</h2>
                            <span class="text-xs px-2.5 py-0.5 rounded-full font-semibold
                                @if($event->status === 'published') bg-emerald-100 text-emerald-700
                                @elseif($event->status === 'draft') bg-amber-100 text-amber-700
                                @elseif($event->status === 'completed') bg-blue-100 text-blue-700
                                @else bg-red-100 text-red-700 @endif">
                                {{ strtoupper($event->status) }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 flex items-center gap-4">
                            <span class="flex items-center gap-1">
                                <svg class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /></svg>
                                {{ $event->venue->name ?? '-' }}
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                {{ $event->start_time->translatedFormat('d M Y') }}
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                {{ $event->start_time->format('H:i') }} WIB
                            </span>
                        </p>

                        {{-- Ticket Tiers Summary --}}
                        @if($event->ticketTiers->isNotEmpty())
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach($event->ticketTiers as $tier)
                                    <div class="flex items-center gap-1.5 px-3 py-1 rounded-lg border text-xs font-semibold"
                                         style="border-color: {{ $tier->color }}40; background-color: {{ $tier->color }}08; color: {{ $tier->color }}">
                                        <div class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $tier->color }}"></div>
                                        {{ $tier->name }}:
                                        <span class="text-gray-600">{{ $tier->sold_count }}/{{ $tier->quota }}</span>
                                        <span class="text-gray-400">(Rp{{ number_format($tier->price, 0, ',', '.') }})</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('events.show', $event->slug) }}"
                           class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition">
                            Lihat
                        </a>
                        <form action="{{ route('admin.events.destroy', $event) }}" method="POST"
                              onsubmit="return confirm('Yakin ingin menghapus event ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 text-xs font-semibold rounded-lg transition border border-red-200">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-10 text-gray-500 bg-white rounded-xl border border-gray-200">
                <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                </div>
                Belum ada event. Buat event baru sekarang!
            </div>
        @endforelse
    </div>
</div>
@endsection
