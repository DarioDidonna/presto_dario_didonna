<footer class="bg-footer-dark border-top-alpha text-muted-custom pt-5 pb-3 mt-auto">
    <div class="container">
        <div class="row gy-4">
            
            <div class="col-12 col-md-4">
                <a class="d-flex align-items-center gap-2 text-decoration-none mb-3" href="{{ route('homepage') }}">
                    <i class="bi bi-layers-half text-neon-cyan fs-4 animate-pulse"></i>
                    <span class="fw-bold tracking-wider text-uppercase text-white text-glow fs-5">
                        PRESTO<span class="text-neon-cyan">.</span>IT
                    </span>
                </a>
                <p class="fs-7 lh-base mb-3">
                    {{ __('ui.footer_description') }}
                </p>
            </div>

            <div class="col-6 col-md-2 offset-md-1">
                <h6 class="text-white fw-bold text-uppercase tracking-wider fs-7 mb-3 border-bottom-cyan pb-2">
                    {{ __('ui.footer_explore') }}
                </h6>
                <ul class="list-unstyled d-flex flex-column gap-2 fs-7">
                    <li>
                        <a href="{{ route('homepage') }}" class="footer-link-custom">
                            <i class="bi bi-chevron-right fs-8 me-1"></i> {{ __('ui.home') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('article.index') }}" class="footer-link-custom">
                            <i class="bi bi-chevron-right fs-8 me-1"></i> {{ __('ui.announcements') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('create.article') }}" class="footer-link-custom">
                            <i class="bi bi-chevron-right fs-8 me-1"></i> {{ __('ui.new_ad') }}
                        </a>
                    </li>
                </ul>
            </div>

            <div class="col-6 col-md-2">
                <h6 class="text-white fw-bold text-uppercase tracking-wider fs-7 mb-3 border-bottom-cyan pb-2">
                    {{ __('ui.footer_security') }}
                </h6>
                <ul class="list-unstyled d-flex flex-column gap-2 fs-7">
                    @if (Auth::user()?->is_revisor)
                        <li>
                            <a href="{{ route('revisor.index') }}" class="footer-link-custom d-flex align-items-center gap-1">
                                <i class="bi bi-shield-lock-fill text-neon-amber"></i> {{ __('ui.revisor_area') }}
                            </a>
                        </li>
                    @endif
                    <li>
                        <span class="footer-static-info">
                            <i class="bi bi-robot me-1 text-neon-cyan"></i> {{ __('ui.footer_feature_moderation') }}
                        </span>
                    </li>
                    <li>
                        <span class="footer-static-info">
                            <i class="bi bi-eye-slash me-1"></i> {{ __('ui.footer_feature_faces') }}
                        </span>
                    </li>
                    <li>
                        <span class="footer-static-info">
                            <i class="bi bi-patch-check me-1"></i> {{ __('ui.footer_feature_watermark') }}
                        </span>
                    </li>
                </ul>
            </div>

            <div class="col-12 col-md-3">
                <h6 class="text-white fw-bold text-uppercase tracking-wider fs-7 mb-3 border-bottom-cyan pb-2">
                    {{ __('ui.footer_start_now') }}
                </h6>
                <p class="fs-7 mb-3">{{ __('ui.footer_start_text') }}</p>
                <a href="{{ route('create.article') }}" class="btn btn-cyber-gradient btn-sm text-uppercase tracking-wider fs-8 fw-bold">
                    <i class="bi bi-plus-circle-fill me-1"></i> {{ __('ui.publish_ad') }}
                </a>
            </div>

        </div>

        <hr class="border-secondary-subtle my-4">

        <div class="row align-items-center fs-7">
            <div class="col-12 text-center">
                &copy; {{ date('Y') }} <span class="text-white fw-semibold">Presto</span>. {{ __('ui.all_rights_reserved') }}
            </div>
        </div>
    </div>
</footer>