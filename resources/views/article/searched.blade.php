<x-layout :title="__('ui.search_title', ['query' => $query])">

    <div class="position-relative overflow-hidden bg-navbar py-5 pt-custom-navbar border-bottom border-secondary-subtle">
        <div class="container position-relative z-1 py-4">
            <div class="row align-items-center gy-3">
                <div class="col-12 col-md-8 text-center text-md-start">
                    <span
                        class="badge bg-transparent border border-neon-cyan text-neon-cyan text-uppercase tracking-widest px-3 py-2 mb-3 fs-8 fw-semibold">
                        {{ __('ui.explore_marketplace') }}
                    </span>
                    <h1 class="display-5 fw-black text-white text-uppercase tracking-tight mb-0 text-break">
                        {{ __('ui.results_for') }}: <span class="text-glow text-neon-cyan text-none-transform">"{{ $query }}"</span>
                    </h1>
                </div>
                <div class="col-12 col-md-4 text-center text-md-end">
                    <span class="badge bg-search border border-secondary text-white rounded-pill px-3 py-2 fs-6 font-monospace">
                        {{ $articles->total() }} {{ $articles->total() == 1 ? __('ui.single_result') : __('ui.results_found') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="article-index-wrapper py-5">
        <div class="container">

            @if ($articles->count() > 0)
                <div class="row g-4">
                    @foreach ($articles as $article)
                        <div class="col-12 col-md-6 col-lg-4 d-flex">
                            <x-card :article="$article" />
                        </div>
                    @endforeach
                </div>

                @if ($articles->hasPages())
                    <div class="d-flex justify-content-center mt-5 pagination-cyber">
                        {{ $articles->links() }}
                    </div>
                @endif
            @else
                <div class="row justify-content-center py-5">
                    <div
                        class="col-12 col-md-6 text-center py-5 border border-dashed border-secondary rounded-4 bg-navbar-dark shadow">
                        <div class="fs-1 mb-3">
                            <i class="bi bi-search text-neon-cyan"></i>
                        </div>
                        <h4 class="text-white fw-bold text-uppercase tracking-wide mb-2">{{ __('ui.no_articles_match') }}</h4>
                        <p class="text-muted-custom fs-7 mb-4">{{ __('ui.search_tip') }}</p>
                        <a href="{{ route('article.index') }}"
                            class="btn btn-outline-neon-cyan text-uppercase tracking-wider fs-7 fw-bold px-4 py-2">
                            <i class="bi bi-arrow-left me-2"></i>{{ __('ui.back_to_all_ads') }}
                        </a>
                    </div>
                </div>
            @endif

        </div>
    </div>

</x-layout>
