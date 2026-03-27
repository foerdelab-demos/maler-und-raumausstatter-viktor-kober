<?php
/**
 * Viktor Kober – Maler & Renovierungsarbeiten
 * Landingpage – index.php
 */

// ── Formularverarbeitung ──────────────────────────────────────────────────────
$formSuccess = false;
$formErrors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kontakt_form'])) {

    // ── Cloudflare Turnstile Verifikation ─────────────────────────────────────
    // Secret Key aus Umgebungsvariable lesen (in .env oder Server-Config setzen).
    // Fallback ist der Platzhalter – vor Live-Gang ersetzen!
    $turnstileSecret   = getenv('TURNSTILE_SECRET_KEY') ?: 'YOUR_TURNSTILE_SECRET_KEY';
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
        $to      = 'koberviktor@web.de';
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

/**
 * Konfiguration – vor Live-Gang anpassen
 * TURNSTILE_SITE_KEY: Cloudflare Dashboard → Turnstile → Site Key
 * TURNSTILE_SECRET_KEY kann alternativ als Umgebungsvariable gesetzt werden.
 */
define('TURNSTILE_SITE_KEY', getenv('TURNSTILE_SITE_KEY') ?: 'YOUR_SITE_KEY');

// Hilfsfunktion: Formularwert sicher ausgeben
function h(string $key): string
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST[$key])) {
        return htmlspecialchars(strip_tags($_POST[$key]), ENT_QUOTES, 'UTF-8');
    }
    return '';
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Malerarbeiten, Tapezierarbeiten und Renovierungen in Büdelsdorf und Umgebung. Zuverlässig, sauber und persönlich – Viktor Kober, Ihr Malerbetrieb aus der Region.">
    <meta name="robots" content="index, follow">
    <title>Viktor Kober – Maler &amp; Renovierungsarbeiten in Büdelsdorf</title>

    <!-- Google Fonts: Clean, hochwertige Typografie -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Cloudflare Turnstile -->
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>

    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- ═══════════════════════════════════════════════════════════════════════════
     HEADER / NAVIGATION
═══════════════════════════════════════════════════════════════════════════ -->
<header class="site-header" id="site-header">
    <div class="container header-inner">

        <a href="#" class="logo" aria-label="Viktor Kober – Startseite">
            <span class="logo-name">Viktor Kober</span>
            <span class="logo-tagline">Maler &amp; Renovierung</span>
        </a>

        <button class="nav-toggle" id="nav-toggle" aria-label="Menü öffnen" aria-expanded="false" aria-controls="main-nav">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <nav class="main-nav" id="main-nav" aria-label="Hauptnavigation">
            <ul>
                <li><a href="#leistungen">Leistungen</a></li>
                <li><a href="#referenzen">Referenzen</a></li>
                <li><a href="#kundenstimmen">Kundenstimmen</a></li>
                <li><a href="#ablauf">Ablauf</a></li>
                <li><a href="#kontakt" class="nav-cta">Anfrage stellen</a></li>
            </ul>
        </nav>

    </div>
</header>


<!-- ═══════════════════════════════════════════════════════════════════════════
     HERO SECTION
═══════════════════════════════════════════════════════════════════════════ -->
<section class="hero" id="hero">

    <!-- Bildbereich: Hier echtes Hochformat-Bild eines frisch renovierten Raumes einfügen -->
    <div class="hero-image" aria-hidden="true">
        <div class="hero-image-overlay"></div>
    </div>

    <div class="container hero-content">
        <div class="hero-text">
            <p class="hero-pre">Malerbetrieb aus Büdelsdorf</p>
            <h1 class="hero-headline">Malerarbeiten,<br>die Ihr Zuhause<br>sichtbar aufwerten.</h1>
            <p class="hero-sub">Saubere Arbeit, zuverlässige Termine und ein Ergebnis,<br class="br-desktop"> das Sie täglich erfreut – persönlich betreut von Viktor Kober.</p>

            <div class="hero-actions">
                <a href="#kontakt" class="btn btn-primary">Kostenlos anfragen</a>
                <a href="tel:+49000000000" class="btn btn-secondary">
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

</section>


<!-- ═══════════════════════════════════════════════════════════════════════════
     VERTRAUEN
═══════════════════════════════════════════════════════════════════════════ -->
<section class="trust section-light" id="vertrauen">
    <div class="container">

        <div class="section-intro text-center">
            <h2>Warum Kunden auf Viktor Kober vertrauen</h2>
            <p>Kein Großbetrieb. Kein anonymer Handwerker. Sondern ein Meister, der selbst auf der Baustelle steht – und für das Ergebnis geradesteht.</p>
        </div>

        <div class="trust-grid">

            <div class="trust-card">
                <div class="trust-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                </div>
                <h3>Direkter Ansprechpartner</h3>
                <p>Sie sprechen immer mit Viktor Kober persönlich. Keine Weiterleitungen, kein Callcenter – von der ersten Anfrage bis zur Übergabe.</p>
            </div>

            <div class="trust-card">
                <div class="trust-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                </div>
                <h3>Saubere Ausführung</h3>
                <p>Ordentliche Vorbereitung, sorgfältige Abdeckung, präzises Abkleben – und am Ende eine Übergabe, bei der keine Spuren vom Handwerker zu sehen sind.</p>
            </div>

            <div class="trust-card">
                <div class="trust-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                </div>
                <h3>Zuverlässige Termine</h3>
                <p>Was vereinbart wird, wird eingehalten. Kein ewiges Warten, keine kurzfristigen Absagen – weil Ihr Alltag und Ihr Vertrauen das wert sind.</p>
            </div>

            <div class="trust-card">
                <div class="trust-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                </div>
                <h3>Faires, klares Angebot</h3>
                <p>Kein versteckter Kleindruck. Das Angebot zeigt genau, was geplant ist – damit Sie wissen, was Sie erwartet, bevor der erste Pinsel angesetzt wird.</p>
            </div>

            <div class="trust-card">
                <div class="trust-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                </div>
                <h3>Aus der Region</h3>
                <p>Büdelsdorf, Rendsburg, Eckernförde und Umgebung – kurze Wege, schnelle Reaktion, und ein Handwerker, der in der Nachbarschaft bekannt ist.</p>
            </div>

            <div class="trust-card">
                <div class="trust-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3H14z"></path><path d="M7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"></path></svg>
                </div>
                <h3>Handwerksqualität</h3>
                <p>Jahrelange Erfahrung, hochwertige Materialien und der eigene Anspruch, keine Arbeit abzuliefern, die man selbst nicht gern nach Hause nehmen würde.</p>
            </div>

        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════════════════
     LEISTUNGEN
═══════════════════════════════════════════════════════════════════════════ -->
<section class="services section-white" id="leistungen">
    <div class="container">

        <div class="section-intro text-center">
            <h2>Was Viktor Kober für Sie übernimmt</h2>
            <p>Von der Renovierung einzelner Zimmer bis zur vollständigen Sanierung – handwerklich sauber, termingerecht und mit dem Blick für Details.</p>
        </div>

        <div class="services-grid">

            <article class="service-card">
                <!-- Bild: Frisch gestrichenes Wohnzimmer, helle Wandfarbe, saubere Kanten -->
                <div class="service-image service-image--innen" aria-hidden="true"></div>
                <div class="service-body">
                    <h3>Malerarbeiten innen</h3>
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
            <p>Keine Versprechen ohne Belege. Hier sehen Sie echte Ergebnisse aus abgeschlossenen Projekten in der Region.</p>
        </div>

        <!-- Galerie-Hinweis: Hier echte Vorher-Nachher-Bilder aus abgeschlossenen Projekten einfügen -->
        <div class="gallery-grid">

            <figure class="gallery-item gallery-item--wide">
                <!-- Bild: Wohnzimmer nach Renovierung – helle, sauber gestrichene Wände, klare Kanten -->
                <div class="gallery-image gallery-image--1" aria-label="Renoviertes Wohnzimmer, Büdelsdorf"></div>
                <figcaption>Wohnzimmer-Renovierung, Büdelsdorf</figcaption>
            </figure>

            <figure class="gallery-item">
                <!-- Bild: Detailaufnahme frisch gestrichene Wand, Übergang Wand/Decke, perfekte Kante -->
                <div class="gallery-image gallery-image--2" aria-label="Saubere Wandkante nach Anstrich"></div>
                <figcaption>Präziser Kantenanstrich</figcaption>
            </figure>

            <figure class="gallery-item">
                <!-- Bild: Schlafzimmer mit edler Strukturtapete, warme Beleuchtung -->
                <div class="gallery-image gallery-image--3" aria-label="Schlafzimmer mit Strukturtapete"></div>
                <figcaption>Strukturtapete Schlafzimmer, Rendsburg</figcaption>
            </figure>

            <figure class="gallery-item">
                <!-- Bild: Frisch renovierter Flur, helles Grau, saubere Sockelleisten -->
                <div class="gallery-image gallery-image--4" aria-label="Renovierter Flur mit frischem Anstrich"></div>
                <figcaption>Flurrenovierung mit neuem Anstrich</figcaption>
            </figure>

            <figure class="gallery-item gallery-item--wide">
                <!-- Bild: Hausfassade frisch gestrichen, vorher verwittert, jetzt frisch und gepflegt -->
                <div class="gallery-image gallery-image--5" aria-label="Fassade nach Außenanstrich"></div>
                <figcaption>Fassadenanstrich, Eckernförde</figcaption>
            </figure>

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

                <ul class="benefits-list">
                    <li>
                        <strong>Neues Wohngefühl</strong>
                        <span>Ein frisch renoviertes Zimmer wirkt größer, heller und einladender. Der Unterschied ist sofort spürbar – auch wenn man es nicht bewusst erwartet.</span>
                    </li>
                    <li>
                        <strong>Gepflegte Räume</strong>
                        <span>Saubere Wände ohne Flecken, Risse oder vergilbte Stellen – das gibt einem Zuhause die Pflege zurück, die es verdient.</span>
                    </li>
                    <li>
                        <strong>Werterhalt</strong>
                        <span>Regelmäßige Renovierungen sind keine Kür, sondern Pflege. Sie schützen Flächen, erhalten den Wert der Immobilie und sparen langfristig Kosten.</span>
                    </li>
                    <li>
                        <strong>Zuhause wieder gern zeigen</strong>
                        <span>Ob Besuch oder der eigene Blick morgens – wenn die Wände frisch sind, macht das etwas mit der Stimmung. Das Zuhause fühlt sich wieder richtig an.</span>
                    </li>
                </ul>
            </div>

            <!-- Bild: Stimmungsvoller Wohnraum, warmes Licht, frisch gestrichene Wand, gemütliche Einrichtung -->
            <div class="benefits-image" aria-hidden="true"></div>
        </div>

    </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════════════════
     KUNDENSTIMMEN
═══════════════════════════════════════════════════════════════════════════ -->
<section class="testimonials section-white" id="kundenstimmen">
    <div class="container">

        <div class="section-intro text-center">
            <h2>Was Kunden sagen</h2>
            <p>Nicht wir reden am lautesten – sondern die, für die wir gearbeitet haben.</p>
        </div>

        <div class="testimonials-grid">

            <blockquote class="testimonial-card">
                <div class="testimonial-stars" aria-label="5 von 5 Sternen">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                </div>
                <p>„Viktor hat unser Schlafzimmer und den Flur renoviert. Er war pünktlich, hat sauber gearbeitet und alles ordentlich abgedeckt. Am Ende war keine einzige Farbspritzer zu finden. Wir würden ihn jederzeit wieder beauftragen."</p>
                <footer class="testimonial-author">
                    <strong>Sandra K.</strong>
                    <span>Büdelsdorf</span>
                </footer>
            </blockquote>

            <blockquote class="testimonial-card">
                <div class="testimonial-stars" aria-label="5 von 5 Sternen">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                </div>
                <p>„Klare Kommunikation von Anfang an, das Angebot war verständlich und fair. Viktor hat sich an alles gehalten, was besprochen wurde – und das Ergebnis ist wirklich schön geworden. Die Wohnküche wirkt jetzt komplett anders."</p>
                <footer class="testimonial-author">
                    <strong>Thomas M.</strong>
                    <span>Rendsburg</span>
                </footer>
            </blockquote>

            <blockquote class="testimonial-card">
                <div class="testimonial-stars" aria-label="5 von 5 Sternen">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                </div>
                <p>„Ich hatte Sorge, dass es chaotisch wird, weil wir während der Arbeiten noch in der Wohnung waren. Viktor war diskret, hat auf Ordnung geachtet und täglich aufgeräumt. Das war angenehmer, als ich erwartet hatte."</p>
                <footer class="testimonial-author">
                    <strong>Petra W.</strong>
                    <span>Eckernförde</span>
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
                    <p>Per Telefon, WhatsApp oder Kontaktformular – ganz wie es Ihnen passt. Kurz beschreiben, was ansteht, und wir melden uns schnellstmöglich zurück.</p>
                </div>
            </li>

            <li class="process-step">
                <div class="process-number" aria-hidden="true">02</div>
                <div class="process-content">
                    <h3>Gemeinsame Besichtigung</h3>
                    <p>Viktor Kober kommt persönlich vorbei, schaut sich die Situation an, beantwortet Fragen und bespricht, was möglich ist und was Sie sich wünschen.</p>
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
                    <p>Zum vereinbarten Termin beginnen die Arbeiten – gründlich vorbereitet, sauber ausgeführt, ohne unnötige Unterbrechungen oder Verzögerungen.</p>
                </div>
            </li>

            <li class="process-step">
                <div class="process-number" aria-hidden="true">05</div>
                <div class="process-content">
                    <h3>Übergabe &amp; Abnahme</h3>
                    <p>Gemeinsam schauen wir uns das Ergebnis an. Erst wenn Sie zufrieden sind, ist die Arbeit erledigt. Ordentlich, vollständig und ohne Rückstände.</p>
                </div>
            </li>

        </ol>

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
                <p>Schreiben Sie uns oder rufen Sie einfach an – Viktor Kober meldet sich persönlich bei Ihnen. Kein Callcenter, kein automatisches System.</p>

                <ul class="contact-details">
                    <li>
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 2.18h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.09 6.09l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                        <div>
                            <strong>Telefon</strong>
                            <a href="tel:+49000000000">+49 (0) 000 000 000</a><!-- Echte Nummer eintragen -->
                        </div>
                    </li>
                    <li>
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                        <div>
                            <strong>WhatsApp</strong>
                            <a href="https://wa.me/49000000000" target="_blank" rel="noopener noreferrer">Nachricht schreiben</a><!-- Echte Nummer eintragen -->
                        </div>
                    </li>
                    <li>
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        <div>
                            <strong>Standort</strong>
                            <span>Büdelsdorf und Umgebung</span>
                        </div>
                    </li>
                </ul>

                <p class="contact-note">Erreichbar Mo – Fr, 7:00 – 18:00 Uhr.<br>Anfragen per Formular werden innerhalb eines Werktages beantwortet.</p>
            </div>

            <div class="contact-form-wrap">

                <?php if ($formSuccess): ?>
                    <div class="form-success" role="alert">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        <h3>Nachricht gesendet!</h3>
                        <p>Vielen Dank für Ihre Anfrage. Viktor Kober meldet sich zeitnah persönlich bei Ihnen.</p>
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
                            Mit dem Absenden stimmen Sie der Verarbeitung Ihrer Daten zur Bearbeitung Ihrer Anfrage zu. Details: <a href="#datenschutz">Datenschutzerklärung</a>.
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


<!-- ═══════════════════════════════════════════════════════════════════════════
     FOOTER
═══════════════════════════════════════════════════════════════════════════ -->
<footer class="site-footer" id="datenschutz">
    <div class="container footer-inner">

        <div class="footer-brand">
            <span class="logo-name">Viktor Kober</span>
            <span class="logo-tagline">Maler &amp; Renovierung</span>
            <p>Zuverlässige Malerarbeiten in Büdelsdorf, Rendsburg, Eckernförde und Umgebung.</p>
        </div>

        <div class="footer-links">
            <h4>Navigation</h4>
            <ul>
                <li><a href="#leistungen">Leistungen</a></li>
                <li><a href="#referenzen">Referenzen</a></li>
                <li><a href="#kundenstimmen">Kundenstimmen</a></li>
                <li><a href="#ablauf">Ablauf</a></li>
                <li><a href="#kontakt">Kontakt</a></li>
            </ul>
        </div>

        <div class="footer-contact">
            <h4>Kontakt</h4>
            <p>
                <a href="tel:+49000000000">+49 (0) 000 000 000</a><br><!-- Echte Nummer eintragen -->
                <a href="mailto:koberviktor@web.de">koberviktor@web.de</a>
            </p>
            <p>Büdelsdorf und Umgebung<br>Mo – Fr, 7:00 – 18:00 Uhr</p>
        </div>

    </div>

    <div class="footer-bottom">
        <div class="container">
            <p>&copy; <?= date('Y') ?> Viktor Kober – Maler &amp; Renovierungsarbeiten. Alle Rechte vorbehalten.</p>
            <p><a href="#datenschutz">Datenschutz</a> · <a href="#impressum">Impressum</a></p>
        </div>
    </div>
</footer>

<script src="script.js"></script>
</body>
</html>
