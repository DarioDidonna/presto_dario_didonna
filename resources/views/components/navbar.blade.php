<nav class="navbar navbar-expand-xl navbar-dark bg-dark border-bottom border-secondary fixed-top shadow-sm transition-all py-1" id="mainNavbar">
    <div class="container-fluid px-2 px-sm-4">
        
        <!-- Brand / Logo -->
        <a class="navbar-brand d-flex align-items-center gap-2 me-2 me-xl-3" href="{{ route('homepage') }}">
            <div class="brand-icon-wrapper">
                <i class="bi bi-layers-half text-neon-cyan fs-4 brand-icon animate-pulse"></i>
            </div>
            <span class="fw-bold tracking-wider text-uppercase text-white text-glow fs-5">
                PRESTO<span class="text-neon-cyan">.</span>IT
            </span>
        </a>

        <!-- Toggler Button (Attiva il collasso a 'xl' per proteggere i display dei laptop piccoli) -->
        <button class="navbar-toggler custom-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false"
            aria-label="Toggle navigation">
            <span class="toggler-icon top-bar"></span>
            <span class="toggler-icon middle-bar"></span>
            <span class="toggler-icon bottom-bar"></span>
        </button>

        <!-- Contenuto Collassabile -->
        <div class="collapse navbar-collapse" id="navbarContent">
            
            <!-- Link di Navigazione Sinistra (Spaziature ridotte per prevenire il wrapping) -->
            <ul class="navbar-nav me-auto mb-2 mb-xl-0 ms-xl-2 gap-1 fs-7">
                <li class="nav-item">
                    <a class="nav-link nav-link-custom text-white fw-semibold px-2" href="{{ route('homepage') }}">
                        <i class="bi bi-house-door me-1 text-neon-cyan"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link nav-link-custom text-white-50 hover-neon transition-all px-2" href="{{ route('article.index') }}">
                        <i class="bi bi-grid me-1"></i> Annunci
                    </a>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link nav-link-custom text-white-50 hover-neon dropdown-toggle px-2" href="#"
                        id="categoriesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-tags me-1"></i> Categorie
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark custom-dropdown-animate border border-secondary shadow-lg p-2 bg-dark"
                        aria-labelledby="categoriesDropdown">
                        @foreach ($categories as $category)
                            <li>
                                <a class="dropdown-menu-item-custom text-white-50 py-2 px-3 rounded d-block text-decoration-none transition-all"
                                    href="{{ route('byCategory', ['category' => $category]) }}">
                                    {{ $category->name }}
                                </a>
                            </li>
                            @if (!$loop->last)
                                <li>
                                    <hr class="dropdown-divider border-secondary my-1 opacity-25">
                                </li>
                            @endif
                        @endforeach
                        <li>
                            <hr class="dropdown-divider border-secondary my-1 opacity-25">
                        </li>
                        <li>
                            <a class="dropdown-menu-item-custom text-neon-cyan fw-semibold py-2 px-3 rounded d-block text-decoration-none"
                                href="#">
                                <i class="bi bi-arrow-right-short me-1"></i> Vedi tutti
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>

            <!-- Barra di Ricerca (Ridimensionata a col-xl-3 per lasciare spazio ai pulsanti utente) -->
            <form class="d-flex mx-auto col-12 col-xl-3 px-0 px-xl-2 my-3 my-xl-0 position-relative" action="{{ route('article.search') }}" method="GET">
                <div class="input-group search-group-custom w-100 border border-secondary rounded overflow-hidden">
                    <span class="input-group-text bg-black border-0 text-muted ps-3">
                        <i class="bi bi-search text-neon-cyan"></i>
                    </span>
                    <input class="form-control border-0 text-white search-input-custom bg-black ps-2 py-1-5 fs-7"
                        type="search" name="query" placeholder="Cerca un annuncio..." aria-label="Search">
                    <button class="btn btn-search-submit border-0 text-uppercase text-neon-cyan fw-bold bg-black px-3 transition-all"
                        type="submit">
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
            </form>

            <!-- Menu Autenticazione / Lingua Destra -->
            <ul class="navbar-nav ms-auto mb-2 mb-xl-0 align-items-xl-center gap-2 fs-7">

                <!-- Dropdown Lingua -->
                <li class="nav-item dropdown lang-dropdown-wrapper me-xl-1">
                    <a class="nav-link lang-selector-link d-flex align-items-center gap-1 px-2 py-1 rounded text-white-50 hover-neon"
                        href="#" id="langDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="text-uppercase fw-semibold text-white fs-7">{{ App::getLocale() ?? 'IT' }}</span>
                        <i class="bi bi-globe2 text-neon-cyan fs-6"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark custom-dropdown-animate border border-secondary shadow-lg p-1 bg-dark"
                        aria-labelledby="langDropdown">
                        <li>
                            <form action="#" method="POST" class="m-0">
                                @csrf
                                <button type="submit"
                                    class="dropdown-menu-item-custom w-100 border-0 bg-transparent text-white-50 text-start d-flex align-items-center justify-content-between py-2 px-3 rounded">
                                    <span>Italiano</span><span class="text-muted fs-7">IT</span>
                                </button>
                            </form>
                        </li>
                        <li>
                            <form action="#" method="POST" class="m-0">
                                @csrf
                                <button type="submit"
                                    class="dropdown-menu-item-custom w-100 border-0 bg-transparent text-white-50 text-start d-flex align-items-center justify-content-between py-2 px-3 rounded">
                                    <span>English</span><span class="text-muted fs-7">EN</span>
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>

                @auth
                    <!-- Pulsante Zona Revisore -->
                    @if (Auth::user()->is_revisor)
                        <li class="nav-item position-relative me-xl-1 my-1 my-xl-0">
                            <a class="nav-link btn-revisor-alert px-2 py-1 rounded border border-warning d-flex align-items-center gap-1.5 text-decoration-none bg-warning bg-opacity-10"
                                href="{{ route('revisor.index') }}" title="Dashboard Revisore">
                                <i class="bi bi-shield-check text-neon-amber animate-pulse"></i>
                                <span class="fs-8 fw-bold text-uppercase tracking-wider text-neon-amber">Revisore</span>
                                <span class="badge bg-warning text-dark rounded-circle px-1.5 font-monospace fs-8">
                                     {{ \App\Models\Article::toBeRevisedCount() }} 
                                </span>
                            </a>
                        </li>
                    @endif

                    <!-- Pulsante Crea Annuncio -->
                    <li class="nav-item me-xl-1 my-1 my-xl-0">
                        <a class="btn btn-outline-neon-cyan btn-sm rounded px-2.5 py-1.5 fw-semibold d-flex align-items-center gap-1 text-uppercase tracking-wide fs-8 transition-all"
                            href="{{ route('create.article') }}">
                            <i class="bi bi-plus-circle-fill"></i> Annuncio
                        </a>
                    </li>

                    <!-- Dropdown Profilo -->
                    <li class="nav-item dropdown">
                        <a class="nav-link profile-dropdown-toggle d-flex align-items-center gap-2 ps-1 text-white"
                            href="#" id="userDropdown" role="button" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <div class="avatar-container border border-2 border-neon-cyan rounded-circle overflow-hidden d-flex"
                                style="width: 28px; height: 28px;">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=0F172A&color=06B6D4&bold=true"
                                    alt="Avatar" class="img-fluid">
                            </div>
                            <span class="text-white fw-medium max-w-100 truncate d-none d-xl-inline-block">{{ Auth::user()->name }}</span>
                            <i class="bi bi-chevron-down text-muted fs-8 d-none d-xl-inline-block"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark custom-dropdown-animate border border-secondary shadow-lg p-2 mt-2 bg-dark"
                            aria-labelledby="userDropdown">
                            <li class="px-3 py-2 border-bottom border-secondary mb-2 d-xl-none">
                                <div class="fw-bold text-white truncate">{{ Auth::user()->name }}</div>
                                <div class="text-muted fs-7 truncate">{{ Auth::user()->email }}</div>
                            </li>
                            <li>
                                <a class="dropdown-menu-item-custom text-white-50 py-2 px-3 rounded d-block text-decoration-none"
                                    href="#">
                                    <i class="bi bi-person-workspace me-2 text-light"></i> Il Mio Profilo
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-menu-item-custom text-white-50 py-2 px-3 rounded d-block text-decoration-none"
                                    href="#">
                                    <i class="bi bi-collection me-2 text-light"></i> I miei Annunci
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider border-secondary my-2 opacity-25">
                            </li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST" class="m-0">
                                    @csrf
                                    <button type="submit"
                                        class="dropdown-menu-item-custom w-100 border-0 bg-transparent text-start text-danger fw-semibold d-flex align-items-center py-2 px-3 rounded">
                                        <i class="bi bi-box-arrow-right me-2"></i> Esci
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link text-white-50 hover-neon transition-all px-2.5 py-1.5"
                            href="{{ route('login') }}">Accedi</a>
                    </li>
                    <li class="nav-item ms-xl-1">
                        <a class="btn btn-gradient-primary border-0 rounded px-3 py-1.5 fw-semibold shadow-sm transition-all text-uppercase tracking-wide fs-8 text-white bg-info"
                            href="{{ route('register') }}">
                            Registrati
                        </a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>