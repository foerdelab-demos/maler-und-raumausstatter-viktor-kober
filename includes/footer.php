<!-- ═══════════════════════════════════════════════════════════════════════════
     FOOTER
═══════════════════════════════════════════════════════════════════════════ -->
<footer class="site-footer" id="datenschutz" itemscope itemtype="https://schema.org/HousePainter">
    <meta itemprop="name" content="Viktor Kober – Maler & Raumausstatter Büdelsdorf">
    <meta itemprop="url" content="https://maler-buedelsdorf.de/">
    <meta itemprop="priceRange" content="€€">

    <div class="container footer-inner">

        <div class="footer-brand">
            <div class="footer-brand-logos">
                <img src="img/initials-logo.svg" alt="Initiallogo Viktor Kober" class="footer-logo-initials-img" aria-hidden="true">
                <img src="img/Viktor%20Kober.png" alt="Viktor Kober – Maler & Raumausstatter in Büdelsdorf" class="footer-logo-img" itemprop="logo">
            </div>
            <p>Ihr <strong>Maler &amp; Raumausstatter in Büdelsdorf</strong> – zuverlässige Raumgestaltung in Büdelsdorf, Rendsburg, Eckernförde und der gesamten Region Rendsburg-Eckernförde. Persönlich betreut von Viktor Kober.</p>
        </div>

        <div class="footer-links">
            <h4>Navigation</h4>
            <ul>
                <li><a href="#leistungen">Leistungen</a></li>
                <li><a href="#referenzen">Referenzen</a></li>
                <li><a href="#kundenstimmen">Kundenstimmen</a></li>
                <li><a href="#einsatzgebiet">Einsatzgebiet</a></li>
                <li><a href="#faq">FAQ</a></li>
                <li><a href="#ablauf">Ablauf</a></li>
                <li><a href="#kontakt">Kontakt</a></li>
            </ul>
        </div>

        <div class="footer-links">
            <h4>Einsatzgebiet</h4>
            <ul>
                <li><a href="#einsatzgebiet">Maler Büdelsdorf</a></li>
                <li><a href="#einsatzgebiet">Maler Rendsburg</a></li>
                <li><a href="#einsatzgebiet">Maler Eckernförde</a></li>
                <li><a href="#einsatzgebiet">Maler Fockbek</a></li>
                <li><a href="#einsatzgebiet">Maler Osterrönfeld</a></li>
                <li><a href="#einsatzgebiet">Maler Schleswig</a></li>
                <li><a href="#einsatzgebiet">Kreis Rendsburg-Eckernförde</a></li>
            </ul>
        </div>

        <div class="footer-contact">
            <h4>Kontakt</h4>
            <p>
                <a href="tel:+491743226804" itemprop="telephone">0174 3226804</a><br>
                <a href="mailto:info@maler-buedelsdorf.de" itemprop="email">info@maler-buedelsdorf.de</a>
            </p>
            <p itemprop="address" itemscope itemtype="https://schema.org/PostalAddress">
                <span itemprop="streetAddress">Am Stadtpark 7</span><br>
                <span itemprop="postalCode">24782</span> <span itemprop="addressLocality">Büdelsdorf</span><br>
                <span itemprop="addressRegion">Schleswig-Holstein</span>, <span itemprop="addressCountry">Deutschland</span>
            </p>
            <p><strong>Öffnungszeiten:</strong><br>Mo. – Do. 08:00 – 17:00 Uhr<br>Fr. 08:00 – 14:00 Uhr</p>
        </div>

    </div>

    <div class="footer-bottom">
        <div class="container">
            <p>&copy; <?= date('Y') ?> Viktor Kober – Raumausstatter & Renovierungsarbeiten. Alle Rechte vorbehalten.</p>
            <p><a href="datenschutz.php">Datenschutz</a> · <a href="impressum.php">Impressum</a></p>
            <p class="footer-credit">Seite erstellt von Fördelab · <a href="https://foerdelab.de" target="_blank" rel="noopener noreferrer">foerdelab.de</a> · <a href="https://fördelab.de" target="_blank" rel="noopener noreferrer">fördelab.de</a></p>
        </div>
    </div>
</footer>

<!-- Lightbox -->
<div class="lightbox" id="lightbox" role="dialog" aria-modal="true" aria-label="Bildergalerie" aria-hidden="true">
    <button class="lightbox-close" type="button" aria-label="Schließen">
        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
    </button>
    <button class="lightbox-nav lightbox-nav--prev" type="button" aria-label="Vorheriges Bild">
        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
    </button>
    <button class="lightbox-nav lightbox-nav--next" type="button" aria-label="Nächstes Bild">
        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
    </button>
    <figure class="lightbox-stage">
        <img class="lightbox-image" src="" alt="">
        <figcaption class="lightbox-caption"></figcaption>
    </figure>
</div>

<script src="assets/script.js"></script>
</body>
</html>
