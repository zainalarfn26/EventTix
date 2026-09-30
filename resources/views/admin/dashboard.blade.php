@extends('layouts.app')

@section('title', 'Admin Dashboard - EventTix')

@section('content')
<div>
    <h1 class="text-2xl font-extrabold text-white mb-6">👑 {{ auth()->user()->hasRole('admin') ? 'Admin' : 'Organizer' }} Dashboard</h1>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-slate-800/60 border border-slate-700 rounded-xl p-5">
            <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">💰 Total Revenue</p>
            <p class="text-2xl font-extrabold text-emerald-400">Rp{{ number_format($totalRevenue, 0, ',', '.') }}</p>
        </div>
        <div class="bg-slate-800/60 border border-slate-700 rounded-xl p-5">
            <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">🎫 Tiket Terjual</p>
            <p class="text-2xl font-extrabold text-indigo-400">{{ number_format($totalTicketsSold) }}</p>
        </div>
        <div class="bg-slate-800/60 border border-slate-700 rounded-xl p-5">
            <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">📅 Total Event</p>
            <p class="text-2xl font-extrabold text-purple-400">{{ $totalEventsCount }}</p>
        </div>
        <div class="bg-slate-800/60 border border-slate-700 rounded-xl p-5">
            <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">✅ Total Check-in</p>
            <p class="text-2xl font-extrabold text-amber-400">{{ number_format($totalCheckedInCount) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Recent Transactions --}}
        <div class="bg-slate-800/60 border border-slate-700 rounded-xl">
            <div class="px-5 py-4 border-b border-slate-700">
                <h2 class="text-sm font-bold text-white">💳 Transaksi Terbaru</h2>
            </div>
            <div class="divide-y divide-slate-700/50">
                @forelse($recentTransactions as $trx)
                    <div class="px-5 py-3 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-white">{{ $trx->order->user->name ?? '-' }}</p>
                            <p class="text-xs text-slate-400">{{ $trx->order->event->title ?? '-' }}</p>
                            <p class="text-xs text-slate-500 font-mono">{{ $trx->order_code }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold text-white">Rp{{ number_format($trx->gross_amount, 0, ',', '.') }}</p>
                            <span class="text-xs px-2 py-0.5 rounded-full
                                @if($trx->transaction_status === 'settlement') bg-emerald-500/20 text-emerald-300
                                @elseif($trx->transaction_status === 'pending') bg-amber-500/20 text-amber-300
                                @else bg-red-500/20 text-red-300 @endif">
                                {{ strtoupper($trx->transaction_status) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="p-5 text-center text-sm text-slate-500">Belum ada transaksi.</div>
                @endforelse
            </div>
        </div>

        {{-- Recent Check-ins --}}
        <div class="bg-slate-800/60 border border-slate-700 rounded-xl">
            <div class="px-5 py-4 border-b border-slate-700">
                <h2 class="text-sm font-bold text-white">✅ Check-in Terbaru</h2>
            </div>
            <div class="divide-y divide-slate-700/50">
                @forelse($recentCheckIns as $ticket)
                    <div class="px-5 py-3 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white" style="background-color: {{ $ticket->ticketTier->color ?? '#6366f1' }}">
                                {{ substr($ticket->ticketTier->name ?? 'N', 0, 2) }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-white">{{ $ticket->user->name ?? '-' }}</p>
                                <p class="text-xs text-slate-400">{{ $ticket->event->title ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-bold text-indigo-300">{{ $ticket->ticketTier->name ?? '-' }}</p>
                            <p class="text-xs text-slate-500">{{ $ticket->checked_in_at?->format('H:i') }}</p>
                        </div>
                    </div>
                @empty
                    <div class="p-5 text-center text-sm text-slate-500">Belum ada check-in.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="mt-6 flex gap-3">
        @role('admin')
        <a href="{{ route('admin.events.index') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-bold rounded-xl transition">
            📅 Kelola Event
        </a>
        <a href="{{ route('admin.events.create') }}" class="px-4 py-2 bg-purple-600 hover:bg-purple-500 text-white text-sm font-bold rounded-xl transition">
            ➕ Buat Event Baru
        </a>
        @endrole
        <a href="{{ route('scanner.index') }}" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white text-sm font-bold rounded-xl transition">
            📱 Gate Scanner
        </a>
    </div>
</div>
@endsection
