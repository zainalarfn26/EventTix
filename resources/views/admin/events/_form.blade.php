@php
    $isEdit = isset($event);
    $defaultTiers = [
        ['id' => null, 'name' => 'VVIP', 'price' => '', 'quota' => '', 'color' => '#f59e0b', 'zone_label' => '', 'wristband_color' => 'Emas', 'description' => '', 'is_active' => true, 'sold_count' => 0],
        ['id' => null, 'name' => 'VIP', 'price' => '', 'quota' => '', 'color' => '#8b5cf6', 'zone_label' => '', 'wristband_color' => 'Ungu', 'description' => '', 'is_active' => true, 'sold_count' => 0],
        ['id' => null, 'name' => 'REGULAR', 'price' => '', 'quota' => '', 'color' => '#3b82f6', 'zone_label' => '', 'wristband_color' => 'Biru', 'description' => '', 'is_active' => true, 'sold_count' => 0],
    ];

    if ($isEdit) {
        $defaultTiers = $event->ticketTiers->map(fn ($t) => [
            'id' => $t->id, 'name' => $t->name, 'price' => (int) $t->price, 'quota' => $t->quota,
            'color' => $t->color, 'zone_label' => $t->zone_label ?? '', 'wristband_color' => $t->wristband_color ?? '',
            'description' => $t->description ?? '', 'is_active' => (bool) $t->is_active, 'sold_count' => $t->sold_count,
        ])->values()->all();
    }

    $tiersInit = old('tiers')
        ? collect(old('tiers'))->map(fn ($t) => [
            'id' => $t['id'] ?? null, 'name' => $t['name'] ?? '', 'price' => $t['price'] ?? '', 'quota' => $t['quota'] ?? '',
            'color' => $t['color'] ?? '#6366f1', 'zone_label' => $t['zone_label'] ?? '', 'wristband_color' => $t['wristband_color'] ?? '',
            'description' => $t['description'] ?? '', 'is_active' => !empty($t['is_active']),
            'sold_count' => $isEdit && !empty($t['id']) ? ($event->ticketTiers->firstWhere('id', (int) $t['id'])->sold_count ?? 0) : 0,
        ])->values()->all()
        : $defaultTiers;

    $val = fn ($key, $default = '') => old($key, $isEdit ? ($event->{$key} ?? $default) : $default);
    $fmtDate = fn ($key) => old($key, $isEdit ? $event->{$key}?->format('Y-m-d\TH:i') : '');
    $inputCls = 'w-full bg-white border border-gray-300 text-gray-900 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition';
    $tierCls = 'w-full bg-white border border-gray-300 text-gray-900 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition';
@endphp

@if($errors->any())
    <div class="mb-4 p-3 rounded-xl bg-red-50 text-red-600 border border-red-200 text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ $isEdit ? route('admin.events.update', $event) : route('admin.events.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Left: Event Details --}}
        <div class="bg-white border border-gray-200 rounded-xl p-6 space-y-4 shadow-sm">
            <h2 class="text-lg font-bold text-gray-900 mb-2">Detail Event</h2>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Judul Event *</label>
                <input type="text" name="title" value="{{ $val('title') }}" required class="{{ $inputCls }}" placeholder="Contoh: Coldplay World Tour Jakarta">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Venue *</label>
                <select name="venue_id" required class="{{ $inputCls }}">
                    <option value="">Pilih Venue...</option>
                    @foreach($venues as $venue)
                        <option value="{{ $venue->id }}" {{ (string) $val('venue_id') === (string) $venue->id ? 'selected' : '' }}>
                            {{ $venue->name }} — {{ $venue->city }} (Kapasitas {{ number_format($venue->capacity) }})
                        </option>
                    @endforeach
                </select>
                @if($venues->isEmpty())
                    <p class="text-xs text-amber-600 mt-1">Belum ada venue. <a href="{{ route('admin.venues.index') }}" class="underline font-semibold">Tambah venue dulu</a>.</p>
                @endif
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea name="description" rows="3" class="{{ $inputCls }} resize-none" placeholder="Deskripsi event...">{{ $val('description') }}</textarea>
            </div>

            <div x-data='{ preview: @json($isEdit && $event->banner_image ? asset("storage/" . $event->banner_image) : null), removed: false }'>
                <label class="block text-sm font-medium text-gray-700 mb-1">Banner Image</label>
                <template x-if="preview && !removed">
                    <div class="mb-2 relative rounded-xl overflow-hidden border border-gray-200 h-36 bg-gray-100">
                        <img :src="preview" class="w-full h-full object-cover" alt="Banner">
                        @if($isEdit)
                        <button type="button" @click="removed = true" class="absolute top-2 right-2 bg-white/90 hover:bg-white text-red-600 text-xs font-semibold px-2.5 py-1 rounded-lg shadow">Hapus banner</button>
                        @endif
                    </div>
                </template>
                <input type="hidden" name="remove_banner" :value="removed ? 1 : 0">
                <input type="file" name="banner_image" accept="image/*"
                       @change="if ($event.target.files[0]) { preview = URL.createObjectURL($event.target.files[0]); removed = false }"
                       class="{{ $inputCls }} file:mr-3 file:bg-indigo-600 file:text-white file:font-semibold file:border-0 file:rounded-lg file:px-3 file:py-1 file:text-xs">
                <p class="text-xs text-gray-400 mt-1">Maks. 2 MB (JPG/PNG).</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mulai *</label>
                    <input type="datetime-local" name="start_time" value="{{ $fmtDate('start_time') }}" required class="{{ $inputCls }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Selesai *</label>
                    <input type="datetime-local" name="end_time" value="{{ $fmtDate('end_time') }}" required class="{{ $inputCls }}">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                @php $currentStatus = $val('status', 'published'); @endphp
                <select name="status" required class="{{ $inputCls }}">
                    <option value="draft" {{ $currentStatus === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="published" {{ $currentStatus === 'published' ? 'selected' : '' }}>Published</option>
                    <option value="completed" {{ $currentStatus === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ $currentStatus === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
        </div>

        {{-- Right: Ticket Tiers --}}
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-900">Kelas Tiket</h2>
                <button type="button" @click="addTier()" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg transition">
                    + Tambah Kelas
                </button>
            </div>

            <div class="space-y-4">
                <template x-for="(tier, index) in tiers" :key="index">
                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 relative">
                        <input type="hidden" :name="`tiers[${index}][id]`" :value="tier.id ?? ''">

                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full" :style="`background:${tier.color}`"></span>
                                <span class="text-xs font-semibold text-gray-500" x-text="tier.sold_count > 0 ? `Terjual ${tier.sold_count} tiket` : 'Belum ada penjualan'"></span>
                            </div>
                            <div class="flex items-center gap-3">
                                @if($isEdit)
                                <label class="flex items-center gap-1.5 text-xs font-medium text-gray-600 cursor-pointer">
                                    <input type="checkbox" value="1" :name="`tiers[${index}][is_active]`" :checked="tier.is_active" @change="tier.is_active = $event.target.checked" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    Aktif dijual
                                </label>
                                @else
                                <input type="hidden" :name="`tiers[${index}][is_active]`" value="1">
                                @endif
                                <button type="button" @click="removeTier(index)" x-show="tiers.length > 1 && !(tier.sold_count > 0)"
                                        class="w-6 h-6 flex items-center justify-center bg-red-50 hover:bg-red-100 text-red-600 rounded-md transition" title="Hapus kelas">
                                    <x-icon name="x" class="h-3.5 w-3.5" />
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Nama Kelas *</label>
                                <input type="text" :name="`tiers[${index}][name]`" x-model="tier.name" required class="{{ $tierCls }}" placeholder="VVIP / VIP / Regular">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Harga (Rp) *</label>
                                <input type="number" :name="`tiers[${index}][price]`" x-model="tier.price" required min="0" class="{{ $tierCls }}" placeholder="500000">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Kuota *</label>
                                <input type="number" :name="`tiers[${index}][quota]`" x-model="tier.quota" required :min="Math.max(1, tier.sold_count || 0)" class="{{ $tierCls }}" placeholder="100">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Warna Zona *</label>
                                <div class="flex items-center gap-2">
                                    <input type="color" :name="`tiers[${index}][color]`" x-model="tier.color" class="w-10 h-10 rounded-lg cursor-pointer border border-gray-300">
                                    <span class="text-xs text-gray-500 font-mono" x-text="tier.color"></span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Warna Gelang</label>
                                <input type="text" :name="`tiers[${index}][wristband_color]`" x-model="tier.wristband_color" class="{{ $tierCls }}" placeholder="Emas / Ungu / dll">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Label Zona</label>
                                <input type="text" :name="`tiers[${index}][zone_label]`" x-model="tier.zone_label" class="{{ $tierCls }}" placeholder="Zona Depan Panggung">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Deskripsi</label>
                                <input type="text" :name="`tiers[${index}][description]`" x-model="tier.description" class="{{ $tierCls }}" placeholder="Benefit kelas ini...">
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- Submit --}}
    <div class="mt-6 flex justify-end gap-3">
        <a href="{{ route('admin.events.index') }}" class="px-6 py-3 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 font-semibold rounded-xl transition">Batal</a>
        <button type="submit" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition shadow-sm">
            {{ $isEdit ? 'Simpan Perubahan' : 'Buat Event' }}
        </button>
    </div>
</form>

@push('scripts')
<script>
function eventForm() {
    return {
        tiers: @json($tiersInit),

        addTier() {
            const colors = ['#ef4444', '#22c55e', '#ec4899', '#14b8a6', '#f97316', '#06b6d4'];
            this.tiers.push({
                id: null, name: '', price: '', quota: '',
                color: colors[this.tiers.length % colors.length],
                zone_label: '', wristband_color: '', description: '',
                is_active: true, sold_count: 0,
            });
        },

        removeTier(index) {
            this.tiers.splice(index, 1);
        }
    };
}
</script>
@endpush
