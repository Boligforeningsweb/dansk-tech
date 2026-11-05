<?php
if (!defined('APP_ROOT')) { http_response_code(404); exit; }

$products = load_products();
$product = find_product_by_slug(query_value('produkt'));
$badgeLanguage = badge_language();
$badges = $product ? product_badges($product, $badgeLanguage) : [];
$badge = $badges[query_value('badge')] ?? ($badges ? reset($badges) : null);
$generic = generic_badge($badgeLanguage);
$supporter = supporter_badges($badgeLanguage)['vi-stoetter-dansk-tech'];

$url = locale_url('/badge');
$title = t('Badges til jeres website | Den danske tech stack');
$description = t('Gratis badges til jeres website: "Dansk alternativ til Stripe" til produkterne på listen, og "Vi støtter dansk tech" til alle andre.');
$example = ['label' => $badgeLanguage === 'en' ? 'European alternative to' : 'Dansk alternativ til', 'name' => 'Stripe', 'language' => $badgeLanguage];

partial('head', [
  'title' => $title,
  'description' => $description,
  'canonical' => $url,
  // Varianter med valgt produkt er værktøjssider, ikke indhold
  'robots' => $_GET ? 'noindex, follow' : null,
  'schema' => schema_json([schema_organization(), schema_website(), schema_breadcrumbs([[t('Forside'), locale_path('/')], ['Badge', locale_path('/badge')]], $url . '#breadcrumb')]),
]);
partial('site-header');
?>
  <main id="main-content" tabindex="-1" class="mx-auto max-w-7xl px-6 py-12 sm:py-16 lg:px-8">
    <?php partial('breadcrumbs', ['items' => [[t('Forside'), locale_path('/')], ['Badge', null]]]); ?>

    <div class="mt-10 max-w-3xl">
      <h1 class="text-4xl font-semibold tracking-tight text-pretty text-gray-900 sm:text-5xl dark:text-white"><?php echo e(t('Badges til jeres website')); ?></h1>
      <p class="mt-6 text-lg/8 text-gray-700 dark:text-gray-300"><?php echo e(t('Sæt et gratis badge på jeres website. Badgesene er helt frivillige – ingen betaler for at være på listen, og ingen skal have et badge for at komme med.')); ?></p>
    </div>

    <section class="mt-10" aria-labelledby="badge-language-heading">
      <h2 id="badge-language-heading" class="text-sm font-semibold text-gray-900 dark:text-white"><?php echo e(t('Sprog på badget')); ?></h2>
      <div class="mt-3 flex flex-wrap items-center gap-4">
        <nav class="language-switcher" aria-labelledby="badge-language-heading">
          <?php foreach (['da' => 'Dansk', 'en' => 'English'] as $language => $label): ?>
          <a href="<?php echo e(locale_path('/badge') . '?' . http_build_query(array_merge($_GET, ['sprog' => $language]))); ?>" lang="<?php echo $language; ?>"<?php echo $badgeLanguage === $language ? ' aria-current="true"' : ''; ?>><?php echo $label; ?></a>
          <?php endforeach; ?>
        </nav>
        <p class="text-sm/6 text-gray-700 dark:text-gray-300"><?php echo e(t('Vælg dansk til hjemmemarkedet eller engelsk til internationale kunder.')); ?></p>
      </div>
      <p class="mt-3 max-w-2xl text-sm/6 text-gray-700 dark:text-gray-300"><?php echo e(t('Den engelske udgave fremhæver jeres nordiske rødder: “Nordic tech” eller “European alternative to …”.')); ?></p>
    </section>

    <div class="mt-10 grid grid-cols-1 gap-6 lg:grid-cols-3">
      <a href="#tech" class="group rounded-2xl border border-gray-200 p-6 hover:border-gray-300 hover:shadow-md transition-all duration-200 dark:border-gray-700">
        <p lang="<?php echo $badgeLanguage; ?>" class="text-base font-semibold text-gray-900 dark:text-white"><?php echo e($generic['name']); ?></p>
        <p class="mt-1 text-sm/6 text-gray-700 dark:text-gray-300"><?php echo e(t('Et enkelt badge til jeres egen teknologi.')); ?></p>
        <div class="badge-preview mt-5" aria-hidden="true"><?php echo badge_svg($generic, 'lys', 'kompakt'); ?></div>
      </a>
      <a href="#produkt" class="group rounded-2xl border border-gray-200 p-6 hover:border-gray-300 hover:shadow-md transition-all duration-200 dark:border-gray-700">
        <p class="text-base font-semibold text-gray-900 dark:text-white"><?php echo e(t('Er jeres produkt på listen?')); ?></p>
        <p class="mt-1 text-sm/6 text-gray-700 dark:text-gray-300"><?php echo e(t('Vis kunderne, at I er et dansk alternativ.')); ?></p>
        <div class="badge-preview mt-5" aria-hidden="true"><?php echo badge_svg($example, 'lys', 'kompakt'); ?></div>
      </a>
      <a href="#stoette" class="group rounded-2xl border border-gray-200 p-6 hover:border-gray-300 hover:shadow-md transition-all duration-200 dark:border-gray-700">
        <p class="text-base font-semibold text-gray-900 dark:text-white"><?php echo e(t('Vil I støtte dansk tech?')); ?></p>
        <p class="mt-1 text-sm/6 text-gray-700 dark:text-gray-300"><?php echo e(t('Til alle virksomheder – også jer, der ikke selv laver software.')); ?></p>
        <div class="badge-preview mt-5" aria-hidden="true"><?php echo badge_svg($supporter, 'lys', 'kompakt'); ?></div>
      </a>
    </div>

    <section id="tech" class="mt-20 scroll-mt-6 border-t border-gray-200 pt-16 dark:border-white/10" aria-labelledby="tech-heading">
      <p class="text-sm font-semibold text-gray-700 dark:text-gray-300"><?php echo e(t('Til jeres egen teknologi')); ?></p>
      <h2 id="tech-heading" lang="<?php echo $badgeLanguage; ?>" class="mt-2 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white"><?php echo e($generic['name']); ?></h2>
      <p class="mt-4 max-w-2xl text-base/7 text-gray-700 dark:text-gray-300"><?php echo e(t('Et enkelt badge til jeres egen teknologi. Badget linker til listen.')); ?></p>
      <?php partial('badge-variants', ['product' => null, 'badge' => $generic]); ?>
    </section>

    <section id="produkt" class="mt-20 scroll-mt-6 border-t border-gray-200 pt-16 dark:border-white/10">
      <p class="text-sm font-semibold text-gray-700 dark:text-gray-300"><?php echo e(t('Til produkterne på listen')); ?></p>
      <h2 class="mt-2 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white"><?php echo e(t('Er jeres produkt på listen?')); ?></h2>
      <h3 class="mt-8 text-lg font-semibold text-gray-900 dark:text-white"><?php echo e(t('1. Vælg jeres produkt')); ?></h3>
      <form method="get" action="<?php echo e(locale_path('/badge#produkt')); ?>" class="mt-4 flex max-w-xl gap-3">
        <input type="hidden" name="sprog" value="<?php echo $badgeLanguage; ?>">
        <label for="produkt-valg" class="sr-only"><?php echo e(t('Produkt')); ?></label>
        <select id="produkt-valg" name="produkt" onchange="this.form.submit()" class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-base text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
          <option value=""><?php echo e(t('Vælg produkt …')); ?></option>
          <?php foreach ($products as $item): ?>
          <option value="<?php echo e(product_slug($item)); ?>"<?php echo $product && product_slug($item) === product_slug($product) ? ' selected' : ''; ?>><?php echo e($item['name']); ?></option>
          <?php endforeach; ?>
        </select>
        <noscript><button type="submit" class="rounded-lg bg-gray-900 px-4 py-3 text-sm font-semibold text-white"><?php echo e(t('Vis')); ?></button></noscript>
      </form>
      <p class="mt-3 text-sm text-gray-700 dark:text-gray-300"><?php echo e(t('Er jeres produkt ikke på listen endnu?')); ?> <a href="https://github.com/Boligforeningsweb/dansk-tech#bidrag" class="font-semibold text-gray-900 hover:underline dark:text-white"><?php echo e(t('Foreslå det på GitHub')); ?></a>.</p>
    </section>

    <?php if ($product && $badge): ?>
    <section class="mt-10">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white"><?php echo e(t('2. Vælg badge')); ?></h3>
      <p class="mt-2 text-sm/6 text-gray-700 dark:text-gray-300"><?php echo e(t('Anbefalet: Badges med et alternativ linker til den relevante side på listen.')); ?></p>
      <div class="mt-5 flex flex-wrap gap-2">
        <?php foreach ($badges as $item): $active = $item['slug'] === $badge['slug']; ?>
        <a href="<?php echo e(locale_path('/badge')); ?>?<?php echo e(http_build_query(['produkt' => product_slug($product), 'badge' => $item['slug'], 'sprog' => $badgeLanguage])); ?>#udseende"<?php echo $active ? ' aria-current="true"' : ''; ?><?php echo $item['slug'] === BADGE_GENERIC ? ' lang="' . $badgeLanguage . '"' : ''; ?> class="rounded-full px-3.5 py-1.5 text-sm font-medium <?php echo $active ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'border border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800'; ?>"><?php echo e($item['name']); ?></a>
        <?php endforeach; ?>
      </div>
    </section>

    <section id="udseende" class="mt-10 scroll-mt-6">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white"><?php echo e(t('3. Vælg udseende og kopiér koden')); ?></h3>
      <p class="mt-2 text-sm/6 text-gray-700 dark:text-gray-300"><?php echo e(t('Badget linker til')); ?> <a href="<?php echo e($badge['link']); ?>" class="break-all font-semibold text-gray-900 hover:underline dark:text-white"><?php echo e(preg_replace('#^https://#', '', rtrim($badge['link'], '/'))); ?></a><?php echo e(t('. Brug Markdown-koden i fx en README på GitHub.')); ?></p>
      <?php partial('badge-variants', ['product' => $product, 'badge' => $badge]); ?>
    </section>
    <?php endif; ?>

    <section id="stoette" class="mt-20 scroll-mt-6 border-t border-gray-200 pt-16 dark:border-white/10">
      <p class="text-sm font-semibold text-gray-700 dark:text-gray-300"><?php echo e(t('Til alle virksomheder')); ?></p>
      <h2 class="mt-2 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white"><?php echo e(t('Vil I støtte dansk tech?')); ?></h2>
      <p class="mt-4 max-w-2xl text-base/7 text-gray-700 dark:text-gray-300"><?php echo e(t('Badget linker til listen, så andre også kan finde gode danske systemer – uanset om I selv laver software eller ej.')); ?></p>
      <?php partial('badge-variants', ['product' => null, 'badge' => $supporter]); ?>
    </section>

    <section class="mt-16 grid grid-cols-1 gap-8 rounded-2xl bg-gray-50 px-6 py-10 sm:px-10 lg:grid-cols-3 dark:bg-gray-800">
      <div>
        <h2 class="text-sm font-semibold text-gray-900 dark:text-white"><?php echo e(t('Koster det noget?')); ?></h2>
        <p class="mt-2 text-sm/6 text-gray-700 dark:text-gray-300"><?php echo e(t('Nej. Badget er gratis, og ingen produkter betaler for at være på listen.')); ?></p>
      </div>
      <div>
        <h2 class="text-sm font-semibold text-gray-900 dark:text-white"><?php echo e(t('Skal vi have badget for at være med?')); ?></h2>
        <p class="mt-2 text-sm/6 text-gray-700 dark:text-gray-300"><?php echo e(t('Nej. Det er helt frivilligt og påvirker ikke jeres plads på listen. "Vi støtter dansk tech" kan alle bruge.')); ?></p>
      </div>
      <div>
        <h2 class="text-sm font-semibold text-gray-900 dark:text-white"><?php echo e(t('Hvor skal det stå?')); ?></h2>
        <p class="mt-2 text-sm/6 text-gray-700 dark:text-gray-300"><?php echo e(t('Typisk i footeren, på "Om os"-siden, på en side om datasikkerhed og GDPR eller i en README på GitHub.')); ?></p>
      </div>
    </section>
  </main>
<?php partial('footer'); ?>
  <script>
    document.querySelectorAll('.copy').forEach(function(button) {
      const originalText = button.textContent;
      let resetTimer;
      button.addEventListener('click', async function() {
        try {
          await navigator.clipboard.writeText(button.dataset.copy);
          clearTimeout(resetTimer);
          button.textContent = <?php echo json_encode(t('Kopieret ✓')); ?>;
          resetTimer = setTimeout(function() { button.textContent = originalText; }, 2000);
        } catch (error) {
          window.prompt(<?php echo json_encode(t('Kopiér koden herfra:')); ?>, button.dataset.copy);
        }
      });
    });
  </script>
</body>
</html>
