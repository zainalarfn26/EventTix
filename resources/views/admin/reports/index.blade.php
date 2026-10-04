@extends('layouts.admin')

@section('title', 'Reports - Admin EventTix')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Reports</h1>
        <p class="text-sm text-gray-500 mt-1">Performa penjualan dan kehadiran tiap event.</p>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-[#EBF5FF] border border-blue-100 rounded-xl p-5"><p class="text-xs font-semibold text-gray-600">Total Revenue</p><p class="text-xl lg:text-2xl font-bold text-gray-900 mt-1">Rp{{ number_format($totals['revenue'], 0, ',', '.') }}</p></div>
        <div class="bg-[#FFF5F5] border border-red-100 rounded-xl p-5"><p class="text-xs font-semibold text-gray-600">Tiket Terjual</p><p class="text-xl lg:text-2xl font-bold text-gray-900 mt-1">{{ number_format($totals['tickets']) }} <span class="text-sm font-medium text-gray-400">/ {{ number_format($totals['quota']) }}</span></p></div>
        <div class="bg-[#FFFAF0] border border-orange-100 rounded-xl p-5"><p class="text-xs font-semibold text-gray-600">Total Check-in</p><p class="text-xl lg:text-2xl font-bold text-gray-900 mt-1">{{ number_format($totals['checked_in']) }}</p></div>
        <div class="bg-[#E6FFFA] border border-teal-100 rounded-xl p-5"><p class="text-xs font-semibold text-gray-600">Kehadiran</p><p class="text-xl lg:text-2xl font-bold text-gray-900 mt-1">{{ $totals['tickets'] > 0 ? round($totals['checked_in'] / $totals['tickets'] * 100) : 0 }}%</p></div>
    </div>

    <form method="GET" class="bg-white border border-gray-200 rounded-xl p-4 mb-6 flex gap-3">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari event..." class="flex-1 bg-gray-50 border border-gray-200 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
        <button type="submit" class="px-5 py-2 bg-gray-900 hover:bg-gray-800 text-white text-sm font-semibold rounded-lg transition">Cari</button>
        @if(request('q'))<a href="{{ route('admin.reports.index') }}" class="px-3 py-2 text-sm font-semibold text-gray-500 hover:text-gray-700">Reset</a>@endif
    </form>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-gray-50 text-gray-500">
                    <tr>
                        <th class="px-6 py-4 font-medium">Event</th>
                        <th class="px-6 py-4 font-medium">Penjualan</th>
                        <th class="px-6 py-4 font-medium">Check-in</th>
                        <th class="px-6 py-4 font-medium">Revenue</th>
                        <th class="px-6 py-4 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($events as $event)
                        @php
                            $quota = $event->ticketTiers->sum('quota');
                            $salesPct = $quota > 0 ? min(100, round($event->active_tickets_count / $quota * 100)) : 0;
                            $attPct = $event->active_tickets_count > 0 ? round($event->checked_in_count / $event->active_tickets_count * 100) : 0;
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <p class="font-semibold text-gray-900 max-w-[260px] truncate">{{ $event->title }}</p>
                                <p class="text-xs text-gray-500">{{ $event->start_time->translatedFormat('d M Y') }} • {{ ucfirst($event->status) }}</p>
                            </td>
                            <td class="px-6 py-4 w-56">
                                <p class="text-xs text-gray-600 mb-1">{{ $event->active_tickets_count }} / {{ number_format($quota) }} <span class="text-gray-400">({{ $salesPct }}%)</span></p>
                                <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden"><div class="h-full bg-indigo-500 rounded-full" style="width: {{ $salesPct }}%"></div></div>
                            </td>
                            <td class="px-6 py-4 w-56">
                                <p class="text-xs text-gray-600 mb-1">{{ $event->checked_in_count }} hadir <span class="text-gray-400">({{ $attPct }}%)</span></p>
                                <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden"><div class="h-full bg-emerald-500 rounded-full" style="width: {{ $attPct }}%"></div></div>
                            </td>
                            <td class="px-6 py-4 font-semibold text-gray-900">Rp{{ number_format($event->paid_revenue ?? 0, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.reports.show', $event) }}" class="text-xs font-medium border border-indigo-100 bg-indigo-50 px-3 py-1.5 rounded-md text-indigo-700 hover:bg-indigo-100 transition">Detail</a>
                                <a href="{{ route('admin.reports.export', $event) }}" class="text-xs font-medium border border-gray-200 px-3 py-1.5 rounded-md text-gray-600 hover:border-indigo-200 hover:text-indigo-600 transition ml-1">CSV</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500">Belum ada data event.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
