<footer class="bg-footer-dark border-top-alpha text-muted-custom pt-5 pb-3 mt-auto">
    <div class="container">
        <div class="row gy-4">
            
            <div class="col-12 col-md-4">
                <a class="d-flex align-items-center gap-2 text-decoration-none mb-3" href="{{ route('homepage') }}">
                    <i class="bi bi-layers-half text-neon-cyan fs-4 animate-pulse"></i>
                    <span class="fw-bold tracking-wider text-uppercase text-white text-glow fs-5">
                        PRESTO<span class="text-neon-cyan">
                </a>
                <p class="fs-7 lh-base mb-3">
                    La piattaforma di annunci di nuova generazione, ottimizzata con intelligenza artificiale e localizzazione multi-lingua automatica per connettere venditori e acquirenti in tutto il mondo.
                </p>
                <div class="d-flex gap-2">
                    <a href="#" class="social-icon-link" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" class="social-icon-link" aria-label="GitHub"><i class="bi bi-github"></i></a>
                    <a href="#" class="social-icon-link" aria-label="Discord"><i class="bi bi-discord"></i></a>
                    <a href="#" class="social-icon-link" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
                </div>
            </div>

            <div class="col-6 col-md-2 offset-md-1">
                <h6 class="text-white fw-bold text-uppercase tracking-wider fs-7 mb-3 border-bottom-cyan pb-2">
                    Esplora
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
                    Sicurezza & AI
                </h6>
                <ul class="list-unstyled d-flex flex-column gap-2 fs-7">
                    <li>
                        <a href="{{ route('revisor.index') }}" class="footer-link-custom d-flex align-items-center gap-1">
                            <i class="bi bi-shield-lock-fill text-neon-amber"></i> {{ __('ui.revisor_area') }}
                        </a>
                    </li>
                    <li>
                        <span class="footer-static-info">
                            <i class="bi bi-robot me-1 text-neon-cyan"></i> AI Text Moderation
                        </span>
                    </li>
                    <li>
                        <span class="footer-static-info">
                            <i class="bi bi-eye-slash me-1"></i> Privacy Blur & Face Mask
                        </span>
                    </li>
                    <li>
                        <span class="footer-static-info">
                            <i class="bi bi-patch-check me-1"></i> Smart Watermark
                        </span>
                    </li>
                </ul>
            </div>

            <div class="col-12 col-md-3">
                <h6 class="text-white fw-bold text-uppercase tracking-wider fs-7 mb-3 border-bottom-cyan pb-2">
                    Resta Aggiornato
                </h6>
                <p class="fs-7 mb-3">{{ __('ui.newsletter_text') }}</p>
                <form class="footer-newsletter-form" id="newsletterForm">
                    <div class="input-group search-group-custom">
                        <input type="email" class="form-control bg-search-input border-0 search-input-custom ps-3 py-2 fs-7" placeholder="{{ __('ui.email_placeholder') }}" aria-label="Email" required>
                        <button class="btn btn-search-submit border-0 px-3 transition-all" type="submit">
                            <i class="bi bi-send-fill fs-7"></i>
                        </button>
                    </div>
                </form>
            </div>

        </div>

        <hr class="border-secondary-subtle my-4">

        <div class="row align-items-center fs-7">
            <div class="col-12 col-md-6 text-center text-md-start mb-2 mb-md-0">
                &copy; 2026 <span class="text-white fw-semibold">Presto</span>. Tutti i diritti riservati. Corso Web Developer Final Project.
            </div>
            <div class="col-12 col-md-6 text-center text-md-end d-flex justify-content-center justify-content-md-end gap-3">
                <span class="footer-system-status d-flex align-items-center gap-1">
                    <span class="status-indicator-green"></span> Core v10.4 (Laravel)
                </span>
                <a href="#" class="footer-link-custom text-decoration-none">Privacy Policy</a>
                <a href="#" class="footer-link-custom text-decoration-none">Termini di Servizio</a>
            </div>
        </div>
    </div>
</footer>