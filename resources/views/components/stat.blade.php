@props(['label', 'value', 'hint' => null, 'href' => null, 'dark' => false])
@php $tag = $href ? 'a' : 'div'; @endphp
<{{ $tag }} @if($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => 'block rounded-xl border p-5 transition ' . ($dark ? 'bg-ink border-ink text-white' : 'bg-white border-gray-200 text-gray-900') . ($href ? ' hover:border-ink' : '')]) }}>
    <p class="eyebrow {{ $dark ? '!text-white/60' : '' }}">{{ $label }}</p>
    <p class="mt-2 font-display text-2xl lg:text-[1.75rem] font-bold leading-none tracking-tight">{{ $value }}</p>
    @if($hint)
        <p class="mt-2 text-xs {{ $dark ? 'text-white/60' : 'text-gray-500' }}">{{ $hint }}</p>
    @endif
</{{ $tag }}>
