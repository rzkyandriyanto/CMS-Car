/**
 * YZA Automotive · Unified Trade-In & Vehicle Catalog Horizontal Slider
 * Smooth slide transition between Slide 0 (Jual Mobil / Trade-In) and Slide 1 (Kurasi Mobil Pilihan)
 */
(() => {
    const container = document.querySelector('[data-unified-slider]');
    if (!container) return;

    const viewport = container.querySelector('.unified-slider-viewport');
    const track = container.querySelector('[data-unified-track]');
    const slides = Array.from(container.querySelectorAll('.unified-slide'));
    const tabButtons = Array.from(container.querySelectorAll('[data-unified-goto]'));
    const nextButtons = Array.from(container.querySelectorAll('[data-unified-next]'));
    const prevButtons = Array.from(container.querySelectorAll('[data-unified-prev]'));

    if (!viewport || !track || slides.length < 2) return;

    let currentIndex = 0;
    let isAnimating = false;

    // Update viewport height to fit active slide naturally
    const updateViewportHeight = () => {
        const activeSlide = slides[currentIndex];
        if (activeSlide) {
            const h = activeSlide.offsetHeight;
            if (h > 0) {
                viewport.style.height = `${h}px`;
            }
        }
    };

    const goToSlide = (index, immediate = false) => {
        if (index < 0 || index >= slides.length) return;
        currentIndex = index;
        container.setAttribute('data-active-slide', String(currentIndex));

        // Update track position (each slide is 50% of the 200% track)
        if (immediate) {
            track.style.transition = 'none';
            track.style.transform = `translate3d(-${currentIndex * 50}%, 0, 0)`;
            // Force reflow
            void track.offsetWidth;
            track.style.transition = '';
        } else {
            track.style.transform = `translate3d(-${currentIndex * 50}%, 0, 0)`;
        }

        // Update nav tabs
        tabButtons.forEach((btn) => {
            const target = parseInt(btn.getAttribute('data-unified-goto') || '0', 10);
            btn.classList.toggle('is-active', target === currentIndex);
            btn.setAttribute('aria-selected', target === currentIndex ? 'true' : 'false');
        });

        // Update accessibility
        slides.forEach((slide, idx) => {
            slide.setAttribute('aria-hidden', idx === currentIndex ? 'false' : 'true');
        });

        updateViewportHeight();

        // Refresh nested carousel layout if moving to catalog slide
        if (currentIndex === 1) {
            setTimeout(() => {
                window.dispatchEvent(new Event('resize'));
                const nestedViewport = container.querySelector('.featured-carousel-viewport');
                if (nestedViewport) {
                    nestedViewport.dispatchEvent(new Event('scroll'));
                }
            }, 300);
        }
    };

    // Event listeners for triggers
    nextButtons.forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            goToSlide(1);
        });
    });

    prevButtons.forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            goToSlide(0);
        });
    });

    tabButtons.forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const target = parseInt(btn.getAttribute('data-unified-goto') || '0', 10);
            goToSlide(target);
        });
    });

    // Touch Swipe gesture support on Slide 0
    let touchStartX = 0;
    let touchStartY = 0;
    let touchMoved = false;

    container.addEventListener('touchstart', (e) => {
        if (!e.touches || e.touches.length !== 1) return;
        touchStartX = e.touches[0].clientX;
        touchStartY = e.touches[0].clientY;
        touchMoved = true;
    }, { passive: true });

    container.addEventListener('touchend', (e) => {
        if (!touchMoved || !e.changedTouches || e.changedTouches.length !== 1) return;
        touchMoved = false;
        const diffX = e.changedTouches[0].clientX - touchStartX;
        const diffY = e.changedTouches[0].clientY - touchStartY;

        // If predominantly horizontal swipe with at least 50px delta
        if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 50) {
            if (diffX < 0 && currentIndex === 0) {
                // Swiped left on Trade -> go to catalog
                goToSlide(1);
            } else if (diffX > 0 && currentIndex === 1 && touchStartX < 60) {
                // Swiped right from left edge on Catalog -> go back to trade
                goToSlide(0);
            }
        }
    }, { passive: true });

    // Handle Navbar Links: #mobil and #jual-mobil
    document.querySelectorAll('a[href="#mobil"]').forEach((link) => {
        link.addEventListener('click', () => {
            goToSlide(1);
        });
    });

    document.querySelectorAll('a[href="#jual-mobil"]').forEach((link) => {
        link.addEventListener('click', () => {
            goToSlide(0);
        });
    });

    // Handle initial URL hash
    const handleInitialHash = () => {
        if (window.location.hash === '#mobil') {
            goToSlide(1, true);
        } else if (window.location.hash === '#jual-mobil') {
            goToSlide(0, true);
        } else {
            goToSlide(0, true);
        }
    };

    window.addEventListener('hashchange', () => {
        if (window.location.hash === '#mobil') goToSlide(1);
        else if (window.location.hash === '#jual-mobil') goToSlide(0);
    });

    window.addEventListener('resize', updateViewportHeight);
    window.addEventListener('load', updateViewportHeight);

    // Initialize
    handleInitialHash();
    updateViewportHeight();

    // Toggle 3D flip card on click/tap
    const memberCardFlipper = document.querySelector('.yza-card-flipper-wrap');
    if (memberCardFlipper) {
        memberCardFlipper.addEventListener('click', () => {
            memberCardFlipper.classList.toggle('is-flipped');
        });
    }
})();

