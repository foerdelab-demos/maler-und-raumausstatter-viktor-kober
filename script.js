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

})();
