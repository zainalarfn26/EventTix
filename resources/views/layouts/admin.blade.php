<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard - EventTix')</title>

    <!-- TailwindCSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Chart.js for Sales Trend -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
@php
    $navItems = [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'match' => 'admin.dashboard', 'admin' => false,
         'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
        ['label' => 'Events', 'route' => 'admin.events.index', 'match' => 'admin.events.*', 'admin' => true,
         'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
        ['label' => 'Venues', 'route' => 'admin.venues.index', 'match' => 'admin.venues.*', 'admin' => true,
         'icon' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z'],
        ['label' => 'Gate Scanner', 'route' => 'scanner.index', 'match' => 'scanner.index', 'admin' => false,
         'icon' => 'M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z'],
        ['label' => 'Tickets', 'route' => 'admin.tickets.index', 'match' => 'admin.tickets.*', 'admin' => true,
         'icon' => 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z'],
        ['label' => 'Promo Codes', 'route' => 'admin.promos.index', 'match' => 'admin.promos.*', 'admin' => true,
         'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z'],
        ['label' => 'Finances', 'route' => 'admin.finances.index', 'match' => ['admin.finances.*', 'admin.orders.*'], 'admin' => true,
         'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['label' => 'Accounts', 'route' => 'admin.accounts.index', 'match' => 'admin.accounts.*', 'admin' => true,
         'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
        ['label' => 'Reports', 'route' => 'admin.reports.index', 'match' => 'admin.reports.*', 'admin' => true,
         'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
    ];
    $isAdmin = auth()->user()->hasRole('admin');
    $settingsIcon = 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z';
@endphp
<body class="bg-gray-50 text-gray-800 h-screen flex overflow-hidden font-sans antialiased" x-data="{ mobileNav: false }">

    {{-- Mobile drawer overlay --}}
    <div x-show="mobileNav" x-cloak @click="mobileNav = false" class="fixed inset-0 bg-slate-900/50 z-30 md:hidden"></div>

    <!-- Sidebar -->
    <aside class="w-64 bg-[#1e293b] text-white flex-col h-full fixed md:static inset-y-0 left-0 z-40 transform transition-transform duration-200 md:translate-x-0 hidden md:flex"
           :class="mobileNav ? '!flex translate-x-0' : ''">
        <div class="h-16 flex items-center px-6 border-b border-slate-700/50">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 text-xl font-bold text-indigo-400">
                <span class="text-2xl">🎟️</span>
                <span>Event<span class="text-white">Tix</span></span>
            </a>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
            @foreach($navItems as $item)
                @if(!$item['admin'] || $isAdmin)
                    @php $active = request()->routeIs($item['match']); @endphp
                    <a href="{{ route($item['route']) }}" id="nav-{{ \Illuminate\Support\Str::slug($item['label']) }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium transition-colors {{ $active ? 'bg-indigo-500/20 text-indigo-300' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 {{ $active ? '' : 'opacity-70' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}" /></svg>
                        {{ $item['label'] }}
                    </a>
                @endif
            @endforeach

            <div class="pt-4 mt-4 border-t border-slate-700/50">
                <a href="{{ route('admin.settings') }}" id="nav-settings"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium transition-colors {{ request()->routeIs('admin.settings*') ? 'bg-indigo-500/20 text-indigo-300' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 {{ request()->routeIs('admin.settings*') ? '' : 'opacity-70' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $settingsIcon }}" /></svg>
                    Settings
                </a>
            </div>
        </nav>

        <div class="p-4 border-t border-slate-700/50">
            <a href="{{ route('events.index') }}" class="flex items-center justify-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-medium rounded-lg transition-colors w-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
                Go to Website
            </a>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        <!-- Top Header -->
        <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 sm:px-6 z-10">
            <button @click="mobileNav = !mobileNav" class="md:hidden mr-3 text-gray-500 hover:text-gray-700" aria-label="Toggle menu">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
            </button>

            <div class="flex-1 max-w-lg">
                @if($isAdmin)
                    <form action="{{ route('admin.search') }}" method="GET" class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </span>
                        <input type="text" name="q" value="{{ request()->routeIs('admin.search') ? request('q') : '' }}" id="global-search"
                               class="block w-full rounded-md border-0 py-1.5 pl-10 pr-3 text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 bg-gray-50"
                               placeholder="Cari event, akun, order, atau kode tiket...">
                    </form>
                @endif
            </div>

            <div class="flex items-center gap-5 ml-4">
                {{-- Notifications --}}
                @if($isAdmin)
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" @click.away="open = false" class="relative text-gray-400 hover:text-gray-600 transition" id="notif-bell">
                        <span class="sr-only">Notifications</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" /></svg>
                        @if(($adminNotif['pending_count'] ?? 0) > 0)
                            <span class="absolute -top-1 -right-1 h-4 min-w-4 px-1 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center">{{ $adminNotif['pending_count'] }}</span>
                        @endif
                    </button>
                    <div x-show="open" x-cloak x-transition class="absolute right-0 mt-3 w-80 bg-white rounded-xl shadow-lg ring-1 ring-gray-200 z-50 overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                            <p class="text-sm font-bold text-gray-900">Notifikasi</p>
                            <span class="text-xs text-gray-500">{{ $adminNotif['pending_count'] ?? 0 }} order menunggu bayar</span>
                        </div>
                        <div class="max-h-80 overflow-y-auto divide-y divide-gray-100">
                            @forelse(($adminNotif['recent_paid'] ?? []) as $o)
                                <a href="{{ route('admin.orders.show', $o) }}" class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 transition">
                                    <span class="mt-0.5 h-8 w-8 flex items-center justify-center rounded-full bg-emerald-100 text-emerald-600 text-sm">💰</span>
                                    <span class="min-w-0">
                                        <span class="block text-sm font-semibold text-gray-900 truncate">{{ $o->user->name ?? 'User' }} membayar Rp{{ number_format($o->total_amount, 0, ',', '.') }}</span>
                                        <span class="block text-xs text-gray-500 truncate">{{ $o->event->title ?? '-' }} • {{ $o->paid_at?->diffForHumans() }}</span>
                                    </span>
                                </a>
                            @empty
                                <p class="px-4 py-8 text-center text-sm text-gray-500">Belum ada pembayaran terbaru.</p>
                            @endforelse
                        </div>
                        <a href="{{ route('admin.finances.index') }}" class="block text-center text-xs font-semibold text-indigo-600 hover:bg-indigo-50 py-2.5 border-t border-gray-100">Lihat semua transaksi</a>
                    </div>
                </div>
                @endif

                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" @click.away="open = false" class="flex items-center gap-2 focus:outline-none">
                        <img class="h-8 w-8 rounded-full object-cover bg-gray-200" src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'Admin') }}&background=6366f1&color=fff" alt="">
                        <span class="text-sm font-medium text-gray-700 hidden sm:block">{{ auth()->user()->name ?? 'Admin' }}</span>
                        <svg class="h-4 w-4 text-gray-400 hidden sm:block" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </button>

                    <div x-show="open" x-cloak x-transition class="absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-lg py-1 ring-1 ring-gray-200 z-50">
                        <div class="px-4 py-2 border-b border-gray-100 text-xs text-gray-500">
                            Signed in as <br><strong class="text-gray-700">{{ auth()->user()->name ?? 'Admin' }}</strong>
                            <span class="block text-[11px] text-gray-400 capitalize">{{ auth()->user()->getRoleNames()->first() }}</span>
                        </div>
                        <a href="{{ route('admin.settings') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Pengaturan Akun</a>
                        <a href="{{ route('scanner.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Gate Scanner</a>
                        <form action="{{ route('logout') }}" method="POST" class="border-t border-gray-100">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 font-medium">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
            @yield('content')
        </main>

    </div>

    <script>
        const swalBase = { confirmButtonColor: '#4f46e5', cancelButtonColor: '#94a3b8', background: '#ffffff', color: '#111827', customClass: { popup: 'rounded-2xl' } };
        const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3500, timerProgressBar: true, background: '#f8fafc', color: '#1e293b' });

        document.addEventListener('DOMContentLoaded', () => {
            @if(session('success'))
                Toast.fire({ icon: 'success', title: @json(session('success')) });
            @endif
            @if(session('error'))
                Swal.fire({ ...swalBase, icon: 'error', title: 'Gagal', text: @json(session('error')) });
            @endif
            @if($errors->any() && !View::hasSection('inline_errors'))
                Toast.fire({ icon: 'error', title: 'Periksa kembali isian form Anda.' });
            @endif
        });

        // Generic confirm dialog: <form data-confirm="Pesan" data-confirm-title="Judul" data-confirm-button="Ya">
        document.addEventListener('submit', function (e) {
            const form = e.target;
            if (!form.dataset || !form.dataset.confirm || form.dataset.confirmed === '1') return;
            e.preventDefault();
            const danger = form.dataset.confirmType !== 'primary';
            Swal.fire({
                ...swalBase,
                icon: danger ? 'warning' : 'question',
                title: form.dataset.confirmTitle || 'Apakah Anda yakin?',
                text: form.dataset.confirm,
                showCancelButton: true,
                confirmButtonText: form.dataset.confirmButton || 'Ya, lanjutkan',
                cancelButtonText: 'Batal',
                confirmButtonColor: danger ? '#dc2626' : '#4f46e5',
                reverseButtons: true,
            }).then(r => {
                if (r.isConfirmed) {
                    form.dataset.confirmed = '1';
                    form.submit();
                }
            });
        }, true);
    </script>

    @stack('scripts')
</body>
</html>
