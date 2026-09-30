@extends('layouts.app')

@section('title', 'Buat Event Baru - Admin SeatPulse')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <a href="{{ route('admin.events.index') }}" class="text-xs text-indigo-400 font-semibold hover:underline">← Kembali ke Daftar Event</a>
        <h1 class="text-3xl font-black text-white mt-1">Tambah Event & Konser Baru</h1>
    </div>

    <div class="bg-slate-800 border border-slate-700 rounded-3xl p-8 shadow-2xl">
        <form action="{{ route('admin.events.store') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Pilih Venue Konser</label>
                <select name="venue_id" class="w-full bg-slate-900 border border-slate-700 focus:border-indigo-500 rounded-xl px-4 py-3 text-white text-sm focus:outline-none" required>
                    @foreach($venues as $v)
                        <option value="{{ $v->id }}">{{ $v->name }} ({{ $v->city }}) — Kapasitas {{ $v->capacity }} Kursi</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Judul Event / Nama Konser</label>
                <input type="text" name="title" placeholder="Contoh: Bruno Mars Live in Jakarta 2026" class="w-full bg-slate-900 border border-slate-700 focus:border-indigo-500 rounded-xl px-4 py-3 text-white text-sm focus:outline-none" required />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Waktu Mulai</label>
                    <input type="datetime-local" name="start_time" class="w-full bg-slate-900 border border-slate-700 focus:border-indigo-500 rounded-xl px-4 py-3 text-white text-sm focus:outline-none" required />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Waktu Selesai</label>
                    <input type="datetime-local" name="end_time" class="w-full bg-slate-900 border border-slate-700 focus:border-indigo-500 rounded-xl px-4 py-3 text-white text-sm focus:outline-none" required />
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Deskripsi Event</label>
                <textarea name="description" rows="4" placeholder="Jelaskan keseruan konser & detail lineup penampil..." class="w-full bg-slate-900 border border-slate-700 focus:border-indigo-500 rounded-xl px-4 py-3 text-white text-sm focus:outline-none"></textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Status Publikasi</label>
                <select name="status" class="w-full bg-slate-900 border border-slate-700 focus:border-indigo-500 rounded-xl px-4 py-3 text-white text-sm focus:outline-none">
                    <option value="published">Published (Langsung Tampil & Bisa Dipesan)</option>
                    <option value="draft">Draft (Simpan Sementara)</option>
                </select>
            </div>

            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 rounded-xl shadow-lg transition">
                🚀 Simpan & Publikasikan Event
            </button>
        </form>
    </div>
</div>
@endsection
