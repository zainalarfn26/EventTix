<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SeatPulse - Real-Time Event Ticketing')</title>

    <!-- TailwindCSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Midtrans Snap Sandbox JS -->
    <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key', 'SB-Mid-client-dummy-key') }}"></script>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex flex-col font-sans">

    <!-- Header Navigation -->
    <nav class="bg-slate-800/80 backdrop-blur border-b border-slate-700 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ route('events.index') }}" class="flex items-center gap-2 text-xl font-bold text-indigo-400 hover:text-indigo-300">
                <span class="text-2xl">🎟️</span>
                <span>Seat<span class="text-white">Pulse</span></span>
            </a>

            <div class="flex items-center gap-4">
                <a href="{{ route('events.index') }}" class="text-sm font-medium hover:text-indigo-400 transition">Jadwal Event</a>
                <a href="{{ route('scanner.index') }}" class="text-sm font-medium bg-slate-700 hover:bg-slate-600 text-slate-200 px-3 py-1.5 rounded-lg border border-slate-600 transition">
                    📱 Gate Scanner
                </a>

                @auth
                    @if(auth()->user()->hasAnyRole(['admin', 'organizer']))
                        <a href="{{ route('admin.dashboard') }}" class="text-xs font-bold bg-indigo-600 hover:bg-indigo-500 text-white px-3 py-1.5 rounded-lg transition">
                            👑 Admin Panel
                        </a>
                    @endif

                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-slate-300 font-semibold">👤 {{ auth()->user()->name }}</span>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-slate-400 hover:text-rose-400 font-bold underline">Logout</button>
                        </form>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="text-xs font-bold bg-slate-700 hover:bg-slate-600 text-white px-3.5 py-1.5 rounded-lg border border-slate-600 transition">
                        🔑 Login
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-slate-800/40 border-t border-slate-800 text-center py-6 text-slate-500 text-sm">
        <p>© {{ date('Y') }} SeatPulse Ticketing Platform. Built for High-Concurrency & Production Enterprise Usage.</p>
    </footer>

    @stack('scripts')
</body>
</html>
