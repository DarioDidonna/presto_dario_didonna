<nav class="navbar navbar-expand-lg navbar-dark bg-custom-dark fixed-top shadow-sm transition-all" id="mainNavbar">
    <div class="container-fluid px-4">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('homepage') }}">
            <div class="brand-icon-wrapper">
                <i class="bi bi-layers-half text-neon-cyan fs-4 brand-icon animate-pulse"></i>
            </div>
            <span class="fw-bold tracking-wider text-uppercase text-white text-glow fs-5">
                PRESTO<span class="text-neon-cyan">.</span>IT
            </span>
        </a>

        <button class="navbar-toggler custom-toggler border-0" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false"
            aria-label="Toggle navigation">
            <span class="toggler-icon top-bar"></span>
            <span class="toggler-icon middle-bar"></span>
            <span class="toggler-icon bottom-bar"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4 gap-1">
                <li class="nav-item">
                    <a class="nav-link nav-link-custom active-custom" href="{{ route('homepage') }}">
                        <i class="bi bi-house-door me-1"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link nav-link-custom" href="#">
                        <i class="bi bi-grid me-1"></i> Annunci
                    </a>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link nav-link-custom dropdown-toggle" href="#" id="categoriesDropdown"
                        role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-tags me-1"></i> Categorie
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark custom-dropdown-animate border-0 shadow-lg p-2"
                        aria-labelledby="categoriesDropdown">
                        <li><a class="dropdown-menu-item-custom" href="#">Tech & Electronics</a></li>
                        <li><a class="dropdown-menu-item-custom" href="#">Gaming & Media</a></li>
                        <li><a class="dropdown-menu-item-custom" href="#">Design & Creative</a></li>
                        <li>
                            <hr class="dropdown-divider border-secondary-subtle my-2">
                        </li>
                        <li><a class="dropdown-menu-item-custom text-neon-cyan fw-semibold" href="#"><i
                                    class="bi bi-arrow-right-short me-1"></i> Vedi tutti</a></li>
                    </ul>
                </li>
            </ul>

            <form class="d-flex mx-auto col-12 col-lg-5 px-0 px-lg-3 my-3 my-lg-0 position-relative" action="#"
                method="GET">
                <div class="input-group search-group-custom">
                    <span class="input-group-text bg-search-input border-0 text-muted ps-3">
                        <i class="bi bi-search text-neon-cyan"></i>
                    </span>
                    <input class="form-control bg-search-input border-0 text-white search-input-custom ps-2 py-2"
                        type="search" name="q" placeholder="Cerca..." aria-label="Search">
                    <button class="btn btn-search-submit border-0 text-uppercase fw-bold px-3 transition-all"
                        type="submit">
                    </button>
                </div>
            </form>

            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center gap-2">
                <li class="nav-item dropdown lang-dropdown-wrapper me-lg-2">
                    <a class="nav-link lang-selector-link d-flex align-items-center gap-1 px-2 py-1 rounded"
                        href="#" id="langDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="text-uppercase fw-semibold fs-7">{{ App::getLocale() ?? 'IT' }}</span>
                        <i class="bi bi-globe2 text-neon-cyan fs-6"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark custom-dropdown-animate border-0 shadow-lg p-1 min-w-xs"
                        aria-labelledby="langDropdown">
                        <li>
                            <form action="#" method="POST" class="m-0">
                                @csrf
                                <button type="submit"
                                    class="dropdown-menu-item-custom w-100 border-0 bg-transparent text-start d-flex align-items-center justify-content-between">
                                    <span>Italiano</span><span class="text-muted fs-7">IT</span>
                                </button>
                            </form>
                        </li>
                        <li>
                            <form action="#" method="POST" class="m-0">
                                @csrf
                                <button type="submit"
                                    class="dropdown-menu-item-custom w-100 border-0 bg-transparent text-start d-flex align-items-center justify-content-between">
                                    <span>English</span><span class="text-muted fs-7">EN</span>
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>

                @auth
                    @if (Auth::user()->is_revisor)
                        <li class="nav-item position-relative me-lg-2">
                            <a class="nav-link btn-revisor-alert px-3 py-1 rounded-pill d-flex align-items-center gap-1 text-decoration-none"
                                href="#" title="Dashboard Revisore">
                                <i class="bi bi-shield-check text-neon-amber animate-pulse"></i>
                                <span
                                    class="fs-7 fw-bold text-uppercase tracking-wider text-neon-amber d-inline-block d-lg-none d-xl-inline-block">{{ __('ui.revisor') }}</span>
                                <span class="badge bg-neon-amber text-dark rounded-circle px-2 ms-1 font-monospace">
                                    {{-- {{ \App::models\Announcement::toBeRevisionedCount() }} --}}
                                </span>
                            </a>
                        </li>
                    @endif

                    <li class="nav-item me-lg-2">
                        <a class="btn btn-outline-neon-cyan btn-sm rounded-pill px-3 py-1-5 fw-semibold d-flex align-items-center gap-1 text-uppercase tracking-wide fs-7 transition-all"
                            href="{{route('create.article')}}">
                            <i class="bi bi-plus-circle-fill"></i> Crea Annuncio
                        </a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link profile-dropdown-toggle d-flex align-items-center gap-2 ps-2" href="#"
                            id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="avatar-container border border-2 border-neon-cyan rounded-circle overflow-hidden">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=0F172A&color=06B6D4&bold=true"
                                    alt="Avatar" class="img-fluid">
                            </div>
                            <span
                                class="text-white fw-medium max-w-120 truncate d-none d-lg-inline-block">{{ Auth::user()->name }}</span>
                            <i class="bi bi-chevron-down text-muted fs-8 d-none d-lg-inline-block"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark custom-dropdown-animate border-0 shadow-lg p-2 mt-2"
                            aria-labelledby="userDropdown">
                            <li class="px-3 py-2 border-bottom border-secondary-subtle mb-2 d-lg-none">
                                <div class="fw-bold text-white truncate">{{ Auth::user()->name }}</div>
                                <div class="text-muted fs-7 truncate">{{ Auth::user()->email }}</div>
                            </li>
                            <li>
                                <a class="dropdown-menu-item-custom" href="#">
                                    <i class="bi bi-person-workspace me-2 text-muted"></i> Il Mio Profilo
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-menu-item-custom" href="#">
                                    <i class="bi bi-collection me-2 text-muted"></i> I miei Annunci
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider border-secondary-subtle my-2">
                            </li>
                            <li>
                                <form action="{{route('logout')}}" method="POST" class="m-0">
                                    @csrf
                                    <button type="submit"
                                        class="dropdown-menu-item-custom w-100 border-0 bg-transparent text-start text-danger-custom fw-semibold d-flex align-items-center">
                                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link text-white-50 hover-white transition-all px-2 fs-6"
                            href="{{ route('login') }}">Accedi</a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-gradient-primary border-0 rounded-pill px-4 fw-semibold shadow-sm transition-all text-uppercase tracking-wide fs-7"
                            href="{{ route('register') }}">
                            Registrati
                        </a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>
