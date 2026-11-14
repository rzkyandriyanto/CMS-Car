/**
 * YZA Automotive - Hero Luxury Showcase Slider
 * Reference: Hyundai USA Hero Slider (Segmented progress bars, smooth autoplay, pause/play, swipe)
 */
(() => {
    'use strict';

    document.addEventListener('DOMContentLoaded', () => {
        const slider = document.querySelector('[data-hero-slider]');
        if (!slider) return;

        const slides = [...slider.querySelectorAll('.hero-slide')];
        const textPanes = [...slider.querySelectorAll('.hero-slide-text-pane')];
        const infoCards = [...slider.querySelectorAll('.hero-vehicle-slider-info')];
        const segmentItems = [...slider.querySelectorAll('.hero-segment-item')];
        const segmentBars = [...slider.querySelectorAll('.hero-segment-bar')];
        const fillElements = segmentBars.map(bar => bar.querySelector('.hero-segment-fill'));

        const titleEl = slider.querySelector('[data-slider-name]');
        const counterEl = slider.querySelector('[data-counter-active]');
        const pauseBtn = slider.querySelector('[data-slider-pause]');
        const prevBtn = slider.querySelector('[data-slider-prev]');
        const nextBtn = slider.querySelector('[data-slider-next]');

        const iconPause = pauseBtn?.querySelector('.icon-pause');
        const iconPlay = pauseBtn?.querySelector('.icon-play');

        const totalSlides = slides.length;
        if (totalSlides < 2) return;

        const DURATION = 6000; // 6 seconds per slide
        let currentIndex = 0;
        let isPlaying = true;
        let startTime = performance.now();
        let elapsed = 0;
        let rafId = null;

        const carNames = slides.map(slide => slide.dataset.carName || 'YZA Premium Collection');

        const updateUI = (index) => {
            // Update slides
            slides.forEach((slide, i) => {
                const active = i === index;
                slide.classList.toggle('is-active', active);
                slide.setAttribute('aria-hidden', String(!active));
            });

            // Update text panes
            textPanes.forEach((pane, i) => {
                pane.classList.toggle('is-active', i === index);
            });

            // Update info cards
            infoCards.forEach((card, i) => {
                card.classList.toggle('is-active', i === index);
            });

            // Update segmented items (labels and container state)
            segmentItems.forEach((item, i) => {
                const active = i === index;
                const passed = i < index;
                item.classList.toggle('is-active', active);
                item.classList.toggle('is-passed', passed);
                item.setAttribute('aria-selected', String(active));
            });

            // Update segmented bars
            segmentBars.forEach((bar, i) => {
                const active = i === index;
                const passed = i < index;
                bar.classList.toggle('is-active', active);
                bar.classList.toggle('is-passed', passed);
                bar.setAttribute('aria-selected', String(active));
                
                const fill = fillElements[i];
                if (fill) {
                    if (passed) {
                        fill.style.width = '100%';
                    } else if (!active) {
                        fill.style.width = '0%';
                    }
                }
            });

            // Update slide title
            if (titleEl) {
                titleEl.style.opacity = '0';
                setTimeout(() => {
                    titleEl.textContent = carNames[index] || '';
                    titleEl.style.opacity = '1';
                }, 150);
            }

            // Update counter
            if (counterEl) {
                counterEl.textContent = String(index + 1).padStart(2, '0');
            }
        };

        const tick = (now) => {
            if (!isPlaying) {
                startTime = now - elapsed;
                rafId = requestAnimationFrame(tick);
                return;
            }

            elapsed = now - startTime;

            if (elapsed >= DURATION) {
                nextSlide();
            } else {
                // Update active progress bar fill
                const progress = Math.min(1, Math.max(0, elapsed / DURATION));
                const activeFill = fillElements[currentIndex];
                if (activeFill) {
                    activeFill.style.width = `${(progress * 100).toFixed(2)}%`;
                }
            }

            // Always continue loop to keep animation running
            rafId = requestAnimationFrame(tick);
        };

        const goToSlide = (newIndex) => {
            currentIndex = (newIndex + totalSlides) % totalSlides;
            elapsed = 0;
            startTime = performance.now();

            // Reset all fill elements properly based on the new index:
            // Passed bars (< currentIndex) stay 100%
            // Current bar (=== currentIndex) starts at 0%
            // Future bars (> currentIndex) stay 0%
            fillElements.forEach((fill, i) => {
                if (fill) {
                    if (i < currentIndex) {
                        fill.style.width = '100%';
                    } else {
                        fill.style.width = '0%';
                    }
                }
            });

            updateUI(currentIndex);
        };

        const nextSlide = () => goToSlide(currentIndex + 1);
        const prevSlide = () => goToSlide(currentIndex - 1);

        const setPlaying = (play) => {
            isPlaying = play;
            if (iconPause && iconPlay) {
                iconPause.style.display = isPlaying ? 'inline-block' : 'none';
                iconPlay.style.display = isPlaying ? 'none' : 'inline-block';
            }
            if (pauseBtn) {
                pauseBtn.setAttribute('aria-label', isPlaying ? 'Jeda tayangan otomatis' : 'Lanjutkan tayangan otomatis');
                pauseBtn.setAttribute('title', isPlaying ? 'Jeda tayangan' : 'Lanjutkan tayangan');
            }
            if (isPlaying) {
                startTime = performance.now() - elapsed;
            }
        };

        // Controls events
        nextBtn?.addEventListener('click', (e) => {
            e.preventDefault();
            nextSlide();
        });

        prevBtn?.addEventListener('click', (e) => {
            e.preventDefault();
            prevSlide();
        });

        pauseBtn?.addEventListener('click', (e) => {
            e.preventDefault();
            setPlaying(!isPlaying);
        });

        // Click on segmented items / bars
        const clickableSegments = segmentItems.length ? segmentItems : segmentBars;
        clickableSegments.forEach((item, i) => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                goToSlide(i);
            });
        });

        // Keyboard navigation
        slider.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowRight') {
                e.preventDefault();
                nextSlide();
            } else if (e.key === 'ArrowLeft') {
                e.preventDefault();
                prevSlide();
            }
        });

        // Touch swipe support (Mobile)
        let touchStartX = 0;
        let touchStartY = 0;

        slider.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
            touchStartY = e.changedTouches[0].screenY;
        }, { passive: true });

        slider.addEventListener('touchend', (e) => {
            const touchEndX = e.changedTouches[0].screenX;
            const touchEndY = e.changedTouches[0].screenY;
            const diffX = touchEndX - touchStartX;
            const diffY = touchEndY - touchStartY;

            // Only trigger if horizontal swipe is dominant and > 40px
            if (Math.abs(diffX) > 40 && Math.abs(diffX) > Math.abs(diffY)) {
                if (diffX < 0) {
                    nextSlide();
                } else {
                    prevSlide();
                }
            }
        }, { passive: true });

        // Initialize state
        goToSlide(0);
        rafId = requestAnimationFrame(tick);

        // ==========================================================
        // TRANSPARENT NAVBAR -> SOLID BLACK ON SCROLL
        // (Active when passing section hero / reaching section mobil)
        // ==========================================================
        const navbar = document.querySelector('[data-public-navbar]');
        if (navbar) {
            const mobilSection = document.getElementById('mobil');
            const toggle = navbar.querySelector('.show-mobile-toggle');
            const links = navbar.querySelector('.show-nav-links');

            const updateNavbarScroll = () => {
                const scrollY = window.scrollY || window.pageYOffset || document.documentElement.scrollTop;
                
                // Transparan hanya ketika mentok di atas (scrollY <= 20).
                // Begitu mulai gerak scroll ke bawah (> 20px), langsung aktifkan background hitam.
                const isScrolled = scrollY > 20;
                navbar.classList.toggle('is-scrolled', isScrolled);
            };

            window.addEventListener('scroll', updateNavbarScroll, { passive: true });
            window.addEventListener('resize', updateNavbarScroll, { passive: true });
            updateNavbarScroll();

            // Mobile menu drawer toggle
            if (toggle && links) {
                toggle.addEventListener('click', () => {
                    setTimeout(() => {
                        navbar.classList.toggle('menu-open', links.classList.contains('open'));
                    }, 20);
                });
            }
        }
    });
})();

