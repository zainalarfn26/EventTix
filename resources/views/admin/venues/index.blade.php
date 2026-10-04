@extends('layouts.admin')

@section('title', 'Kelola Venue - Admin EventTix')

@section('content')
<div class="max-w-7xl mx-auto" x-data="venuePage()">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Venue</h1>
            <p class="text-sm text-gray-500 mt-1">Lokasi tempat event diselenggarakan.</p>
        </div>
        <button @click="openCreate()" id="btn-add-venue" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition shadow-sm">+ Tambah Venue</button>
    </div>

    <form method="GET" class="bg-white border border-gray-200 rounded-xl p-4 mb-6 flex gap-3">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama, kota, atau alamat..." class="flex-1 bg-gray-50 border border-gray-200 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
        <button type="submit" class="px-5 py-2 bg-gray-900 hover:bg-gray-800 text-white text-sm font-semibold rounded-lg transition">Cari</button>
        @if(request('q'))<a href="{{ route('admin.venues.index') }}" class="px-3 py-2 text-sm font-semibold text-gray-500 hover:text-gray-700">Reset</a>@endif
    </form>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        @forelse($venues as $venue)
            @php
                $payload = [
                    'id' => $venue->id, 'name' => $venue->name, 'city' => $venue->city, 'address' => $venue->address,
                    'capacity' => $venue->capacity, 'image' => $venue->layout_image ? asset('storage/' . $venue->layout_image) : null,
                ];
            @endphp
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden flex flex-col">
                @if($venue->layout_image)
                    <img src="{{ asset('storage/' . $venue->layout_image) }}" alt="Denah {{ $venue->name }}" class="h-36 w-full object-cover bg-gray-100">
                @else
                    <div class="h-36 bg-gradient-to-br from-indigo-50 to-sky-50 flex items-center justify-center text-4xl">🏟️</div>
                @endif
                <div class="p-5 flex-1 flex flex-col">
                    <h2 class="text-base font-bold text-gray-900">{{ $venue->name }}</h2>
                    <p class="text-sm text-gray-500 mt-0.5">{{ $venue->city }}</p>
                    @if($venue->address)<p class="text-xs text-gray-400 mt-2 line-clamp-2">{{ $venue->address }}</p>@endif
                    <div class="mt-3 flex items-center gap-3 text-xs font-semibold">
                        <span class="px-2.5 py-1 bg-gray-50 border border-gray-100 rounded-md text-gray-600">👥 {{ number_format($venue->capacity) }}</span>
                        <span class="px-2.5 py-1 bg-indigo-50 border border-indigo-100 rounded-md text-indigo-600">📅 {{ $venue->events_count }} event</span>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-100 flex gap-2 mt-auto">
                        <button type="button" @click='openEdit(@json($payload, JSON_HEX_APOS | JSON_HEX_AMP))'
                                class="flex-1 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold rounded-lg transition border border-indigo-100">Edit</button>
                        <form action="{{ route('admin.venues.destroy', $venue) }}" method="POST" data-confirm="Venue &quot;{{ $venue->name }}&quot; akan dihapus permanen." data-confirm-title="Hapus venue?" data-confirm-button="Ya, hapus">
                            @csrf @method('DELETE')
                            <button type="submit" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 text-xs font-semibold rounded-lg transition border border-red-200">Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="md:col-span-2 xl:col-span-3 text-center py-12 text-gray-500 bg-white rounded-xl border border-gray-200">Belum ada venue.</div>
        @endforelse
    </div>

    {{-- Modal --}}
    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/50" @click="open = false"></div>
        <form x-ref="form" :action="mode === 'edit' ? updateUrl.replace('__ID__', form.id) : storeUrl" method="POST" enctype="multipart/form-data"
              x-show="open" x-transition class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
            @csrf
            <input type="hidden" name="_method" value="PUT" :disabled="mode !== 'edit'">
            <input type="hidden" name="_mode" :value="mode">
            <input type="hidden" name="_id" :value="form.id ?? ''">

            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900" x-text="mode === 'edit' ? 'Edit Venue' : 'Tambah Venue'"></h3>
                <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
            </div>

            <div class="p-6 space-y-4">
                @if($errors->any() && old('_mode'))
                    <div class="p-3 rounded-xl bg-red-50 text-red-600 border border-red-200 text-sm">
                        <ul class="list-disc list-inside space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Venue *</label>
                    <input type="text" name="name" x-model="form.name" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kota *</label>
                        <input type="text" name="city" x-model="form.city" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kapasitas *</label>
                        <input type="number" name="capacity" min="0" x-model="form.capacity" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                    <textarea name="address" rows="2" x-model="form.address" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none resize-none"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Denah / Gambar Venue</label>
                    <template x-if="form.image && !form.remove">
                        <div class="mb-2 relative h-28 rounded-xl overflow-hidden border border-gray-200">
                            <img :src="form.image" class="w-full h-full object-cover" alt="">
                            <button type="button" @click="form.remove = true" class="absolute top-2 right-2 bg-white/90 text-red-600 text-xs font-semibold px-2.5 py-1 rounded-lg shadow">Hapus gambar</button>
                        </div>
                    </template>
                    <input type="hidden" name="remove_layout" :value="form.remove ? 1 : 0">
                    <input type="file" name="layout_image" accept="image/*" @change="if ($event.target.files[0]) { form.image = URL.createObjectURL($event.target.files[0]); form.remove = false }"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm file:mr-3 file:bg-indigo-600 file:text-white file:font-semibold file:border-0 file:rounded-lg file:px-3 file:py-1 file:text-xs">
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3 rounded-b-2xl">
                <button type="button" @click="open = false" class="px-5 py-2.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-semibold rounded-xl transition">Batal</button>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition" x-text="mode === 'edit' ? 'Simpan Perubahan' : 'Tambah Venue'"></button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function venuePage() {
    const blank = { id: null, name: '', city: '', address: '', capacity: '', image: null, remove: false };
    const hasOld = @json((bool) old('_mode'));
    return {
        open: hasOld,
        mode: @json(old('_mode', 'create')),
        storeUrl: @json(route('admin.venues.store')),
        updateUrl: @json(route('admin.venues.update', '__ID__')),
        form: hasOld
            ? { id: @json(old('_id')), name: @json(old('name', '')), city: @json(old('city', '')), address: @json(old('address', '')), capacity: @json(old('capacity', '')), image: null, remove: false }
            : { ...blank },
        openCreate() { this.mode = 'create'; this.form = { ...blank }; this.open = true; },
        openEdit(v) { this.mode = 'edit'; this.form = { ...blank, ...v }; this.open = true; },
    };
}
</script>
@endpush
