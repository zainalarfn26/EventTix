<!DOCTYPE html>
<html lang="id">
<head>
    <title>@yield('title', 'EventTix - Tiket Konser & Festival')</title>
    <meta name="description" content="@yield('description', 'Beli tiket konser dan festival, simpan QR di ponsel, dan tukar dengan gelang di pintu masuk. Resmi, cepat, tanpa antre.')">
    @include('layouts.partials.head')
    <!-- Midtrans Snap Sandbox JS -->
    <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key', 'SB-Mid-client-dummy-key') }}"></script>
</head>
@php
    $authUser = auth()->user();
    $isAdminUser = $authUser?->hasRole('admin');
    $isOrganizerUser = $authUser?->hasRole('organizer');
    $roleLabel = $isAdminUser ? 'Admin' : ($isOrganizerUser ? 'Organizer' : 'Penonton');
    $navLink = fn ($active) => 'px-3 py-2 text-sm font-medium rounded-lg transition ' . ($active ? 'text-ink bg-gray-100' : 'text-gray-600 hover:text-ink hover:bg-gray-100');
@endphp
<body class="min-h-screen flex flex-col antialiased">

    <header class="sticky top-0 z-50 bg-paper/85 backdrop-blur border-b border-gray-200" x-data="{ mobile: false }">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-4">
            <a href="{{ route('events.index') }}" class="flex items-center gap-2.5 group" aria-label="EventTix">
                <span class="relative h-8 w-8 rounded-lg bg-ink flex items-center justify-center">
                    <svg viewBox="0 0 32 32" class="h-5 w-5" aria-hidden="true"><path d="M5 9h22v5a2.5 2.5 0 000 5v5H5v-5a2.5 2.5 0 000-5z" fill="#FF5A1F"/></svg>
                </span>
                <span class="font-display text-xl font-bold tracking-tight text-ink">EventTix</span>
            </a>

            <nav class="hidden md:flex items-center gap-1 flex-1 ml-6" aria-label="Navigasi utama">
                <a href="{{ route('events.index') }}" class="{{ $navLink(request()->routeIs('events.*')) }}">Event</a>
                @auth
                    @if($isAdminUser)
                        <a href="{{ route('admin.dashboard') }}" class="{{ $navLink(request()->routeIs('admin.*')) }}">Panel Admin</a>
                        <a href="{{ route('scanner.index') }}" class="{{ $navLink(request()->routeIs('scanner.*')) }}">Gate Scanner</a>
                    @elseif($isOrganizerUser)
                        <a href="{{ route('scanner.index') }}" class="{{ $navLink(request()->routeIs('scanner.*')) }}">Gate Scanner</a>
                        <a href="{{ route('admin.dashboard') }}" class="{{ $navLink(request()->routeIs('admin.*')) }}">Dasbor</a>
                    @else
                        <a href="{{ route('user.orders.index') }}" class="{{ $navLink(request()->routeIs('user.orders.*') || request()->routeIs('tickets.*')) }}">Tiket Saya</a>
                    @endif
                @endauth
            </nav>

            <div class="hidden md:flex items-center gap-2">
                @auth
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.away="open = false" class="flex items-center gap-2.5 pl-1.5 pr-2.5 py-1.5 rounded-full border border-gray-200 bg-white hover:border-gray-400 transition" aria-haspopup="true">
                            <span class="h-7 w-7 rounded-full bg-ink text-white text-xs font-bold flex items-center justify-center">{{ strtoupper(mb_substr($authUser->name, 0, 1)) }}</span>
                            <span class="text-sm font-semibold text-ink max-w-[9rem] truncate">{{ $authUser->name }}</span>
                            <x-icon name="chevron-down" class="h-3.5 w-3.5 text-gray-400" />
                        </button>
                        <div x-show="open" x-cloak x-transition.origin.top.right class="absolute right-0 mt-2 w-56 bg-white rounded-xl border border-gray-200 shadow-[0_18px_40px_-18px_rgba(20,19,15,0.35)] py-1.5 z-50">
                            <div class="px-4 py-2.5 border-b border-gray-100">
                                <p class="text-sm font-semibold text-ink truncate">{{ $authUser->name }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ $authUser->email }}</p>
                                <span class="mt-1.5 inline-block text-[10px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-gray-100 text-gray-600">{{ $roleLabel }}</span>
                            </div>
                            @if($isAdminUser || $isOrganizerUser)
                                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><x-icon name="home" class="h-4 w-4 text-gray-400" /> Dasbor</a>
                                <a href="{{ route('scanner.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><x-icon name="qr" class="h-4 w-4 text-gray-400" /> Gate Scanner</a>
                                <a href="{{ route('admin.settings') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><x-icon name="cog" class="h-4 w-4 text-gray-400" /> Pengaturan</a>
                            @else
                                <a href="{{ route('user.orders.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><x-icon name="ticket" class="h-4 w-4 text-gray-400" /> Tiket Saya</a>
                            @endif
                            <form action="{{ route('logout') }}" method="POST" class="border-t border-gray-100 mt-1.5 pt-1.5">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-2.5 px-4 py-2 text-sm text-red-600 hover:bg-red-50 font-medium"><x-icon name="logout" class="h-4 w-4" /> Keluar</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-semibold text-white bg-ink hover:bg-gray-800 rounded-lg transition">Masuk</a>
                @endauth
            </div>

            <button class="md:hidden p-2 -mr-2 text-ink" @click="mobile = !mobile" aria-label="Buka menu">
                <x-icon name="menu" x-show="!mobile" />
                <x-icon name="x" x-show="mobile" x-cloak />
            </button>
        </div>

        {{-- Mobile menu --}}
        <div x-show="mobile" x-cloak x-transition class="md:hidden border-t border-gray-200 bg-paper px-4 py-3 space-y-1">
            <a href="{{ route('events.index') }}" class="block {{ $navLink(request()->routeIs('events.*')) }}">Event</a>
            @auth
                @if($isAdminUser)
                    <a href="{{ route('admin.dashboard') }}" class="block {{ $navLink(false) }}">Panel Admin</a>
                    <a href="{{ route('scanner.index') }}" class="block {{ $navLink(false) }}">Gate Scanner</a>
                @elseif($isOrganizerUser)
                    <a href="{{ route('scanner.index') }}" class="block {{ $navLink(false) }}">Gate Scanner</a>
                    <a href="{{ route('admin.dashboard') }}" class="block {{ $navLink(false) }}">Dasbor</a>
                @else
                    <a href="{{ route('user.orders.index') }}" class="block {{ $navLink(false) }}">Tiket Saya</a>
                @endif
                <form action="{{ route('logout') }}" method="POST" class="pt-2 border-t border-gray-200 mt-2">
                    @csrf
                    <button type="submit" class="w-full text-left px-3 py-2 text-sm font-medium text-red-600">Keluar ({{ $authUser->name }})</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="block text-center px-4 py-2.5 text-sm font-semibold text-white bg-ink rounded-lg">Masuk</a>
            @endauth
        </div>
    </header>

    <main class="flex-1 max-w-6xl w-full mx-auto px-4 sm:px-6 py-8 sm:py-10">
        @yield('content')
    </main>

    <footer class="bg-ink text-white/70 mt-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-10 grid grid-cols-1 md:grid-cols-3 gap-8">
            <div>
                <div class="flex items-center gap-2.5">
                    <span class="h-8 w-8 rounded-lg bg-white/10 flex items-center justify-center">
                        <svg viewBox="0 0 32 32" class="h-5 w-5" aria-hidden="true"><path d="M5 9h22v5a2.5 2.5 0 000 5v5H5v-5a2.5 2.5 0 000-5z" fill="#FF5A1F"/></svg>
                    </span>
                    <span class="font-display text-lg font-bold text-white">EventTix</span>
                </div>
                <p class="mt-4 text-sm leading-relaxed max-w-xs">Tiket konser dan festival dengan QR pribadi. Tunjukkan di gerbang, terima gelang, masuk.</p>
            </div>
            <div>
                <p class="eyebrow !text-white/40">Jelajah</p>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a href="{{ route('events.index') }}" class="hover:text-white transition">Semua event</a></li>
                    @auth
                        @if(!$isAdminUser && !$isOrganizerUser)
                            <li><a href="{{ route('user.orders.index') }}" class="hover:text-white transition">Tiket saya</a></li>
                        @endif
                    @else
                        <li><a href="{{ route('login') }}" class="hover:text-white transition">Masuk ke akun</a></li>
                    @endauth
                </ul>
            </div>
            <div>
                <p class="eyebrow !text-white/40">Cara kerjanya</p>
                <ol class="mt-4 space-y-2.5 text-sm">
                    <li><span class="font-mono text-flame-400 mr-2">01</span>Pilih event &amp; kelas tiket</li>
                    <li><span class="font-mono text-flame-400 mr-2">02</span>Bayar lewat QRIS atau VA</li>
                    <li><span class="font-mono text-flame-400 mr-2">03</span>Scan QR, ambil gelang</li>
                </ol>
            </div>
        </div>
        <div class="border-t border-white/10">
            <p class="max-w-6xl mx-auto px-4 sm:px-6 py-4 text-xs text-white/40">&copy; {{ date('Y') }} EventTix. Pembayaran diproses oleh Midtrans.</p>
        </div>
    </footer>

    @include('layouts.partials.swal')
    @stack('scripts')
</body>
</html>
