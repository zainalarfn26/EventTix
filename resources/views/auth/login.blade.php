@extends('layouts.app')

@section('title', 'Masuk - EventTix')

@section('content')
<div class="max-w-4xl mx-auto my-6 md:my-10 grid md:grid-cols-[1fr_1.1fr] rounded-2xl overflow-hidden border border-gray-200 bg-white"
     x-data="{ email: '', password: '' }">

    {{-- Brand panel --}}
    <div class="relative bg-ink text-white p-8 md:p-10 flex flex-col justify-between min-h-[220px]">
        <div>
            <p class="eyebrow !text-white/50">EventTix</p>
            <h1 class="font-display text-3xl md:text-4xl font-bold leading-[1.05] mt-4">Satu akun,<br>semua tiketmu.</h1>
            <p class="mt-4 text-sm text-white/60 max-w-xs leading-relaxed">Masuk untuk melihat tiket, mengelola event, atau bertugas di gerbang.</p>
        </div>
        <div class="mt-10 space-y-1.5" aria-hidden="true">
            <div class="h-2.5 rounded-sm" style="background:#FF5A1F;width:100%"></div>
            <div class="h-2.5 rounded-sm" style="background:#F6F3EC;width:78%"></div>
            <div class="h-2.5 rounded-sm" style="background:#8B8677;width:56%"></div>
            <div class="h-2.5 rounded-sm" style="background:#3F3B35;width:34%"></div>
        </div>
    </div>

    {{-- Form --}}
    <div class="p-8 md:p-10">
        <h2 class="font-display text-2xl font-bold">Masuk</h2>
        <p class="text-sm text-gray-500 mt-1">Gunakan email dan password akunmu.</p>

        @if($errors->any())
            <div class="mt-5 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 font-medium">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST" class="mt-6 space-y-4">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                <input id="email" type="email" name="email" x-model="email" placeholder="nama@email.com" required
                       class="w-full bg-white border border-gray-300 focus:border-ink focus:ring-0 rounded-lg px-4 py-3 text-sm outline-none transition">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                <input id="password" type="password" name="password" x-model="password" placeholder="Password" required
                       class="w-full bg-white border border-gray-300 focus:border-ink focus:ring-0 rounded-lg px-4 py-3 text-sm outline-none transition">
            </div>
            <button type="submit" class="w-full bg-ink hover:bg-gray-800 text-white font-semibold py-3 rounded-lg transition flex items-center justify-center gap-2">
                Masuk <x-icon name="arrow-right" class="h-4 w-4" />
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-dashed border-gray-300">
            <p class="eyebrow mb-3">Akun demo</p>
            <div class="flex flex-wrap gap-2">
                <button type="button" @click="email = 'admin@seatpulse.com'; password = 'password123'"
                        class="text-xs font-semibold px-3 py-1.5 rounded-full border border-gray-300 hover:border-ink hover:bg-paper transition">Admin</button>
                <button type="button" @click="email = 'organizer@seatpulse.com'; password = 'password123'"
                        class="text-xs font-semibold px-3 py-1.5 rounded-full border border-gray-300 hover:border-ink hover:bg-paper transition">Organizer</button>
                <button type="button" @click="email = 'customer@seatpulse.com'; password = 'password123'"
                        class="text-xs font-semibold px-3 py-1.5 rounded-full border border-gray-300 hover:border-ink hover:bg-paper transition">Customer</button>
            </div>
        </div>
    </div>
</div>
@endsection
