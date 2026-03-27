/**
 * Viktor Kober – Maler & Renovierungsarbeiten
 * script.js – Dezente Interaktionen
 *
 * Enthält:
 *  1. Mobiles Menü (Toggle)
 *  2. Header-Verhalten beim Scrollen
 *  3. Smooth Scrolling für Anker-Links
 *  4. Kontaktformular-Validierung
 */

(function () {
    'use strict';

    /* ── 1. Mobiles Menü ────────────────────────────────────────────────────── */
    const navToggle = document.getElementById('nav-toggle');
    const mainNav   = document.getElementById('main-nav');

    if (navToggle && mainNav) {

        // Toggle beim Klick auf den Burger-Button
        navToggle.addEventListener('click', function () {
            const isOpen = mainNav.classList.toggle('is-open');
            navToggle.setAttribute('aria-expanded', String(isOpen));
            navToggle.setAttribute('aria-label', isOpen ? 'Menü schließen' : 'Menü öffnen');
        });

        // Menü schließen, wenn ein Nav-Link geklickt wird
        mainNav.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                mainNav.classList.remove('is-open');
                navToggle.setAttribute('aria-expanded', 'false');
                navToggle.setAttribute('aria-label', 'Menü öffnen');
            });
        });

        // Menü schließen bei Klick außerhalb
        document.addEventListener('click', function (e) {
            if (
                mainNav.classList.contains('is-open') &&
                !mainNav.contains(e.target) &&
                !navToggle.contains(e.target)
            ) {
                mainNav.classList.remove('is-open');
                navToggle.setAttribute('aria-expanded', 'false');
                navToggle.setAttribute('aria-label', 'Menü öffnen');
            }
        });

        // Menü schließen bei Escape-Taste
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && mainNav.classList.contains('is-open')) {
                mainNav.classList.remove('is-open');
                navToggle.setAttribute('aria-expanded', 'false');
                navToggle.setAttribute('aria-label', 'Menü öffnen');
                navToggle.focus();
            }
        });
    }


    /* ── 2. Header-Verhalten beim Scrollen ──────────────────────────────────── */
    const siteHeader = document.getElementById('site-header');

    if (siteHeader) {
        let lastScrollY = window.scrollY;

        function handleHeaderScroll() {
            const currentScrollY = window.scrollY;

            // "scrolled"-Klasse ab 20px Scrolltiefe hinzufügen
            if (currentScrollY > 20) {
                siteHeader.classList.add('scrolled');
            } else {
                siteHeader.classList.remove('scrolled');
            }

            lastScrollY = currentScrollY;
        }

        // Passive Scroll-Listener für bessere Performance
        window.addEventListener('scroll', handleHeaderScroll, { passive: true });

        // Einmal initial ausführen
        handleHeaderScroll();
    }


    /* ── 3. Smooth Scrolling für Anker-Links ────────────────────────────────── */
    // Verarbeitet Links wie <a href="#kontakt">
    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
        anchor.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href');

            // Leere Anker überspringen
            if (targetId === '#') return;

            const targetEl = document.querySelector(targetId);
            if (!targetEl) return;

            e.preventDefault();

            // Header-Höhe berücksichtigen
            const headerHeight = siteHeader ? siteHeader.offsetHeight : 0;
            const targetTop    = targetEl.getBoundingClientRect().top + window.scrollY - headerHeight - 16;

            window.scrollTo({
                top:      targetTop,
                behavior: 'smooth',
            });

            // URL-Hash aktualisieren, ohne zu springen
            if (history.pushState) {
                history.pushState(null, '', targetId);
            }
        });
    });


    /* ── 4. Kontaktformular-Validierung ─────────────────────────────────────── */
    const contactForm = document.getElementById('contact-form');

    if (contactForm) {

        // Fehlermeldung für ein Feld setzen
        function setFieldError(fieldId, message) {
            const field     = document.getElementById(fieldId);
            const errorEl   = document.getElementById(fieldId + '-error');

            if (!field || !errorEl) return;

            if (message) {
                field.classList.add('is-invalid');
                errorEl.textContent = message;
                field.setAttribute('aria-describedby', fieldId + '-error');
                field.setAttribute('aria-invalid', 'true');
            } else {
                field.classList.remove('is-invalid');
                errorEl.textContent = '';
                field.removeAttribute('aria-describedby');
                field.removeAttribute('aria-invalid');
            }
        }

        // Feld beim Bearbeiten (input-Event) live validieren
        function addLiveValidation(fieldId, validator) {
            const field = document.getElementById(fieldId);
            if (!field) return;

            field.addEventListener('input', function () {
                const error = validator(this.value.trim());
                setFieldError(fieldId, error);
            });

            field.addEventListener('blur', function () {
                const error = validator(this.value.trim());
                setFieldError(fieldId, error);
            });
        }

        // Validierungsregeln
        addLiveValidation('name', function (val) {
            if (!val) return 'Bitte geben Sie Ihren Namen an.';
            if (val.length < 2) return 'Der Name muss mindestens 2 Zeichen lang sein.';
            return '';
        });

        addLiveValidation('email', function (val) {
            if (!val) return ''; // E-Mail ist optional, nur validieren wenn vorhanden
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
                return 'Bitte geben Sie eine gültige E-Mail-Adresse ein.';
            }
            return '';
        });

        addLiveValidation('nachricht', function (val) {
            if (!val) return 'Bitte schildern Sie kurz Ihr Anliegen.';
            if (val.length < 10) return 'Bitte beschreiben Sie Ihr Anliegen etwas ausführlicher.';
            return '';
        });

        // Gesamtformular beim Absenden validieren
        contactForm.addEventListener('submit', function (e) {
            let hasErrors = false;

            // Name
            const nameVal = document.getElementById('name') ? document.getElementById('name').value.trim() : '';
            if (!nameVal || nameVal.length < 2) {
                setFieldError('name', nameVal ? 'Der Name muss mindestens 2 Zeichen lang sein.' : 'Bitte geben Sie Ihren Namen an.');
                hasErrors = true;
            }

            // Telefon und E-Mail: mindestens eines muss ausgefüllt sein
            const telefonVal = document.getElementById('telefon') ? document.getElementById('telefon').value.trim() : '';
            const emailVal   = document.getElementById('email') ? document.getElementById('email').value.trim() : '';

            if (!telefonVal && !emailVal) {
                setFieldError('email', 'Bitte geben Sie eine Telefonnummer oder E-Mail-Adresse an.');
                hasErrors = true;
            } else if (emailVal && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal)) {
                setFieldError('email', 'Bitte geben Sie eine gültige E-Mail-Adresse ein.');
                hasErrors = true;
            } else {
                setFieldError('email', '');
            }

            // Nachricht
            const nachrichtVal = document.getElementById('nachricht') ? document.getElementById('nachricht').value.trim() : '';
            if (!nachrichtVal || nachrichtVal.length < 10) {
                setFieldError('nachricht', nachrichtVal ? 'Bitte beschreiben Sie Ihr Anliegen etwas ausführlicher.' : 'Bitte schildern Sie kurz Ihr Anliegen.');
                hasErrors = true;
            }

            // Formular stoppen, wenn Fehler vorhanden
            if (hasErrors) {
                e.preventDefault();

                // Zum ersten fehlerhaften Feld scrollen
                const firstInvalid = contactForm.querySelector('.is-invalid');
                if (firstInvalid) {
                    const headerHeight = siteHeader ? siteHeader.offsetHeight : 0;
                    const top = firstInvalid.getBoundingClientRect().top + window.scrollY - headerHeight - 24;
                    window.scrollTo({ top: top, behavior: 'smooth' });
                    firstInvalid.focus();
                }
            }
        });
    }

    /* ── 5. Phone icon vibrate every 3-5 seconds ──────────────────────────────── */
    const phoneBtn = document.querySelector('.hero-actions .btn-secondary');
    if (phoneBtn) {
        function triggerVibrate() {
            phoneBtn.classList.add('is-vibrating');
            setTimeout(function () {
                phoneBtn.classList.remove('is-vibrating');
            }, 400);
            // Next vibration in 3-5 seconds
            var delay = 3000 + Math.random() * 2000;
            setTimeout(triggerVibrate, delay);
        }
        // Start after hero animation finishes
        setTimeout(triggerVibrate, 2000);
    }


    /* ── 6. Scroll reveal: Section intros ───────────────────────────────────── */
    var sectionIntros = document.querySelectorAll('.section-intro');
    sectionIntros.forEach(function (el) {
        el.classList.add('reveal-up');
    });

    /* ── 7. Scroll reveal: Cards (alternate left/right on mobile) ───────────── */
    var allCards = document.querySelectorAll('.trust-card, .service-card, .testimonial-card');
    allCards.forEach(function (card, i) {
        card.classList.add('reveal-card');
        if (i % 2 === 0) {
            card.classList.add('from-left');
        } else {
            card.classList.add('from-right');
        }
    });

    /* ── Intersection Observer for reveals ──────────────────────────────────── */
    if ('IntersectionObserver' in window) {
        var revealObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15 });

        document.querySelectorAll('.reveal-up, .reveal-card').forEach(function (el) {
            revealObserver.observe(el);
        });
    } else {
        // Fallback: show everything
        document.querySelectorAll('.reveal-up, .reveal-card').forEach(function (el) {
            el.classList.add('is-visible');
        });
    }


    /* ── 8. Gallery Carousel ────────────────────────────────────────────────── */
    var track = document.getElementById('gallery-track');
    var prevBtn = document.getElementById('carousel-prev');
    var nextBtn = document.getElementById('carousel-next');
    var dotsContainer = document.getElementById('carousel-dots');

    if (track && prevBtn && nextBtn && dotsContainer) {
        var slides = track.querySelectorAll('.gallery-slide');
        var currentSlide = 0;
        var totalSlides = slides.length;

        // Create dots
        for (var d = 0; d < totalSlides; d++) {
            var dot = document.createElement('button');
            dot.className = 'carousel-dot' + (d === 0 ? ' is-active' : '');
            dot.setAttribute('aria-label', 'Bild ' + (d + 1));
            dot.dataset.index = d;
            dotsContainer.appendChild(dot);
        }

        var dots = dotsContainer.querySelectorAll('.carousel-dot');

        function goToSlide(index) {
            if (index < 0) index = totalSlides - 1;
            if (index >= totalSlides) index = 0;
            currentSlide = index;
            track.style.transform = 'translateX(-' + (currentSlide * 100) + '%)';
            dots.forEach(function (dot, i) {
                dot.classList.toggle('is-active', i === currentSlide);
            });
        }

        prevBtn.addEventListener('click', function () { goToSlide(currentSlide - 1); });
        nextBtn.addEventListener('click', function () { goToSlide(currentSlide + 1); });

        dotsContainer.addEventListener('click', function (e) {
            if (e.target.classList.contains('carousel-dot')) {
                goToSlide(parseInt(e.target.dataset.index, 10));
            }
        });

        // Swipe support for touch devices
        var startX = 0;
        var isDragging = false;
        track.addEventListener('touchstart', function (e) {
            startX = e.touches[0].clientX;
            isDragging = true;
        }, { passive: true });
        track.addEventListener('touchend', function (e) {
            if (!isDragging) return;
            isDragging = false;
            var diff = startX - e.changedTouches[0].clientX;
            if (Math.abs(diff) > 50) {
                goToSlide(currentSlide + (diff > 0 ? 1 : -1));
            }
        }, { passive: true });
    }


    /* ── 9. Benefits red bar fill on scroll ─────────────────────────────────── */
    var benefitsList = document.getElementById('benefits-list');
    if (benefitsList && 'IntersectionObserver' in window) {
        var benefitItems = benefitsList.querySelectorAll('li');

        var barObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('bar-filled');
                    barObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.3 });

        benefitItems.forEach(function (item, i) {
            // Stagger the observation with a slight delay per item
            item.style.transitionDelay = (i * 0.15) + 's';
            item.querySelector('.bar-fill').style.transitionDelay = (i * 0.15) + 's';
            barObserver.observe(item);
        });
    }


    /* ── 10. Star pop + golden glow on testimonial cards ─────────────────── */
    var starGroups = document.querySelectorAll('.testimonial-stars');
    if (starGroups.length && 'IntersectionObserver' in window) {
        var starObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    var el = entry.target;
                    el.classList.add('stars-animate');
                    // After last star pops (0.48s delay + 0.4s anim), start glow
                    setTimeout(function () {
                        el.classList.add('stars-glow');
                    }, 900);
                    starObserver.unobserve(el);
                }
            });
        }, { threshold: 0.5 });

        starGroups.forEach(function (group) {
            starObserver.observe(group);
        });
    }


    /* ── 11. Process steps staggered reveal ─────────────────────────────────── */
    var processSteps = document.querySelectorAll('.process-step');
    if (processSteps.length && 'IntersectionObserver' in window) {
        var stepObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    stepObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.2 });

        processSteps.forEach(function (step, i) {
            step.classList.add('reveal-step');
            step.style.transitionDelay = (i * 0.15) + 's';
            stepObserver.observe(step);
        });
    }


    /* ── 11. Contact phone vibrate ──────────────────────────────────────────── */
    var contactPhone = document.querySelector('.contact-phone-vibrate');
    if (contactPhone && 'IntersectionObserver' in window) {
        var phoneInterval = null;

        function startContactPhoneVibrate() {
            if (phoneInterval) return;
            function vibrateOnce() {
                contactPhone.classList.add('is-vibrating');
                setTimeout(function () {
                    contactPhone.classList.remove('is-vibrating');
                }, 400);
                phoneInterval = setTimeout(vibrateOnce, 3000 + Math.random() * 2000);
            }
            vibrateOnce();
        }

        function stopContactPhoneVibrate() {
            contactPhone.classList.remove('is-vibrating');
            if (phoneInterval) {
                clearTimeout(phoneInterval);
                phoneInterval = null;
            }
        }

        var contactPhoneObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    startContactPhoneVibrate();
                } else {
                    stopContactPhoneVibrate();
                }
            });
        }, { threshold: 0.3 });

        contactPhoneObserver.observe(contactPhone);
    }


    /* ── 12. Before/After Compare Slider ────────────────────────────────────── */
    var compareSlider = document.getElementById('compare-slider');
    if (compareSlider) {
        var beforeWrap = compareSlider.querySelector('.compare-before-wrap');
        var beforeImg = beforeWrap.querySelector('img');
        var handle = document.getElementById('compare-handle');
        var isDraggingCompare = false;

        function setSliderPosition(x) {
            var rect = compareSlider.getBoundingClientRect();
            var pos = (x - rect.left) / rect.width;
            pos = Math.max(0.02, Math.min(0.98, pos));
            var pct = pos * 100;
            beforeWrap.style.width = pct + '%';
            handle.style.left = pct + '%';
            // Keep before image full width of slider
            beforeImg.style.width = (rect.width) + 'px';
        }

        // Set initial image width
        function initCompareWidth() {
            var rect = compareSlider.getBoundingClientRect();
            beforeImg.style.width = rect.width + 'px';
        }
        initCompareWidth();
        window.addEventListener('resize', initCompareWidth);

        compareSlider.addEventListener('mousedown', function (e) {
            isDraggingCompare = true;
            setSliderPosition(e.clientX);
            e.preventDefault();
        });

        document.addEventListener('mousemove', function (e) {
            if (!isDraggingCompare) return;
            setSliderPosition(e.clientX);
        });

        document.addEventListener('mouseup', function () {
            isDraggingCompare = false;
        });

        compareSlider.addEventListener('touchstart', function (e) {
            isDraggingCompare = true;
            setSliderPosition(e.touches[0].clientX);
        }, { passive: true });

        document.addEventListener('touchmove', function (e) {
            if (!isDraggingCompare) return;
            setSliderPosition(e.touches[0].clientX);
        }, { passive: true });

        document.addEventListener('touchend', function () {
            isDraggingCompare = false;
        });
    }

})();
