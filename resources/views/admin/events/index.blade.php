@extends('layouts.app')

@section('title', 'Manajemen Event - Admin SeatPulse')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('admin.dashboard') }}" class="text-xs text-indigo-400 font-semibold hover:underline">← Kembali ke Dashboard</a>
            <h1 class="text-3xl font-black text-white mt-1">Manajemen Event & Konser</h1>
        </div>

        <a href="{{ route('admin.events.create') }}" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-2.5 px-5 rounded-xl shadow-lg transition">
            ➕ Buat Event Baru
        </a>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-500/20 border border-emerald-500/50 rounded-xl text-emerald-300 text-sm font-semibold">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-slate-800 border border-slate-700 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-900/80 text-slate-400 uppercase font-mono border-b border-slate-700">
                    <tr>
                        <th class="p-4">Nama Event</th>
                        <th class="p-4">Venue & Kota</th>
                        <th class="p-4">Jadwal Concert</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/60">
                    @forelse($events as $evt)
                        <tr class="hover:bg-slate-700/30 transition">
                            <td class="p-4 font-bold text-white text-sm">
                                <a href="{{ route('events.show', $evt->slug) }}" class="hover:text-indigo-400">
                                    {{ $evt->title }}
                                </a>
                            </td>
                            <td class="p-4 text-slate-300">
                                {{ $evt->venue->name ?? 'Venue' }} ({{ $evt->venue->city ?? '' }})
                            </td>
                            <td class="p-4 text-slate-400">
                                {{ $evt->start_time->format('d M Y, H:i') }} WIB
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                    ● {{ strtoupper($evt->status) }}
                                </span>
                            </td>
                            <td class="p-4 text-center">
                                <form action="{{ route('admin.events.destroy', $evt->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus event ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-400 hover:text-rose-300 text-xs font-bold bg-rose-500/10 hover:bg-rose-500/20 px-3 py-1.5 rounded-lg border border-rose-500/30 transition">
                                        🗑️ Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-500">Belum ada event. Klik "Buat Event Baru" di atas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
