@extends('layouts.app')

@section('title', 'EventTix - Tiket Konser & Festival')

@section('content')
@php
    $cities = $events->map(fn($e) => $e->venue->city ?? null)->filter()->unique()->values();
    $featured = $events->first(fn($e) => $e->end_time >= now()) ?? $events->first();
    $stripeFor = function ($event) {
        $colors = $event->ticketTiers->pluck('color')->filter()->values();
        if ($colors->isEmpty()) { $colors = collect(['#14130F', '#FF5A1F']); }
        $stops = [];
        $w = 22;
        foreach ($colors as $i => $c) { $stops[] = "$c " . ($i * $w) . "px, $c " . (($i + 1) * $w) . "px"; }
        return 'background: repeating-linear-gradient(115deg, ' . implode(', ', $stops) . ');';
    };
    $payload = $events->map(fn($e) => [
        'id' => $e->id,
        'q' => mb_strtolower($e->title . ' ' . ($e->venue->name ?? '') . ' ' . ($e->venue->city ?? '')),
        'city' => $e->venue->city ?? '',
    ])->values();
@endphp

{{-- Hero --}}
<section class="grid lg:grid-cols-[1.2fr_1fr] gap-10 items-end pb-12 border-b border-gray-200">
    <div>
        <p class="eyebrow mb-5">Tiket live event &middot; Indonesia</p>
        <h1 class="font-display text-5xl md:text-6xl lg:text-7xl font-bold leading-[0.95] text-ink">
            Cari tiketmu.<br>
            <span class="text-flame-500">Datang, tunjukkan QR,</span><br>
            masuk.
        </h1>
        <p class="mt-6 text-gray-600 max-w-md leading-relaxed">
            Pilih kelas, bayar, dan tiket langsung ada di ponselmu. Di lokasi cukup scan, lalu petugas memasangkan gelang sesuai kelas.
        </p>
    </div>

    @if($featured)
    <a href="{{ route('events.show', $featured->slug) }}" class="group block bg-ink text-white rounded-2xl overflow-hidden relative">
        <div class="h-3 stripes" style="{{ $stripeFor($featured) }}"></div>
        <div class="p-6 pb-5">
            <p class="eyebrow !text-white/50">Berikutnya</p>
            <h2 class="font-display text-2xl font-bold mt-3 leading-tight line-clamp-2">{{ $featured->title }}</h2>
            <p class="mt-2 text-sm text-white/60">{{ $featured->venue->name ?? '' }}, {{ $featured->venue->city ?? '' }}</p>
        </div>
        <div class="perf perf-ink mx-0"></div>
        <div class="px-6 py-4 flex items-center justify-between">
            <div>
                <p class="font-mono text-2xl font-bold leading-none">{{ $featured->start_time->translatedFormat('d') }}</p>
                <p class="eyebrow !text-white/50 mt-1">{{ $featured->start_time->translatedFormat('M Y') }}</p>
            </div>
            <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-flame-400 group-hover:gap-2.5 transition-all">
                Lihat tiket <x-icon name="arrow-right" class="h-4 w-4" />
            </span>
        </div>
    </a>
    @endif
</section>

@if($events->isEmpty())
    <div class="py-24 text-center">
        <p class="font-display text-2xl font-bold">Belum ada event.</p>
        <p class="text-gray-500 mt-1">Cek lagi nanti, jadwal baru segera hadir.</p>
    </div>
@else
<section x-data='{ q: "", city: "", items: @json($payload, JSON_HEX_APOS | JSON_HEX_AMP),
        match(id) { const i = this.items.find(x => x.id === id); return (!this.q || i.q.includes(this.q.toLowerCase())) && (!this.city || i.city === this.city); },
        get shown() { return this.items.filter(i => this.match(i.id)).length; } }' class="pt-10">

    <div class="flex flex-col md:flex-row md:items-center gap-4 mb-8">
        <div class="relative md:w-80">
            <span class="absolute inset-y-0 left-3 flex items-center text-gray-400"><x-icon name="search" class="h-4 w-4" /></span>
            <input x-model="q" type="text" id="event-search" placeholder="Cari event atau venue"
                   class="w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-9 pr-3 text-sm focus:border-ink focus:ring-0">
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" @click="city = ''" :class="city === '' ? 'bg-ink text-white border-ink' : 'bg-white text-gray-700 border-gray-300 hover:border-ink'" class="px-3.5 py-1.5 text-sm font-medium rounded-full border transition">Semua kota</button>
            @foreach($cities as $c)
                <button type="button" @click="city = @js($c)" :class="city === @js($c) ? 'bg-ink text-white border-ink' : 'bg-white text-gray-700 border-gray-300 hover:border-ink'" class="px-3.5 py-1.5 text-sm font-medium rounded-full border transition">{{ $c }}</button>
            @endforeach
        </div>
        <p class="md:ml-auto text-sm text-gray-500"><span x-text="shown" class="font-semibold text-ink"></span> event</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($events as $event)
            @php
                $ended = $event->end_time < now();
                $soldOut = $event->ticketTiers->isNotEmpty() && $event->ticketTiers->every(fn($t) => $t->isSoldOut());
            @endphp
            <a href="{{ route('events.show', $event->slug) }}" x-show="match({{ $event->id }})" x-transition.opacity
               class="group flex flex-col bg-white border border-gray-200 rounded-2xl overflow-hidden hover:border-ink transition-colors {{ $ended ? 'opacity-70' : '' }}">

                <div class="relative h-44 overflow-hidden" @if(!$event->banner_image) style="{{ $stripeFor($event) }}" @endif>
                    @if($event->banner_image)
                        <img src="{{ Storage::url($event->banner_image) }}" alt="{{ $event->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    @endif
                    <div class="absolute top-3 left-3 bg-white rounded-lg px-2.5 py-1.5 text-center leading-none shadow-sm">
                        <p class="font-mono text-lg font-bold">{{ $event->start_time->translatedFormat('d') }}</p>
                        <p class="eyebrow mt-1 !text-[9px]">{{ $event->start_time->translatedFormat('M') }}</p>
                    </div>
                    @if($ended || $soldOut)
                        <span class="absolute top-3 right-3 px-2.5 py-1 text-[11px] font-bold rounded-md {{ $ended ? 'bg-gray-900 text-white' : 'bg-flame-500 text-white' }}">
                            {{ $ended ? 'Selesai' : 'Sold out' }}
                        </span>
                    @endif
                </div>

                <div class="perf"></div>

                <div class="p-5 flex-1 flex flex-col">
                    <h2 class="font-display text-lg font-bold leading-snug line-clamp-2 group-hover:text-flame-600 transition-colors">{{ $event->title }}</h2>
                    <div class="mt-3 space-y-1.5 text-sm text-gray-500">
                        <p class="flex items-center gap-2"><x-icon name="pin" class="h-4 w-4 text-gray-400 shrink-0" /> <span class="truncate">{{ $event->venue->name }}, {{ $event->venue->city }}</span></p>
                        <p class="flex items-center gap-2"><x-icon name="clock" class="h-4 w-4 text-gray-400 shrink-0" /> {{ $event->start_time->format('H:i') }} &ndash; {{ $event->end_time->format('H:i') }} WIB</p>
                    </div>

                    @if($event->ticketTiers->isNotEmpty())
                    <div class="mt-auto pt-5 flex items-end justify-between">
                        <div class="flex items-center gap-1.5">
                            @foreach($event->ticketTiers->take(5) as $tier)
                                <span class="h-2.5 w-2.5 rounded-full ring-1 ring-black/10" style="background-color: {{ $tier->color }}" title="{{ $tier->name }}"></span>
                            @endforeach
                            <span class="ml-1 text-xs text-gray-500">{{ $event->ticketTiers->count() }} kelas</span>
                        </div>
                        <div class="text-right leading-none">
                            <p class="eyebrow !text-[9px]">Mulai</p>
                            <p class="font-mono text-sm font-bold mt-1">Rp{{ $event->lowest_price }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </a>
        @endforeach
    </div>

    <div x-show="shown === 0" x-cloak class="py-20 text-center">
        <p class="font-display text-xl font-bold">Tidak ada yang cocok.</p>
        <p class="text-gray-500 text-sm mt-1">Coba kata kunci lain atau pilih semua kota.</p>
    </div>
</section>
@endif
@endsection
