<x-layout :title="__('ui.orders_title')">

    <div class="position-relative overflow-hidden bg-navbar py-5 pt-custom-navbar border-bottom border-secondary-subtle">
        <div class="container position-relative z-1 py-4 text-center">
            <span class="badge bg-transparent border border-neon-cyan text-neon-cyan text-uppercase tracking-widest px-3 py-2 mb-3 fs-8 fw-semibold">
                <i class="bi bi-receipt me-1" aria-hidden="true"></i>{{ __('ui.orders_badge') }}
            </span>
            <h1 class="display-5 fw-black text-white text-uppercase tracking-tight mb-0">{{ __('ui.orders_title') }}</h1>
        </div>
    </div>

    <div class="py-5">
        <div class="container">

            @if (session()->has('message'))
                <div class="alert alert-success bg-success bg-opacity-10 border-success text-success d-flex align-items-center mb-4" role="status">
                    <i class="bi bi-check-circle-fill me-2" aria-hidden="true"></i> {{ session('message') }}
                </div>
            @endif

            @forelse ($orders as $order)
                @php
                    $status = [
                        'pending' => ['bg-warning text-dark', 'bi-hourglass-split'],
                        'accepted' => ['bg-success', 'bi-check-circle'],
                        'declined' => ['bg-danger', 'bi-x-circle'],
                        'cancelled' => ['bg-secondary', 'bi-slash-circle'],
                    ][$order->status];
                @endphp
                <article class="p-4 rounded-3 border border-secondary-subtle bg-navbar-card shadow-lg mb-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <div class="d-flex align-items-center gap-2">
                            @if ($order->seller)
                                <x-avatar :user="$order->seller" :size="32" />
                            @endif
                            <div>
                                <h2 class="h6 text-white fw-bold mb-0">{{ __('ui.cart_seller', ['name' => $order->seller?->name ?? __('ui.anonymous_user')]) }}</h2>
                                <span class="text-muted-custom fs-8">{{ __('ui.orders_sent_on', ['date' => $order->created_at->translatedFormat('j F Y, H:i')]) }}</span>
                            </div>
                        </div>
                        <span class="badge {{ $status[0] }} fs-7">
                            <i class="bi {{ $status[1] }} me-1" aria-hidden="true"></i>{{ __('ui.order_status_'.$order->status) }}
                        </span>
                    </div>

                    @foreach ($order->items as $item)
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

                    @if ($order->message)
                        <p class="text-muted-custom fs-7 fst-italic mt-3 mb-0">
                            <i class="bi bi-chat-left-quote me-1" aria-hidden="true"></i>{{ $order->message }}
                        </p>
                    @endif

                    <div class="d-flex justify-content-between align-items-baseline border-top border-secondary-subtle pt-3 mt-3">
                        <span class="text-muted-custom fs-7">{{ __('ui.order_status_help_'.$order->status) }}</span>
                        <span class="text-white fw-bold font-monospace">{{ __('ui.cart_total') }}: {{ number_format((float) $order->total, 2, ',', '.') }} €</span>
                    </div>
                </article>
            @empty
                <div class="row justify-content-center py-5">
                    <div class="col-12 col-md-6 text-center py-5 border border-dashed border-secondary rounded-4 bg-navbar-dark shadow">
                        <div class="fs-1 mb-3"><i class="bi bi-receipt text-neon-cyan" aria-hidden="true"></i></div>
                        <h2 class="h4 text-white fw-bold text-uppercase tracking-wide mb-2">{{ __('ui.orders_empty') }}</h2>
                        <p class="text-muted-custom fs-7 mb-4">{{ __('ui.orders_empty_text') }}</p>
                        <a href="{{ route('article.index') }}" class="btn btn-outline-neon-cyan text-uppercase tracking-wider fs-7 fw-bold px-4 py-2">
                            <i class="bi bi-search me-2" aria-hidden="true"></i>{{ __('ui.cart_continue') }}
                        </a>
                    </div>
                </div>
            @endforelse

        </div>
    </div>

</x-layout>
