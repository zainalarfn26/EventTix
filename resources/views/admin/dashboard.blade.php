@extends('layouts.admin')

@section('title', 'Dashboard - EventTix')

@section('content')
@php $isAdmin = auth()->user()->hasRole('admin'); @endphp
<div class="max-w-7xl mx-auto">
    <div class="mb-8 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div>
            <p class="eyebrow mb-3">{{ now()->translatedFormat('l, d F Y') }}</p>
            <h1 class="font-display text-3xl font-bold">Halo, {{ \Illuminate\Support\Str::before(auth()->user()->name, ' ') }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $isAdmin ? 'Ringkasan aktivitas EventTix.' : 'Ringkasan check-in dan event yang kamu kelola.' }}</p>
        </div>
        @if($isAdmin)
        <div class="flex gap-2">
            <a href="{{ route('admin.events.create') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold bg-ink text-white px-4 py-2 rounded-lg hover:bg-gray-800 transition"><x-icon name="plus" class="h-4 w-4" /> Event baru</a>
            <a href="{{ route('admin.reports.index') }}" class="text-sm font-semibold bg-white border border-gray-300 text-ink px-4 py-2 rounded-lg hover:border-ink transition">Laporan</a>
        </div>
        @else
        <a href="{{ route('scanner.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold bg-flame-500 text-white px-4 py-2 rounded-lg hover:bg-flame-600 transition"><x-icon name="qr" class="h-4 w-4" /> Buka scanner</a>
        @endif
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 {{ $isAdmin ? 'lg:grid-cols-4' : 'lg:grid-cols-3' }} gap-4 mb-8">
        @if($isAdmin)
            <x-stat dark label="Total pendapatan" :value="'Rp' . number_format($totalRevenue, 0, ',', '.')"
                    :hint="$pendingOrdersCount > 0 ? $pendingOrdersCount . ' order menunggu pembayaran' : null"
                    :href="route('admin.finances.index')" />
        @endif
        <x-stat label="Total event" :value="$totalEventsCount" :href="$isAdmin ? route('admin.events.index') : null" />
        <x-stat label="Tiket terjual" :value="number_format($totalTicketsSold)" :href="$isAdmin ? route('admin.tickets.index') : null" />
        <x-stat label="Sudah check-in" :value="number_format($totalCheckedInCount)" :href="route('scanner.index')" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-8">

            {{-- Events Overview --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-base font-bold text-gray-900">Events Overview</h2>
                    @if($isAdmin)
                        <a href="{{ route('admin.events.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">Kelola semua →</a>
                    @endif
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-gray-50 text-gray-500">
                            <tr>
                                <th class="px-6 py-4 font-medium">Event</th>
                                <th class="px-6 py-4 font-medium">Status</th>
                                <th class="px-6 py-4 font-medium">Tiket</th>
                                @if($isAdmin)<th class="px-6 py-4 font-medium">Revenue</th>@endif
                                @if($isAdmin)<th class="px-6 py-4 font-medium text-right">Actions</th>@endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($recentEvents as $event)
                                @php $quota = $event->ticketTiers->sum('quota'); @endphp
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900 max-w-[220px] truncate">{{ $event->title }}</div>
                                        <div class="text-xs text-gray-500">{{ $event->start_time->translatedFormat('d M Y') }} • {{ $event->venue->name ?? '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($event->status === 'published') bg-emerald-100 text-emerald-800
                                            @elseif($event->status === 'draft') bg-amber-100 text-amber-800
                                            @elseif($event->status === 'completed') bg-blue-100 text-blue-800
                                            @else bg-red-100 text-red-800 @endif">{{ ucfirst($event->status) }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">{{ $event->active_tickets_count }} / {{ number_format($quota) }}</td>
                                    @if($isAdmin)<td class="px-6 py-4 font-medium text-gray-900">Rp{{ number_format($event->paid_revenue ?? 0, 0, ',', '.') }}</td>@endif
                                    @if($isAdmin)
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('admin.events.edit', $event) }}" class="text-gray-500 hover:text-indigo-600 font-medium text-xs border border-gray-200 px-3 py-1.5 rounded-md mr-1 hover:border-indigo-200 transition">Edit</a>
                                        <a href="{{ route('admin.reports.show', $event) }}" class="text-gray-500 hover:text-indigo-600 font-medium text-xs border border-gray-200 px-3 py-1.5 rounded-md hover:border-indigo-200 transition">Laporan</a>
                                    </td>
                                    @endif
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-10 text-center text-gray-500">Belum ada event.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Ticket Sales Trend (real) --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-base font-bold text-gray-900 mb-6">Ticket Sales Trend <span class="text-xs font-normal text-gray-400">(12 bulan terakhir)</span></h2>
                <div class="h-64 w-full">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

            {{-- Recent Transactions --}}
            @if($isAdmin)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-base font-bold text-gray-900">Recent Transactions</h2>
                    @if($isAdmin)
                        <a href="{{ route('admin.finances.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">Lihat semua →</a>
                    @endif
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-gray-50 text-gray-500">
                            <tr>
                                <th class="px-6 py-4 font-medium">Order</th>
                                <th class="px-6 py-4 font-medium">Pembeli</th>
                                <th class="px-6 py-4 font-medium">Status</th>
                                <th class="px-6 py-4 font-medium text-right">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($recentTransactions->take(6) as $trx)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4">
                                        @if($isAdmin && $trx->order)
                                            <a href="{{ route('admin.orders.show', $trx->order) }}" class="font-medium text-indigo-600 hover:text-indigo-500">{{ $trx->order_code }}</a>
                                        @else
                                            <span class="font-medium text-gray-900">{{ $trx->order_code }}</span>
                                        @endif
                                        <div class="text-xs text-gray-500 max-w-[220px] truncate">{{ $trx->order->event->title ?? 'Unknown Event' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">{{ $trx->order->user->name ?? '-' }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($trx->transaction_status === 'settlement') bg-emerald-100 text-emerald-800
                                            @elseif($trx->transaction_status === 'pending') bg-amber-100 text-amber-800
                                            @else bg-red-100 text-red-800 @endif">{{ ucfirst($trx->transaction_status) }}</span>
                                    </td>
                                    <td class="px-6 py-4 font-medium text-gray-900 text-right">Rp{{ number_format($trx->gross_amount, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-6 py-10 text-center text-gray-500">Belum ada transaksi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>

        {{-- Side Content: Recent Check-ins --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 h-full flex flex-col">
                <div class="px-6 py-5 border-b border-gray-200">
                    <h2 class="text-base font-bold text-gray-900">Recent Check-ins</h2>
                </div>
                <div class="flex-1 divide-y divide-gray-100 overflow-y-auto max-h-[900px]">
                    @forelse($recentCheckIns as $ticket)
                        <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold text-white shadow-sm" style="background-color: {{ $ticket->ticketTier->color ?? '#14130F' }}">
                                    {{ substr($ticket->ticketTier->name ?? 'N', 0, 2) }}
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900">{{ $ticket->user->name ?? '-' }}</p>
                                    <p class="text-xs text-gray-500 line-clamp-1 w-32" title="{{ $ticket->event->title ?? '-' }}">{{ $ticket->event->title ?? '-' }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="inline-block px-2 py-1 bg-gray-50 border border-gray-100 text-gray-600 rounded-md text-xs font-semibold">{{ $ticket->ticketTier->name ?? '-' }}</span>
                                <p class="text-[11px] text-gray-400 mt-1 font-medium">{{ $ticket->checked_in_at?->format('H:i') }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-sm text-gray-500 flex flex-col items-center justify-center h-full">
                            <svg class="h-12 w-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            Belum ada check-in.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('salesChart');
        if(ctx && typeof Chart !== 'undefined') {
            let gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, 'rgba(255, 90, 31, 0.22)');
            gradient.addColorStop(1, 'rgba(255, 90, 31, 0.0)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($chartLabels),
                    datasets: [{
                        label: 'Sales Trend',
                        data: @json($chartData),
                        fill: true,
                        backgroundColor: gradient,
                        borderColor: '#FF5A1F',
                        tension: 0.4,
                        borderWidth: 2,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#FF5A1F',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#14130F',
                            padding: 10,
                            titleFont: { size: 13, family: 'Inter' },
                            bodyFont: { size: 14, family: 'Inter', weight: 'bold' },
                            displayColors: false,
                            callbacks: { label: ctx => ctx.parsed.y + ' Tickets' }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { color: '#94a3b8', precision: 0, font: { family: 'Inter', size: 11 } },
                            grid: { color: '#f1f5f9' },
                            border: { display: false }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: '#94a3b8', font: { family: 'Inter', size: 11 } },
                            border: { display: false }
                        }
                    },
                    interaction: { mode: 'index', intersect: false }
                }
            });
        }
    });
</script>
@endpush
