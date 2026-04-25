<?php
/**
 * Konfiguration – Cloudflare Turnstile
 *
 * Site- und Secret-Key aus dem Cloudflare Dashboard hier eintragen:
 *   Cloudflare Dashboard → Turnstile → (Widget auswählen)
 *
 * Diese Datei wird von index.php eingebunden und sollte ausschließlich
 * Konfigurations-Konstanten enthalten – keine Ausgabe!
 */

define('TURNSTILE_SITE_KEY',   'YOUR_SITE_KEY');
define('TURNSTILE_SECRET_KEY', 'YOUR_TURNSTILE_SECRET_KEY');
