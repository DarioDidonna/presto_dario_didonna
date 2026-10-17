@props(['rating', 'max' => \App\Models\Review::MAX_RATING])
{{-- Valutazione in stelle (anche mezze, per le medie) con testo per gli screen reader --}}
<span {{ $attributes->merge(['class' => 'stars text-neon-amber text-nowrap']) }} title="{{ __('ui.reviews_stars', ['rating' => round($rating, 1), 'max' => $max]) }}">
    @for ($i = 1; $i <= $max; $i++)
        <i class="bi {{ $rating >= $i ? 'bi-star-fill' : ($rating >= $i - 0.5 ? 'bi-star-half' : 'bi-star') }}" aria-hidden="true"></i>
    @endfor
    <span class="visually-hidden">{{ __('ui.reviews_stars', ['rating' => round($rating, 1), 'max' => $max]) }}</span>
</span>
