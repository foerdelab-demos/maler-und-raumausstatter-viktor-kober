# Viktor Kober – Raumausstatter & Renovierungsarbeiten

Professionelle Landingpage für **Viktor Kober**, Raumausstatter aus Büdelsdorf (Schleswig-Holstein).
Raumgestaltung, Tapezierarbeiten, Renovierungen, Fassadenarbeiten, Plissee & Insektenschutz.

🌐 **Live:** [maler-buedelsdorf.de](https://maler-buedelsdorf.de)

---

## Inhalt dieser README

1. [Funktionen](#funktionen)
2. [Projektstruktur](#projektstruktur)
3. [Technologien](#technologien)
4. [Cloudflare Turnstile aktivieren](#cloudflare-turnstile-aktivieren) ← **wichtig zur Übergabe**
5. [Kontaktformular – Empfänger-Adresse](#kontaktformular--empfänger-adresse)
6. [Credits](#credits)

---

## Funktionen

- **Responsive Design** – optimiert für Desktop, Tablet & Smartphone
- **Animierter Hero-Hintergrund** – dezente, malerische Bewegung im Seitenkopf
- **Leistungsübersicht** – alle Services auf einen Blick
- **Referenz-Galerie** – Carousel mit abgeschlossenen Projekten
- **Vorher/Nachher-Slider** – interaktiver Vergleich
- **Kundenstimmen** – echte Google-Bewertungen
- **Kontaktformular** – mit serverseitiger Validierung & Cloudflare Turnstile (Spam-Schutz)
- **SEO-optimiert** – Meta-Tags, Open Graph, Twitter Cards, JSON-LD Schema
- **Barrierefreiheit** – ARIA-Labels, semantisches HTML

---

## Projektstruktur

```
Viktor-Kober/
├── index.php              # Startseite (Formular-Logik + Inhalt)
├── impressum.php          # Impressum
├── datenschutz.php        # Datenschutzerklärung
├── includes/
│   ├── header.php         # Head, Meta-Tags & Navigation
│   └── footer.php         # Footer & Scripts
│	└── config.php         # Konfiguration – Cloudflare Turnstile (steht auf gitignore)
├── assets/
│   ├── style.css          # Stylesheet
│   └── script.js          # JavaScript (Carousel, Slider, etc.)
├── img/                   # Bilder & Logos
└── README.md
```

---

## Technologien

| Bereich      | Technologie                        |
| ------------ | ---------------------------------- |
| Backend      | PHP 7.0+                           |
| Frontend     | HTML5, CSS3, Vanilla JavaScript    |
| Schriftarten | Google Fonts (Inter)               |
| Spam-Schutz  | Cloudflare Turnstile               |
| SEO          | JSON-LD, Open Graph, Twitter Cards |

---

## Cloudflare Turnstile aktivieren

Cloudflare Turnstile schützt das Kontaktformular kostenlos vor Spam-Bots – ohne nervige „Ich bin kein Roboter"-Klicks.
**Ohne aktivierten Turnstile funktioniert das Kontaktformular nicht.** Die Einrichtung dauert ca. 5 Minuten.

### Schritt 1 – Cloudflare-Konto anlegen

1. Auf [dash.cloudflare.com/sign-up](https://dash.cloudflare.com/sign-up) einen kostenlosen Account erstellen.
2. E-Mail bestätigen und einloggen.

### Schritt 2 – Turnstile-Widget erstellen

1. Im Cloudflare-Dashboard links im Menü auf **„Turnstile"** klicken.
2. Auf **„Add Site"** (bzw. „Widget hinzufügen") klicken.
3. Folgende Angaben eintragen:
   - **Widget-Name:** z. B. `maler-buedelsdorf.de Kontaktformular`
   - **Hostnames:** `maler-buedelsdorf.de` (und ggf. `www.maler-buedelsdorf.de`)
   - **Widget-Modus:** `Managed` (empfohlen)
4. Auf **„Create"** klicken.

### Schritt 3 – Schlüssel kopieren

Nach dem Erstellen werden zwei Schlüssel angezeigt:

| Schlüssel      | Wofür                                     | Wo er hingehört                  |
| -------------- | ----------------------------------------- | -------------------------------- |
| **Site Key**   | öffentlich – wird im HTML eingebunden     | in die Variable `TURNSTILE_SITE_KEY`   |
| **Secret Key** | geheim – für die Server-Prüfung           | in die Variable `TURNSTILE_SECRET_KEY` |

> ⚠️ **Secret Key niemals öffentlich teilen** (nicht auf GitHub, nicht in den Dateien selbst sichtbar speichern).

### Schritt 4 – Schlüssel auf dem Server eintragen

Die empfohlene Methode: **Umgebungsvariablen** beim Hoster setzen.
Die meisten Hoster (z. B. All-Inkl, IONOS, Strato, Hetzner, Cloudways) haben dafür eine Einstellung im Control-Panel (oft unter „PHP-Einstellungen" oder „Environment Variables"):

```
TURNSTILE_SITE_KEY   = <dein Site Key aus Cloudflare>
TURNSTILE_SECRET_KEY = <dein Secret Key aus Cloudflare>
```

Nach dem Speichern kurz abwarten oder PHP neu starten lassen – fertig.

Wenn dies nicht gehen sollte, dann kann die `includes/config.php` wie folgt befüllt werden (aber nur als Workaround):
```
define('TURNSTILE_SITE_KEY',   'YOUR_SITE_KEY');
define('TURNSTILE_SECRET_KEY', 'YOUR_TURNSTILE_SECRET_KEY');
```

### Schritt 5 – Funktion prüfen

1. Seite öffnen, zum Kontaktformular scrollen.
2. Unten im Formular sollte die Turnstile-Checkbox („Cloudflare schützt dich") sichtbar sein.
3. Testformular absenden – bei Erfolg erscheint die Bestätigungsmeldung.

### Falls der Hoster keine Umgebungsvariablen unterstützt

Alternativ können die Schlüssel direkt in `index.php` eingetragen werden:

- Zeile **16:** `'YOUR_TURNSTILE_SECRET_KEY'` → durch den **Secret Key** ersetzen
- Zeile **105:** `'YOUR_SITE_KEY'` → durch den **Site Key** ersetzen

> Diese Variante funktioniert, ist aber weniger sicher – die Datei sollte dann nicht öffentlich eingesehen werden können.

---

## Kontaktformular – Empfänger-Adresse

Anfragen aus dem Formular werden per E-Mail an folgende Adresse gesendet:

```
info@maler-buedelsdorf.de
```

Die Absender-Adresse ist `noreply@maler-buedelsdorf.de` (Reply-To wird automatisch auf die Adresse des Absenders gesetzt).

Soll die Empfänger-Adresse geändert werden: in [index.php](index.php) in der Zeile `$to = 'info@maler-buedelsdorf.de';` anpassen.

---

## Credits

Konzept, Design & Entwicklung:

- **[FördeLab](https://foerdelab.de)** – Digitalagentur aus dem Norden
- **[JasonHolweg.de](https://jasonholweg.de)** – Webentwicklung & Umsetzung

Bei Fragen zur Website oder technischen Anpassungen: einfach bei FördeLab melden.

---

<p align="center">
  <sub>© 2026 Viktor Kober – Raumausstatter & Renovierungsarbeiten. Alle Rechte vorbehalten.</sub>
</p>
