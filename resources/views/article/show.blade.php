<x-layout>
    <div class="article-show-wrapper py-5 my-5">
        <div class="container py-4 position-relative z-1">

            <div class="mb-4">
                <a href="{{ route('article.index') }}"
                    class="text-neon-cyan text-decoration-none text-uppercase tracking-wider fs-8 fw-bold transition-all hover-back">
                    <i class="bi bi-arrow-left me-2"></i> Torna alla bacheca
                </a>
            </div>

            <div class="row g-5">

                <div class="col-12 col-lg-7">
                    <div id="articleCarousel"
                        class="carousel slide border border-neon-cyan shadow-lg rounded-3 overflow-hidden bg-navbar"
                        data-bs-ride="false">

                        <div class="carousel-indicators">
                            <button type="button" data-bs-target="#articleCarousel" data-bs-slide-to="0" class="active"
                                aria-current="true" aria-label="Slide 1"></button>
                            <button type="button" data-bs-target="#articleCarousel" data-bs-slide-to="1"
                                aria-label="Slide 2"></button>
                            <button type="button" data-bs-target="#articleCarousel" data-bs-slide-to="2"
                                aria-label="Slide 3"></button>
                        </div>

                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <img src="https://picsum.photos/800/500?random=1"
                                    class="d-block w-100 img-fluid object-fit-cover" style="max-height: 450px;"
                                    alt="Placeholder 1">
                                <div class="carousel-caption d-none d-md-block bg-dark-translucent rounded-3 py-1">
                                    <p class="fs-8 text-neon-cyan tracking-wider text-uppercase m-0">Vista Principale
                                    </p>
                                </div>
                            </div>
                            <div class="carousel-item">
                                <img src="https://picsum.photos/800/500?random=2"
                                    class="d-block w-100 img-fluid object-fit-cover" style="max-height: 450px;"
                                    alt="Placeholder 2">
                                <div class="carousel-caption d-none d-md-block bg-dark-translucent rounded-3 py-1">
                                    <p class="fs-8 text-neon-amber tracking-wider text-uppercase m-0">Dettaglio
                                        Componente</p>
                                </div>
                            </div>
                            <div class="carousel-item">
                                <img src="https://picsum.photos/800/500?random=3"
                                    class="d-block w-100 img-fluid object-fit-cover" style="max-height: 450px;"
                                    alt="Placeholder 3">
                                <div class="carousel-caption d-none d-md-block bg-dark-translucent rounded-3 py-1">
                                    <p class="fs-8 text-neon-cyan tracking-wider text-uppercase m-0">Panoramica
                                        Posteriore</p>
                                </div>
                            </div>
                        </div>

                        <button class="carousel-control-prev" type="button" data-bs-target="#articleCarousel"
                            data-bs-slide="prev">
                            <span
                                class="carousel-control-prev-icon bg-cyber-dark p-3 rounded-circle border border-neon-cyan"
                                aria-hidden="true"></span>
                            <span class="visually-hidden">Precedente</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#articleCarousel"
                            data-bs-slide="next">
                            <span
                                class="carousel-control-next-icon bg-cyber-dark p-3 rounded-circle border border-neon-cyan"
                                aria-hidden="true"></span>
                            <span class="visually-hidden">Successivo</span>
                        </button>
                    </div>
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
                                    class="badge bg-transparent border border-secondary text-muted fs-9 text-uppercase tracking-wider">Senza
                                    Categoria</span>
                            @endif

                            <span class="text-muted-custom fs-8 font-monospace">
                                <i class="bi bi-calendar3 me-1"></i> {{ $article->created_at->format('d/m/Y') }}
                            </span>
                        </div>

                        {{-- <h1 class="h2 fw-black text-white text-uppercase tracking-tight mb-3">
                            {{ $article->title }}
                        </h1> --}}

                        <h1 class="h2 fw-black text-white text-uppercase tracking-tight mb-3 d-flex flex-wrap align-items-baseline gap-2">
                            <span>{{ $article->title }}</span>
                            <span class="fs-7 text-neon-cyan font-monospace fw-normal text-none-transform">
                                <i class="bi bi-person-fill me-1"></i>Pubblicato da: {{ $article->user->name ?? 'Utente Anonimo' }}
                            </span>
                        </h1>

                        <div
                            class="py-3 px-4 rounded-3 bg-cyber-dark border-start border-3 border-neon-cyan mb-4 shadow-sm">
                            <span class="text-muted-custom fs-8 text-uppercase tracking-wider d-block mb-1">Prezzo
                                Richiesto</span>
                            <span class="display-6 fw-bold text-glow text-neon-cyan font-monospace">
                                {{ number_format($article->price, 2, ',', '.') }} €
                            </span>
                        </div>

                        <div class="flex-grow-1 mb-4">
                            <h6 class="text-white text-uppercase tracking-wide fs-7 fw-bold mb-2">
                                <i class="bi bi-file-text me-2 text-neon-cyan"></i>Descrizione Articolo
                            </h6>
                            <p class="text-muted-custom fs-6 lh-base text-pre-wrap">{{ $article->description }}</p>
                        </div>

                        <div class="pt-4 border-top border-secondary-subtle mt-auto">
                            <div class="row g-2">
                                <div class="col-12 col-md-8">
                                    <button
                                        class="btn btn-cyber-gradient w-100 text-uppercase tracking-wider fs-7 fw-bold py-25">
                                        <i class="bi bi-chat-left-dots-fill me-2"></i> Contatta il Venditore
                                    </button>
                                </div>
                                <div class="col-12 col-md-4">
                                    <button
                                        class="btn btn-outline-secondary w-100 text-uppercase tracking-wider fs-7 fw-bold py-25 text-white border-secondary-subtle">
                                        <i class="bi bi-heart"></i> Salva
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
