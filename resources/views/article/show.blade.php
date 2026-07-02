<x-layout>
    <div class="article-show-wrapper py-5 my-5">
        <div class="container py-4 position-relative z-1">

            <div class="mb-4">
                <a href="{{ route('article.index') }}"
                    class="text-neon-cyan text-decoration-none text-uppercase tracking-wider fs-8 fw-bold transition-all hover-back">
                    <i class="bi bi-arrow-left me-2"></i> {{ __('ui.back_to_board') }}
                </a>
            </div>

            <div class="row g-5">

                <div class="col-12 col-lg-7">
                    @if ($article->images->count() > 0)
                        <div id="articleCarousel"
                            class="carousel slide border border-neon-cyan shadow-lg rounded-3 overflow-hidden bg-navbar"
                            data-bs-ride="false">

                            @if ($article->images->count() > 1)
                                <div class="carousel-indicators">
                                    @foreach ($article->images as $key => $image)
                                        <button type="button" data-bs-target="#articleCarousel"
                                            data-bs-slide-to="{{ $key }}"
                                            class="{{ $loop->first ? 'active' : '' }}"
                                            aria-current="{{ $loop->first ? 'true' : 'false' }}"
                                            aria-label="Slide {{ $key + 1 }}"></button>
                                    @endforeach
                                </div>
                            @endif

                            <div class="carousel-inner">
                                @foreach ($article->images as $key => $image)
                                    <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                        <img src="{{ Storage::url($image->path) }}"
                                            class="d-block w-100 img-fluid object-fit-cover" style="height: 450px;"
                                            alt="Immagine {{ $key + 1 }} dell'articolo {{ $article->title }}">
                                    </div>
                                @endforeach
                            </div>

                            @if ($article->images->count() > 1)
                                <button class="carousel-control-prev" type="button" data-bs-target="#articleCarousel"
                                    data-bs-slide="prev">
                                    <span
                                        class="carousel-control-prev-icon bg-cyber-dark p-3 rounded-circle border border-neon-cyan"
                                        aria-hidden="true"></span>
                                    <span class="visually-hidden">{{ __('ui.previous') }}</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#articleCarousel"
                                    data-bs-slide="next">
                                    <span
                                        class="carousel-control-next-icon bg-cyber-dark p-3 rounded-circle border border-neon-cyan"
                                        aria-hidden="true"></span>
                                    <span class="visually-hidden">{{ __('ui.next') }}</span>
                                </button>
                            @endif
                        </div>
                    @else
                        <div
                            class="border border-secondary shadow-lg rounded-3 overflow-hidden bg-navbar text-center p-2">
                            <img src="https://picsum.photos/800/500?random=tech"
                                class="img-fluid rounded-3 w-100 object-fit-cover opacity-75" style="height: 450px;"
                                alt="Nessuna foto inserita dall'utente">
                            <div class="py-2 text-muted-custom font-monospace fs-8">
                                <i class="bi bi-image-alt me-1"></i> [NESSUNA IMMAGINE DISPONIBILE]
                            </div>
                        </div>
                    @endif
                </div>

                <div class="col-12 col-lg-5 d-flex flex-column justify-content-between">
                    <div
                        class="p-4 rounded-3 border border-secondary-subtle bg-navbar-card shadow-lg h-100 d-flex flex-column">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            @if ($article->category)
                                <a href="{{ route('byCategory', ['category' => $article->category]) }}"
                                    class="badge bg-transparent border border-neon-amber text-neon-amber text-uppercase tracking-wider fs-9 text-decoration-none transition-all hover-glow-amber">
                                    <i class="bi bi-tag-fill me-1"></i> {{ $article->category->name }}
                                </a>
                            @else
                                <span
                                    class="badge bg-transparent border border-secondary text-muted fs-9 text-uppercase tracking-wider">{{ __('ui.no_category') }}</span>
                            @endif

                            <span class="text-muted-custom fs-8 font-monospace">
                                <i class="bi bi-calendar3 me-1"></i> {{ $article->created_at->format('d/m/Y') }}
                            </span>
                        </div>

                        <h1
                            class="h2 fw-black text-white text-uppercase tracking-tight mb-3 d-flex flex-wrap align-items-baseline gap-2">
                            <span>{{ $article->title }}</span>
                            <span class="fs-7 text-neon-cyan font-monospace fw-normal text-none-transform">
                                <i
                                    class="bi bi-person-fill me-1"></i>{{ __('ui.published_by', ['name' => $article->user->name ?? __('ui.anonymous_user')]) }}
                            </span>
                        </h1>

                        <div
                            class="py-3 px-4 rounded-3 bg-cyber-dark border-start border-3 border-neon-cyan mb-4 shadow-sm">
                            <span
                                class="text-muted-custom fs-8 text-uppercase tracking-wider d-block mb-1">{{ __('ui.requested_price') }}</span>
                            <span class="display-6 fw-bold text-glow text-neon-cyan font-monospace">
                                {{ number_format($article->price, 2, ',', '.') }} €
                            </span>
                        </div>

                        <div class="flex-grow-1 mb-4">
                            <h6 class="text-white text-uppercase tracking-wide fs-7 fw-bold mb-2">
                                <i class="bi bi-file-text me-2 text-neon-cyan"></i>{{ __('ui.article_description') }}
                            </h6>
                            <p class="text-muted-custom fs-6 lh-base text-pre-wrap">{{ $article->description }}</p>
                        </div>

                        <div class="pt-4 border-top border-secondary-subtle mt-auto">
                            <div class="row g-2">
                                <div class="col-12 col-md-8">
                                    <button
                                        class="btn btn-cyber-gradient w-100 text-uppercase text-light tracking-wider fs-7 fw-bold py-25">
                                        <i class="bi bi-chat-left-dots-fill me-2"></i> {{ __('ui.contact_seller') }}
                                    </button>
                                </div>
                                <div class="col-12 col-md-4">
                                    <button
                                        class="btn btn-outline-secondary w-100 text-uppercase tracking-wider fs-7 fw-bold py-25 text-white border-secondary-subtle">
                                        <i class="bi bi-heart"></i> {{ __('ui.save') }}
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</x-layout>
