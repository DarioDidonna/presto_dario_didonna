@props(['user', 'size' => 32])
{{-- Foto profilo se caricata, altrimenti le iniziali (nessun servizio esterno) --}}
<span {{ $attributes->merge(['class' => 'avatar-circle border border-2 border-neon-cyan rounded-circle overflow-hidden d-inline-flex align-items-center justify-content-center flex-shrink-0']) }}
    style="width: {{ $size }}px; height: {{ $size }}px; font-size: {{ round($size * 0.38) }}px;">
    @if ($user->avatarUrl())
        <img src="{{ $user->avatarUrl() }}" alt="{{ __('ui.avatar_alt', ['name' => $user->name]) }}"
            width="{{ $size }}" height="{{ $size }}" class="w-100 h-100 object-fit-cover">
    @else
        <span aria-hidden="true">{{ $user->initials() }}</span>
    @endif
</span>
