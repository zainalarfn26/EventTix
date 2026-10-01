<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'EventTix - Real-Time Event Ticketing')</title>

    <!-- TailwindCSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Midtrans Snap Sandbox JS -->
    <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key', 'SB-Mid-client-dummy-key') }}"></script>
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col antialiased">

    <!-- Header Navigation -->
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ route('events.index') }}" class="flex items-center gap-2 text-xl font-bold text-indigo-600 hover:text-indigo-500 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                <span>Event<span class="text-gray-900">Tix</span></span>
            </a>

            <div class="flex items-center gap-3">
                <a href="{{ route('events.index') }}" class="text-sm font-medium text-gray-600 hover:text-indigo-600 transition px-3 py-2 rounded-lg hover:bg-gray-100">Events</a>

                @auth
                    @role('admin')
                        <a href="{{ route('scanner.index') }}" class="text-sm font-medium text-gray-600 hover:text-indigo-600 transition px-3 py-2 rounded-lg hover:bg-gray-100">
                            Gate Scanner
                        </a>
                        <a href="{{ route('admin.dashboard') }}" class="text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition">
                            Admin Panel
                        </a>
                    @elserole('organizer')
                        <a href="{{ route('scanner.index') }}" class="text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition">
                            Gate Scanner
                        </a>
                    @else
                        <a href="{{ route('user.orders.index') }}" class="text-sm font-medium text-gray-600 hover:text-indigo-600 transition px-3 py-2 rounded-lg hover:bg-gray-100">
                            Tiket Saya
                        </a>
                    @endrole

                    <div class="relative ml-1" x-data="{ open: false }">
                        <button @click="open = !open" @click.away="open = false" class="flex items-center gap-2 focus:outline-none px-2 py-1.5 rounded-lg hover:bg-gray-100 transition">
                            <img class="h-8 w-8 rounded-full object-cover" src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=6366f1&color=fff&size=64" alt="">
                            <span class="text-sm font-medium text-gray-700 hidden sm:block">{{ auth()->user()->name }}</span>
                            <svg class="h-4 w-4 text-gray-400 hidden sm:block" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <div x-show="open" x-cloak x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg py-1 ring-1 ring-gray-200 z-50">
                            <div class="px-4 py-2.5 border-b border-gray-100">
                                <p class="text-xs text-gray-500">Signed in as</p>
                                <p class="text-sm font-semibold text-gray-800">{{ auth()->user()->name }}</p>
                            </div>
                            @role('admin')
                                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Dashboard</a>
                                <a href="{{ route('scanner.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Gate Scanner</a>
                            @elserole('organizer')
                                <a href="{{ route('scanner.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Gate Scanner</a>
                            @else
                                <a href="{{ route('user.orders.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Tiket Saya</a>
                            @endrole
                            <form action="{{ route('logout') }}" method="POST" class="border-t border-gray-100">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 font-medium">Logout</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition">
                        Login
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
    <footer class="bg-white border-t border-gray-200 text-center py-6 text-gray-400 text-sm">
        <p>&copy; {{ date('Y') }} EventTix Ticketing Platform.</p>
    </footer>

    @stack('scripts')
</body>
</html>
