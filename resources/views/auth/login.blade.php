@extends('layouts.app')

@section('title', 'Login - EventTix')

@section('content')
<div class="max-w-md mx-auto my-12" x-data="{ email: '', password: '' }">
    <div class="bg-white border border-gray-200 rounded-2xl p-8 shadow-sm space-y-6">
        <div class="text-center">
            <div class="w-14 h-14 bg-indigo-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Masuk ke EventTix</h1>
            <p class="text-sm text-gray-500 mt-1">Akses dashboard admin, organizer, atau akun penonton.</p>
        </div>

        @if($errors->any())
            <div class="p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-600 font-medium">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- Quick Demo Fill Buttons -->
        <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 space-y-3">
            <span class="text-xs font-semibold text-gray-500 block text-center uppercase tracking-wider">Demo Login 1-Klik</span>
            <div class="grid grid-cols-3 gap-2">
                <button
                    type="button"
                    @click="email = 'admin@seatpulse.com'; password = 'password123'"
                    class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold py-2 px-2 rounded-lg border border-indigo-200 transition">
                    Admin
                </button>
                <button
                    type="button"
                    @click="email = 'organizer@seatpulse.com'; password = 'password123'"
                    class="bg-purple-50 hover:bg-purple-100 text-purple-700 text-xs font-semibold py-2 px-2 rounded-lg border border-purple-200 transition">
                    Organizer
                </button>
                <button
                    type="button"
                    @click="email = 'customer@seatpulse.com'; password = 'password123'"
                    class="bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-semibold py-2 px-2 rounded-lg border border-emerald-200 transition">
                    Customer
                </button>
            </div>
        </div>

        <form action="{{ route('login') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Alamat Email</label>
                <input
                    type="email"
                    name="email"
                    x-model="email"
                    placeholder="nama@email.com"
                    class="w-full bg-white border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 rounded-xl px-4 py-3 text-gray-900 text-sm outline-none transition"
                    required
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                <input
                    type="password"
                    name="password"
                    x-model="password"
                    placeholder="••••••••"
                    class="w-full bg-white border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 rounded-xl px-4 py-3 text-gray-900 text-sm outline-none transition"
                    required
                />
            </div>

            <button
                type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-xl shadow-sm transition">
                Masuk Sekarang
            </button>
        </form>
    </div>
</div>
@endsection
