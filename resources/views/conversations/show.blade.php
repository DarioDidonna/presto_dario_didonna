@php($back = $perspective === 'buyer' ? ['orders.index', 'ui.orders_title'] : ['sales.index', 'ui.sales_title'])
<x-layout :title="__('ui.messages_title')">

    <div class="position-relative overflow-hidden bg-navbar py-5 pt-custom-navbar border-bottom border-secondary-subtle">
        <div class="container position-relative z-1 py-4 text-center">
            <span class="badge bg-transparent border border-neon-cyan text-neon-cyan text-uppercase tracking-widest px-3 py-2 mb-3 fs-8 fw-semibold">
                <i class="bi bi-chat-dots me-1" aria-hidden="true"></i>{{ __('ui.orders_badge') }}
            </span>
            <h1 class="display-5 fw-black text-white text-uppercase tracking-tight mb-0">{{ __('ui.messages_title') }}</h1>
        </div>
    </div>

    <div class="py-5">
        <div class="container">
            <a href="{{ route($back[0]) }}" class="text-neon-cyan text-decoration-none fs-7 fw-semibold d-inline-flex align-items-center mb-4">
                <i class="bi bi-arrow-left me-2" aria-hidden="true"></i>{{ __($back[1]) }}
            </a>

            <div class="row g-4 align-items-start">
                <div class="col-12 col-lg-5 order-lg-2">
                    <x-purchase-request-card :request="$purchaseRequest" :perspective="$perspective" :conversation-link="false" class="mb-0" />
                </div>
                <div class="col-12 col-lg-7 order-lg-1">
                    <livewire:conversation :purchase-request="$purchaseRequest" />
                </div>
            </div>
        </div>
    </div>

</x-layout>
