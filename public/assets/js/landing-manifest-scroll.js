/**
 * YZA Automotive - Section 2: McLaren Reference Manifesto
 * Interactive Scroll-Scrubbed Text Reveal Animation
 */
(() => {
    'use strict';

    document.addEventListener('DOMContentLoaded', () => {
        const section = document.querySelector('.mclaren-manifest-section');
        const quoteEl = section?.querySelector('[data-scroll-reveal]');
        if (!section || !quoteEl) return;

        // Respect user preference for reduced motion
        const prefersReducedMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;
        if (prefersReducedMotion) {
            return;
        }

        // Tokenize text into words while preserving content
        const rawText = quoteEl.textContent.trim();
        const words = rawText.split(/\s+/).filter(Boolean);
        if (!words.length) return;

        // Wrap each word in a span for individual scrub control
        quoteEl.innerHTML = words
            .map(word => `<span class="manifest-word">${word}</span>`)
            .join(' ');

        const wordElements = quoteEl.querySelectorAll('.manifest-word');
        const totalWords = wordElements.length;

        // Enable scroll reveal styling
        section.classList.add('has-scroll-reveal');

        let scheduled = false;

        const onScroll = () => {
            scheduled = false;
            const rect = quoteEl.getBoundingClientRect();
            const vh = window.innerHeight || document.documentElement.clientHeight;

            // Trigger window: begins when top of quote enters bottom 82% of screen
            // finishes when top of quote reaches top 28% of screen
            const startY = vh * 0.82;
            const endY = vh * 0.28;

            let progress = (startY - rect.top) / (startY - endY);
            if (progress < 0) progress = 0;
            if (progress > 1) progress = 1;

            // Scrub words progressively based on scroll progress
            for (let i = 0; i < totalWords; i++) {
                const threshold = i / totalWords;
                const shouldBeActive = progress > threshold;
                wordElements[i].classList.toggle('is-active', shouldBeActive);
            }
        };

        const requestTick = () => {
            if (!scheduled) {
                scheduled = true;
                requestAnimationFrame(onScroll);
            }
        };

        window.addEventListener('scroll', requestTick, { passive: true });
        window.addEventListener('resize', requestTick, { passive: true });
        window.addEventListener('pageshow', requestTick, { passive: true });

        // Run initial check
        onScroll();
    });
})();

