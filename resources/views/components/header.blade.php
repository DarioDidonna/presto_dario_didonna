<header class="presto-hero-section position-relative d-flex align-items-center overflow-hidden">
    <div class="hero-grid-overlay"></div>
    <div class="hero-radial-glow"></div>

    <div class="container position-relative z-index-2">
        <div class="row align-items-center gy-5">

            <div class="col-12 col-lg-6 text-center text-lg-start animate-fade-in-up">
                <div class="d-inline-flex align-items-center gap-2 hero-badge px-3 py-1 rounded-pill mb-3">
                    <span class="badge-pulse-dot"></span>
                    <span class="text-uppercase tracking-wider fs-8 fw-bold text-neon-cyan">La rivoluzione degli
                        annunci</span>
                </div>

                <h1 class="display-4 fw-black text-white text-uppercase tracking-tight mb-3">
                    Vendi e Compra <br class="d-none d-sm-inline">
                    <span class="text-glow text-gradient-cyan-amber">In Un Lampo</span>
                </h1>

                <p class="lead text-muted-custom mb-4 fs-5 lh-base max-w-540 mx-auto mx-lg-0">
                    Benvenuto su <strong class="text-white">Presto.it</strong>, la piattaforma di annunci di nuova
                    generazione ottimizzata, ultra-veloce e sicura. Pubblica subito le tue commissioni o trova l'affare
                    che stai cercando.
                </p>

                <div class="d-flex flex-column flex-sm-row justify-content-center justify-content-lg-start gap-3">
                    <a href="{{route('create.article')}}"
                        class="btn btn-gradient-primary border-0 rounded-pill px-4 py-2-5 fw-bold text-uppercase tracking-wider fs-7 shadow shadow-cyan transition-all">
                        <i class="bi bi-plus-circle-fill me-2"></i> Pubblica Annuncio
                    </a>
                    <a href="#"
                        class="btn btn-outline-custom text-light rounded-pill px-4 py-2-5 fw-bold text-uppercase tracking-wider fs-7 transition-all"
                        id="heroExploreBtn">
                        <i class="bi bi-grid-3x3-gap-fill me-2 text-neon-cyan"></i> Esplora Categorie
                    </a>
                </div>
            </div>

            <div
                class="col-12 col-lg-6 d-flex justify-content-center align-items-center position-relative animate-fade-in-right">
                <div class="hero-visual-container position-relative">
                    <div class="visual-circle-glow"></div>

                    <div class="hero-floating-card p-4 shadow-lg border border-secondary-subtle">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-lightning-charge-fill text-neon-cyan fs-4"></i>
                                <span class="fw-bold tracking-wider text-uppercase text-white fs-7">Presto Tech
                                    Tracker</span>
                            </div>
                            <span
                                class="badge bg-amber-alpha text-neon-amber border border-warning border-opacity-10 fs-8 px-2 py-1 uppercase font-monospace">Live</span>
                        </div>

                        <div class="row g-3">
                            <div class="col-6">
                                <div class="stat-box p-3 rounded">
                                    <div class="text-muted-custom fs-8 text-uppercase mb-1">Articoli Totali</div>
                                    <div class="fs-4 fw-bold text-white font-monospace text-glow"
                                        id="headerCounterAnnouncements">0</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-box p-3 rounded">
                                    <div class="text-muted-custom fs-8 text-uppercase mb-1">Utenti Online</div>
                                    <div class="fs-4 fw-bold text-neon-cyan font-monospace" id="headerCounterUsers">0
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 progress-container-custom">
                            <div class="progress-bar-custom animate-loading-bar"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</header>
