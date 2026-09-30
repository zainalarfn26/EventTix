@extends('layouts.app')

@section('title', 'Admin Dashboard - SeatPulse')

@section('content')
<div class="space-y-8">
    <!-- Header Title & Quick Nav -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <span class="text-xs font-bold text-indigo-400 uppercase tracking-widest">Enterprise Panel</span>
            <h1 class="text-3xl font-black text-white mt-1">Admin & Organizer Dashboard</h1>
            <p class="text-slate-400 text-sm mt-1">Ringkasan transaksi, penjualan tiket, dan statistik kedatangan gate real-time.</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.events.index') }}" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs py-2.5 px-4 rounded-xl shadow-lg transition">
                ➕ Kelola & Tambah Event
            </a>
            <a href="{{ route('scanner.index') }}" class="bg-slate-700 hover:bg-slate-600 text-slate-200 font-bold text-xs py-2.5 px-4 rounded-xl border border-slate-600 transition">
                📱 Gate Scanner Panitia
            </a>
        </div>
    </div>

    <!-- Analytics Stat Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Revenue Card -->
        <div class="bg-slate-800 border border-slate-700 p-6 rounded-2xl shadow-xl flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center text-2xl font-bold">
                💰
            </div>
            <div>
                <span class="text-xs font-medium text-slate-400 block uppercase">Total Gross Revenue</span>
                <span class="text-xl font-black text-white">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Tickets Sold Card -->
        <div class="bg-slate-800 border border-slate-700 p-6 rounded-2xl shadow-xl flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-500/20 text-indigo-400 border border-indigo-500/30 flex items-center justify-center text-2xl font-bold">
                🎟️
            </div>
            <div>
                <span class="text-xs font-medium text-slate-400 block uppercase">Tiket Terjual (Issued)</span>
                <span class="text-xl font-black text-white">{{ number_format($totalTicketsSold) }} Tiket</span>
            </div>
        </div>

        <!-- Events Count Card -->
        <div class="bg-slate-800 border border-slate-700 p-6 rounded-2xl shadow-xl flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-500/20 text-purple-400 border border-purple-500/30 flex items-center justify-center text-2xl font-bold">
                🎪
            </div>
            <div>
                <span class="text-xs font-medium text-slate-400 block uppercase">Total Event Aktif</span>
                <span class="text-xl font-black text-white">{{ number_format($totalEventsCount) }} Event</span>
            </div>
        </div>

        <!-- Checked In Count Card -->
        <div class="bg-slate-800 border border-slate-700 p-6 rounded-2xl shadow-xl flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-500/20 text-amber-400 border border-amber-500/30 flex items-center justify-center text-2xl font-bold">
                ✅
            </div>
            <div>
                <span class="text-xs font-medium text-slate-400 block uppercase">Pengunjung Checked-in</span>
                <span class="text-xl font-black text-white">{{ number_format($totalCheckedInCount) }} Orang</span>
            </div>
        </div>
    </div>

    <!-- Recent Transactions Table -->
    <div class="bg-slate-800 border border-slate-700 rounded-2xl p-6 shadow-xl space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-white">📊 Log Transaksi Terbaru</h2>
            <span class="text-xs text-slate-400 font-mono">Real-time Sync</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-900/80 text-slate-400 uppercase font-mono border-b border-slate-700">
                    <tr>
                        <th class="p-3">Order ID</th>
                        <th class="p-3">Event & Kursi</th>
                        <th class="p-3">Customer</th>
                        <th class="p-3">Nominal</th>
                        <th class="p-3">Status Payment</th>
                        <th class="p-3">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/60">
                    @forelse($recentTransactions as $tx)
                        <tr class="hover:bg-slate-700/30 transition">
                            <td class="p-3 font-mono font-bold text-indigo-400">{{ $tx->order_id }}</td>
                            <td class="p-3">
                                <span class="font-semibold text-white block">{{ $tx->reservation->event->title ?? '-' }}</span>
                                <span class="text-slate-400 text-xs">Kursi: {{ $tx->reservation->seat->seat_number ?? '-' }} ({{ $tx->reservation->seat->category ?? '-' }})</span>
                            </td>
                            <td class="p-3 text-slate-200">{{ $tx->reservation->user->name ?? '-' }}</td>
                            <td class="p-3 font-bold text-emerald-400">Rp {{ number_format($tx->gross_amount, 0, ',', '.') }}</td>
                            <td class="p-3">
                                @if($tx->transaction_status === 'settlement')
                                    <span class="px-2 py-0.5 rounded-md bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 font-bold">✓ LUNAS</span>
                                @elseif($tx->transaction_status === 'pending')
                                    <span class="px-2 py-0.5 rounded-md bg-amber-500/20 text-amber-300 border border-amber-500/30 font-bold">⏱️ PENDING</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-md bg-rose-500/20 text-rose-300 border border-rose-500/30 font-bold">{{ strtoupper($tx->transaction_status) }}</span>
                                @endif
                            </td>
                            <td class="p-3 text-slate-400">{{ $tx->created_at->format('d M H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-6 text-center text-slate-500">Belum ada data transaksi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
