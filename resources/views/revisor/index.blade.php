<x-layout>
    <div class="container-fluid pt-5">

        <div class="row mb-5 pt-5 justify-content-center">
            <div class="col-12 col-md-6 col-lg-4 mt-5">
                <div class="rounded shadow-lg bg-dark p-3 text-center border border-secondary">
                    <h1 class="display-5 m-0 text-uppercase fw-bold tracking-wide text-white fs-3">
                        <i class="bi bi-shield-check me-2 text-neon-cyan animate-pulse"></i>Revisor Dashboard
                    </h1>
                </div>
            </div>
        </div>

        @if ($article_to_check)
            <div class="row justify-content-center pt-3 px-xl-5">

                <div class="col-12 col-lg-7 mb-4 mb-lg-0">
                    @if ($article_to_check->images->count())
                        <div class="card bg-dark border border-secondary shadow-lg rounded p-3">

                            <div id="revisorImageCarousel" class="carousel slide" data-bs-ride="false">
                                <div class="carousel-inner rounded border border-secondary bg-black">
                                    @foreach ($article_to_check->images as $key => $image)
                                        <div class="carousel-item {{ $key === 0 ? 'active' : '' }}">
                                            <div class="position-relative text-center bg-black">
                                                <img src="{{ $article_to_check->images->isNotEmpty() ? $article_to_check->images->first()->getUrl(300, 300) : 'https://picsum.photos/300/200' }}"
                                                    class="img-fluid object-fit-contain"
                                                    alt="Immagine {{ $key + 1 }}">

                                                <span
                                                    class="position-absolute top-0 start-0 m-3 badge bg-dark bg-opacity-75 border border-secondary text-neon-cyan font-monospace">
                                                    {{ $key + 1 }} / {{ $article_to_check->images->count() }}
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                @if ($article_to_check->images->count() > 1)
                                    <button class="carousel-control-prev" type="button"
                                        data-bs-target="#revisorImageCarousel" data-bs-slide="prev">
                                        <span
                                            class="carousel-control-prev-icon p-3 bg-dark bg-opacity-50 rounded-circle border border-secondary"
                                            aria-hidden="true"></span>
                                        <span class="visually-hidden">Previous</span>
                                    </button>
                                    <button class="carousel-control-next" type="button"
                                        data-bs-target="#revisorImageCarousel" data-bs-slide="next">
                                        <span
                                            class="carousel-control-next-icon p-3 bg-dark bg-opacity-50 rounded-circle border border-secondary"
                                            aria-hidden="true"></span>
                                        <span class="visually-hidden">Next</span>
                                    </button>
                                @endif
                            </div>

                            @if ($article_to_check->images->count() > 1)
                                <div class="row g-2 mt-3 justify-content-center">
                                    @foreach ($article_to_check->images as $key => $image)
                                        <div class="col-2">
                                            <button type="button" data-bs-target="#revisorImageCarousel"
                                                data-bs-slide-to="{{ $key }}"
                                                class="btn p-0 w-100 rounded overflow-hidden border {{ $key === 0 ? 'border-neon-cyan' : 'border-secondary' }} opacity-75 hover-opacity-100"
                                                aria-current="{{ $key === 0 ? 'true' : 'false' }}"
                                                aria-label="Slide {{ $key + 1 }}">
                                                <img src="{{ $image->getUrl(300, 300) }}"
                                                    class="img-fluid object-fit-cover">
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                        </div>

                        <div class="card bg-dark border border-secondary shadow-lg rounded p-3 mt-4">
                            <h5 class="text-neon-cyan font-monospace text-uppercase small mb-3">
                                <i class="bi bi-cpu me-2"></i>Google Vision AI Analysis
                            </h5>

                            <div class="row">
                                @foreach ($article_to_check->images as $key => $image)
                                    <div class="col-12 col-md-6 mb-3">
                                        <div class="p-3 bg-black rounded border border-secondary h-100 d-flex flex-column justify-content-between">
                                            
                                            <div>
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <span class="badge bg-secondary font-monospace">Imm #{{ $key + 1 }}</span>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="text-neon-cyan font-monospace small d-block text-uppercase mb-2">Labels</label>
                                                    <div class="d-flex flex-wrap gap-1">
                                                        @if ($image->labels)
                                                            @foreach ($image->labels as $label)
                                                                <span class="badge bg-dark border border-secondary text-light font-monospace small">
                                                                    #{{ $label }}
                                                                </span>
                                                            @endforeach
                                                        @else
                                                            <span class="text-muted small fst-italic">No labels detected</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mt-2">
                                                <label class="text-neon-cyan font-monospace small d-block text-uppercase mb-2">Ratings</label>
                                                <div class="row justify-content-center align-items-center g-1 text-white-50 small font-monospace">
                                                    <div class="col-2 text-center">
                                                        <div class="{{ $image->adult }}"></div>
                                                    </div>
                                                    <div class="col-10 text-uppercase">Adult</div>

                                                    <div class="col-2 text-center">
                                                        <div class="{{ $image->violence }}"></div>
                                                    </div>
                                                    <div class="col-10 text-uppercase">Violence</div>

                                                    <div class="col-2 text-center">
                                                        <div class="{{ $image->spoof }}"></div>
                                                    </div>
                                                    <div class="col-10 text-uppercase">Spoof</div>

                                                    <div class="col-2 text-center">
                                                        <div class="{{ $image->racy }}"></div>
                                                    </div>
                                                    <div class="col-10 text-uppercase">Racy</div>

                                                    <div class="col-2 text-center">
                                                        <div class="{{ $image->medical }}"></div>
                                                    </div>
                                                    <div class="col-10 text-uppercase">Medical</div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="card bg-dark border border-secondary shadow-lg rounded p-5 text-center d-flex flex-column align-items-center justify-content-center">
                            <i class="bi bi-image text-muted display-2 mb-3"></i>
                            <p class="text-muted-custom fst-italic">Nessuna immagine caricata per questo articolo</p>
                        </div>
                    @endif
                </div>

                <div class="col-12 col-lg-5 ps-lg-4 d-flex flex-column justify-content-between">
                    <div class="card bg-dark border border-secondary shadow-lg rounded p-4 h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge bg-black text-neon-cyan border border-secondary text-uppercase font-monospace tracking-wide px-3 py-2">
                                    <i class="bi bi-hourglass-split me-1 animate-pulse"></i>{{ __('ui.review_phase') }}
                                </span>
                                <h4 class="text-neon-cyan fw-bold font-monospace m-0 fs-3">
                                    {{ $article_to_check->price }}€
                                </h4>
                            </div>

                            <h1 class="text-white fw-bold mb-2 display-6">{{ $article_to_check->title }}</h1>

                            <div class="d-flex gap-3 mb-4 text-muted-custom fs-6 font-monospace">
                                <span><i class="bi bi-person me-1"></i>{{ $article_to_check->user->name }}</span>
                                <span><i class="bi bi-tag me-1"></i>#{{ __($article_to_check->category->name) }}</span>
                            </div>

                            <hr class="border-secondary my-3">

                            <div class="p-3 bg-black rounded border border-secondary min-height-custom">
                                <label class="text-neon-cyan font-monospace small mb-2 d-block text-uppercase">Descrizione Prodotto</label>
                                <p class="text-light mb-0 leading-relaxed font-sans">
                                    {{ $article_to_check->description }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-4">
                            @if (session()->has('message'))
                                <div class="alert alert-success text-center shadow rounded border-0 bg-success bg-opacity-25 text-white d-flex align-items-center justify-content-center p-3 mb-3">
                                    <i class="bi bi-check-circle-fill me-2 fs-5 text-success"></i>
                                    <span class="font-monospace fs-7">{{ session('message') }}</span>
                                </div>
                            @endif

                            <div class="d-flex justify-content-between gap-3">
                                <form action="{{ route('reject', ['article' => $article_to_check]) }}" method="POST" class="w-100">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-outline-danger py-2.5 w-100 fw-bold shadow-sm border-2">
                                        <i class="bi bi-x-circle me-2"></i>{{ __('ui.reject') }}
                                    </button>
                                </form>

                                <form action="{{ route('accept', ['article' => $article_to_check]) }}" method="POST" class="w-100">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-success py-2.5 w-100 fw-bold shadow-sm text-black fw-black">
                                        <i class="bi bi-check-circle me-2"></i>{{ __('ui.accept') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        @else
            <div class="row justify-content-center align-items-center text-center py-5">
                <div class="col-12 col-md-6 col-lg-5 mt-4">
                    <div class="p-5 rounded bg-dark border border-secondary shadow-lg">
                        <div class="mb-4">
                            <i class="bi bi-clipboard-check text-neon-cyan display-1 animate-pulse"></i>
                        </div>
                        <h1 class="fst-italic display-6 text-white mb-3">{{ __('ui.great_job') }}</h1>
                        <p class="text-muted-custom mb-4">{{ __('ui.no_new_articles') }}</p>
                        <a href="{{ route('homepage') }}" class="btn btn-outline-info px-4 py-2 fw-bold shadow">
                            <i class="bi bi-house-door me-2"></i>{{ __('ui.go_homepage') }}
                        </a>
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-layout>