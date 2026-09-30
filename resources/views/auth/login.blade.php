@extends('layouts.app')

@section('title', 'Login - SeatPulse')

@section('content')
<div class="max-w-md mx-auto my-12" x-data="{ email: '', password: '' }">
    <div class="bg-slate-800 border border-slate-700 rounded-3xl p-8 shadow-2xl space-y-6">
        <div class="text-center">
            <span class="text-4xl">🔐</span>
            <h1 class="text-2xl font-black text-white mt-2">Masuk ke SeatPulse</h1>
            <p class="text-xs text-slate-400 mt-1">Akses dashboard admin, organizer, atau akun penonton.</p>
        </div>

        @if($errors->any())
            <div class="p-3 bg-rose-500/20 border border-rose-500/50 rounded-xl text-xs text-rose-300">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- Quick Demo Fill Buttons -->
        <div class="bg-slate-900/80 p-3 rounded-xl border border-slate-700/60 space-y-2">
            <span class="text-xs font-bold text-slate-400 block text-center uppercase tracking-wider">⚡ Demo Login 1-Klik</span>
            <div class="grid grid-cols-3 gap-2">
                <button
                    type="button"
                    @click="email = 'admin@seatpulse.com'; password = 'password123'"
                    class="bg-indigo-600/30 hover:bg-indigo-600 text-indigo-200 text-xs font-bold py-1.5 px-2 rounded-lg border border-indigo-500/40 transition">
                    👑 Admin
                </button>
                <button
                    type="button"
                    @click="email = 'organizer@seatpulse.com'; password = 'password123'"
                    class="bg-purple-600/30 hover:bg-purple-600 text-purple-200 text-xs font-bold py-1.5 px-2 rounded-lg border border-purple-500/40 transition">
                    🎪 Organizer
                </button>
                <button
                    type="button"
                    @click="email = 'customer@seatpulse.com'; password = 'password123'"
                    class="bg-emerald-600/30 hover:bg-emerald-600 text-emerald-200 text-xs font-bold py-1.5 px-2 rounded-lg border border-emerald-500/40 transition">
                    🎟️ Customer
                </button>
            </div>
        </div>

        <form action="{{ route('login') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Alamat Email</label>
                <input
                    type="email"
                    name="email"
                    x-model="email"
                    placeholder="nama@email.com"
                    class="w-full bg-slate-900 border border-slate-700 focus:border-indigo-500 rounded-xl px-4 py-3 text-white text-sm focus:outline-none"
                    required
                />
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Password</label>
                <input
                    type="password"
                    name="password"
                    x-model="password"
                    placeholder="••••••••"
                    class="w-full bg-slate-900 border border-slate-700 focus:border-indigo-500 rounded-xl px-4 py-3 text-white text-sm focus:outline-none"
                    required
                />
            </div>

            <button
                type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 rounded-xl shadow-lg transition">
                Masuk Sekarang ➔
            </button>
        </form>
    </div>
</div>
@endsection
