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
            <div class="row justify-content-center pt-3">

                <div class="col-12 col-md-8">
                    <div class="row justify-content-center">
                        @for ($i = 0; $i < 6; $i++)
                            <div class="col-6 col-md-4 mb-4 text-center">
                                <img src="https://picsum.photos/300"
                                    class="img-fluid rounded shadow transition-all category-card-wrapper"
                                    alt="immagine segnaposto">
                            </div>
                        @endfor
                    </div>
                </div>

                <div class="col-12 col-md-4 ps-md-4 d-flex flex-column justify-content-between">
                    <div>
                        <span class="badge bg-dark text-neon-cyan text-uppercase mb-2 font-monospace tracking-wide">
                            <i class="bi bi-hourglass-split me-1 animate-pulse"></i>{{ __('ui.review_phase') }}
                        </span>
                        <h1 class="text-white fw-bold mb-2">{{ $article_to_check->title }}</h1>
                        <h3 class="fs-5 text-muted-custom mb-3">
                            <i class="bi bi-person me-1"></i>{{ __('ui.author') }}: <span
                                class="text-white">{{ $article_to_check->user->name }}</span>
                        </h3>
                        <h4 class="text-neon-cyan fw-semibold mb-3 font-monospace fs-3">
                            {{ $article_to_check->price }}€
                        </h4>
                        <h4 class="fst-italic text-light mb-4 fs-6">
                            <i class="bi bi-tag me-1"></i>#{{ __($article_to_check->category->name) }}
                        </h4>

                        <div class="p-3 bg-dark rounded border border-secondary mb-4">
                            <p class="h6 text-white-50 mb-0 leading-relaxed">{{ $article_to_check->description }}</p>
                        </div>
                    </div>

                    @if (session()->has('message'))
                        <div class="row justify-content-center mb-4">
                            <div class="col-12 col-md-6">
                                <div
                                    class="alert alert-success text-center shadow rounded border-0 bg-success bg-opacity-25 text-white d-flex align-items-center justify-content-center p-3">
                                    <i class="bi bi-check-circle-fill me-2 fs-5 text-success"></i>
                                    <span class="font-monospace fs-7">{{ session('message') }}</span>
                                </div>
                            </div>
                        </div>
                    @endif


                    <div class="d-flex pb-4 justify-content-between gap-2">
                        <form action="{{ route('reject', ['article' => $article_to_check]) }}" method="POST"
                            class="w-100">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-danger py-2 w-100 fw-bold shadow-sm">
                                <i class="bi bi-x-circle me-2"></i>{{ __('ui.reject') }}
                            </button>
                        </form>

                        <form action="{{ route('accept', ['article' => $article_to_check]) }}" method="POST"
                            class="w-100">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success py-2 w-100 fw-bold shadow-sm">
                                <i class="bi bi-check-circle me-2"></i>{{ __('ui.accept') }}
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        @else
            <div class="row justify-content-center align-items-center height-custom text-center py-5">
                <div class="col-12 col-md-6">
                    <div class="p-5 rounded bg-dark border border-secondary shadow-lg">
                        <div class="mb-4">
                            <i class="bi bi-clipboard-check text-success display-1"></i>
                        </div>
                        <h1 class="fst-italic display-6 text-white mb-3">{{ __('ui.great_job') }}</h1>
                        <p class="text-muted-custom mb-4">{{ __('ui.no_new_articles') }}
                        </p>
                        <a href="{{ route('homepage') }}" class="btn btn-success px-4 py-2 fw-bold shadow">
                            <i class="bi bi-house-door me-2"></i>{{ __('ui.go_homepage') }}
                        </a>
                    </div>
                </div>
            </div>
        @endif



    </div>
</x-layout>
