@extends('layouts.app')

@section('title', 'Kelola Event - Admin EventTix')

@section('content')
<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-extrabold text-white">📅 Kelola Event</h1>
        <a href="{{ route('admin.events.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-bold rounded-xl transition">
            ➕ Buat Event Baru
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-sm font-semibold">
            {{ session('success') }}
        </div>
    @endif

    <div class="space-y-4">
        @forelse($events as $event)
            <div class="bg-slate-800/60 border border-slate-700 rounded-xl p-5 hover:border-slate-600 transition">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <h2 class="text-lg font-bold text-white">{{ $event->title }}</h2>
                            <span class="text-xs px-2 py-0.5 rounded-full font-bold
                                @if($event->status === 'published') bg-emerald-500/20 text-emerald-300
                                @elseif($event->status === 'draft') bg-amber-500/20 text-amber-300
                                @elseif($event->status === 'completed') bg-blue-500/20 text-blue-300
                                @else bg-red-500/20 text-red-300 @endif">
                                {{ strtoupper($event->status) }}
                            </span>
                        </div>
                        <p class="text-sm text-slate-400">
                            📍 {{ $event->venue->name ?? '-' }} · 📅 {{ $event->start_time->translatedFormat('d M Y') }} · 🕐 {{ $event->start_time->format('H:i') }} WIB
                        </p>

                        {{-- Ticket Tiers Summary --}}
                        @if($event->ticketTiers->isNotEmpty())
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach($event->ticketTiers as $tier)
                                    <div class="flex items-center gap-1.5 px-3 py-1 rounded-lg border text-xs font-semibold"
                                         style="border-color: {{ $tier->color }}60; background-color: {{ $tier->color }}10; color: {{ $tier->color }}">
                                        <div class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $tier->color }}"></div>
                                        {{ $tier->name }}:
                                        <span class="text-slate-300">{{ $tier->sold_count }}/{{ $tier->quota }}</span>
                                        <span class="text-slate-500">(Rp{{ number_format($tier->price, 0, ',', '.') }})</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('events.show', $event->slug) }}" target="_blank"
                           class="px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-white text-xs font-bold rounded-lg transition">
                            👁️ Lihat
                        </a>
                        <form action="{{ route('admin.events.destroy', $event) }}" method="POST"
                              onsubmit="return confirm('Yakin ingin menghapus event ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="px-3 py-1.5 bg-red-600/20 hover:bg-red-600/40 text-red-300 text-xs font-bold rounded-lg transition border border-red-500/30">
                                🗑️ Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-10 text-slate-500">Belum ada event. Buat event baru sekarang!</div>
        @endforelse
    </div>
</div>
@endsection
