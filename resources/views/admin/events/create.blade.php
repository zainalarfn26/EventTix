@extends('layouts.app')

@section('title', 'Buat Event Baru - Admin EventTix')

@section('content')
<div x-data="createEventForm()">
    <div class="mb-6">
        <a href="{{ route('admin.events.index') }}" class="text-sm text-indigo-400 hover:text-indigo-300 inline-flex items-center gap-1">
            ← Kembali ke Daftar Event
        </a>
        <h1 class="text-2xl font-extrabold text-white mt-2">➕ Buat Event Baru</h1>
    </div>

    @if($errors->any())
        <div class="mb-4 p-3 rounded-xl bg-red-500/20 text-red-300 border border-red-500/30 text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.events.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Left: Event Details --}}
            <div class="bg-slate-800/60 border border-slate-700 rounded-xl p-6 space-y-4">
                <h2 class="text-lg font-bold text-white mb-2">📋 Detail Event</h2>

                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-1">Judul Event *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                           class="w-full bg-slate-900/60 border border-slate-600 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                           placeholder="Contoh: Coldplay World Tour Jakarta">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-1">Venue *</label>
                    <select name="venue_id" required
                            class="w-full bg-slate-900/60 border border-slate-600 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="">Pilih Venue...</option>
                        @foreach($venues as $venue)
                            <option value="{{ $venue->id }}" {{ old('venue_id') == $venue->id ? 'selected' : '' }}>
                                {{ $venue->name }} — {{ $venue->city }} (Kapasitas {{ number_format($venue->capacity) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-1">Deskripsi</label>
                    <textarea name="description" rows="3"
                              class="w-full bg-slate-900/60 border border-slate-600 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none resize-none"
                              placeholder="Deskripsi event...">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-1">Banner Image</label>
                    <input type="file" name="banner_image" accept="image/*"
                           class="w-full bg-slate-900/60 border border-slate-600 text-white rounded-xl px-4 py-2.5 text-sm file:mr-3 file:bg-indigo-600 file:text-white file:font-bold file:border-0 file:rounded-lg file:px-3 file:py-1 file:text-xs">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-300 mb-1">Mulai *</label>
                        <input type="datetime-local" name="start_time" value="{{ old('start_time') }}" required
                               class="w-full bg-slate-900/60 border border-slate-600 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-300 mb-1">Selesai *</label>
                        <input type="datetime-local" name="end_time" value="{{ old('end_time') }}" required
                               class="w-full bg-slate-900/60 border border-slate-600 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-1">Status *</label>
                    <select name="status" required
                            class="w-full bg-slate-900/60 border border-slate-600 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ old('status', 'published') === 'published' ? 'selected' : '' }}>Published</option>
                    </select>
                </div>
            </div>

            {{-- Right: Ticket Tiers --}}
            <div class="bg-slate-800/60 border border-slate-700 rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-white">🎫 Kelas Tiket</h2>
                    <button type="button" @click="addTier()"
                            class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-lg transition">
                        + Tambah Kelas
                    </button>
                </div>

                <div class="space-y-4">
                    <template x-for="(tier, index) in tiers" :key="index">
                        <div class="bg-slate-900/40 border border-slate-600 rounded-xl p-4 relative">
                            {{-- Remove button --}}
                            <button type="button" @click="removeTier(index)" x-show="tiers.length > 1"
                                    class="absolute top-2 right-2 w-6 h-6 flex items-center justify-center bg-red-500/20 hover:bg-red-500/40 text-red-300 rounded-full text-xs font-bold transition">
                                ✕
                            </button>

                            <div class="grid grid-cols-2 gap-3 mb-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-400 mb-1">Nama Kelas *</label>
                                    <input type="text" :name="`tiers[${index}][name]`" x-model="tier.name" required
                                           class="w-full bg-slate-800 border border-slate-600 text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none"
                                           placeholder="VVIP / VIP / Regular">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-400 mb-1">Harga (Rp) *</label>
                                    <input type="number" :name="`tiers[${index}][price]`" x-model="tier.price" required min="0"
                                           class="w-full bg-slate-800 border border-slate-600 text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none"
                                           placeholder="500000">
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-3 mb-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-400 mb-1">Kuota *</label>
                                    <input type="number" :name="`tiers[${index}][quota]`" x-model="tier.quota" required min="1"
                                           class="w-full bg-slate-800 border border-slate-600 text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none"
                                           placeholder="100">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-400 mb-1">Warna Zona *</label>
                                    <div class="flex items-center gap-2">
                                        <input type="color" :name="`tiers[${index}][color]`" x-model="tier.color"
                                               class="w-10 h-10 rounded-lg cursor-pointer border-0">
                                        <span class="text-xs text-slate-400 font-mono" x-text="tier.color"></span>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-400 mb-1">Warna Gelang</label>
                                    <input type="text" :name="`tiers[${index}][wristband_color]`" x-model="tier.wristband_color"
                                           class="w-full bg-slate-800 border border-slate-600 text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none"
                                           placeholder="Emas / Ungu / dll">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-400 mb-1">Label Zona</label>
                                    <input type="text" :name="`tiers[${index}][zone_label]`" x-model="tier.zone_label"
                                           class="w-full bg-slate-800 border border-slate-600 text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none"
                                           placeholder="Zona Depan Panggung">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-400 mb-1">Deskripsi</label>
                                    <input type="text" :name="`tiers[${index}][description]`" x-model="tier.description"
                                           class="w-full bg-slate-800 border border-slate-600 text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none"
                                           placeholder="Benefit kelas ini...">
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- Submit --}}
        <div class="mt-6 flex justify-end">
            <button type="submit"
                    class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold rounded-xl text-lg transition-all shadow-lg">
                🚀 Buat Event
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function createEventForm() {
    return {
        tiers: [
            { name: 'VVIP', price: '', quota: '', color: '#f59e0b', zone_label: '', wristband_color: 'Emas', description: '' },
            { name: 'VIP', price: '', quota: '', color: '#8b5cf6', zone_label: '', wristband_color: 'Ungu', description: '' },
            { name: 'REGULAR', price: '', quota: '', color: '#3b82f6', zone_label: '', wristband_color: 'Biru', description: '' },
        ],

        addTier() {
            const colors = ['#ef4444', '#22c55e', '#ec4899', '#14b8a6', '#f97316', '#06b6d4'];
            this.tiers.push({
                name: '',
                price: '',
                quota: '',
                color: colors[this.tiers.length % colors.length],
                zone_label: '',
                wristband_color: '',
                description: '',
            });
        },

        removeTier(index) {
            this.tiers.splice(index, 1);
        }
    };
}
</script>
@endpush
