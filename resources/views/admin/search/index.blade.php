@extends('layouts.admin')

@section('title', 'Pencarian Global - Admin EventTix')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Hasil Pencarian</h1>
            <p class="text-sm text-gray-500 mt-1">
                @if($query !== '')
                    Ditemukan <span class="font-semibold text-indigo-600">{{ $totalResults }}</span> hasil untuk kata kunci <span class="font-semibold text-gray-900">"{{ $query }}"</span>
                @else
                    Ketik kata kunci di bilah pencarian atas untuk mencari data.
                @endif
            </p>
        </div>
        <form action="{{ route('admin.search') }}" method="GET" class="w-full sm:w-80">
            <div class="relative">
                <input type="text" name="q" value="{{ $query }}" placeholder="Cari lagi..." 
                       class="w-full bg-white border border-gray-300 rounded-xl pl-4 pr-10 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none shadow-sm">
                <button type="submit" class="absolute right-3 top-2.5 text-gray-400 hover:text-indigo-600">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </button>
            </div>
        </form>
    </div>

    @if($query !== '' && $totalResults === 0)
        <div class="bg-white border border-gray-200 rounded-2xl p-12 text-center shadow-sm">
            <div class="w-16 h-16 bg-paper border border-gray-200 rounded-2xl flex items-center justify-center mx-auto mb-4 text-gray-400">
                <x-icon name="search" class="h-8 w-8" />
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-1">Tidak ada hasil ditemukan</h3>
            <p class="text-sm text-gray-500 max-w-md mx-auto">
                Tidak ada data event, akun, pesanan, tiket, venue, atau promo yang cocok dengan kata kunci "{{ $query }}". Coba gunakan kata kunci lain.
            </p>
        </div>
    @endif

    {{-- Events Section --}}
    @if($events->isNotEmpty())
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                <div class="flex items-center gap-2">
                    <x-icon name="calendar" class="h-4 w-4 text-gray-700" />
                    <h2 class="text-base font-bold text-gray-900">Event ({{ $events->count() }})</h2>
                </div>
                <a href="{{ route('admin.events.index', ['q' => $query]) }}" class="text-xs font-semibold text-indigo-600 hover:underline">Lihat semua event →</a>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($events as $event)
                    <div class="p-4 sm:px-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-gray-50 transition">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-bold text-gray-900">{{ $event->title }}</h3>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold 
                                    @if($event->status === 'published') bg-emerald-100 text-emerald-700
                                    @elseif($event->status === 'draft') bg-amber-100 text-amber-700
                                    @else bg-gray-100 text-gray-700 @endif">
                                    {{ strtoupper($event->status) }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1 flex items-center gap-2 flex-wrap">
                                <span><x-icon name="pin" class="h-3 w-3 inline text-gray-400 mr-0.5" /> {{ $event->venue->name ?? 'Venue Belum Ditentukan' }}</span>
                                <span>&bull;</span>
                                <span><x-icon name="clock" class="h-3 w-3 inline text-gray-400 mr-0.5" /> {{ $event->start_time->translatedFormat('d M Y, H:i') }} WIB</span>
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('events.show', $event->slug) }}" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition">Lihat Publik</a>
                            <a href="{{ route('admin.events.edit', $event) }}" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold rounded-lg transition border border-indigo-100">Kelola Event</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Users / Accounts Section --}}
    @if($users->isNotEmpty())
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                <div class="flex items-center gap-2">
                    <x-icon name="users" class="h-4 w-4 text-gray-700" />
                    <h2 class="text-base font-bold text-gray-900">Akun Pengguna ({{ $users->count() }})</h2>
                </div>
                <a href="{{ route('admin.accounts.index', ['q' => $query]) }}" class="text-xs font-semibold text-indigo-600 hover:underline">Buka kelola akun →</a>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($users as $user)
                    @php $role = $user->getRoleNames()->first() ?? 'customer'; @endphp
                    <div class="p-4 sm:px-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-gray-50 transition">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-full bg-ink text-white flex items-center justify-center font-bold text-sm">
                                {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-900">{{ $user->name }}</h3>
                                <p class="text-xs text-gray-500">{{ $user->email }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase
                                {{ $role === 'admin' ? 'bg-rose-100 text-rose-700' : ($role === 'organizer' ? 'bg-indigo-100 text-indigo-700' : 'bg-emerald-100 text-emerald-700') }}">
                                {{ $role }}
                            </span>
                            <a href="{{ route('admin.accounts.index', ['q' => $user->email]) }}" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition">Detail</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Orders Section --}}
    @if($orders->isNotEmpty())
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                <div class="flex items-center gap-2">
                    <x-icon name="cash" class="h-4 w-4 text-gray-700" />
                    <h2 class="text-base font-bold text-gray-900">Pesanan ({{ $orders->count() }})</h2>
                </div>
                <a href="{{ route('admin.finances.index', ['q' => $query]) }}" class="text-xs font-semibold text-indigo-600 hover:underline">Buka finances →</a>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($orders as $order)
                    <div class="p-4 sm:px-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-gray-50 transition">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-sm font-bold text-indigo-600">{{ $order->order_code }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold
                                    @if($order->status === 'paid') bg-emerald-100 text-emerald-700
                                    @elseif($order->status === 'pending') bg-amber-100 text-amber-700
                                    @else bg-red-100 text-red-700 @endif">
                                    {{ strtoupper($order->status) }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                Pembeli: <span class="font-medium text-gray-700">{{ $order->user->name ?? '-' }}</span> ({{ $order->user->email ?? '-' }}) • Event: <span class="font-medium text-gray-700">{{ $order->event->title ?? '-' }}</span>
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-bold text-gray-900">Rp{{ number_format($order->total_amount, 0, ',', '.') }}</span>
                            <a href="{{ route('admin.orders.show', $order) }}" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold rounded-lg transition border border-indigo-100">Rincian Order</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Tickets Section --}}
    @if($tickets->isNotEmpty())
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                <div class="flex items-center gap-2">
                    <x-icon name="ticket" class="h-4 w-4 text-gray-700" />
                    <h2 class="text-base font-bold text-gray-900">Tiket ({{ $tickets->count() }})</h2>
                </div>
                <a href="{{ route('admin.tickets.index', ['q' => $query]) }}" class="text-xs font-semibold text-indigo-600 hover:underline">Kelola semua tiket →</a>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($tickets as $ticket)
                    <div class="p-4 sm:px-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-gray-50 transition">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-sm font-bold text-gray-900">{{ $ticket->ticket_code }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold
                                    @if($ticket->status === 'checked_in') bg-emerald-100 text-emerald-700
                                    @elseif($ticket->status === 'active') bg-indigo-100 text-indigo-700
                                    @else bg-red-100 text-red-700 @endif">
                                    {{ strtoupper($ticket->status) }}
                                </span>
                                <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-gray-100 text-gray-700">
                                    Kelas: {{ $ticket->ticketTier->name ?? '-' }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                Pemegang: {{ $ticket->user->name ?? '-' }} • Event: {{ $ticket->event->title ?? '-' }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('tickets.show', $ticket->ticket_code) }}" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition">Buka E-Tiket</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Venues & Promos Section (Two Columns) --}}
    @if($venues->isNotEmpty() || $promos->isNotEmpty())
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @if($venues->isNotEmpty())
                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                        <div class="flex items-center gap-2">
                            <x-icon name="pin" class="h-4 w-4 text-gray-700" />
                            <h2 class="text-base font-bold text-gray-900">Venue ({{ $venues->count() }})</h2>
                        </div>
                        <a href="{{ route('admin.venues.index', ['q' => $query]) }}" class="text-xs font-semibold text-indigo-600 hover:underline">Kelola venue →</a>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @foreach($venues as $venue)
                            <div class="p-4 flex items-center justify-between hover:bg-gray-50 transition">
                                <div>
                                    <h3 class="text-sm font-bold text-gray-900">{{ $venue->name }}</h3>
                                    <p class="text-xs text-gray-500">{{ $venue->city }} • Kapasitas: {{ number_format($venue->capacity) }}</p>
                                </div>
                                <a href="{{ route('admin.venues.index', ['q' => $venue->name]) }}" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition">Buka</a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($promos->isNotEmpty())
                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                        <div class="flex items-center gap-2">
                            <x-icon name="tag" class="h-4 w-4 text-gray-700" />
                            <h2 class="text-base font-bold text-gray-900">Kode Promo ({{ $promos->count() }})</h2>
                        </div>
                        <a href="{{ route('admin.promos.index', ['q' => $query]) }}" class="text-xs font-semibold text-indigo-600 hover:underline">Kelola promo →</a>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @foreach($promos as $promo)
                            <div class="p-4 flex items-center justify-between hover:bg-gray-50 transition">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-sm font-bold text-gray-900 bg-gray-100 px-2 py-0.5 rounded">{{ $promo->code }}</span>
                                        <span class="text-xs font-semibold text-emerald-600">
                                            {{ $promo->discount_type === 'percentage' ? $promo->amount . '%' : 'Rp' . number_format($promo->amount, 0, ',', '.') }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Pemakaian: {{ $promo->current_usages }} / {{ $promo->max_usages ?? '∞' }}
                                    </p>
                                </div>
                                <a href="{{ route('admin.promos.index', ['q' => $promo->code]) }}" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition">Kelola</a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
