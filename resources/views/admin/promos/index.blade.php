@extends('layouts.admin')

@section('title', 'Kode Promo - Admin EventTix')

@section('content')
<div class="max-w-7xl mx-auto" x-data="promoPage()">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Kode Promo</h1>
            <p class="text-sm text-gray-500 mt-1">Buat dan atur kode diskon untuk pembeli tiket.</p>
        </div>
        <button @click="openCreate()" id="btn-add-promo" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition shadow-sm">+ Buat Promo</button>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white border border-gray-200 rounded-xl p-5"><p class="text-xs font-semibold text-gray-500">Total Promo</p><p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total'] }}</p></div>
        <div class="bg-white border border-gray-200 rounded-xl p-5"><p class="text-xs font-semibold text-gray-500">Sedang Berlaku</p><p class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['active'] }}</p></div>
        <div class="bg-white border border-gray-200 rounded-xl p-5"><p class="text-xs font-semibold text-gray-500">Total Pemakaian</p><p class="text-2xl font-bold text-indigo-600 mt-1">{{ number_format($stats['usages']) }}</p></div>
    </div>

    <form method="GET" class="bg-white border border-gray-200 rounded-xl p-4 mb-6 flex flex-col sm:flex-row gap-3">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari kode promo..." class="flex-1 bg-gray-50 border border-gray-200 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
        <select name="status" onchange="this.form.submit()" class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
            <option value="">Semua</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
        </select>
        <button type="submit" class="px-5 py-2 bg-gray-900 hover:bg-gray-800 text-white text-sm font-semibold rounded-lg transition">Cari</button>
        @if(request()->hasAny(['q', 'status']))<a href="{{ route('admin.promos.index') }}" class="px-3 py-2 text-sm font-semibold text-gray-500 hover:text-gray-700 text-center">Reset</a>@endif
    </form>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-gray-50 text-gray-500">
                    <tr>
                        <th class="px-6 py-4 font-medium">Kode</th>
                        <th class="px-6 py-4 font-medium">Diskon</th>
                        <th class="px-6 py-4 font-medium">Pemakaian</th>
                        <th class="px-6 py-4 font-medium">Berlaku s/d</th>
                        <th class="px-6 py-4 font-medium">Status</th>
                        <th class="px-6 py-4 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($promos as $promo)
                        @php
                            $expired = $promo->valid_until && $promo->valid_until->isPast();
                            $full = $promo->max_usages !== null && $promo->current_usages >= $promo->max_usages;
                            $payload = [
                                'id' => $promo->id, 'code' => $promo->code, 'discount_type' => $promo->discount_type, 'amount' => $promo->amount,
                                'max_usages' => $promo->max_usages, 'valid_until' => $promo->valid_until?->format('Y-m-d\TH:i'), 'is_active' => (bool) $promo->is_active,
                            ];
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4"><span class="font-mono font-bold text-gray-900 bg-gray-100 px-2.5 py-1 rounded-md">{{ $promo->code }}</span></td>
                            <td class="px-6 py-4 font-semibold text-gray-900">{{ $promo->discount_type === 'percentage' ? $promo->amount . '%' : 'Rp' . number_format($promo->amount, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-gray-600">
                                {{ $promo->current_usages }} / {{ $promo->max_usages ?? '∞' }}
                                @if($promo->max_usages)
                                    <div class="w-24 h-1.5 bg-gray-100 rounded-full mt-1.5 overflow-hidden"><div class="h-full bg-indigo-500 rounded-full" style="width: {{ min(100, round($promo->current_usages / $promo->max_usages * 100)) }}%"></div></div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $promo->valid_until ? $promo->valid_until->translatedFormat('d M Y H:i') : 'Tanpa batas' }}</td>
                            <td class="px-6 py-4">
                                @if(!$promo->is_active)
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Nonaktif</span>
                                @elseif($expired)
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Kedaluwarsa</span>
                                @elseif($full)
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Kuota habis</span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Aktif</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="inline-flex gap-1.5">
                                    <form action="{{ route('admin.promos.toggle', $promo) }}" method="POST">@csrf @method('PATCH')
                                        <button class="text-xs font-medium border border-gray-200 px-3 py-1.5 rounded-md text-gray-600 hover:border-indigo-200 hover:text-indigo-600 transition">{{ $promo->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                    </form>
                                    <button type="button" @click='openEdit(@json($payload, JSON_HEX_APOS | JSON_HEX_AMP))' class="text-xs font-medium border border-indigo-100 bg-indigo-50 px-3 py-1.5 rounded-md text-indigo-700 hover:bg-indigo-100 transition">Edit</button>
                                    <form action="{{ route('admin.promos.destroy', $promo) }}" method="POST" data-confirm="Kode promo {{ $promo->code }} akan dihapus permanen." data-confirm-title="Hapus promo?" data-confirm-button="Ya, hapus">@csrf @method('DELETE')
                                        <button class="text-xs font-medium border border-red-200 bg-red-50 px-3 py-1.5 rounded-md text-red-600 hover:bg-red-100 transition">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500">Belum ada kode promo.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal --}}
    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/50" @click="open = false"></div>
        <form :action="mode === 'edit' ? updateUrl.replace('__ID__', form.id) : storeUrl" method="POST" x-transition class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
            @csrf
            <input type="hidden" name="_method" value="PUT" :disabled="mode !== 'edit'">
            <input type="hidden" name="_mode" :value="mode">
            <input type="hidden" name="_id" :value="form.id ?? ''">

            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900" x-text="mode === 'edit' ? 'Edit Promo' : 'Buat Promo Baru'"></h3>
                <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
            </div>

            <div class="p-6 space-y-4">
                @if($errors->any() && old('_mode'))
                    <div class="p-3 rounded-xl bg-red-50 text-red-600 border border-red-200 text-sm">
                        <ul class="list-disc list-inside space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kode Promo *</label>
                    <input type="text" name="code" x-model="form.code" required maxlength="50" @input="form.code = form.code.toUpperCase().replace(/[^A-Z0-9_-]/g, '')"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm font-mono uppercase focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="HEMAT50">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Diskon *</label>
                        <select name="discount_type" x-model="form.discount_type" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                            <option value="percentage">Persentase (%)</option>
                            <option value="fixed">Nominal (Rp)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" x-text="form.discount_type === 'percentage' ? 'Besar Diskon (1-100 %) *' : 'Potongan (Rp) *'"></label>
                        <input type="number" name="amount" x-model="form.amount" required min="1" :max="form.discount_type === 'percentage' ? 100 : null"
                               class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Batas Pemakaian</label>
                        <input type="number" name="max_usages" x-model="form.max_usages" min="1" placeholder="Kosong = tanpa batas"
                               class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Berlaku Sampai</label>
                        <input type="datetime-local" name="valid_until" x-model="form.valid_until"
                               class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                </div>
                <label class="flex items-center gap-2 text-sm font-medium text-gray-700 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" x-model="form.is_active" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    Promo aktif
                </label>
            </div>

            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3 rounded-b-2xl">
                <button type="button" @click="open = false" class="px-5 py-2.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-semibold rounded-xl transition">Batal</button>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition" x-text="mode === 'edit' ? 'Simpan Perubahan' : 'Buat Promo'"></button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function promoPage() {
    const blank = { id: null, code: '', discount_type: 'percentage', amount: '', max_usages: '', valid_until: '', is_active: true };
    const hasOld = @json((bool) old('_mode'));
    return {
        open: hasOld,
        mode: @json(old('_mode', 'create')),
        storeUrl: @json(route('admin.promos.store')),
        updateUrl: @json(route('admin.promos.update', '__ID__')),
        form: hasOld
            ? { id: @json(old('_id')), code: @json(old('code', '')), discount_type: @json(old('discount_type', 'percentage')), amount: @json(old('amount', '')), max_usages: @json(old('max_usages', '')), valid_until: @json(old('valid_until', '')), is_active: @json((bool) old('is_active')) }
            : { ...blank },
        openCreate() { this.mode = 'create'; this.form = { ...blank }; this.open = true; },
        openEdit(p) {
            this.mode = 'edit';
            this.form = { ...blank, ...p, max_usages: p.max_usages ?? '', valid_until: p.valid_until ?? '' };
            this.open = true;
        },
    };
}
</script>
@endpush
