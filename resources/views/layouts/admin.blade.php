<!DOCTYPE html>
<html lang="id">
<head>
    <title>@yield('title', 'Panel - EventTix')</title>
    @include('layouts.partials.head')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        main h1 { letter-spacing: -0.02em; }
    </style>
</head>
@php
    $isAdmin = auth()->user()->hasRole('admin');
    $navGroups = [
        'Ringkasan' => [
            ['label' => 'Dasbor', 'route' => 'admin.dashboard', 'match' => 'admin.dashboard', 'admin' => false, 'icon' => 'home'],
            ['label' => 'Laporan', 'route' => 'admin.reports.index', 'match' => 'admin.reports.*', 'admin' => true, 'icon' => 'chart'],
        ],
        'Event' => [
            ['label' => 'Event', 'route' => 'admin.events.index', 'match' => 'admin.events.*', 'admin' => true, 'icon' => 'calendar'],
            ['label' => 'Venue', 'route' => 'admin.venues.index', 'match' => 'admin.venues.*', 'admin' => true, 'icon' => 'pin'],
            ['label' => 'Tiket', 'route' => 'admin.tickets.index', 'match' => 'admin.tickets.*', 'admin' => true, 'icon' => 'ticket'],
            ['label' => 'Gate Scanner', 'route' => 'scanner.index', 'match' => 'scanner.*', 'admin' => false, 'icon' => 'qr'],
        ],
        'Penjualan' => [
            ['label' => 'Keuangan', 'route' => 'admin.finances.index', 'match' => ['admin.finances.*', 'admin.orders.*'], 'admin' => true, 'icon' => 'cash'],
            ['label' => 'Kode Promo', 'route' => 'admin.promos.index', 'match' => 'admin.promos.*', 'admin' => true, 'icon' => 'tag'],
        ],
        'Sistem' => [
            ['label' => 'Akun', 'route' => 'admin.accounts.index', 'match' => 'admin.accounts.*', 'admin' => true, 'icon' => 'users'],
            ['label' => 'Pengaturan', 'route' => 'admin.settings', 'match' => 'admin.settings*', 'admin' => false, 'icon' => 'cog'],
        ],
    ];
    $roleLabel = ['admin' => 'Administrator', 'organizer' => 'Organizer'][auth()->user()->getRoleNames()->first()] ?? 'Pengguna';
@endphp
<body class="bg-paper text-ink h-screen flex overflow-hidden antialiased" x-data="{ mobileNav: false }">

    <div x-show="mobileNav" x-cloak @click="mobileNav = false" class="fixed inset-0 bg-ink/60 z-30 md:hidden"></div>

    <aside class="w-60 bg-ink text-white flex-col h-full fixed md:static inset-y-0 left-0 z-40 hidden md:flex"
           :class="mobileNav ? '!flex' : ''">
        <div class="h-16 flex items-center px-5 border-b border-white/10">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5">
                <span class="h-8 w-8 rounded-lg bg-white/10 flex items-center justify-center text-flame-500">
                    <x-icon name="ticket" class="h-4 w-4" />
                </span>
                <span class="font-display text-lg font-bold tracking-tight">EventTix</span>
            </a>
        </div>

        <nav class="flex-1 px-3 py-5 overflow-y-auto space-y-6">
            @foreach($navGroups as $group => $items)
                @php $visible = collect($items)->filter(fn($i) => !$i['admin'] || $isAdmin); @endphp
                @if($visible->isNotEmpty())
                    <div>
                        <p class="px-3 mb-2 text-[10px] font-semibold uppercase tracking-[0.14em] text-white/35">{{ $group }}</p>
                        <div class="space-y-0.5">
                            @foreach($visible as $item)
                                @php $active = request()->routeIs($item['match']); @endphp
                                <a href="{{ route($item['route']) }}" id="nav-{{ \Illuminate\Support\Str::slug($item['label']) }}"
                                   class="relative flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ $active ? 'bg-white/10 text-white' : 'text-white/60 hover:text-white hover:bg-white/5' }}">
                                    @if($active)<span class="absolute left-0 top-1/2 -translate-y-1/2 h-4 w-0.5 rounded-r bg-flame-500"></span>@endif
                                    <x-icon :name="$item['icon']" class="h-4 w-4 {{ $active ? 'text-flame-500' : '' }}" />
                                    {{ $item['label'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </nav>

        <div class="p-3 border-t border-white/10">
            <a href="{{ route('events.index') }}" class="flex items-center gap-2 px-3 py-2 text-sm text-white/60 hover:text-white rounded-lg hover:bg-white/5 transition-colors">
                <x-icon name="globe" class="h-4 w-4" /> Lihat situs
            </a>
        </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 sm:px-6 z-10">
            <button @click="mobileNav = !mobileNav" class="md:hidden mr-3 text-gray-500 hover:text-ink" aria-label="Menu">
                <x-icon name="menu" class="h-5 w-5" />
            </button>

            <div class="flex-1 max-w-lg">
                @if($isAdmin)
                    <form action="{{ route('admin.search') }}" method="GET" class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400"><x-icon name="search" class="h-4 w-4" /></span>
                        <input type="text" name="q" value="{{ request()->routeIs('admin.search') ? request('q') : '' }}" id="global-search"
                               class="block w-full rounded-lg border border-gray-200 bg-paper py-2 pl-9 pr-3 text-sm placeholder:text-gray-400 focus:border-ink focus:ring-0 focus:outline-none"
                               placeholder="Cari event, akun, order, atau kode tiket">
                    </form>
                @endif
            </div>

            <div class="flex items-center gap-4 ml-4">
                @if($isAdmin)
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" @click.away="open = false" class="relative text-gray-500 hover:text-ink transition" id="notif-bell">
                        <span class="sr-only">Notifikasi</span>
                        <x-icon name="bell" class="h-5 w-5" />
                        @if(($adminNotif['pending_count'] ?? 0) > 0)
                            <span class="absolute -top-1.5 -right-1.5 h-4 min-w-4 px-1 rounded-full bg-flame-500 text-white text-[10px] font-bold flex items-center justify-center">{{ $adminNotif['pending_count'] }}</span>
                        @endif
                    </button>
                    <div x-show="open" x-cloak x-transition class="absolute right-0 mt-3 w-80 bg-white rounded-xl border border-gray-200 shadow-xl z-50 overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                            <p class="text-sm font-bold">Notifikasi</p>
                            <span class="text-xs text-gray-500">{{ $adminNotif['pending_count'] ?? 0 }} order menunggu bayar</span>
                        </div>
                        <div class="max-h-80 overflow-y-auto divide-y divide-gray-100">
                            @forelse(($adminNotif['recent_paid'] ?? []) as $o)
                                <a href="{{ route('admin.orders.show', $o) }}" class="flex items-start gap-3 px-4 py-3 hover:bg-paper transition">
                                    <span class="mt-0.5 h-7 w-7 flex items-center justify-center rounded-md bg-emerald-50 text-emerald-600"><x-icon name="check" class="h-3.5 w-3.5" /></span>
                                    <span class="min-w-0">
                                        <span class="block text-sm font-semibold truncate">{{ $o->user->name ?? 'User' }} membayar Rp{{ number_format($o->total_amount, 0, ',', '.') }}</span>
                                        <span class="block text-xs text-gray-500 truncate">{{ $o->event->title ?? '-' }} &middot; {{ $o->paid_at?->diffForHumans() }}</span>
                                    </span>
                                </a>
                            @empty
                                <p class="px-4 py-8 text-center text-sm text-gray-500">Belum ada pembayaran terbaru.</p>
                            @endforelse
                        </div>
                        <a href="{{ route('admin.finances.index') }}" class="block text-center text-xs font-semibold hover:bg-paper py-2.5 border-t border-gray-100">Lihat semua transaksi</a>
                    </div>
                </div>
                @endif

                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" @click.away="open = false" class="flex items-center gap-2 focus:outline-none">
                        <span class="h-8 w-8 rounded-lg bg-ink text-white flex items-center justify-center text-xs font-bold">{{ strtoupper(mb_substr(auth()->user()->name ?? 'A', 0, 1)) }}</span>
                        <span class="hidden sm:block text-left leading-tight">
                            <span class="block text-sm font-semibold">{{ auth()->user()->name }}</span>
                            <span class="block text-[11px] text-gray-500">{{ $roleLabel }}</span>
                        </span>
                        <x-icon name="chevron-down" class="h-3.5 w-3.5 text-gray-400 hidden sm:block" />
                    </button>
                    <div x-show="open" x-cloak x-transition class="absolute right-0 mt-2 w-52 bg-white rounded-xl border border-gray-200 shadow-xl py-1 z-50">
                        <a href="{{ route('admin.settings') }}" class="block px-4 py-2 text-sm hover:bg-paper">Pengaturan akun</a>
                        <a href="{{ route('scanner.index') }}" class="block px-4 py-2 text-sm hover:bg-paper">Gate Scanner</a>
                        <a href="{{ route('events.index') }}" class="block px-4 py-2 text-sm hover:bg-paper">Lihat situs</a>
                        <form action="{{ route('logout') }}" method="POST" class="border-t border-gray-100 mt-1">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 font-medium">Keluar</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
            @yield('content')
        </main>
    </div>

    @include('layouts.partials.swal')
    @if($errors->any() && !View::hasSection('inline_errors'))
        <script>document.addEventListener('DOMContentLoaded', () => Toast.fire({ icon: 'error', title: 'Periksa kembali isian form Anda.' }));</script>
    @endif

    @stack('scripts')
</body>
</html>
