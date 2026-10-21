
document.addEventListener('DOMContentLoaded', () => {

    /* ==========================================================================
       1. NAVBAR & GLOBAL NAVIGATION
       ========================================================================== */
    const navbar = document.getElementById('mainNavbar');

    // Navbar più compatta e scura dopo i primi pixel di scroll
    const updateNavbar = () => {
        if (!navbar) return;
        navbar.classList.toggle('navbar-scrolled', window.scrollY > 20);
    };
    window.addEventListener('scroll', updateNavbar, { passive: true });
    updateNavbar();

    // Profilo: anteprima della foto scelta prima del caricamento
    document.querySelectorAll('[data-avatar-preview]').forEach(input => {
        input.addEventListener('change', () => {
            const target = document.getElementById(input.dataset.avatarPreview);
            const file = input.files && input.files[0];
            if (!target || !file || !file.type.startsWith('image/')) return;

            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.alt = '';
            img.className = 'w-100 h-100 object-fit-cover';
            img.onload = () => URL.revokeObjectURL(img.src);
            target.replaceChildren(img);
        });
    });

    // Carrello aggiornato senza ricaricare la pagina (es. dalla chat): aggiorna il contatore nella navbar
    window.addEventListener('cart-updated', (event) => {
        const icon = document.querySelector('[data-cart-icon]');
        if (!icon) return;
        let badge = icon.querySelector('.cart-badge');
        if (!badge) {
            badge = document.createElement('span');
            badge.className = 'badge rounded-pill bg-warning text-dark cart-badge';
            icon.appendChild(badge);
        }
        badge.textContent = event.detail.count;
    });

    // Footer: alone leggero che segue il cursore
    const footer = document.querySelector('[data-footer-glow]');
    if (footer && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
        footer.addEventListener('pointermove', (event) => {
            const box = footer.getBoundingClientRect();
            footer.style.setProperty('--fx', `${event.clientX - box.left}px`);
            footer.style.setProperty('--fy', `${event.clientY - box.top}px`);
        });
        footer.addEventListener('pointerleave', () => footer.style.setProperty('--fx', '-999px'));
    }

    // I dropdown (anche su mobile) sono gestiti da Bootstrap tramite data-bs-toggle="dropdown":
    // un secondo gestore qui li apriva e richiudeva subito.

    document.addEventListener('keydown', (e) => {
        if (e.key === '/' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
            e.preventDefault();
            const searchInput = document.querySelector('.search-input-custom');
            if (searchInput) searchInput.focus();
        }
    });


    /* ==========================================================================
       2. AUTHENTICATION & LOGIN FORM LOGIC
       ========================================================================== */
    // Mostra/nascondi password: <button data-toggle-password="id-del-campo">
    // (prima era un onclick verso una funzione non globale, quindi non funzionava)
    document.querySelectorAll('[data-toggle-password]').forEach(button => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.togglePassword);
            const icon = button.querySelector('i');
            if (!input) return;

            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            button.setAttribute('aria-pressed', show ? 'true' : 'false');
            if (icon) {
                icon.classList.toggle('bi-eye', show);
                icon.classList.toggle('bi-eye-slash', !show);
                icon.classList.toggle('text-neon-cyan', show);
            }
        });
    });


    /* ==========================================================================
       3. HERO AREA & COUNTERS ANIMATION
       ========================================================================== */
    const announcementCounter = document.getElementById('headerCounterAnnouncements');
    const userCounter = document.getElementById('headerCounterUsers');
    const exploreBtn = document.getElementById('heroExploreBtn');

    // Il valore reale arriva dal server in data-count (ed è già scritto nell'HTML se il JS non parte)
    const animateCounter = (element, duration) => {
        if (!element) return;
        const targetValue = parseInt(element.dataset.count, 10);
        if (!Number.isFinite(targetValue) || targetValue <= 0) return;
        let startValue = 0;
        element.textContent = '0';
        const stepTime = Math.abs(Math.floor(duration / targetValue));

        const timer = setInterval(() => {
            startValue += Math.ceil(targetValue / 100); 
            if (startValue >= targetValue) {
                element.textContent = targetValue;
                clearInterval(timer);
            } else {
                element.textContent = startValue;
            }
        }, Math.max(stepTime, 15));
    };

    setTimeout(() => {
        animateCounter(announcementCounter, 1500);
        animateCounter(userCounter, 1200);
    }, 400);

    if (exploreBtn) {
        exploreBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const categoriesSection = document.getElementById('categoriesSection');
            if (categoriesSection) {
                categoriesSection.scrollIntoView({ behavior: 'smooth' });
            }
        });
    }


    /* ==========================================================================
       4. INTERACTIVE COMPONENTS (Cards & Radar Network)
       ========================================================================== */
    const categoryCards = document.querySelectorAll('.category-card-wrapper');
    const nodes = document.querySelectorAll('.network-node');
    const logText = document.getElementById('radarLogText');

    categoryCards.forEach(card => {
        card.addEventListener('mousedown', () => card.style.transform = 'translateY(-2px) scale(0.98)');
        card.addEventListener('mouseup', () => card.style.transform = 'translateY(-5px) scale(1)');
        card.addEventListener('mouseleave', () => card.style.transform = '');
    });

    if (nodes.length > 0 && logText) {
        nodes.forEach(node => {
            node.addEventListener('mouseenter', () => {
                const info = node.getAttribute('data-info');
                logText.style.opacity = '0';
                setTimeout(() => {
                    logText.textContent = `> ${info}`;
                    logText.style.color = node.querySelector('.dot-amber') ? 'var(--accent-amber)' : 'var(--accent-cyan)';
                    logText.style.opacity = '1';
                }, 100);
            });

            node.addEventListener('mouseleave', () => {
                logText.style.opacity = '0';
                setTimeout(() => {
                    logText.textContent = logText.dataset.hint;
                    logText.style.color = 'var(--accent-cyan)';
                    logText.style.opacity = '1';
                }, 100);
            });
        });
    }


    /* ==========================================================================
       5. INTERSECTION OBSERVERS (Performance-focused Scroll Animations)
       ========================================================================== */
    const steps = document.querySelectorAll('.class-step');
    const revisorCard = document.querySelector('.revisor-card');
    const btnBecomeRevisor = document.getElementById('btnBecomeRevisor');

    if ('IntersectionObserver' in window && steps.length > 0) {
        const stepObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    const index = Array.from(steps).indexOf(entry.target);
                    setTimeout(() => {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }, index * 120);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

        steps.forEach(step => {
            step.style.opacity = '0';
            step.style.transform = 'translateY(15px)';
            step.style.transition = 'opacity 0.6s cubic-bezier(0.16, 1, 0.3, 1), transform 0.6s cubic-bezier(0.16, 1, 0.3, 1)';
            stepObserver.observe(step);
        });
    }

    if ('IntersectionObserver' in window && revisorCard) {
        const revisorObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        revisorCard.style.opacity = '0';
        revisorCard.style.transform = 'translateY(25px)';
        revisorCard.style.transition = 'opacity 0.7s cubic-bezier(0.16, 1, 0.3, 1), transform 0.7s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.3s ease';
        revisorObserver.observe(revisorCard);
    }

    // Evita doppi invii della candidatura; il testo arriva tradotto da data-loading-text
    if (btnBecomeRevisor && btnBecomeRevisor.form) {
        btnBecomeRevisor.form.addEventListener('submit', () => {
            btnBecomeRevisor.disabled = true;
            btnBecomeRevisor.textContent = btnBecomeRevisor.dataset.loadingText || '…';
        });
    }
});
