
document.addEventListener('DOMContentLoaded', () => {
    const navbar = document.getElementById('mainNavbar');

    window.addEventListener('scroll', () => {
        if (window.scrollY > 20) {
            navbar.classList.add('shadow-lg');
            navbar.style.paddingTop = '0.5rem';
            navbar.style.paddingBottom = '0.5rem';
            navbar.style.backgroundColor = '#060a14'; 
        } else {
            navbar.classList.remove('shadow-lg');
            navbar.style.paddingTop = '0.85rem';
            navbar.style.paddingBottom = '0.85rem';
            navbar.style.backgroundColor = 'var(--bg-navbar)';
        }
    });

    const dropdowns = document.querySelectorAll('.navbar-collapse .dropdown');

    if (window.innerWidth < 992) {
        dropdowns.forEach(dropdown => {
            const toggle = dropdown.querySelector('.dropdown-toggle');
            toggle.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropdown.classList.toggle('show');
                const menu = dropdown.querySelector('.dropdown-menu');
                menu.classList.toggle('show');
            });
        });
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === '/' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
            e.preventDefault();
            const searchInput = document.querySelector('.search-input-custom');
            if (searchInput) {
                searchInput.focus();
            }
        }
    });
});





document.addEventListener('DOMContentLoaded', () => {
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
                    submitBtn.style.backgroundColor = '#10b981'; // Diventa verde al successo
                }, 1200);
            }
        });
    }
});



document.addEventListener('DOMContentLoaded', () => {
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
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
                toggleIcon.classList.add('text-neon-cyan');
            } else {
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.remove('text-neon-cyan');
                toggleIcon.classList.add('bi-eye');
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
});




document.addEventListener('DOMContentLoaded', () => {
    // Riferimenti ai nodi del contatore della Header
    const announcementCounter = document.getElementById('headerCounterAnnouncements');
    const userCounter = document.getElementById('headerCounterUsers');
    const exploreBtn = document.getElementById('heroExploreBtn');

    const animateCounter = (element, targetValue, duration) => {
        if (!element) return;

        let startValue = 0;
        const stepTime = Math.abs(Math.floor(duration / targetValue));

        const timer = setInterval(() => {
            startValue += 1;
            element.textContent = startValue;

            if (startValue >= targetValue) {
                element.textContent = targetValue;
                clearInterval(timer);
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
});





document.addEventListener('DOMContentLoaded', () => {
    const categoryCards = document.querySelectorAll('.category-card-wrapper');

    if (categoryCards.length > 0) {
        categoryCards.forEach(card => {
            card.addEventListener('mousedown', () => {
                card.style.transform = 'translateY(-2px) scale(0.98)';
            });

            card.addEventListener('mouseup', () => {
                card.style.transform = 'translateY(-5px) scale(1)';
            });

            card.addEventListener('mouseleave', () => {
                card.style.transform = '';
            });
        });
    }
});



document.addEventListener('DOMContentLoaded', () => {
    const nodes = document.querySelectorAll('.network-node');
    const logText = document.getElementById('radarLogText');

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
});



function togglePasswordVisibility(inputId, button) {
    const input = document.getElementById(inputId);
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    }
}


document.addEventListener('DOMContentLoaded', () => {
    const steps = document.querySelectorAll('.class-step');

    if ('IntersectionObserver' in window && steps.length > 0) {
        const stepObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    const allStepsArray = Array.from(steps);
                    const index = allStepsArray.indexOf(entry.target);

                    setTimeout(() => {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }, index * 120); 

                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -40px 0px'
        });

        steps.forEach(step => {
            step.style.opacity = '0';
            step.style.transform = 'translateY(15px)';
            step.style.transition = 'opacity 0.6s cubic-bezier(0.16, 1, 0.3, 1), transform 0.6s cubic-bezier(0.16, 1, 0.3, 1)';
            stepObserver.observe(step);
        });
    }
});




document.addEventListener('DOMContentLoaded', () => {
    const revisorCard = document.querySelector('.revisor-card');
    const btnBecomeRevisor = document.getElementById('btnBecomeRevisor');

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
});