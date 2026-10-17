@props(['summary'])
{{-- Media e numero delle recensioni di un venditore (User::reviewSummary()) --}}
<span {{ $attributes->merge(['class' => 'd-inline-flex align-items-center gap-2 fs-7']) }}>
    @if ($summary['count'] > 0)
        <x-stars :rating="$summary['average']" />
        <span class="text-white fw-semibold font-monospace">{{ number_format($summary['average'], 1, ',', '.') }}</span>
        <span class="text-muted-custom">({{ trans_choice('ui.reviews_count', $summary['count'], ['count' => $summary['count']]) }})</span>
    @else
        <span class="text-muted-custom"><i class="bi bi-star me-1" aria-hidden="true"></i>{{ __('ui.reviews_none_yet') }}</span>
    @endif
</span>
