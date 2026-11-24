# Cookievalg på Den danske tech stack

Tilpasset fra Velabos PR #63 (commit `16e01e67b92ac958756e4ef421b0007992b2d760`).

## Gennemgang og afgrænsning

Den 24. november 2026 blev repositoryets PHP, JavaScript og indlejringer samt den offentlige forsides HTML gennemgået. Ingen GTM-container, statistikværktøjer, marketingpixels eller eksterne iframes blev fundet. Velabos container, leverandørliste, cookieoprydning og virksomhedsnummer er derfor ikke kopieret.

Aktuel lagring:

| Navn | Type | Formål | Levetid |
| --- | --- | --- | --- |
| `dts_language` | Førstepartscookie, HttpOnly, SameSite=Lax, Secure på HTTPS | Sprogpræference | Op til ét år |
| `dts-theme` | localStorage | Manuelt tema | Indtil ændring eller sletning |
| `dansk-tech.cookie-consent.v1` | localStorage | Udtrykkeligt cookievalg | Højst 180 dage |

Portrætter og GitHub-avatarer er almindelige eksterne billeder og kan give billedværten IP-adresse og tekniske forespørgselsoplysninger. Favicons hentes gennem serverens cache (Google faviconservice); GitHub-bidragyderlisten hentes også på serveren. Sociale delingslinks åbner først tjenesten ved klik. Produktlinks har eksisterende referral-/UTM-parametre. Ingen af disse funktioner starter statistik eller annoncering i browseren, og de er ikke koblet til de valgfrie kategorier. De er beskrevet på `/privatliv` og `/en/privatliv`.

## Integration

Det fælles head sætter lokale Consent Mode v2-standarder til `denied` og `ads_data_redaction` til `true` før andre scripts. Det henter intet fra Google. `assets/cookie-consent.js` og CSS leveres med sitets eksisterende indholdshash i URL'en.

Footeren indeholder Cookievalg og den fælles banner-/dialog-partial. Det gælder alle HTML-sidetyper og begge sprog, inklusive 404 og privatlivssiden. Sitemap og SVG'er har ingen brugerflade. Uden JavaScript er privatlivssiden tilgængelig, og ingen valgfri tracking starter.

Afvis og tillad har samme styling. Dialogen har nødvendige indstillinger samt separate statistik- og marketingvalg, begge fravalgt fra start. Teksterne oplyser, at ingen af de valgfrie kategorier er i brug. Accept gemmer kun indstillingen og starter ingen tjenester, heller ikke i produktion.

Valget har formatet `{version:1, analytics:boolean, marketing:boolean, at:number}`. Manglende, ugyldige, fremtidige, ikke-positive og udløbne tidsstempler behandles som intet valg. Hvis localStorage er blokeret, gælder valget kun det aktuelle dokument. Ved en fejlet skrivning forsøger vi at slette et tidligere valg, så fx fuld lagerkvote ikke gendanner en ældre accept efter tilbagetrækning. `storage`-events opdaterer andre faner, også en åben dialog, ved ændring eller sletning.

Opdateringer sender alene lokale `gtag('consent', 'update', …)`-kommandoer og eventet `dansk_tech_consent_update` til `dataLayer`. Eventet er ikke en konvertering. Der er ingen trackingruntime at stoppe eller trackingcookies at rydde; ændringer kræver derfor ingen genindlæsning. Sprog og tema bevares ved afvisning.

## Hvis tracking tilføjes senere

Den aktuelle løsning indeholder bevidst ingen generisk GTM-loader. Tilføjelse kræver verificeret container/leverandør, præcis produktionsværtsliste og miljøkontrol, kategoriopdeling af faktiske tags, cookieoprydning og stop/genindlæsning ved tilbagetrækning. Opdatér teksterne og skift consentversion/nøgle, så nuværende valg ikke bruges som tilladelse til nye formål eller leverandører. Tracking må kun starte efter et nyt, aktuelt samtykke. App-tracking og konverteringsmåling skal implementeres og verificeres særskilt.

## Verifikation

`npm test --prefix tools/browser` kører cookiechecks sammen med de eksisterende browserregressioner. Eksterne requests blokeres; billeder erstattes lokalt. Cookiechecks dækker validering og udløb, første besøg, de fire kategori-kombinationer, genindlæsning, tilbagetrækning, blokeret lagring, ændringer mellem faner, begge sprog, alle HTML-sidetyper, ingen JavaScript, korrekt JS-MIME-type, tastatur/fokus uden sidehop, fejlet skrivning ved tilbagetrækning, mobil ved 200 % skrift samt Axe i lyst/mørkt tema. Der findes ingen container at verificere.
