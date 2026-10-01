@extends('layouts.admin')

@section('title', 'Dashboard - EventTix')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-8 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-[#EBF5FF] rounded-2xl p-6 shadow-sm border border-blue-100 flex flex-col justify-center">
            <div class="flex items-center gap-2 mb-2">
                <span class="text-xl">💰</span>
                <h3 class="text-sm font-semibold text-gray-700">Total Revenue</h3>
            </div>
            <p class="text-3xl font-bold text-gray-900">Rp{{ number_format($totalRevenue, 0, ',', '.') }}</p>
        </div>
        
        <div class="bg-[#E6FFFA] rounded-2xl p-6 shadow-sm border border-teal-100 flex flex-col justify-center">
            <div class="flex items-center gap-2 mb-2">
                <span class="text-xl">📅</span>
                <h3 class="text-sm font-semibold text-gray-700">Active Events</h3>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ $totalEventsCount }}</p>
        </div>

        <div class="bg-[#FFF5F5] rounded-2xl p-6 shadow-sm border border-red-100 flex flex-col justify-center">
            <div class="flex items-center gap-2 mb-2">
                <span class="text-xl">🎫</span>
                <h3 class="text-sm font-semibold text-gray-700">Tickets Sold</h3>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ number_format($totalTicketsSold) }}</p>
        </div>

        <div class="bg-[#FFFAF0] rounded-2xl p-6 shadow-sm border border-orange-100 flex flex-col justify-center">
            <div class="flex items-center gap-2 mb-2">
                <span class="text-xl">✅</span>
                <h3 class="text-sm font-semibold text-gray-700">Total Check-in</h3>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ number_format($totalCheckedInCount) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Main Events and Chart Section --}}
        <div class="lg:col-span-2 space-y-8">
            
            {{-- Events Management / Recent Transactions --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white">
                    <h2 class="text-base font-bold text-gray-900">Events Management</h2>
                    @role('admin')
                    <div class="flex gap-2 w-full sm:w-auto">
                        <a href="{{ route('admin.events.create') }}" class="flex-1 sm:flex-none text-center text-sm font-semibold bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">Add New Event</a>
                        <a href="{{ route('admin.events.index') }}" class="flex-1 sm:flex-none text-center text-sm font-semibold bg-blue-50 text-blue-600 px-4 py-2 rounded-lg hover:bg-blue-100 transition">View Reports</a>
                    </div>
                    @endrole
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-gray-50 text-gray-500">
                            <tr>
                                <th class="px-6 py-4 font-medium">ID</th>
                                <th class="px-6 py-4 font-medium">Event Name</th>
                                <th class="px-6 py-4 font-medium">Organizer</th>
                                <th class="px-6 py-4 font-medium">Status</th>
                                <th class="px-6 py-4 font-medium">Revenue</th>
                                <th class="px-6 py-4 font-medium text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($recentTransactions->take(5) as $trx)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 text-gray-500 font-medium">#{{ $loop->iteration }}</td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900 flex items-center gap-2">
                                            {{ $trx->order->event->title ?? 'Unknown Event' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">{{ $trx->order->user->name ?? '-' }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            @if($trx->transaction_status === 'settlement') bg-emerald-100 text-emerald-800
                                            @elseif($trx->transaction_status === 'pending') bg-amber-100 text-amber-800
                                            @else bg-red-100 text-red-800 @endif">
                                            {{ ucfirst($trx->transaction_status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-medium text-gray-900">Rp{{ number_format($trx->gross_amount, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="#" class="text-gray-500 hover:text-indigo-600 font-medium text-xs border border-gray-200 px-3 py-1.5 rounded-md mr-1 hover:border-indigo-200 transition">Edit</a>
                                        <a href="#" class="text-gray-500 hover:text-indigo-600 font-medium text-xs border border-gray-200 px-3 py-1.5 rounded-md hover:border-indigo-200 transition">Manage</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="h-10 w-10 text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                            No recent transactions.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Ticket Sales Trend (Mocked for visual) --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-base font-bold text-gray-900 mb-6">Ticket Sales Trend</h2>
                <div class="h-64 w-full">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

        </div>

        {{-- Side Content: Recent Check-ins --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 h-full flex flex-col">
                <div class="px-6 py-5 border-b border-gray-200">
                    <h2 class="text-base font-bold text-gray-900">Recent Check-ins</h2>
                </div>
                <div class="flex-1 divide-y divide-gray-100 overflow-y-auto max-h-[700px]">
                    @forelse($recentCheckIns as $ticket)
                        <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold text-white shadow-sm" style="background-color: {{ $ticket->ticketTier->color ?? '#6366f1' }}">
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
            // Soft gradient
            let gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, 'rgba(45, 212, 191, 0.3)'); // teal-400
            gradient.addColorStop(1, 'rgba(45, 212, 191, 0.0)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov'],
                    datasets: [{
                        label: 'Sales Trend',
                        data: [50, 65, 80, 75, 250, 150, 110, 130, 270, 210, 160],
                        fill: true,
                        backgroundColor: gradient,
                        borderColor: '#2dd4bf', // teal-400
                        tension: 0.4,
                        borderWidth: 2,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#2dd4bf',
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
                            backgroundColor: '#1e293b',
                            padding: 10,
                            titleFont: { size: 13, family: 'Inter' },
                            bodyFont: { size: 14, family: 'Inter', weight: 'bold' },
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y + ' Tickets';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                borderDash: [4, 4],
                                color: '#f1f5f9',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#94a3b8',
                                font: { family: 'Inter', size: 11 }
                            },
                            border: { display: false }
                        },
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                color: '#94a3b8',
                                font: { family: 'Inter', size: 11 }
                            },
                            border: { display: false }
                        }
                    },
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    }
                }
            });
        }
    });
</script>
@endpush
