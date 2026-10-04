@extends('layouts.admin')

@section('title', 'Kelola Akun - Admin EventTix')

@section('content')
@php
    $roleStyles = [
        'admin' => 'bg-rose-100 text-rose-700',
        'organizer' => 'bg-indigo-100 text-indigo-700',
        'customer' => 'bg-emerald-100 text-emerald-700',
    ];
    $roleLabels = ['admin' => 'Admin', 'organizer' => 'Organizer', 'customer' => 'Customer'];
@endphp
<div class="max-w-7xl mx-auto" x-data="accountPage()">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Kelola Akun</h1>
            <p class="text-sm text-gray-500 mt-1">Tambah, ubah role, reset password, atau hapus akun pengguna.</p>
        </div>
        <button @click="openCreate()" id="btn-add-account" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition shadow-sm">+ Tambah Akun</button>
    </div>

    {{-- Role tabs --}}
    <div class="flex flex-wrap gap-2 mb-4">
        @php
            $tabs = ['' => ['Semua', $roleCounts['all']], 'admin' => ['Admin', $roleCounts['admin']], 'organizer' => ['Organizer', $roleCounts['organizer']], 'customer' => ['Customer', $roleCounts['customer']]];
        @endphp
        @foreach($tabs as $key => [$label, $count])
            <a href="{{ route('admin.accounts.index', array_filter(['role' => $key, 'q' => request('q')])) }}"
               class="px-4 py-2 rounded-lg text-sm font-semibold border transition {{ (request('role', '') === $key) ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-600 border-gray-200 hover:border-indigo-200 hover:text-indigo-600' }}">
                {{ $label }} <span class="ml-1 text-xs opacity-80">{{ $count }}</span>
            </a>
        @endforeach
    </div>

    <form method="GET" class="bg-white border border-gray-200 rounded-xl p-4 mb-6 flex gap-3">
        @if(request('role'))<input type="hidden" name="role" value="{{ request('role') }}">@endif
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama atau email..." class="flex-1 bg-gray-50 border border-gray-200 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
        <button type="submit" class="px-5 py-2 bg-gray-900 hover:bg-gray-800 text-white text-sm font-semibold rounded-lg transition">Cari</button>
        @if(request()->hasAny(['q', 'role']))<a href="{{ route('admin.accounts.index') }}" class="px-3 py-2 text-sm font-semibold text-gray-500 hover:text-gray-700">Reset</a>@endif
    </form>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-gray-50 text-gray-500">
                    <tr>
                        <th class="px-6 py-4 font-medium">Pengguna</th>
                        <th class="px-6 py-4 font-medium">Role</th>
                        <th class="px-6 py-4 font-medium">Order</th>
                        <th class="px-6 py-4 font-medium">Tiket</th>
                        <th class="px-6 py-4 font-medium">Bergabung</th>
                        <th class="px-6 py-4 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $user)
                        @php
                            $role = $user->getRoleNames()->first() ?? 'customer';
                            $isSelf = $user->id === auth()->id();
                            $payload = ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'role' => $role];
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <img class="h-9 w-9 rounded-full" src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=6366f1&color=fff&size=64" alt="">
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ $user->name }} @if($isSelf)<span class="ml-1 text-[10px] font-bold uppercase bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded">Anda</span>@endif</p>
                                        <p class="text-xs text-gray-500">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4"><span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $roleStyles[$role] ?? 'bg-gray-100 text-gray-600' }}">{{ $roleLabels[$role] ?? ucfirst($role) }}</span></td>
                            <td class="px-6 py-4 text-gray-600">{{ $user->orders_count }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $user->tickets_count }}</td>
                            <td class="px-6 py-4 text-gray-500">{{ $user->created_at?->translatedFormat('d M Y') }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="inline-flex gap-1.5">
                                    <button type="button" @click='openEdit(@json($payload, JSON_HEX_APOS | JSON_HEX_AMP))' class="text-xs font-medium border border-indigo-100 bg-indigo-50 px-3 py-1.5 rounded-md text-indigo-700 hover:bg-indigo-100 transition">Edit</button>
                                    @unless($isSelf)
                                    <form action="{{ route('admin.accounts.destroy', $user) }}" method="POST" data-confirm="Akun {{ $user->name }} ({{ $user->email }}) akan dihapus permanen." data-confirm-title="Hapus akun?" data-confirm-button="Ya, hapus">
                                        @csrf @method('DELETE')
                                        <button class="text-xs font-medium border border-red-200 bg-red-50 px-3 py-1.5 rounded-md text-red-600 hover:bg-red-100 transition">Hapus</button>
                                    </form>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500">Tidak ada akun yang ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">{{ $users->links() }}</div>
        @endif
    </div>

    {{-- Modal --}}
    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/50" @click="open = false"></div>
        <form :action="mode === 'edit' ? updateUrl.replace('__ID__', form.id) : storeUrl" method="POST" x-transition class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto" autocomplete="off">
            @csrf
            <input type="hidden" name="_method" value="PUT" :disabled="mode !== 'edit'">
            <input type="hidden" name="_mode" :value="mode">
            <input type="hidden" name="_id" :value="form.id ?? ''">

            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900" x-text="mode === 'edit' ? 'Edit Akun' : 'Tambah Akun Baru'"></h3>
                <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
            </div>

            <div class="p-6 space-y-4">
                @if($errors->any() && old('_mode'))
                    <div class="p-3 rounded-xl bg-red-50 text-red-600 border border-red-200 text-sm">
                        <ul class="list-disc list-inside space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap *</label>
                    <input type="text" name="name" x-model="form.name" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                    <input type="email" name="email" x-model="form.email" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Password <span x-show="mode === 'create'">*</span>
                        <span x-show="mode === 'edit'" class="text-xs font-normal text-gray-400">(kosongkan jika tidak diganti)</span>
                    </label>
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <input :type="showPw ? 'text' : 'password'" name="password" x-model="form.password" :required="mode === 'create'" minlength="8" autocomplete="new-password"
                                   class="w-full border border-gray-300 rounded-xl px-4 py-2.5 pr-16 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="Minimal 8 karakter">
                            <button type="button" @click="showPw = !showPw" class="absolute inset-y-0 right-3 text-xs font-semibold text-gray-400 hover:text-indigo-600" x-text="showPw ? 'Sembunyi' : 'Lihat'"></button>
                        </div>
                        <button type="button" @click="generatePassword()" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-xl transition">Generate</button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Role *</label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <template x-for="r in roles" :key="r.value">
                            <label class="cursor-pointer">
                                <input type="radio" name="role" :value="r.value" x-model="form.role" class="peer sr-only">
                                <div class="border border-gray-200 rounded-xl px-3 py-3 text-center peer-checked:border-indigo-500 peer-checked:bg-indigo-50 hover:border-indigo-200 transition">
                                    <p class="text-sm font-bold text-gray-900" x-text="r.label"></p>
                                    <p class="text-[11px] text-gray-500 mt-0.5" x-text="r.desc"></p>
                                </div>
                            </label>
                        </template>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3 rounded-b-2xl">
                <button type="button" @click="open = false" class="px-5 py-2.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-semibold rounded-xl transition">Batal</button>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition" x-text="mode === 'edit' ? 'Simpan Perubahan' : 'Buat Akun'"></button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function accountPage() {
    const blank = { id: null, name: '', email: '', password: '', role: 'customer' };
    const hasOld = @json((bool) old('_mode'));
    return {
        open: hasOld,
        showPw: false,
        mode: @json(old('_mode', 'create')),
        storeUrl: @json(route('admin.accounts.store')),
        updateUrl: @json(route('admin.accounts.update', '__ID__')),
        roles: [
            { value: 'admin', label: 'Admin', desc: 'Akses penuh panel' },
            { value: 'organizer', label: 'Organizer', desc: 'Scan tiket gate' },
            { value: 'customer', label: 'Customer', desc: 'Pembeli tiket' },
        ],
        form: hasOld
            ? { id: @json(old('_id')), name: @json(old('name', '')), email: @json(old('email', '')), password: '', role: @json(old('role', 'customer')) }
            : { ...blank },
        openCreate() { this.mode = 'create'; this.showPw = false; this.form = { ...blank }; this.open = true; },
        openEdit(u) { this.mode = 'edit'; this.showPw = false; this.form = { ...blank, ...u, password: '' }; this.open = true; },
        generatePassword() {
            const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
            let pw = '';
            const arr = new Uint32Array(12);
            crypto.getRandomValues(arr);
            arr.forEach(n => pw += chars[n % chars.length]);
            this.form.password = pw;
            this.showPw = true;
        },
    };
}
</script>
@endpush
