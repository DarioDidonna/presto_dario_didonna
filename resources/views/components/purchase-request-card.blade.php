@props(['request', 'perspective' => 'buyer'])
{{-- Una richiesta d'acquisto vista dall'acquirente ("I miei ordini") o dal venditore ("Richieste ricevute") --}}
@php
    $other = $perspective === 'buyer' ? $request->seller : $request->buyer;
    $label = $perspective === 'buyer' ? 'ui.cart_seller' : 'ui.sales_buyer';
    $status = [
        'pending' => ['bg-warning text-dark', 'bi-hourglass-split'],
        'accepted' => ['bg-success', 'bi-check-circle'],
        'declined' => ['bg-danger', 'bi-x-circle'],
        'cancelled' => ['bg-secondary', 'bi-slash-circle'],
    ][$request->status];
@endphp
<article {{ $attributes->merge(['class' => 'p-4 rounded-3 border bg-navbar-card shadow-lg mb-4 '.($request->isPending() && $perspective === 'seller' ? 'border-warning' : 'border-secondary-subtle')]) }}>
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div class="d-flex align-items-center gap-2">
            @if ($other)
                <x-avatar :user="$other" :size="32" />
            @endif
            <div>
                <h2 class="h6 text-white fw-bold mb-0">{{ __($label, ['name' => $other?->name ?? __('ui.anonymous_user')]) }}</h2>
                <span class="text-muted-custom fs-8">{{ __('ui.orders_sent_on', ['date' => $request->created_at->translatedFormat('j F Y, H:i')]) }}</span>
            </div>
        </div>
        <span class="badge {{ $status[0] }} fs-7">
            <i class="bi {{ $status[1] }} me-1" aria-hidden="true"></i>{{ __('ui.order_status_'.$request->status) }}
        </span>
    </div>

    @foreach ($request->items as $item)
        <div class="d-flex align-items-center gap-3 py-2 {{ $loop->last ? '' : 'border-bottom border-secondary-subtle' }}">
            <img src="{{ $item->article?->images->first()?->thumbnailUrl() ?? asset('media/placeholder.svg') }}"
                alt="" width="56" height="56" loading="lazy" class="rounded object-fit-cover flex-shrink-0">
            <div class="flex-grow-1 min-w-0">
                @if ($item->article?->is_accepted)
                    <a href="{{ route('article.show', $item->article) }}" class="text-white fw-semibold text-decoration-none d-block text-truncate">{{ $item->title }}</a>
                @else
                    <span class="text-white fw-semibold d-block text-truncate">{{ $item->title }}</span>
                @endif
            </div>
            <span class="text-neon-cyan fw-bold font-monospace text-nowrap">{{ number_format((float) $item->price, 2, ',', '.') }} €</span>
        </div>
    @endforeach

    @if ($request->message)
        <p class="text-muted-custom fs-7 fst-italic mt-3 mb-0">
            <i class="bi bi-chat-left-quote me-1" aria-hidden="true"></i>{{ $request->message }}
        </p>
    @endif

    {{-- Contatto: il venditore vede sempre l'acquirente; l'acquirente vede il venditore dopo l'accettazione --}}
    @if ($other && ($perspective === 'seller' || $request->status === 'accepted'))
        <p class="fs-7 mt-3 mb-0">
            <i class="bi bi-envelope me-1 text-neon-cyan" aria-hidden="true"></i>
            <a href="mailto:{{ $other->email }}" class="text-neon-cyan">{{ $other->email }}</a>
        </p>
    @endif

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 border-top border-secondary-subtle pt-3 mt-3">
        <span class="text-muted-custom fs-7">{{ __('ui.'.($perspective === 'buyer' ? 'order' : 'sales').'_status_help_'.$request->status) }}</span>
        <span class="text-white fw-bold font-monospace">{{ __('ui.cart_total') }}: {{ number_format((float) $request->total, 2, ',', '.') }} €</span>
    </div>

    @if (trim($slot))
        <div class="d-flex flex-wrap justify-content-end gap-2 mt-3">{{ $slot }}</div>
    @endif
</article>
