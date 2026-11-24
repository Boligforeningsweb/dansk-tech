<?php
if (!defined('APP_ROOT')) { http_response_code(404); exit; }
partial('head', [
  'title' => t('Privatliv og cookies') . ' · ' . t('Den danske tech stack'),
  'description' => t('Sådan bruger Den danske tech stack cookies og lokal lagring.'),
  'canonical' => locale_url('/privatliv'),
]);
partial('site-header');
?>
<main id="main-content" tabindex="-1" class="mx-auto max-w-3xl px-6 py-16 text-gray-900 dark:text-white">
<h1 class="text-3xl font-bold"><?php echo e(t('Privatliv og cookies')); ?></h1>
<section class="mt-8"><h2 class="text-xl font-semibold"><?php echo e(t('Ansvarlig og kontakt')); ?></h2>
<p class="mt-4 text-base/7 text-gray-600 dark:text-gray-300"><?php echo e(t('Den danske tech stack drives af Langsom.com. Spørgsmål om privatliv og brug af hjemmesiden kan sendes til kontakt@langsom.com.')); ?></p>
</section>
<section class="mt-8"><h2 class="text-xl font-semibold"><?php echo e(t('Hvad gemmes i din browser?')); ?></h2>
<p class="mt-4 text-base/7 text-gray-600 dark:text-gray-300"><?php echo e(t('Sprogvalget gemmes i førstepartscookien dts_language i op til ét år. Den bruges til at vise hjemmesiden på dit foretrukne sprog.')); ?></p>
<p class="mt-4 text-base/7 text-gray-600 dark:text-gray-300"><?php echo e(t('Dit manuelle temavalg gemmes i localStorage under dts-theme, indtil du ændrer det eller sletter browserens webstedsdata.')); ?></p>
<p class="mt-4 text-base/7 text-gray-600 dark:text-gray-300"><?php echo e(t('Dit cookievalg gemmes i localStorage under dansk-tech.cookie-consent.v1 i højst 180 dage. Det indeholder kun version, kategorivalg og tidspunkt. Du kan ændre eller trække valget tilbage via Cookievalg i footeren.')); ?></p>
</section>
<section class="mt-8"><h2 class="text-xl font-semibold"><?php echo e(t('Statistik og markedsføring')); ?></h2>
<p class="mt-4 text-base/7 text-gray-600 dark:text-gray-300"><?php echo e(t('Vi bruger i øjeblikket ingen statistikværktøjer, marketingpixels eller Google Tag Manager. Der er ingen leverandører i disse kategorier. Et ja i cookievalget aktiverer derfor ingen tracking. Hvis vi indfører sådanne tjenester, opdaterer vi oplysningerne og beder om et nyt valg.')); ?></p>
</section>
<section class="mt-8"><h2 class="text-xl font-semibold"><?php echo e(t('Billeder og eksterne links')); ?></h2>
<p class="mt-4 text-base/7 text-gray-600 dark:text-gray-300"><?php echo e(t('Portrætter og bidragyderbilleder kan hentes fra eksterne billedværter, herunder GitHub. Ved en sådan billedforespørgsel modtager værten din IP-adresse og almindelige tekniske oplysninger. Produktfavicons hentes via vores server og cache. Disse billeder er ikke statistik- eller marketingpixels.')); ?></p>
<p class="mt-4 text-base/7 text-gray-600 dark:text-gray-300"><?php echo e(t('Produktlinks indeholder kildeparametre, så modtageren kan se, at besøget kommer herfra. Deling på sociale medier åbner først tjenesten, når du klikker på linket. Eksterne hjemmesider har deres egne privatlivsvilkår.')); ?></p>
</section>
</main>
<?php partial('footer'); ?>
</body>
</html>
