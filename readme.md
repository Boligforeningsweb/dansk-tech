# <img src="assets/logo.svg" width="32" height="32" alt="" align="top"> Den danske tech stack

En oversigt over danske tech-systemer som alternativer til store internationale spillere. Få inspiration til din tech stack med danske SaaS-løsninger til betalinger, analytics, monitoring og mere.

🌐 **Live på:** [dansktechstack.dk](https://dansktechstack.dk)

---

## 📖 Om projektet

I en tid hvor der ofte tales om Danmarks afhængighed af udenlandsk software, vil vi fremhæve danske systemer, vi selv har tillid til - med hovedkontor i Danmark eller med danske stiftere eller medstiftere.

Danmark har nemlig en stolt tradition inden for softwareudvikling. Teknologier som Ruby on Rails, TypeScript, C# og PHP har danske rødder, og på dette site har vi samlet en liste over stærke danske alternativer til software, der ellers typisk købes i udlandet.

### Formål

Projektet har til formål at:

- ✅ Sætte fokus på danske tech-produkter til software-virksomheder
- ✅ Give inspiration til danske alternativer i tech stacks
- ✅ Fremme synligheden af dansk tech-innovation
- ✅ Støtte danske iværksættere og software-virksomheder

---

## 👥 Hvem står bag?

Initiativet er startet af en gruppe danske iværksættere, chefer og investorer, der gerne vil bidrage til at fremme dansk tech. Se listen på [dansktechstack.dk/#iværksættere](https://dansktechstack.dk/#iværksættere).

---

<a id="bidrag"></a>
<a id="-hvordan-bidrager-du"></a>

## 🤝 Foreslå et produkt eller en rettelse

Du kan bidrage på to måder:

- **Send et forslag:** [Opret et issue på GitHub](https://github.com/Boligforeningsweb/dansk-tech/issues/new). Skriv produktets navn, hjemmeside, en kort beskrivelse, dets danske tilknytning og hvilke internationale produkter det er et alternativ til. Du behøver ikke skrive kode. Du kan også foreslå rettelser til eksisterende oplysninger.
- **Lav en pull request:** Tilføj eller ret produktet i [products.json](products.json). Se vejledningen nedenfor.

Projektet er non-profit. Det koster ikke noget at blive optaget, og du behøver ikke sætte et badge på dit website.

<a id="kriterier"></a>
<a id="-retningslinjer-for-bidrag"></a>

## 📋 Hvilke produkter kan komme med?

Produktet skal have mindst én af disse danske tilknytninger:

- Hovedkontor i Danmark.
- Dansk stifter eller medstifter.
- Primært dansk ejerskab.

Det skal også kunne bruges i en tech stack hos fx en softwarevirksomhed, en SaaS-virksomhed eller en webshop. Beskriv, hvad produktet gør, og hvilke behov det kan dække. Undgå udokumenterede løfter om funktioner, sikkerhed eller datalagring.

## ✏️ Tilføj et produkt direkte på GitHub

Du behøver ikke installere noget eller starte sitet lokalt.

1. Tjek først [listen](https://dansktechstack.dk/#produkter), så produktet ikke allerede findes.
2. Åbn [products.json](products.json), og klik på blyanten for at redigere. GitHub kan bede dig om at oprette en fork — din egen kopi af projektet.
3. Tilføj dit produkt som et nyt objekt i listen. Brug strukturen nedenfor. Rækkefølgen er valgfri; sitet sorterer automatisk alfabetisk.
4. Gem ændringen med en kort besked, fx `Tilføj DitProdukt.dk`, og opret en pull request til dette repository.
5. Beskriv kort produktet og dets danske tilknytning i din pull request. Vi gennemgår forslaget, før det bliver tilføjet.

```json
{
  "name": "DitProdukt.dk",
  "url": "https://ditprodukt.dk",
  "description": "Kort dansk beskrivelse af, hvad produktet gør.",
  "alternatives": ["Mailchimp", "Campaign Monitor", "Klaviyo"]
}
```

Udskift eksempelteksten og alternativnavnene med oplysninger, der passer til dit produkt. Husk komma mellem objekterne i den eksisterende liste, men intet komma efter det sidste objekt.

| Felt | Hvad skal du skrive? |
| --- | --- |
| `name` | Produktets navn, gerne med domæne, fx `DitProdukt.dk`. |
| `url` | Den fulde hjemmesideadresse, fx `https://ditprodukt.dk`. |
| `description` | 1–2 korte sætninger på dansk om, hvad produktet gør. |
| `alternatives` | Mindst ét internationalt produkt, som dit produkt kan være et alternativ til. |
| `image` | Valgfrit: stien til et eksisterende produktbillede i `images/`. Du kan udelade feltet; så prøver sitet automatisk produktets favicon. |

Skriv **ét alternativ pr. element**: `["Wix", "WordPress"]`, ikke `["Wix, WordPress"]`. Brug samme stavemåde som resten af filen, fx `Mailchimp`.

**Til et almindeligt produktforslag skal du kun ændre `products.json`.** Du skal ikke ændre `original-products.json`, sitets kode eller filerne i `data/`. Vedligeholderne håndterer engelske oversættelser og eventuelle AI-forklaringer. En manglende oversættelse blokerer ikke dit produktforslag.

Et nyt logo er valgfrit. Hvis du vil tilføje et, kan du også vedlægge billedfilen i `images/` og angive stien i `image`.

### Hvad betyder de automatiske checks?

GitHub kontrollerer din pull request automatisk. Røde checks kan fx skyldes ugyldig JSON, et produkt der allerede findes, et manglende felt eller et alternativnavn, der staves anderledes end i resten af listen. Åbn det fejlede check for at se beskeden, og ret din ændring.

Advarsler om manglende oversættelser eller AI-forklaringer er til vedligeholderne. Du skal ikke selv oprette de tekster. Spørg i din pull request, hvis en fejlbesked er uklar.

<details>
<summary>Foretrækker du at bruge Git lokalt?</summary>

Fork repositoryet på GitHub, og kør:

```bash
git clone https://github.com/DIT-BRUGERNAVN/dansk-tech.git
cd dansk-tech
git checkout -b tilfoej-produkt-navn
```

Rediger `products.json`. Hvis du har PHP installeret, kan du kontrollere filen lokalt:

```bash
php tools/validate-products.php
```

Gem og push din ændring:

```bash
git add products.json
git commit -m "Tilføj produktnavn"
git push origin tilfoej-produkt-navn
```

Opret derefter en pull request fra din fork til `Boligforeningsweb/dansk-tech`.

</details>

## 🌍 Dansk, engelsk og badges

Sitet findes på [dansk](https://dansktechstack.dk/) og [engelsk](https://dansktechstack.dk/en). Du bidrager fortsat med én dansk produktbeskrivelse i `products.json`; vedligeholderne står for den engelske udgave.

På [badgesiden](https://dansktechstack.dk/badge) kan du hente gratis badges på begge sprog, fx **Dansk tech** eller **Nordic tech**, samt badges med konkrete alternativnavne. Badges er frivillige.

**English contributions are welcome.** To suggest a product or correction, [open an issue](https://github.com/Boligforeningsweb/dansk-tech/issues/new) with its name, URL, description, connection to Denmark and international alternatives. No coding or Danish translation is required to submit an issue.

## 🛠️ Udvikling og vedligeholdelse

Vil du arbejde på selve sitet? Se [den tekniske vejledning](docs/maintainers.md) for lokal udvikling, CSS, tests, oversættelser, SEO og generering af delingsbilleder. Har du en idé til sitets funktioner, kan du starte med et issue.

## 📝 Licens

Repositoryet indeholder endnu ikke en `LICENSE`-fil. Kontakt projektets vedligeholdere, hvis du har spørgsmål til licensen.

## 📧 Kontakt

Har du spørgsmål eller forslag? [Opret et issue](https://github.com/Boligforeningsweb/dansk-tech/issues/new), eller skriv til [kontakt@langsom.com](mailto:kontakt@langsom.com).

Tak til alle, der bidrager til at gøre danske tech-produkter mere synlige.

**Bygget i København af folkene fra langsom.com + venner fra branchen.**
