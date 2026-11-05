# Teknisk vejledning til vedligeholdere

[Tilbage til README og bidragsvejledningen](../readme.md).

Denne vejledning handler om at udvikle og vedligeholde sitet. For at foreslå eller rette et produkt behøver du kun at ændre `products.json`; du skal ikke bygge CSS, vedligeholde oversættelser eller køre browsertests.

## 🛠️ Kør sitet lokalt (kun maintainers)

```bash
php -S localhost:8000 tools/dev-router.php
```

CSS'en bygges med Tailwind (v3.4, standalone-CLI, kræver ikke Node). Kør dette efter ændringer i PHP-skabelonerne og commit `assets/app.css` – en GitHub Action tjekker, at filen er opdateret:

```bash
./tools/tailwind/build.sh
```

`index.php` er en router: `pages/` indeholder siderne, `partials/` de fælles dele (head, footer, produktkort), `lib/` hjælpefunktioner og `data/people.json` iværksætterne på forsiden. Ukendte URL'er giver en rigtig 404.

Produkterne kan hente et badge på `/badge` (fx "Dansk alternativ til Stripe"), som serveres af `/badge/{produkt}/{alternativ}.svg`. Teksten tegnes som vektorer ud fra `data/badge-font.json` (Inter, se `tools/badge/`).

Sitet findes på dansk på de eksisterende adresser og på engelsk under `/en`, `/en/alternatives` og `/en/badge`. Sprogvælgeren bevarer den aktuelle side, query-parametre og sektion. Begge sprog bruger de samme PHP-skabeloner og kildedata. Canonical, hreflang, sociale metadata, JSON-LD og sitemap omfatter begge sprog. Hvert sprog har sit eget delingsbillede og app-manifest (`site.webmanifest` / `site-en.webmanifest`), så en gemt webapp åbner forsiden på det valgte sprog.

På `/` vælges sprog i rækkefølgen: gyldig `dts_language`-cookie → browserens prioriterede dansk/engelsk i `Accept-Language` → dansk. Cookien gemmer sproget på senest besøgte side i ét år (HttpOnly, SameSite=Lax og Secure ved HTTPS). Sprogvælgeren markerer et udtrykkeligt valg med `site_language`, så dansk også kan vælges fra en engelsk browser uden redirect-loop, hvis cookies er blokeret. JavaScript fjerner markøren fra adresselinjen; valget virker også uden JavaScript. Direkte links til danske og engelske undersider respekteres og opdaterer præferencen. Kendte crawlere omdirigeres ikke ud fra browsersprog og får ingen præferencecookie. HTML er `private, no-store`, og den forhandlede forside har `Vary: Cookie, Accept-Language`; SVG'er og sitemap ændrer ikke sprogpræferencen.

Søgning bevares ved sprogskift som `q` i URL'en. Systemets lyse/mørke tema følges automatisk; temaknappen gemmer et manuelt valg i `localStorage` under `dts-theme`. Uden adgang til storage fungerer systemtemaet og knappen stadig. Produktkort bruger et eksisterende billede fra `images/`, derefter favicon og til sidst produktets initial, hvis billederne fejler.

Badgesiden har et selvstændigt valg af badgesprog (`?sprog=da` eller `?sprog=en`), så man også kan hente engelske badges fra den danske side. De danske SVG- og embedadresser er bevaret. Engelske SVG'er serveres under `/en/badge/…` og linker til den engelske side. De korte badges har teksten “Dansk tech” og “Nordic tech” og kan kopieres direkte fra badgesiden uden produktvalg. De konkrete alternativbadges har “Dansk alternativ til …” og “European alternative to …”; støttebadgene har “Vi støtter dansk tech” og “We support Nordic tech”. Begge sprog tilbyder kompakt/stort og lyst/mørkt udseende samt HTML og Markdown.

Oversættelser vedligeholdes internt i `data/translations/`: `en.json` indeholder sidernes tekster med den danske tekst som nøgle, `products.en.json` produktbeskrivelser med URL som nøgle, og `product-guides.en.json` FAQ-forklaringer og afklaringspunkter. Produktoversættelser bruger samme `sourceHash` som produktguides. Guideoversættelser har også `guideHash` (SHA-256 af dansk forklaring + linjeskift + afklaringspunkt), så en ændret dansk guide ikke efterlader en forældet engelsk forklaring. Manglende eller forældede guideoversættelser bruger den aktuelle produktbeskrivelse; manglende eller forældede produktoversættelser bruger den danske kildebeskrivelse. Der kaldes ingen oversættelsestjeneste ved sidevisning. Bidragydere foreslår fortsat produkter i `products.json`; oversættelser gennemgås af vedligeholderne.

Kør `php tools/check-localization.php` for at kontrollere begge sprogs sider, oversættelser, FAQ, metadata, interne links og badgevarianter, inklusive badgets eget sprog i SVG og HTML-embeds. Kontrollen køres også i GitHub Actions.

README bruger stabile `bidrag`- og `kriterier`-ankre og bevarer de tidligere bidragsankre til eksisterende links. Kør `php tools/check-contribution-links.php` for at kontrollere, at sitets GitHub-fragmentlinks stadig lander det rigtige sted. Denne kontrol køres også ved README-ændringer i GitHub Actions.

En manglende eller forældet engelsk produktbeskrivelse vises med `lang="da"` og en synlig engelsk forklaring. JSON-LD markerer også kildesproget. Kør `php tools/check-localization.php --strict-translations` før udgivelse for at kræve komplette, aktuelle produktoversættelser. Den almindelige kontrol tillader fortsat community-bidrag med en advarsel, indtil vedligeholderne har oversat teksten.

Sprogprioritering, HTTP-redirects, cookie-attributter og crawler-/asset-adfærd testes med `php tools/check-language-preferences.php`. Browserregressioner tester sprogvalg, søgestatus, tema, billedfallback, gentagen kopiering, layouts med normal/200 % skrift og Axe på begge sprog og i begge temaer. Node bruges kun til tests; selve sitet kræver stadig kun PHP:

```bash
npm ci --prefix tools/browser
npx --prefix tools/browser playwright install chromium
npm test --prefix tools/browser
# Valgfrit efter installation af motorerne: DTS_BROWSER=firefox eller DTS_BROWSER=webkit
```

Testen starter og stopper sin egen PHP-server med isoleret cache og erstatter eksterne billedtjenester. `DTS_CHROME_PATH` kan pege på en lokal Chrome-installation. GitHub Actions kører browserkontrollen i Chromium.

Hvert udenlandsk produkt i `alternatives` får automatisk sin egen side på `/alternativer/{navn}` (fx `/alternativer/stripe`), og `/alternativer` viser dem alle A–Å. Alle eksisterende alternativsider tillader indeksering og kommer i `/sitemap.xml`, også sider med ét dansk produkt. Google afgør selv, hvilke sider der faktisk indekseres.

Alternativsiderne har en FAQ med produktforklaringer og vejledning, tydeligt deklareret som skrevet med AI. De enkelte forklaringer ligger i `data/product-guides.json`, med produktets URL som nøgle og felterne `explanation`, `consideration` og `sourceHash`. Teksterne bygger kun på oplysningerne i `products.json`; spørgsmål under "Inden du vælger" er afklaringspunkter, ikke påstande om produktets funktioner. Der kaldes ingen AI-tjeneste ved sidevisning.

`sourceHash` er SHA-256 af produktnavn + linjeskift + produktbeskrivelse (`hash('sha256', $product['name'] . "\n" . $product['description'])`). Nye produkter og produkter med ændret navn eller beskrivelse bruger automatisk den aktuelle produktbeskrivelse i FAQ'en, indtil en opdateret AI-forklaring er gennemgået og gemt med en ny hash. Fjernes et produkt fra listen, vises dets forklaring heller ikke længere. Bidragydere skal fortsat kun redigere `products.json`; guides vedligeholdes internt.

Tjek indhold og indeksering lokalt med `php tools/check-alternative-pages.php`. Kontrollen rapporterer også, hvor mange produkter der har en aktuel AI-guide, og advarer om produkter, der bruger deres beskrivelse som fallback. Den tester manglende, ugyldige og forældede guidefiler i isolerede processer.

SEO: Alternativsiderne har sidetitel, meta description, canonical, Open Graph/Twitter og JSON-LD med `CollectionPage`, `BreadcrumbList`, `ItemList`/`SoftwareApplication` og `FAQPage`/`Question`/`Answer`. FAQ-markup bygges fra de samme data som den synlige FAQ, inklusive afklaringspunkter og leverandørhenvisninger. Den fælles `WebSite`-beskrivelse er ens på alle sider. FAQ-schema er en beskrivelse af indholdet, ikke et løfte om særlige Google-visninger; Google viser ikke længere FAQ rich results. Vi tilføjer ikke ukendte priser eller anmeldelser til produkt-schema.

`lastmod` og `dateModified` udelades, fordi filernes ændringstid ved en deployment ikke dokumenterer en reel ændring af indholdet. Felterne er valgfrie og bør først tilføjes igen, når der vedligeholdes pålidelige indholdsdatoer.

---

## 🖼️ OG-billede (kun maintainers)

`og-image.jpg` er en mosaik af iværksætterne, GitHub-bidragyderne og produkternes logoer. Generér det igen, når der er kommet nye produkter eller bidragydere, så tallene er opdaterede:

```bash
pip install pillow
python3 scripts/og-image/build.py            # rød variant
python3 scripts/og-image/build.py --theme dark
python3 scripts/og-image/build.py --language en # engelsk udgave: og-image-en.jpg
```
