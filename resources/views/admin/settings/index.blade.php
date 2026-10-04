@extends('layouts.admin')

@section('title', 'Settings - Admin EventTix')

@section('content')
<div class="max-w-5xl mx-auto space-y-8">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Settings</h1>
        <p class="text-sm text-gray-500 mt-1">Kelola profil akun Anda, keamanan kata sandi, dan informasi konfigurasi sistem.</p>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-xl bg-red-50 text-red-700 border border-red-200 text-sm">
            <p class="font-semibold mb-1">Perhatian:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        {{-- Profile Settings Card --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-4 mb-6 pb-6 border-b border-gray-100">
                    <img class="h-16 w-16 rounded-full object-cover border-2 border-indigo-100" 
                         src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=6366f1&color=fff&size=128" 
                         alt="{{ $user->name }}">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">{{ $user->name }}</h2>
                        <p class="text-sm text-gray-500">{{ $user->email }}</p>
                        <span class="inline-flex mt-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wider
                            {{ $user->hasRole('admin') ? 'bg-rose-100 text-rose-700' : 'bg-indigo-100 text-indigo-700' }}">
                            {{ $user->getRoleNames()->first() ?? 'User' }}
                        </span>
                    </div>
                </div>

                <form action="{{ route('admin.settings.profile') }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                               class="w-full bg-white border border-gray-300 text-gray-900 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                               class="w-full bg-white border border-gray-300 text-gray-900 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition shadow-sm">
                            Simpan Perubahan Profil
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Password Change Card --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-3 mb-6 pb-6 border-b border-gray-100">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-lg">
                        🔒
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900">Ubah Kata Sandi</h2>
                        <p class="text-xs text-gray-500">Perbarui kata sandi untuk menjaga keamanan akun Anda.</p>
                    </div>
                </div>

                <form action="{{ route('admin.settings.password') }}" method="POST" class="space-y-4" autocomplete="off">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi Saat Ini</label>
                        <input type="password" name="current_password" required
                               class="w-full bg-white border border-gray-300 text-gray-900 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi Baru (Min. 8 karakter)</label>
                        <input type="password" name="password" required minlength="8"
                               class="w-full bg-white border border-gray-300 text-gray-900 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Kata Sandi Baru</label>
                        <input type="password" name="password_confirmation" required minlength="8"
                               class="w-full bg-white border border-gray-300 text-gray-900 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition">
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full px-5 py-2.5 bg-gray-900 hover:bg-gray-800 text-white text-sm font-semibold rounded-xl transition shadow-sm">
                            Perbarui Kata Sandi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- System Information --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
        <h2 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
            <span>⚙️</span>
            <span>Informasi Sistem & Lingkungan Aplikasi</span>
        </h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 text-sm">
            <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                <p class="text-xs text-gray-500">Versi PHP</p>
                <p class="font-semibold text-gray-900 mt-0.5">{{ $systemInfo['php_version'] }}</p>
            </div>
            <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                <p class="text-xs text-gray-500">Framework</p>
                <p class="font-semibold text-gray-900 mt-0.5">Laravel v{{ $systemInfo['laravel_version'] }}</p>
            </div>
            <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                <p class="text-xs text-gray-500">Environment</p>
                <p class="font-semibold text-gray-900 mt-0.5 capitalize">{{ $systemInfo['app_env'] }}</p>
            </div>
            <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                <p class="text-xs text-gray-500">Midtrans Gateway</p>
                <p class="font-semibold text-emerald-600 mt-0.5">{{ $systemInfo['midtrans_is_production'] }}</p>
            </div>
            <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                <p class="text-xs text-gray-500">Database Driver</p>
                <p class="font-semibold text-gray-900 mt-0.5 uppercase">{{ $systemInfo['database'] }}</p>
            </div>
            <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                <p class="text-xs text-gray-500">App Debug Mode</p>
                <p class="font-semibold text-gray-900 mt-0.5">{{ $systemInfo['app_debug'] }}</p>
            </div>
            <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 sm:col-span-2">
                <p class="text-xs text-gray-500">Waktu Server</p>
                <p class="font-semibold text-gray-900 mt-0.5">{{ $systemInfo['server_time'] }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
