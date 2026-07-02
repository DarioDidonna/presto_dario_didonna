<x-layout>
    <div class="container pt-custom-navbar my-5">

        <div class="row py-5 justify-content-center align-items-center text-center">
            <div class="col-12 pt-5">
                <h1 class="display-2 fw-black text-white text-uppercase tracking-tight">
                    {{ __('ui.category_articles') }}
                    <span class="fst-italic fw-bold text-glow text-neon-cyan">{{ $category->name }}</span>
                </h1>
            </div>
        </div>

        <div class="row height-custom justify-content-center align-items-center py-5 g-4">

            @forelse ($articles as $article)
                <div class="col-12 col-md-3 d-flex animate-fade-in">
                    <div class="w-100 h-100">
                        <x-card :article="$article" />
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <div class="fs-1 text-muted mb-3">
                        <i class="bi bi-tag-fill text-neon-amber"></i>
                    </div>
                    <h3 class="text-white fw-bold text-uppercase tracking-wide">
                        {{ __('ui.no_articles_in_category') }}
                    </h3>

                    @auth
                        <a class="btn btn-cyber-gradient my-5 text-uppercase tracking-wider fs-7 fw-bold"
                            href="{{ route('create.article') }}">
                            <i class="bi bi-plus-circle me-2"></i>{{ __('ui.publish_article') }}
                        </a>
                    @endauth
                </div>
            @endforelse

        </div>
    </div>
</x-layout>
