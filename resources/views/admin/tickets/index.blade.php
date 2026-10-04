@extends('layouts.admin')

@section('title', 'Kelola Tiket - Admin EventTix')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Tiket</h1>
        <p class="text-sm text-gray-500 mt-1">Pantau semua tiket terjual, check-in manual, atau batalkan tiket bermasalah.</p>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-stat label="Total tiket" :value="number_format($counts['total'])" />
        <x-stat label="Tiket aktif" :value="number_format($counts['active'])" />
        <x-stat label="Sudah check-in" :value="number_format($counts['checked_in'])" />
        <x-stat label="Dibatalkan" :value="number_format($counts['cancelled'])" />
    </div>

    <form method="GET" class="bg-white border border-gray-200 rounded-xl p-4 mb-6 grid grid-cols-1 md:grid-cols-12 gap-3">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Kode tiket, kode order, nama / email pembeli..." class="md:col-span-5 bg-paper border border-gray-200 rounded-lg px-4 py-2 text-sm focus:border-ink focus:ring-0 outline-none">
        <select name="event_id" onchange="this.form.submit()" class="md:col-span-3 bg-paper border border-gray-200 rounded-lg px-4 py-2 text-sm focus:border-ink focus:ring-0 outline-none">
            <option value="">Semua Event</option>
            @foreach($events as $ev)<option value="{{ $ev->id }}" {{ (string) request('event_id') === (string) $ev->id ? 'selected' : '' }}>{{ $ev->title }}</option>@endforeach
        </select>
        <select name="status" onchange="this.form.submit()" class="md:col-span-2 bg-paper border border-gray-200 rounded-lg px-4 py-2 text-sm focus:border-ink focus:ring-0 outline-none">
            <option value="">Semua Status</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
            <option value="checked_in" {{ request('status') === 'checked_in' ? 'selected' : '' }}>Check-in</option>
            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
        </select>
        <div class="md:col-span-2 flex gap-2">
            <button type="submit" class="flex-1 px-4 py-2 bg-ink hover:bg-gray-800 text-white text-sm font-semibold rounded-lg transition">Cari</button>
            @if(request()->hasAny(['q', 'event_id', 'status']))<a href="{{ route('admin.tickets.index') }}" class="px-3 py-2 text-sm font-semibold text-gray-500 hover:text-ink">Reset</a>@endif
        </div>
    </form>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-gray-50 text-gray-500">
                    <tr>
                        <th class="px-6 py-4 font-medium">Tiket</th>
                        <th class="px-6 py-4 font-medium">Pemilik</th>
                        <th class="px-6 py-4 font-medium">Kelas</th>
                        <th class="px-6 py-4 font-medium">Status</th>
                        <th class="px-6 py-4 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($tickets as $ticket)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <a href="{{ route('tickets.show', $ticket->ticket_code) }}" class="font-mono font-bold text-indigo-600 hover:text-indigo-500">{{ $ticket->ticket_code }}</a>
                                <p class="text-xs text-gray-500 max-w-[220px] truncate">{{ $ticket->event->title ?? '-' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-900">{{ $ticket->user->name ?? '-' }}</p>
                                <p class="text-xs text-gray-500">{{ $ticket->user->email ?? '' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-700"><span class="w-2.5 h-2.5 rounded-full" style="background: {{ $ticket->ticketTier->color ?? '#94a3b8' }}"></span>{{ $ticket->ticketTier->name ?? '-' }}</span>
                                @if($ticket->ticketTier?->wristband_color)<p class="text-[11px] text-gray-400">Gelang: {{ $ticket->ticketTier->wristband_color }}</p>@endif
                            </td>
                            <td class="px-6 py-4">
                                @if($ticket->status === 'checked_in')
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Check-in</span>
                                    <p class="text-[11px] text-gray-400 mt-1">{{ $ticket->checked_in_at?->format('d M H:i') }}@if($ticket->checkedInBy) • {{ $ticket->checkedInBy->name }}@endif</p>
                                @elseif($ticket->status === 'cancelled')
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Dibatalkan</span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700">Aktif</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="inline-flex gap-1.5">
                                    @if($ticket->status === 'active')
                                        <form action="{{ route('admin.tickets.check_in', $ticket) }}" method="POST" data-confirm="Tandai tiket {{ $ticket->ticket_code }} sebagai sudah masuk?" data-confirm-title="Check-in manual" data-confirm-button="Ya, check-in" data-confirm-type="primary">@csrf
                                            <button class="text-xs font-medium border border-emerald-200 bg-emerald-50 px-3 py-1.5 rounded-md text-emerald-700 hover:bg-emerald-100 transition">Check-in</button>
                                        </form>
                                        <form action="{{ route('admin.tickets.cancel', $ticket) }}" method="POST" data-confirm="Tiket {{ $ticket->ticket_code }} tidak akan bisa dipakai masuk lagi." data-confirm-title="Batalkan tiket?" data-confirm-button="Ya, batalkan">@csrf
                                            <button class="text-xs font-medium border border-red-200 bg-red-50 px-3 py-1.5 rounded-md text-red-600 hover:bg-red-100 transition">Batalkan</button>
                                        </form>
                                    @elseif($ticket->status === 'checked_in')
                                        <form action="{{ route('admin.tickets.undo_check_in', $ticket) }}" method="POST" data-confirm="Status check-in tiket {{ $ticket->ticket_code }} akan direset sehingga bisa dipindai ulang." data-confirm-title="Reset check-in?" data-confirm-button="Ya, reset" data-confirm-type="primary">@csrf
                                            <button class="text-xs font-medium border border-amber-200 bg-amber-50 px-3 py-1.5 rounded-md text-amber-700 hover:bg-amber-100 transition">Reset Check-in</button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.tickets.reactivate', $ticket) }}" method="POST" data-confirm="Aktifkan kembali tiket {{ $ticket->ticket_code }}?" data-confirm-title="Aktifkan tiket" data-confirm-button="Ya, aktifkan" data-confirm-type="primary">@csrf
                                            <button class="text-xs font-medium border border-indigo-100 bg-indigo-50 px-3 py-1.5 rounded-md text-indigo-700 hover:bg-indigo-100 transition">Aktifkan</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500">Tidak ada tiket ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tickets->hasPages())<div class="px-6 py-4 border-t border-gray-100">{{ $tickets->links() }}</div>@endif
    </div>
</div>
@endsection
