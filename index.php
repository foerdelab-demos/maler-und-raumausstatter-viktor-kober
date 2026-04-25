<?php
/**
 * Viktor Kober – Raumausstatter & Renovierungsarbeiten
 * Landingpage – index.php
 */

// ── Konfiguration (Cloudflare Turnstile Keys) ─────────────────────────────────
require_once __DIR__ . '/includes/config.php';

// ── Formularverarbeitung ──────────────────────────────────────────────────────
$formSuccess = false;
$formErrors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kontakt_form'])) {

    // ── Cloudflare Turnstile Verifikation ─────────────────────────────────────
    $turnstileSecret   = TURNSTILE_SECRET_KEY;
    $turnstileResponse = $_POST['cf-turnstile-response'] ?? '';

    $cfCheck = false;
    if (!empty($turnstileResponse)) {
        $cfPayload = http_build_query([
            'secret'   => $turnstileSecret,
            'response' => $turnstileResponse,
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
        ]);

        $cfContext = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => 'Content-Type: application/x-www-form-urlencoded',
                'content' => $cfPayload,
                'timeout' => 10,
            ],
        ]);

        $cfResult = @file_get_contents(
            'https://challenges.cloudflare.com/turnstile/v0/siteverify',
            false,
            $cfContext
        );

        if ($cfResult !== false) {
            $cfData  = json_decode($cfResult, true);
            $cfCheck = $cfData['success'] ?? false;
        }
    }

    if (!$cfCheck) {
        $formErrors[] = 'Bitte bestätigen Sie, dass Sie kein Bot sind.';
    }

    // ── Eingabevalidierung ────────────────────────────────────────────────────
    $name      = trim(strip_tags($_POST['name']      ?? ''));
    $telefon   = trim(strip_tags($_POST['telefon']   ?? ''));
    $email     = trim(strip_tags($_POST['email']     ?? ''));
    $nachricht = trim(strip_tags($_POST['nachricht'] ?? ''));

    if (empty($name)) {
        $formErrors[] = 'Bitte geben Sie Ihren Namen an.';
    }

    if (empty($telefon) && empty($email)) {
        $formErrors[] = 'Bitte geben Sie eine Telefonnummer oder E-Mail-Adresse an.';
    }

    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $formErrors[] = 'Die eingegebene E-Mail-Adresse ist ungültig.';
    }

    if (empty($nachricht)) {
        $formErrors[] = 'Bitte schildern Sie kurz Ihr Anliegen.';
    }

    // ── E-Mail versenden ──────────────────────────────────────────────────────
    if (empty($formErrors)) {
        $to      = 'info@maler-buedelsdorf.de';
        $subject = '=?UTF-8?B?' . base64_encode('Neue Anfrage über die Website: ' . $name) . '?=';
        $body    = "Neue Kontaktanfrage über die Website\n";
        $body   .= "=====================================\n\n";
        $body   .= "Name:        {$name}\n";
        $body   .= "Telefon:     {$telefon}\n";
        $body   .= "E-Mail:      {$email}\n\n";
        $body   .= "Nachricht:\n{$nachricht}\n\n";
        $body   .= "-------------------------------------\n";
        $body   .= 'Gesendet am: ' . date('d.m.Y \u\m H:i \U\h\r') . "\n";

        $headers  = "From: noreply@viktorkober-maler.de\r\n";
        $headers .= "Reply-To: {$email}\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $headers .= "X-Mailer: PHP/" . PHP_VERSION;

        if (mail($to, $subject, $body, $headers)) {
            $formSuccess = true;
        } else {
            $formErrors[] = 'Die Nachricht konnte leider nicht gesendet werden. Bitte rufen Sie uns direkt an.';
        }
    }
}

// Hilfsfunktion: Formularwert sicher ausgeben
function h(string $key): string
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST[$key])) {
        return htmlspecialchars(strip_tags($_POST[$key]), ENT_QUOTES, 'UTF-8');
    }
    return '';
}
?>
<?php include __DIR__ . '/includes/header.php'; ?>


<!-- ═══════════════════════════════════════════════════════════════════════════
     HERO SECTION
═══════════════════════════════════════════════════════════════════════════ -->
<section class="hero" id="hero">

    <!-- Animierter Hintergrund: Initials-Logo wandert geschwungen vertikal in der linken Hero-Hälfte -->
    <div class="hero-image" aria-hidden="true">
        <div class="hero-image-photo"></div>
        <svg class="hero-logo-trail" viewBox="0 0 400 1000" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <defs>
                <filter id="paintRough" x="-10%" y="-10%" width="120%" height="120%">
                    <feTurbulence type="fractalNoise" baseFrequency="0.9" numOctaves="2" seed="3"/>
                    <feDisplacementMap in="SourceGraphic" scale="4"/>
                </filter>
            </defs>
            <path class="logo-trail-path" pathLength="1" d="M 100 -50 C 200 60, 230 160, 200 250 S 30 400, 60 500 S 230 600, 200 750 S 50 950, 100 1080" filter="url(#paintRough)"/>
        </svg>
        <div class="hero-image-overlay"></div>
        <svg class="hero-logo-rider-svg" viewBox="0 0 400 1000" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <defs>
                <path id="logoRidePath" d="M 100 -50 C 200 60, 230 160, 200 250 S 30 400, 60 500 S 230 600, 200 750 S 50 950, 100 1080"/>
            </defs>
            <image class="hero-logo-rider-img" href="img/initials-logo.svg" x="-70" y="-70" width="140" height="140" preserveAspectRatio="xMidYMid meet">
                <animateMotion dur="5s" repeatCount="indefinite">
                    <mpath href="#logoRidePath"/>
                </animateMotion>
            </image>
        </svg>
    </div>

    <div class="container hero-content">
        <div class="hero-text">
            <p class="hero-pre">Maler & Raumausstatter aus Büdelsdorf</p>
            <h1 class="hero-headline">Raumgestaltung,<br>die Ihr Zuhause<br>sichtbar aufwertet.</h1>
            <p class="hero-sub">Saubere Arbeit, zuverlässige Termine und ein Ergebnis,<br class="br-desktop"> das Sie täglich erfreut – persönlich betreut von mir, Viktor Kober.</p>

            <div class="hero-actions">
                <a href="#kontakt" class="btn btn-primary">Kostenlos anfragen</a>
                <a href="tel:+491743226804" class="btn btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 2.18h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.09 6.09l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                    Jetzt anrufen
                </a>
            </div>

            <ul class="hero-trust">
                <li>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    Saubere Ausführung
                </li>
                <li>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    Persönliche Betreuung
                </li>
                <li>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    Termintreu
                </li>
                <li>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    Aus der Region
                </li>
            </ul>
        </div>
    </div>

    <div class="hero-portrait" aria-hidden="true">
        <picture>
            <source srcset="img/team/viktor.webp" type="image/webp">
            <img src="img/team/viktor.png" alt="Viktor Kober vor seinem Firmenauto" loading="eager" decoding="async">
        </picture>
    </div>

    <div class="hero-portrait-mobile" aria-hidden="true">
        <picture>
            <source srcset="img/team/viktor-only.webp" type="image/webp">
            <img src="img/team/viktor-only.png" alt="Viktor Kober" loading="eager" decoding="async">
        </picture>
    </div>

</section>


<!-- ═══════════════════════════════════════════════════════════════════════════
     VERTRAUEN
═══════════════════════════════════════════════════════════════════════════ -->
<section class="trust section-light" id="vertrauen">
    <div class="container">

        <div class="section-intro text-center">
            <h2>Warum meine Kunden zufrieden sind</h2>
            <p>Kein Großbetrieb. Kein anonymer Handwerker. Ich stehe selbst auf der Baustelle – und stehe persönlich für das Ergebnis gerade.</p>
        </div>

        <div class="trust-grid">

            <div class="trust-card">
                <div class="trust-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                </div>
                <h3>Direkter Ansprechpartner</h3>
                <p>Sie sprechen immer direkt mit mir. Keine Weiterleitungen, kein Callcenter – von der ersten Anfrage bis zur Übergabe.</p>
            </div>

            <div class="trust-card">
                <div class="trust-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                </div>
                <h3>Saubere Ausführung</h3>
                <p>Ordentliche Vorbereitung, sorgfältige Abdeckung, präzises Abkleben – und am Ende eine Übergabe, bei der keine Spuren zu sehen sind.</p>
            </div>

            <div class="trust-card">
                <div class="trust-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                </div>
                <h3>Zuverlässige Termine</h3>
                <p>Was ich vereinbare, halte ich ein. Kein ewiges Warten, keine kurzfristigen Absagen – weil Ihr Alltag und Ihr Vertrauen mir wichtig sind.</p>
            </div>

            <div class="trust-card">
                <div class="trust-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                </div>
                <h3>Faires, klares Angebot</h3>
                <p>Kein versteckter Kleindruck. Mein Angebot zeigt genau, was geplant ist – damit Sie wissen, was Sie erwartet, bevor ich den ersten Pinsel ansetze.</p>
            </div>

            <div class="trust-card">
                <div class="trust-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                </div>
                <h3>Aus der Region</h3>
                <p>Büdelsdorf, Rendsburg, Eckernförde und Umgebung – kurze Wege, schnelle Reaktion. Ich bin in der Nachbarschaft bekannt und immer in der Nähe.</p>
            </div>

            <div class="trust-card">
                <div class="trust-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3H14z"></path><path d="M7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"></path></svg>
                </div>
                <h3>Handwerksqualität</h3>
                <p>Jahrelange Erfahrung, hochwertige Materialien und mein eigener Anspruch: Ich liefere keine Arbeit ab, die ich nicht selbst gern in meinem Zuhause hätte.</p>
            </div>

        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════════════════
     LEISTUNGEN
═══════════════════════════════════════════════════════════════════════════ -->
<section class="services section-white" id="leistungen">
    <div class="services-bg" aria-hidden="true">
        <picture>
            <source srcset="img/team/viktor.webp" type="image/webp">
            <img src="img/team/viktor.png" alt="" loading="lazy" decoding="async">
        </picture>
    </div>
    <div class="container">

        <div class="section-intro text-center">
            <h2>Was ich für Sie übernehme</h2>
            <p>Von der Renovierung einzelner Zimmer bis zur vollständigen Sanierung – handwerklich sauber, termingerecht und mit meinem Blick für Details.</p>
        </div>

        <div class="services-grid">

            <article class="service-card">
                <!-- Bild: Frisch gestrichenes Wohnzimmer, helle Wandfarbe, saubere Kanten -->
                <div class="service-image service-image--innen" aria-hidden="true"></div>
                <div class="service-body">
                    <h3>Raumgestaltung innen</h3>
                    <p>Ob einzelnes Zimmer oder ganze Wohnung – Wände und Decken werden vollflächig und gleichmäßig gestrichen. Mit sauber abgeklebten Kanten, schützenden Abdeckungen und einem Ergebnis, das sofort überzeugt.</p>
                    <a href="#kontakt" class="service-link">Anfrage stellen →</a>
                </div>
            </article>

            <article class="service-card">
                <!-- Bild: Elegante Tapezierarbeit, Strukturtapete, Musterverlauf sauber ausgerichtet -->
                <div class="service-image service-image--tapeten" aria-hidden="true"></div>
                <div class="service-body">
                    <h3>Tapezierarbeiten</h3>
                    <p>Strukturtapeten, Vliestapeten, Fototapeten – fachgerecht verarbeitet ohne Blasen, ohne versetzte Muster. Vorbereitung des Untergrunds inbegriffen, damit alles hält, wie es soll.</p>
                    <a href="#kontakt" class="service-link">Anfrage stellen →</a>
                </div>
            </article>

            <article class="service-card">
                <!-- Bild: Renovierter Flur, vorher/nachher Eindruck, frische helle Atmosphäre -->
                <div class="service-image service-image--renovierung" aria-hidden="true"></div>
                <div class="service-body">
                    <h3>Renovierungsarbeiten</h3>
                    <p>Risse schließen, Untergründe vorbereiten, ausgebesserte Stellen glätten und alles für einen einwandfreien Neuanstrich herrichten. Sorgfältige Vorarbeit ist die halbe Miete.</p>
                    <a href="#kontakt" class="service-link">Anfrage stellen →</a>
                </div>
            </article>

            <article class="service-card">
                <!-- Bild: Haus mit frisch gestrichener Fassade, saubere Kanten an Fenstern -->
                <div class="service-image service-image--fassade" aria-hidden="true"></div>
                <div class="service-body">
                    <h3>Fassadenarbeiten</h3>
                    <p>Ein frischer Außenanstrich schützt das Mauerwerk und wertet das Gesamtbild des Hauses erheblich auf. Inklusive gründlicher Untergrundprüfung, Grundierung und witterungsbeständiger Farbe.</p>
                    <a href="#kontakt" class="service-link">Anfrage stellen →</a>
                </div>
            </article>

            <article class="service-card">
                <!-- Bild: Modernes Plissee an einem Wohnzimmerfenster, schöner Lichteinfall -->
                <div class="service-image service-image--plissee" aria-hidden="true"></div>
                <div class="service-body">
                    <h3>Plissee &amp; Sonnenschutz</h3>
                    <p>Maßgefertigte Plissees und Rollos für jeden Fenstertyp – fachgerecht montiert, passgenau und in zahlreichen Farben und Materialien erhältlich. Licht und Sichtschutz perfekt kombiniert.</p>
                    <a href="#kontakt" class="service-link">Anfrage stellen →</a>
                </div>
            </article>

            <article class="service-card">
                <!-- Bild: Fliegengitter am Fenster oder Balkonrahmen, sauber eingebaut -->
                <div class="service-image service-image--insektenschutz" aria-hidden="true"></div>
                <div class="service-body">
                    <h3>Insektenschutz</h3>
                    <p>Rahmensysteme für Fenster und Türen, maßgefertigt und unauffällig. Schützt gegen Insekten, ohne den Lichteinfall zu beeinträchtigen oder das Erscheinungsbild zu stören.</p>
                    <a href="#kontakt" class="service-link">Anfrage stellen →</a>
                </div>
            </article>

        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════════════════
     REFERENZEN / ERGEBNISSE
═══════════════════════════════════════════════════════════════════════════ -->
<section class="results section-grey" id="referenzen">
    <div class="container">

        <div class="section-intro text-center">
            <h2>Ergebnisse, die für sich sprechen</h2>
            <p>Keine Versprechen ohne Belege. Hier sehen Sie echte Ergebnisse aus meinen abgeschlossenen Projekten in der Region.</p>
        </div>

        <!-- Galerie-Carousel -->
        <div class="gallery-carousel" id="gallery-carousel">
            <div class="gallery-track" id="gallery-track">

                <figure class="gallery-slide">
                    <div class="gallery-image gallery-image--1 lightbox-trigger" role="button" tabindex="0" aria-label="Frisch renoviertes Zimmer mit neuem Anstrich und Bodenbelag öffnen" data-lightbox-src="img/vergleich/zimmer-nachher.jpg" data-lightbox-caption="Komplett-Renovierung Zimmer – Büdelsdorf"></div>
                    <figcaption>Komplett-Renovierung Zimmer – Büdelsdorf</figcaption>
                </figure>

                <figure class="gallery-slide">
                    <div class="gallery-split" aria-label="Vorher und Nachher einer Wohnungsrenovierung">
                        <div class="gallery-split-half lightbox-trigger" role="button" tabindex="0" data-lightbox-src="img/vergleich/zimmer-vorher.jpg" data-lightbox-caption="Vorher – Wohnungsrenovierung, Büdelsdorf">
                            <img src="img/vergleich/zimmer-vorher.jpg" alt="Vorher – Zimmer vor der Renovierung" loading="lazy">
                            <span class="gallery-split-tag">Vorher</span>
                        </div>
                        <div class="gallery-split-half lightbox-trigger" role="button" tabindex="0" data-lightbox-src="img/vergleich/zimmer-nachher.jpg" data-lightbox-caption="Nachher – Wohnungsrenovierung, Büdelsdorf">
                            <img src="img/vergleich/zimmer-nachher.jpg" alt="Nachher – fertig renoviertes Zimmer" loading="lazy">
                            <span class="gallery-split-tag gallery-split-tag--after">Nachher</span>
                        </div>
                    </div>
                    <figcaption>Vorher / Nachher – Wohnungsrenovierung, Büdelsdorf</figcaption>
                </figure>

                <figure class="gallery-slide">
                    <div class="gallery-image gallery-image--2 lightbox-trigger" role="button" tabindex="0" aria-label="Streifentapete im Treppenhaus öffnen" data-lightbox-src="img/services/tapezierarbeiten.jpg" data-lightbox-caption="Streifentapete im Treppenhaus"></div>
                    <figcaption>Streifentapete im Treppenhaus</figcaption>
                </figure>

                <figure class="gallery-slide">
                    <div class="gallery-image gallery-image--3 lightbox-trigger" role="button" tabindex="0" aria-label="Fototapete Kinderzimmer öffnen" data-lightbox-src="img/gallery/fototapete-schmetterlinge.jpg" data-lightbox-caption="Fototapete Kinderzimmer"></div>
                    <figcaption>Fototapete Kinderzimmer</figcaption>
                </figure>

                <figure class="gallery-slide">
                    <div class="gallery-image gallery-image--4 lightbox-trigger" role="button" tabindex="0" aria-label="Spachteltechnik öffnen" data-lightbox-src="img/gallery/spachteltechnik.jpg" data-lightbox-caption="Spachteltechnik – Akzentwand in Beton-Optik"></div>
                    <figcaption>Spachteltechnik – Akzentwand in Beton-Optik</figcaption>
                </figure>

                <figure class="gallery-slide">
                    <div class="gallery-split" aria-label="Vorher und Nachher Fassadenanstrich">
                        <div class="gallery-split-half lightbox-trigger" role="button" tabindex="0" data-lightbox-src="img/vergleich/fassade-vorher.jpg" data-lightbox-caption="Vorher – Fassadenanstrich">
                            <img src="img/vergleich/fassade-vorher.jpg" alt="Vorher – Fassade vor dem Anstrich" loading="lazy">
                            <span class="gallery-split-tag">Vorher</span>
                        </div>
                        <div class="gallery-split-half lightbox-trigger" role="button" tabindex="0" data-lightbox-src="img/vergleich/fassade-nachher.jpg" data-lightbox-caption="Nachher – Fassadenanstrich">
                            <img src="img/vergleich/fassade-nachher.jpg" alt="Nachher – frisch gestrichene Fassade" loading="lazy">
                            <span class="gallery-split-tag gallery-split-tag--after">Nachher</span>
                        </div>
                    </div>
                    <figcaption>Vorher / Nachher – Fassadenanstrich</figcaption>
                </figure>

                <figure class="gallery-slide">
                    <div class="gallery-image gallery-image--6 lightbox-trigger" role="button" tabindex="0" aria-label="Skyline-Fototapete öffnen" data-lightbox-src="img/gallery/fototapete-skyline.jpg" data-lightbox-caption="Fototapete – Skyline-Motiv"></div>
                    <figcaption>Fototapete – Skyline-Motiv</figcaption>
                </figure>

                <figure class="gallery-slide">
                    <div class="gallery-image gallery-image--7 lightbox-trigger" role="button" tabindex="0" aria-label="Plissee im Home-Office öffnen" data-lightbox-src="img/gallery/plissee-office.jpg" data-lightbox-caption="Plissee-Montage – Home-Office"></div>
                    <figcaption>Plissee-Montage – Home-Office</figcaption>
                </figure>

                <figure class="gallery-slide">
                    <div class="gallery-split" aria-label="Vorher und Nachher Wohnzimmer-Renovierung">
                        <div class="gallery-split-half lightbox-trigger" role="button" tabindex="0" data-lightbox-src="img/vergleich/wohnzimmer-vorher.jpg" data-lightbox-caption="Vorher – Wohnzimmer-Renovierung">
                            <img src="img/vergleich/wohnzimmer-vorher.jpg" alt="Vorher – Wohnzimmer im Rohzustand" loading="lazy">
                            <span class="gallery-split-tag">Vorher</span>
                        </div>
                        <div class="gallery-split-half lightbox-trigger" role="button" tabindex="0" data-lightbox-src="img/vergleich/wohnzimmer-nachher.jpg" data-lightbox-caption="Nachher – Wohnzimmer-Renovierung">
                            <img src="img/vergleich/wohnzimmer-nachher.jpg" alt="Nachher – fertiges Wohnzimmer" loading="lazy">
                            <span class="gallery-split-tag gallery-split-tag--after">Nachher</span>
                        </div>
                    </div>
                    <figcaption>Vorher / Nachher – Wohnzimmer-Renovierung</figcaption>
                </figure>

                <figure class="gallery-slide">
                    <div class="gallery-image gallery-image--9 lightbox-trigger" role="button" tabindex="0" aria-label="Duette-Plissee Kinderzimmer öffnen" data-lightbox-src="img/gallery/plissee-duette.jpg" data-lightbox-caption="Duette-Plissee – Kinderzimmer"></div>
                    <figcaption>Duette-Plissee – Kinderzimmer</figcaption>
                </figure>

                <figure class="gallery-slide">
                    <div class="gallery-image gallery-image--10 lightbox-trigger" role="button" tabindex="0" aria-label="Insektenschutz-Pendeltür öffnen" data-lightbox-src="img/gallery/insektenschutz-pendeltuer.jpg" data-lightbox-caption="Insektenschutz-Pendeltür"></div>
                    <figcaption>Insektenschutz-Pendeltür</figcaption>
                </figure>

            </div>

            <button class="carousel-btn carousel-btn--prev" id="carousel-prev" aria-label="Vorheriges Bild">&#8249;</button>
            <button class="carousel-btn carousel-btn--next" id="carousel-next" aria-label="Nächstes Bild">&#8250;</button>

            <div class="carousel-dots" id="carousel-dots"></div>
        </div>

        <div class="results-cta text-center">
            <p>Sie möchten wissen, wie Ihr Projekt aussehen könnte?</p>
            <a href="#kontakt" class="btn btn-primary">Unverbindlich anfragen</a>
        </div>

    </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════════════════
     EMOTIONALE NUTZEN-SEKTION
═══════════════════════════════════════════════════════════════════════════ -->
<section class="benefits section-dark" id="nutzen">
    <div class="container">

        <div class="benefits-inner">
            <div class="benefits-text">
                <h2>Mehr als nur frische Farbe.</h2>
                <p class="benefits-intro">Ein neuer Anstrich verändert, wie sich ein Raum anfühlt. Manchmal sogar, wie man sich selbst darin fühlt.</p>

                <ul class="benefits-list" id="benefits-list">
                    <li>
                        <span class="bar-fill"></span>
                        <div class="benefit-content">
                            <strong>Neues Wohngefühl</strong>
                            <span>Ein frisch renoviertes Zimmer wirkt größer, heller und einladender. Der Unterschied ist sofort spürbar – auch wenn man es nicht bewusst erwartet.</span>
                        </div>
                    </li>
                    <li>
                        <span class="bar-fill"></span>
                        <div class="benefit-content">
                            <strong>Gepflegte Räume</strong>
                            <span>Saubere Wände ohne Flecken, Risse oder vergilbte Stellen – das gibt einem Zuhause die Pflege zurück, die es verdient.</span>
                        </div>
                    </li>
                    <li>
                        <span class="bar-fill"></span>
                        <div class="benefit-content">
                            <strong>Werterhalt</strong>
                            <span>Regelmäßige Renovierungen sind keine Kür, sondern Pflege. Sie schützen Flächen, erhalten den Wert der Immobilie und sparen langfristig Kosten.</span>
                        </div>
                    </li>
                    <li>
                        <span class="bar-fill"></span>
                        <div class="benefit-content">
                            <strong>Zuhause wieder gern zeigen</strong>
                            <span>Ob Besuch oder der eigene Blick morgens – wenn die Wände frisch sind, macht das etwas mit der Stimmung. Das Zuhause fühlt sich wieder richtig an.</span>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Vorher-Nachher Vergleich -->
            <div class="compare-slider" id="compare-slider">
                <img src="img/nachher.jpg" alt="Nachher – frisch renoviert" class="compare-after" draggable="false">
                <div class="compare-before-wrap">
                    <img src="img/vorher.jpg" alt="Vorher – vor der Renovierung" class="compare-before" draggable="false">
                </div>
                <div class="compare-handle" id="compare-handle">
                    <div class="compare-handle-line"></div>
                    <div class="compare-handle-circle">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </div>
                    <div class="compare-handle-line"></div>
                </div>
                <span class="compare-label compare-label--vorher">Vorher</span>
                <span class="compare-label compare-label--nachher">Nachher</span>
            </div>
        </div>

    </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════════════════
     KUNDENSTIMMEN
═══════════════════════════════════════════════════════════════════════════ -->
<section class="testimonials section-white" id="kundenstimmen">
    <div class="container">

        <div class="section-intro text-center">
            <h2>Was meine Kunden sagen</h2>
            <p>Nicht ich rede am lautesten – sondern die, für die ich gearbeitet habe.</p>
        </div>

        <div class="testimonials-grid">

            <blockquote class="testimonial-card">
                <div class="testimonial-stars" aria-label="5 von 5 Sternen">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                    <span class="testimonial-source">Google</span>
                </div>
                <p>&bdquo;Sehr qualitative hochwertige Malerarbeiten und das zum fairen Preis. So einen sauberen Handwerker habe ich noch nie erlebt. Wirklich sehr zu empfehlen! Außerdem habe ich von Herrn Kober Plissees und Insektenschutz einbauen lassen. Alles zu top Qualität.&ldquo;</p>
                <footer class="testimonial-author">
                    <strong>Olga O.</strong>
                    <span>Google-Bewertung</span>
                </footer>
            </blockquote>

            <blockquote class="testimonial-card">
                <div class="testimonial-stars" aria-label="5 von 5 Sternen">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                    <span class="testimonial-source">Google</span>
                </div>
                <p>&bdquo;Sehr saubere Arbeit, sehr zuverlässig und Preisleistung 100&nbsp;% – Präzision und Perfektion. Mehr geht nicht.&ldquo;</p>
                <footer class="testimonial-author">
                    <strong>lack technik</strong>
                    <span>Google-Bewertung</span>
                </footer>
            </blockquote>

            <blockquote class="testimonial-card">
                <div class="testimonial-stars" aria-label="5 von 5 Sternen">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                    <span class="testimonial-source">Google</span>
                </div>
                <p>&bdquo;Tolle Qualität, von der Planung bis zur Ausführung! Individuelle Wünsche wurden berücksichtigt, die Arbeit war präzise und zuverlässig. Wir empfehlen Viktor mehr als gerne weiter.&ldquo;</p>
                <footer class="testimonial-author">
                    <strong>Stefan Geilke</strong>
                    <span>Google-Bewertung</span>
                </footer>
            </blockquote>

        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════════════════
     ABLAUF
═══════════════════════════════════════════════════════════════════════════ -->
<section class="process section-grey" id="ablauf">
    <div class="container">

        <div class="section-intro text-center">
            <h2>So läuft es ab</h2>
            <p>Vom ersten Anruf bis zur fertigen Wand – ein klarer Ablauf ohne Überraschungen.</p>
        </div>

        <ol class="process-steps">

            <li class="process-step">
                <div class="process-number" aria-hidden="true">01</div>
                <div class="process-content">
                    <h3>Anfrage stellen</h3>
                    <p>Per Telefon, WhatsApp oder Kontaktformular – ganz wie es Ihnen passt. Beschreiben Sie kurz, was ansteht, und ich melde mich schnellstmöglich zurück.</p>
                </div>
            </li>

            <li class="process-step">
                <div class="process-number" aria-hidden="true">02</div>
                <div class="process-content">
                    <h3>Gemeinsame Besichtigung</h3>
                    <p>Ich komme persönlich vorbei, schaue mir die Situation an, beantworte Ihre Fragen und bespreche mit Ihnen, was möglich ist und was Sie sich wünschen.</p>
                </div>
            </li>

            <li class="process-step">
                <div class="process-number" aria-hidden="true">03</div>
                <div class="process-content">
                    <h3>Klares Angebot</h3>
                    <p>Sie erhalten ein schriftliches Angebot – verständlich formuliert, ohne versteckte Kosten. Erst wenn Sie zustimmen, geht es weiter.</p>
                </div>
            </li>

            <li class="process-step">
                <div class="process-number" aria-hidden="true">04</div>
                <div class="process-content">
                    <h3>Sorgfältige Umsetzung</h3>
                    <p>Zum vereinbarten Termin beginne ich mit den Arbeiten – gründlich vorbereitet, sauber ausgeführt, ohne unnötige Unterbrechungen oder Verzögerungen.</p>
                </div>
            </li>

            <li class="process-step">
                <div class="process-number" aria-hidden="true">05</div>
                <div class="process-content">
                    <h3>Übergabe &amp; Abnahme</h3>
                    <p>Gemeinsam schauen wir uns das Ergebnis an. Erst wenn Sie zufrieden sind, ist die Arbeit für mich erledigt. Ordentlich, vollständig und ohne Rückstände.</p>
                </div>
            </li>

        </ol>

    </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════════════════
     EINSATZGEBIET / LOKALE SEO
═══════════════════════════════════════════════════════════════════════════ -->
<section class="service-area section-light" id="einsatzgebiet">
    <div class="service-area-bg" aria-hidden="true">
        <picture>
            <source srcset="img/team/viktor.webp" type="image/webp">
            <img src="img/team/viktor.png" alt="" loading="lazy" decoding="async">
        </picture>
    </div>
    <div class="container">

        <div class="section-intro text-center">
            <h2>Ihr Maler &amp; Raumausstatter für Büdelsdorf und die Region</h2>
            <p>Aus Büdelsdorf direkt zu Ihnen nach Hause – mit kurzen Wegen, schneller Reaktion und einem geschulten Blick für die Besonderheiten der Häuser und Wohnungen in Schleswig-Holstein.</p>
        </div>

        <div class="service-area-intro">
            <p>
                Mein Standort in <strong>Büdelsdorf</strong> liegt zentral im Kreis Rendsburg-Eckernförde. Von hier aus bin ich in wenigen Minuten in <strong>Rendsburg</strong>, schnell an der Eider und in kurzer Fahrtzeit an der Ostsee bei <strong>Eckernförde</strong>. Ob Altbauwohnung in der Rendsburger Innenstadt, Einfamilienhaus in Fockbek oder Reihenhaus in Osterrönfeld – ich kenne die Bauweisen, die typischen Herausforderungen und bringe die passende Lösung mit.
            </p>
            <p>
                Ich arbeite für Privatkunden, Vermieter und Hausverwaltungen in folgenden Orten und Gemeinden rund um Büdelsdorf:
            </p>
        </div>

        <div class="service-area-marquee" role="region" aria-label="Standorte im Einsatzgebiet">
            <div class="marquee-row marquee-row-top">
                <ul class="marquee-track">
                    <li class="marquee-item"><strong>Büdelsdorf</strong><span>Hauptstandort – Malerarbeiten, Renovierung, Tapezieren</span></li>
                    <li class="marquee-item"><strong>Rendsburg</strong><span>Altbau, Neubau, Wohnungsrenovierung</span></li>
                    <li class="marquee-item"><strong>Eckernförde</strong><span>Innenanstrich &amp; Fassade an der Ostsee</span></li>
                    <li class="marquee-item"><strong>Fockbek</strong><span>Einfamilienhäuser &amp; Sanierungen</span></li>
                    <li class="marquee-item"><strong>Osterrönfeld</strong><span>Renovierung &amp; Malerarbeiten</span></li>
                    <li class="marquee-item"><strong>Westerrönfeld</strong><span>Innen- &amp; Außenanstrich</span></li>
                    <li class="marquee-item"><strong>Schacht-Audorf</strong><span>Am Nord-Ostsee-Kanal</span></li>
                    <li class="marquee-item"><strong>Borgstedt</strong><span>Wohnungsrenovierung</span></li>

                    <li class="marquee-item" aria-hidden="true"><strong>Büdelsdorf</strong><span>Hauptstandort – Malerarbeiten, Renovierung, Tapezieren</span></li>
                    <li class="marquee-item" aria-hidden="true"><strong>Rendsburg</strong><span>Altbau, Neubau, Wohnungsrenovierung</span></li>
                    <li class="marquee-item" aria-hidden="true"><strong>Eckernförde</strong><span>Innenanstrich &amp; Fassade an der Ostsee</span></li>
                    <li class="marquee-item" aria-hidden="true"><strong>Fockbek</strong><span>Einfamilienhäuser &amp; Sanierungen</span></li>
                    <li class="marquee-item" aria-hidden="true"><strong>Osterrönfeld</strong><span>Renovierung &amp; Malerarbeiten</span></li>
                    <li class="marquee-item" aria-hidden="true"><strong>Westerrönfeld</strong><span>Innen- &amp; Außenanstrich</span></li>
                    <li class="marquee-item" aria-hidden="true"><strong>Schacht-Audorf</strong><span>Am Nord-Ostsee-Kanal</span></li>
                    <li class="marquee-item" aria-hidden="true"><strong>Borgstedt</strong><span>Wohnungsrenovierung</span></li>
                </ul>
            </div>

            <div class="marquee-row marquee-row-bottom">
                <ul class="marquee-track">
                    <li class="marquee-item"><strong>Nübbel</strong><span>Malerarbeiten im Amt Fockbek</span></li>
                    <li class="marquee-item"><strong>Jevenstedt</strong><span>Fassade &amp; Innenanstrich</span></li>
                    <li class="marquee-item"><strong>Nortorf</strong><span>Komplett-Renovierung</span></li>
                    <li class="marquee-item"><strong>Kropp</strong><span>Renovierungsarbeiten</span></li>
                    <li class="marquee-item"><strong>Owschlag</strong><span>Innen- &amp; Außenbereich</span></li>
                    <li class="marquee-item"><strong>Hohn</strong><span>Malerarbeiten &amp; Tapezieren</span></li>
                    <li class="marquee-item"><strong>Schleswig</strong><span>Raumgestaltung &amp; Fassade</span></li>
                    <li class="marquee-item"><strong>Kreis Rendsburg-Eckernförde</strong><span>Komplette Region Schleswig-Holstein</span></li>

                    <li class="marquee-item" aria-hidden="true"><strong>Nübbel</strong><span>Malerarbeiten im Amt Fockbek</span></li>
                    <li class="marquee-item" aria-hidden="true"><strong>Jevenstedt</strong><span>Fassade &amp; Innenanstrich</span></li>
                    <li class="marquee-item" aria-hidden="true"><strong>Nortorf</strong><span>Komplett-Renovierung</span></li>
                    <li class="marquee-item" aria-hidden="true"><strong>Kropp</strong><span>Renovierungsarbeiten</span></li>
                    <li class="marquee-item" aria-hidden="true"><strong>Owschlag</strong><span>Innen- &amp; Außenbereich</span></li>
                    <li class="marquee-item" aria-hidden="true"><strong>Hohn</strong><span>Malerarbeiten &amp; Tapezieren</span></li>
                    <li class="marquee-item" aria-hidden="true"><strong>Schleswig</strong><span>Raumgestaltung &amp; Fassade</span></li>
                    <li class="marquee-item" aria-hidden="true"><strong>Kreis Rendsburg-Eckernförde</strong><span>Komplette Region Schleswig-Holstein</span></li>
                </ul>
            </div>
        </div>

        <div class="service-area-note text-center">
            <p>Ihr Ort ist nicht dabei? Kein Problem – fragen Sie einfach an. Viele weitere Gemeinden in Schleswig-Holstein liegen im Einsatzgebiet.</p>
            <a href="#kontakt" class="btn btn-primary">Jetzt Anfrage stellen</a>
        </div>

    </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════════════════
     FAQ – Häufige Fragen (SEO)
═══════════════════════════════════════════════════════════════════════════ -->
<section class="faq section-white" id="faq">
    <div class="container">

        <div class="section-intro text-center">
            <h2>Häufige Fragen an Ihren Maler in Büdelsdorf</h2>
            <p>Antworten auf die Fragen, die Kundinnen und Kunden aus Büdelsdorf, Rendsburg und Umgebung mir am häufigsten stellen.</p>
        </div>

        <div class="faq-list">

            <details class="faq-item">
                <summary>In welchen Orten arbeiten Sie als Maler und Raumausstatter?</summary>
                <div class="faq-answer">
                    <p>Mein Hauptstandort ist <strong>Büdelsdorf</strong>. Von dort aus übernehme ich Maler-, Tapezier- und Renovierungsarbeiten in <strong>Rendsburg, Eckernförde, Fockbek, Osterrönfeld, Westerrönfeld, Schacht-Audorf, Borgstedt, Nortorf, Kropp, Owschlag, Hohn, Schleswig</strong> und in der gesamten Region <strong>Rendsburg-Eckernförde</strong> in Schleswig-Holstein. Andere Orte auf Anfrage – fragen Sie einfach an.</p>
                </div>
            </details>

            <details class="faq-item">
                <summary>Ist die Besichtigung vor Ort und das Angebot kostenlos?</summary>
                <div class="faq-answer">
                    <p>Ja. Die Erstbesichtigung in Büdelsdorf und Umgebung ist kostenlos und unverbindlich. Sie erhalten anschließend ein schriftliches, transparentes Angebot – ohne versteckte Kosten und ohne Verpflichtung zur Beauftragung.</p>
                </div>
            </details>

            <details class="faq-item">
                <summary>Welche Leistungen bietet Viktor Kober in Büdelsdorf an?</summary>
                <div class="faq-answer">
                    <p>Zum Leistungsspektrum gehören:</p>
                    <ul>
                        <li>Innenanstrich &amp; Raumgestaltung (Wände, Decken)</li>
                        <li>Tapezierarbeiten (Vlies-, Struktur- und Fototapeten)</li>
                        <li>Komplette Wohnungsrenovierungen</li>
                        <li>Fassadenarbeiten &amp; Außenanstriche</li>
                        <li>Plissees, Rollos &amp; Sonnenschutz</li>
                        <li>Maßgefertigter Insektenschutz</li>
                    </ul>
                </div>
            </details>

            <details class="faq-item">
                <summary>Wie schnell ist ein Termin in Büdelsdorf oder Rendsburg möglich?</summary>
                <div class="faq-answer">
                    <p>Anfragen werden in der Regel zeitnah beantwortet. Besichtigungstermine in <strong>Büdelsdorf, Rendsburg</strong> und Umgebung sind meist innerhalb weniger Tage möglich. Die konkrete Auftragsausführung richtet sich nach der aktuellen Auftragslage – Sie erhalten aber immer einen verbindlichen Termin.</p>
                </div>
            </details>

            <details class="faq-item">
                <summary>Arbeiten Sie auch für Vermieter, Hausverwaltungen und Gewerbekunden?</summary>
                <div class="faq-answer">
                    <p>Ja. Neben Privatkunden betreue ich regelmäßig <strong>Vermieter, Hausverwaltungen und Gewerbekunden</strong> im Raum Büdelsdorf, Rendsburg und Eckernförde – zum Beispiel bei Mieterwechseln, Renovierungen von Büroräumen, Praxen oder Fassadenanstrichen an Mehrfamilienhäusern.</p>
                </div>
            </details>

            <details class="faq-item">
                <summary>Wie läuft ein Auftrag von der Anfrage bis zur Übergabe ab?</summary>
                <div class="faq-answer">
                    <p>Der Ablauf ist einfach: Sie melden sich per Telefon, WhatsApp oder Formular. Ich komme für eine kostenlose Besichtigung vorbei und erstelle ein klares schriftliches Angebot. Nach Ihrer Zustimmung führe ich die Arbeiten termingerecht aus und wir gehen gemeinsam durch das Ergebnis. Erst wenn Sie zufrieden sind, ist der Auftrag für mich abgeschlossen.</p>
                </div>
            </details>

            <details class="faq-item">
                <summary>Übernehmen Sie auch kleinere Arbeiten, zum Beispiel nur ein Zimmer streichen?</summary>
                <div class="faq-answer">
                    <p>Ja, auch einzelne Räume sind selbstverständlich möglich – ob Wohnzimmer, Schlafzimmer, Kinderzimmer, Flur oder Küche. Gerade bei einzelnen Zimmern lohnt sich saubere Arbeit besonders, weil der Unterschied zu Nachbarflächen sofort sichtbar wird.</p>
                </div>
            </details>

        </div>

    </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════════════════
     KONTAKT / CTA
═══════════════════════════════════════════════════════════════════════════ -->
<section class="contact section-white" id="kontakt">
    <div class="container">

        <div class="contact-inner">

            <div class="contact-info">
                <h2>Bereit für Ihr Projekt?</h2>
                <p>Schreiben Sie mir oder rufen Sie einfach an – ich melde mich persönlich bei Ihnen. Kein Callcenter, kein automatisches System.</p>

                <ul class="contact-details">
                    <li class="contact-phone-vibrate">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 2.18h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.09 6.09l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                        <div>
                            <strong>Telefon</strong>
                            <a href="tel:+491743226804">0174 3226804</a>
                        </div>
                    </li>
                    <li>
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                        <div>
                            <strong>WhatsApp</strong>
                            <a href="https://wa.me/491743226804" target="_blank" rel="noopener noreferrer">Nachricht schreiben</a>
                        </div>
                    </li>
                    <li>
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        <div>
                            <strong>Standort</strong>
                            <span>Am Stadtpark 7, 24782 Büdelsdorf</span>
                        </div>
                    </li>
                </ul>

                <p class="contact-note">Erreichbar Mo. – Do. 08:00 – 17:00 Uhr | Fr. 08:00 – 14:00 Uhr.<br>Anfragen per Formular werden zeitnah beantwortet.</p>
            </div>

            <div class="contact-form-wrap">

                <?php if ($formSuccess): ?>
                    <div class="form-success" role="alert">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        <h3>Nachricht gesendet!</h3>
                        <p>Vielen Dank für Ihre Anfrage. Ich melde mich zeitnah persönlich bei Ihnen.</p>
                    </div>
                <?php else: ?>

                    <?php if (!empty($formErrors)): ?>
                        <div class="form-errors" role="alert">
                            <ul>
                                <?php foreach ($formErrors as $error): ?>
                                    <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form class="contact-form" id="contact-form" method="POST" action="#kontakt" novalidate>
                        <input type="hidden" name="kontakt_form" value="1">

                        <div class="form-group">
                            <label for="name">Ihr Name <span class="required" aria-label="Pflichtfeld">*</span></label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                value="<?= h('name') ?>"
                                placeholder="Max Mustermann"
                                required
                                autocomplete="name"
                            >
                            <span class="field-error" id="name-error" role="alert"></span>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="telefon">Telefonnummer</label>
                                <input
                                    type="tel"
                                    id="telefon"
                                    name="telefon"
                                    value="<?= h('telefon') ?>"
                                    placeholder="0 123 456 789"
                                    autocomplete="tel"
                                >
                                <span class="field-error" id="telefon-error" role="alert"></span>
                            </div>

                            <div class="form-group">
                                <label for="email">E-Mail-Adresse</label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    value="<?= h('email') ?>"
                                    placeholder="ihre@email.de"
                                    autocomplete="email"
                                >
                                <span class="field-error" id="email-error" role="alert"></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="nachricht">Ihr Anliegen <span class="required" aria-label="Pflichtfeld">*</span></label>
                            <textarea
                                id="nachricht"
                                name="nachricht"
                                rows="5"
                                placeholder="Was soll renoviert oder gestrichen werden? In welchem Ort?"
                                required
                            ><?= h('nachricht') ?></textarea>
                            <span class="field-error" id="nachricht-error" role="alert"></span>
                        </div>

                        <!-- Cloudflare Turnstile Widget – Site Key über TURNSTILE_SITE_KEY-Konstante -->
                        <div class="cf-turnstile" data-sitekey="<?= htmlspecialchars(TURNSTILE_SITE_KEY, ENT_QUOTES, 'UTF-8') ?>" data-theme="light"></div>

                        <p class="form-privacy">
                            Mit dem Absenden stimmen Sie der Verarbeitung Ihrer Daten zur Bearbeitung Ihrer Anfrage zu. Details: <a href="datenschutz.php">Datenschutzerklärung</a>.
                        </p>

                        <button type="submit" class="btn btn-primary btn-full">
                            Anfrage senden
                        </button>
                    </form>

                <?php endif; ?>

            </div>
        </div>
    </div>
</section>


<?php include __DIR__ . '/includes/footer.php'; ?>
