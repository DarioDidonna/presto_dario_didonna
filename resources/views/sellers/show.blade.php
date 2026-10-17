<x-layout :title="__('ui.seller_page_title', ['name' => $seller->name])" :description="__('ui.seller_page_description', ['name' => $seller->name])">

    <div class="position-relative overflow-hidden bg-navbar py-5 pt-custom-navbar border-bottom border-secondary-subtle">
        <div class="container position-relative z-1 py-4">
            <div class="d-flex flex-column flex-md-row align-items-center gap-4 text-center text-md-start">
                <x-avatar :user="$seller" :size="96" />
                <div>
                    <span class="badge bg-transparent border border-neon-cyan text-neon-cyan text-uppercase tracking-widest px-3 py-2 mb-2 fs-8 fw-semibold">
                        <i class="bi bi-shop me-1" aria-hidden="true"></i>{{ __('ui.seller_badge') }}
                    </span>
                    <h1 class="display-6 fw-black text-white text-uppercase tracking-tight mb-2">{{ $seller->name }}</h1>
                    <div class="d-flex flex-wrap justify-content-center justify-content-md-start align-items-center gap-3">
                        <x-seller-rating :summary="$summary" />
                        <span class="text-muted-custom fs-7">
                            <i class="bi bi-calendar3 me-1" aria-hidden="true"></i>{{ __('ui.seller_member_since', ['date' => $seller->created_at?->translatedFormat('F Y')]) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="py-5">
        <div class="container">

            <section class="mb-5" aria-labelledby="seller-ads">
                <h2 id="seller-ads" class="h4 fw-black text-white text-uppercase tracking-tight mb-4">
                    {{ __('ui.seller_ads') }} <span class="text-neon-cyan font-monospace fs-6">({{ $articles->total() }})</span>
                </h2>
                @if ($articles->isEmpty())
                    <p class="text-muted-custom">{{ __('ui.seller_no_ads') }}</p>
                @else
                    <div class="row g-4">
                        @foreach ($articles as $article)
                            <div class="col-12 col-md-6 col-lg-4 d-flex">
                                <x-card :article="$article" />
                            </div>
                        @endforeach
                    </div>
                    <div class="d-flex justify-content-center mt-4">{{ $articles->links() }}</div>
                @endif
            </section>

            <section aria-labelledby="seller-reviews">
                <h2 id="seller-reviews" class="h4 fw-black text-white text-uppercase tracking-tight mb-4">
                    {{ __('ui.seller_reviews') }} <span class="text-neon-cyan font-monospace fs-6">({{ $reviews->total() }})</span>
                </h2>
                @forelse ($reviews as $review)
                    <article class="review-box p-3 p-md-4 rounded-3 mb-3">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <div class="d-flex align-items-center gap-2">
                                @if ($review->reviewer)
                                    <x-avatar :user="$review->reviewer" :size="32" />
                                @endif
                                <div>
                                    <div class="text-white fw-semibold fs-7">{{ $review->reviewer?->name ?? __('ui.anonymous_user') }}</div>
                                    <time class="text-muted-custom fs-8" datetime="{{ $review->created_at->toIso8601String() }}">{{ $review->created_at->translatedFormat('j F Y') }}</time>
                                </div>
                            </div>
                            <x-stars :rating="$review->rating" />
                        </div>
                        @if ($review->comment)
                            <p class="text-muted-custom fs-7 mb-0 mt-3 review-comment">{{ $review->comment }}</p>
                        @endif
                    </article>
                @empty
                    <p class="text-muted-custom">{{ __('ui.seller_no_reviews') }}</p>
                @endforelse
                <div class="d-flex justify-content-center mt-4">{{ $reviews->links() }}</div>
            </section>

        </div>
    </div>

</x-layout>
