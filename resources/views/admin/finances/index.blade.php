@extends('layouts.admin')

@section('title', 'Finances - Admin EventTix')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Finances</h1>
            <p class="text-sm text-gray-500 mt-1">Riwayat transaksi pembayaran dan ringkasan pendapatan.</p>
        </div>
        <a href="{{ route('admin.finances.export', request()->query()) }}" class="px-4 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-semibold rounded-lg transition shadow-sm inline-flex items-center gap-2 justify-center">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
            Export CSV
        </a>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-[#EBF5FF] border border-blue-100 rounded-xl p-5"><p class="text-xs font-semibold text-gray-600">Pendapatan (Settlement)</p><p class="text-xl lg:text-2xl font-bold text-gray-900 mt-1">Rp{{ number_format($summary['revenue'], 0, ',', '.') }}</p><p class="text-xs text-gray-500 mt-1">{{ $summary['settled_count'] }} transaksi</p></div>
        <div class="bg-[#FFFAF0] border border-orange-100 rounded-xl p-5"><p class="text-xs font-semibold text-gray-600">Menunggu Pembayaran</p><p class="text-xl lg:text-2xl font-bold text-gray-900 mt-1">Rp{{ number_format($summary['pending'], 0, ',', '.') }}</p></div>
        <div class="bg-[#E6FFFA] border border-teal-100 rounded-xl p-5"><p class="text-xs font-semibold text-gray-600">Total Diskon Promo</p><p class="text-xl lg:text-2xl font-bold text-gray-900 mt-1">Rp{{ number_format($discounts, 0, ',', '.') }}</p></div>
        <div class="bg-[#FFF5F5] border border-red-100 rounded-xl p-5"><p class="text-xs font-semibold text-gray-600">Gagal / Kedaluwarsa</p><p class="text-xl lg:text-2xl font-bold text-gray-900 mt-1">{{ $summary['failed_count'] }}</p></div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 space-y-6">
            <form method="GET" class="bg-white border border-gray-200 rounded-xl p-4 grid grid-cols-1 md:grid-cols-12 gap-3">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Kode order, nama / email..." class="md:col-span-4 bg-gray-50 border border-gray-200 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                <select name="event_id" class="md:col-span-4 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    <option value="">Semua Event</option>
                    @foreach($events as $ev)<option value="{{ $ev->id }}" {{ (string) request('event_id') === (string) $ev->id ? 'selected' : '' }}>{{ $ev->title }}</option>@endforeach
                </select>
                <select name="status" class="md:col-span-4 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    <option value="">Semua Status</option>
                    @foreach(['settlement' => 'Settlement', 'pending' => 'Pending', 'expire' => 'Expire', 'cancel' => 'Cancel', 'deny' => 'Deny'] as $k => $v)
                        <option value="{{ $k }}" {{ request('status') === $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
                <input type="date" name="from" value="{{ request('from') }}" class="md:col-span-4 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" title="Dari tanggal">
                <input type="date" name="to" value="{{ request('to') }}" class="md:col-span-4 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" title="Sampai tanggal">
                <div class="md:col-span-4 flex gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-gray-900 hover:bg-gray-800 text-white text-sm font-semibold rounded-lg transition">Terapkan</button>
                    @if(request()->hasAny(['q', 'event_id', 'status', 'from', 'to']))<a href="{{ route('admin.finances.index') }}" class="px-3 py-2 text-sm font-semibold text-gray-500 hover:text-gray-700">Reset</a>@endif
                </div>
            </form>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-gray-50 text-gray-500">
                            <tr>
                                <th class="px-6 py-4 font-medium">Order</th>
                                <th class="px-6 py-4 font-medium">Pembeli</th>
                                <th class="px-6 py-4 font-medium">Metode</th>
                                <th class="px-6 py-4 font-medium">Status</th>
                                <th class="px-6 py-4 font-medium text-right">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($transactions as $trx)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4">
                                        @if($trx->order)
                                            <a href="{{ route('admin.orders.show', $trx->order) }}" class="font-mono font-bold text-indigo-600 hover:text-indigo-500">{{ $trx->order_code }}</a>
                                        @else
                                            <span class="font-mono font-bold">{{ $trx->order_code }}</span>
                                        @endif
                                        <p class="text-xs text-gray-500 max-w-[200px] truncate">{{ $trx->order->event->title ?? '-' }}</p>
                                        <p class="text-[11px] text-gray-400">{{ $trx->created_at?->translatedFormat('d M Y H:i') }}</p>
                                    </td>
                                    <td class="px-6 py-4"><p class="font-medium text-gray-900">{{ $trx->order->user->name ?? '-' }}</p><p class="text-xs text-gray-500">{{ $trx->order->user->email ?? '' }}</p></td>
                                    <td class="px-6 py-4 text-gray-600 uppercase text-xs font-semibold">{{ str_replace('_', ' ', $trx->payment_type ?? '-') }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($trx->transaction_status === 'settlement') bg-emerald-100 text-emerald-800
                                            @elseif($trx->transaction_status === 'pending') bg-amber-100 text-amber-800
                                            @else bg-red-100 text-red-800 @endif">{{ ucfirst($trx->transaction_status) }}</span>
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-gray-900 text-right">Rp{{ number_format($trx->gross_amount, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500">Tidak ada transaksi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($transactions->hasPages())<div class="px-6 py-4 border-t border-gray-100">{{ $transactions->links() }}</div>@endif
            </div>
        </div>

        <div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-base font-bold text-gray-900 mb-4">Top Event by Revenue</h2>
                @php $maxRev = max(1, (float) ($eventRevenue->first()->revenue ?? 1)); @endphp
                <div class="space-y-4">
                    @forelse($eventRevenue as $row)
                        <div>
                            <div class="flex justify-between text-sm mb-1.5">
                                <span class="font-medium text-gray-800 truncate pr-3">{{ $row->event->title ?? 'Event dihapus' }}</span>
                                <span class="font-semibold text-gray-900 shrink-0">Rp{{ number_format($row->revenue, 0, ',', '.') }}</span>
                            </div>
                            <div class="h-2 bg-gray-100 rounded-full overflow-hidden"><div class="h-full bg-gradient-to-r from-indigo-500 to-sky-400 rounded-full" style="width: {{ round($row->revenue / $maxRev * 100) }}%"></div></div>
                            <p class="text-[11px] text-gray-400 mt-1">{{ $row->orders }} order dibayar</p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 text-center py-6">Belum ada pendapatan.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
