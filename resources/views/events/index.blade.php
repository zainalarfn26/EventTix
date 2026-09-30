@extends('layouts.app')

@section('title', 'Daftar Event & Konser - SeatPulse')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-extrabold text-white tracking-tight">Consert & Event Lineup</h1>
    <p class="text-slate-400 mt-1">Pilih event impianmu dan pesan posisi kursi favoritmu secara real-time.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($events as $event)
        <div class="bg-slate-800 border border-slate-700 rounded-2xl overflow-hidden shadow-xl hover:border-indigo-500/50 transition duration-300 flex flex-col">
            <div class="h-48 bg-gradient-to-br from-indigo-900 via-purple-900 to-slate-900 p-6 flex flex-col justify-between relative overflow-hidden">
                <div class="absolute -right-10 -bottom-10 opacity-20 text-9xl select-none">🎸</div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 w-fit">
                    ● {{ ucfirst($event->status) }}
                </span>
                <div>
                    <h2 class="text-xl font-bold text-white leading-snug drop-shadow">{{ $event->title }}</h2>
                    <p class="text-xs text-indigo-300 font-medium mt-1">📍 {{ $event->venue->name ?? 'Venue' }} ({{ $event->venue->city ?? '' }})</p>
                </div>
            </div>

            <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
                <p class="text-slate-300 text-sm line-clamp-2">{{ $event->description }}</p>

                <div class="border-t border-slate-700/60 pt-4 flex items-center justify-between text-xs text-slate-400">
                    <div>
                        <span class="block font-medium text-slate-500">WAKTU EVENT</span>
                        <span class="text-slate-200 font-semibold">{{ $event->start_time->format('d M Y, H:i') }} WIB</span>
                    </div>
                    <div>
                        <span class="block font-medium text-slate-500">KAPASITAS</span>
                        <span class="text-slate-200 font-semibold">{{ $event->venue->capacity ?? 0 }} Kursi</span>
                    </div>
                </div>

                <a href="{{ route('events.show', $event->slug) }}" class="w-full text-center bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-2.5 px-4 rounded-xl shadow-lg transition">
                    Pilih Kursi & Pesan Tiket ➔
                </a>
            </div>
        </div>
    @empty
        <div class="col-span-full bg-slate-800/50 border border-dashed border-slate-700 rounded-2xl p-12 text-center text-slate-400">
            <p class="text-lg">Belum ada event yang dipublikasikan saat ini.</p>
        </div>
    @endforelse
</div>
@endsection
