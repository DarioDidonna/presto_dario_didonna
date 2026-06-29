/**
 * ==========================================================================
 * PRESTO.IT - CORE UI ENGINE
 * Gestione delle interazioni dinamiche, animazioni e comportamenti asincroni
 * ==========================================================================
 */

document.addEventListener('DOMContentLoaded', () => {

    /* ==========================================================================
       1. NAVBAR & GLOBAL NAVIGATION
       ========================================================================== */
    const navbar = document.getElementById('mainNavbar');
    const dropdowns = document.querySelectorAll('.navbar-collapse .dropdown');

    window.addEventListener('scroll', () => {
        if (window.scrollY > 20) {
            navbar.classList.add('shadow-lg');
            navbar.style.paddingTop = '0.5rem';
            navbar.style.paddingBottom = '0.5rem';
            navbar.style.backgroundColor = '#060a14'; // Sfondo scuro profondo in fase di scroll
        } else {
            navbar.classList.remove('shadow-lg');
            navbar.style.paddingTop = '0.85rem';
            navbar.style.paddingBottom = '0.85rem';
            navbar.style.backgroundColor = 'var(--bg-navbar)'; // Ripristina il token CSS globale
        }
    });

    if (window.innerWidth < 992) {
        dropdowns.forEach(dropdown => {
            const toggle = dropdown.querySelector('.dropdown-toggle');
            toggle.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropdown.classList.toggle('show');
                dropdown.querySelector('.dropdown-menu').classList.toggle('show');
            });
        });
    }

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
    const toggleBtn = document.getElementById('togglePasswordBtn');
    const passwordField = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    const loginForm = document.getElementById('prestoLoginForm');
    const submitBtn = document.getElementById('prestoSubmitBtn');

    if (toggleBtn && passwordField && toggleIcon) {
        toggleBtn.addEventListener('click', () => {
            const isPassword = passwordField.getAttribute('type') === 'password';
            passwordField.setAttribute('type', isPassword ? 'text' : 'password');

            if (isPassword) {
                toggleIcon.classList.replace('bi-eye', 'bi-eye-slash');
                toggleIcon.classList.add('text-neon-cyan');
            } else {
                toggleIcon.classList.replace('bi-eye-slash', 'bi-eye');
                toggleIcon.classList.remove('text-neon-cyan');
            }
        });
    }

    if (loginForm && submitBtn) {
        loginForm.addEventListener('submit', () => {
            if (loginForm.checkValidity()) {
                const textContainer = submitBtn.querySelector('.btn-text');
                submitBtn.disabled = true;
                if (textContainer) {
                    textContainer.innerHTML = '<i class="bi bi-hourglass-split me-2 animate-pulse"></i>Verifica in corso...';
                }
            }
        });
    }


    /* ==========================================================================
       3. HERO AREA & COUNTERS ANIMATION
       ========================================================================== */
    const announcementCounter = document.getElementById('headerCounterAnnouncements');
    const userCounter = document.getElementById('headerCounterUsers');
    const exploreBtn = document.getElementById('heroExploreBtn');

    const animateCounter = (element, targetValue, duration) => {
        if (!element) return;
        let startValue = 0;
        const stepTime = Math.abs(Math.floor(duration / targetValue));

        const timer = setInterval(() => {
            startValue += Math.ceil(targetValue / 100); // Velocizza l'incremento per numeri alti
            if (startValue >= targetValue) {
                element.textContent = targetValue;
                clearInterval(timer);
            } else {
                element.textContent = startValue;
            }
        }, Math.max(stepTime, 15));
    };

    setTimeout(() => {
        animateCounter(announcementCounter, 1420, 1500);
        animateCounter(userCounter, 84, 1200);
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
                    logText.innerHTML = `> EXTRACTED: "${info}"`;
                    logText.style.color = node.querySelector('.dot-amber') ? 'var(--accent-amber)' : 'var(--accent-cyan)';
                    logText.style.opacity = '1';
                }, 100);
            });

            node.addEventListener('mouseleave', () => {
                logText.style.opacity = '0';
                setTimeout(() => {
                    logText.innerHTML = 'Passa il mouse sopra i nodi del network...';
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

    if (btnBecomeRevisor) {
        btnBecomeRevisor.addEventListener('click', () => {
            const originalHtml = btnBecomeRevisor.innerHTML;
            btnBecomeRevisor.innerHTML = '<i class="bi bi-hourglass-split me-2 animate-pulse"></i> Elaborazione...';
            setTimeout(() => {
                btnBecomeRevisor.innerHTML = originalHtml;
            }, 1000);
        });
    }


    /* ==========================================================================
       6. WIDGETS & MARKETING INTERACTIONS
       ========================================================================== */
    const newsletterForm = document.getElementById('newsletterForm');

    if (newsletterForm) {
        newsletterForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const emailInput = newsletterForm.querySelector('input[type="email"]');
            const submitBtn = newsletterForm.querySelector('button');

            if (emailInput.value.trim() !== "") {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split text-dark animate-pulse"></i>';

                setTimeout(() => {
                    emailInput.value = "";
                    emailInput.placeholder = "Grazie per esserti iscritto!";
                    emailInput.disabled = true;
                    submitBtn.innerHTML = '<i class="bi bi-check-lg text-dark"></i>';
                    submitBtn.style.backgroundColor = '#10b981'; // Cambio colore in verde successo
                }, 1200);
            }
        });
    }
});


/* ==========================================================================
   7. GLOBAL UTILITY FUNCTIONS (Disponibili fuori dal DOMContentLoaded)
   ========================================================================== */

/**
 * @param {string} inputId 
 * @param {HTMLElement} button 
 */
function togglePasswordVisibility(inputId, button) {
    const input = document.getElementById(inputId);
    const icon = button.querySelector('i');

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    } else {
        input.type = 'password';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    }
}

