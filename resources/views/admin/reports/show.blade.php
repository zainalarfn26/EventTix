@extends('layouts.admin')

@section('title', 'Laporan ' . $event->title . ' - Admin EventTix')

@section('content')
<div class="max-w-6xl mx-auto">
    <a href="{{ route('admin.reports.index') }}" class="text-sm text-indigo-600 hover:text-indigo-500 inline-flex items-center gap-1 font-medium">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
        Kembali ke Reports
    </a>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mt-2 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $event->title }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $event->start_time->translatedFormat('d F Y, H:i') }} • {{ $event->venue->name ?? '-' }} • {{ ucfirst($event->status) }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.events.edit', $event) }}" class="px-4 py-2 bg-white border border-gray-200 hover:border-ink text-gray-700 text-sm font-semibold rounded-lg transition">Edit Event</a>
            <a href="{{ route('admin.reports.export', $event) }}" class="px-4 py-2 bg-ink hover:bg-gray-800 text-white text-sm font-semibold rounded-lg transition inline-flex items-center gap-2">
                <x-icon name="download" class="h-4 w-4" />
                Export Peserta (CSV)
            </a>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-stat dark label="Pendapatan" :value="'Rp' . number_format($summary['revenue'], 0, ',', '.')" :hint="'Diskon promo Rp' . number_format($summary['discount'], 0, ',', '.')" />
        <x-stat label="Tiket terjual" :value="$summary['issued']" :hint="'Dari kuota ' . number_format($summary['quota'])" />
        <x-stat label="Check-in" :value="$summary['checked_in']" :hint="'Kehadiran ' . ($summary['issued'] > 0 ? round($summary['checked_in'] / $summary['issued'] * 100) : 0) . '%'" />
        <x-stat label="Order dibayar" :value="$summary['paid_orders']" :hint="$summary['pending_orders'] . ' order menunggu bayar'" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100"><h2 class="text-base font-bold text-gray-900">Rincian per Kelas Tiket</h2></div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm whitespace-nowrap">
                    <thead class="bg-gray-50 text-gray-500">
                        <tr><th class="px-6 py-3 text-left font-medium">Kelas</th><th class="px-6 py-3 text-right font-medium">Harga</th><th class="px-6 py-3 text-left font-medium">Terjual</th><th class="px-6 py-3 text-right font-medium">Check-in</th><th class="px-6 py-3 text-right font-medium">Revenue</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($tierStats as $row)
                            @php $t = $row['tier']; $pct = $t->quota > 0 ? min(100, round($row['issued'] / $t->quota * 100)) : 0; @endphp
                            <tr>
                                <td class="px-6 py-3"><span class="inline-flex items-center gap-2 font-medium text-gray-900"><span class="w-2.5 h-2.5 rounded-full" style="background: {{ $t->color }}"></span>{{ $t->name }}@unless($t->is_active)<span class="text-[10px] bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded">nonaktif</span>@endunless</span></td>
                                <td class="px-6 py-3 text-right text-gray-600">Rp{{ number_format($t->price, 0, ',', '.') }}</td>
                                <td class="px-6 py-3 w-48">
                                    <p class="text-xs text-gray-600 mb-1">{{ $row['issued'] }} / {{ $t->quota }} ({{ $pct }}%)</p>
                                    <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden"><div class="h-full rounded-full" style="width: {{ $pct }}%; background: {{ $t->color }}"></div></div>
                                </td>
                                <td class="px-6 py-3 text-right text-gray-600">{{ $row['checked_in'] }}</td>
                                <td class="px-6 py-3 text-right font-semibold text-gray-900">Rp{{ number_format($row['revenue'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm">
            <div class="px-6 py-4 border-b border-gray-100"><h2 class="text-base font-bold text-gray-900">Check-in Terakhir</h2></div>
            <div class="divide-y divide-gray-100">
                @forelse($recentCheckIns as $t)
                    <div class="px-6 py-3 flex items-center justify-between">
                        <div><p class="text-sm font-semibold text-gray-900">{{ $t->user->name ?? '-' }}</p><p class="text-xs text-gray-500">{{ $t->ticketTier->name ?? '-' }} • {{ $t->checkedInBy->name ?? 'manual' }}</p></div>
                        <span class="text-xs text-gray-400">{{ $t->checked_in_at?->format('d M H:i') }}</span>
                    </div>
                @empty
                    <p class="px-6 py-10 text-center text-sm text-gray-500">Belum ada check-in.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
