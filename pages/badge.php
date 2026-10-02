<?php
if (!defined('APP_ROOT')) { http_response_code(404); exit; }

$products = load_products();
$product = isset($_GET['produkt']) ? find_product_by_slug((string) $_GET['produkt']) : null;
$badges = $product ? product_badges($product) : [];
$badge = $badges[(string) ($_GET['badge'] ?? '')] ?? ($badges ? reset($badges) : null);

$url = SITE_URL . 'badge';
$title = 'Badge til jeres website | Den danske tech stack';
$description = 'Er jeres produkt på Den danske tech stack? Hent et gratis badge – fx "Dansk alternativ til Stripe" – og vis kunderne, at I er dansk software.';
$variants = [
  ['kompakt', 'lys', 'Kompakt, lys'],
  ['kompakt', 'moerk', 'Kompakt, mørk'],
  ['stor', 'lys', 'Stor, lys'],
  ['stor', 'moerk', 'Stor, mørk'],
];
$example = ['label' => 'Dansk alternativ til', 'name' => 'Stripe'];

partial('head', [
  'title' => $title,
  'description' => $description,
  'canonical' => $url,
  // Varianter med valgt produkt er værktøjssider, ikke indhold
  'robots' => $_GET ? 'noindex, follow' : null,
  'schema' => schema_json([schema_organization(), schema_website($description), schema_breadcrumbs([['Forside', '/'], ['Badge', '/badge']], $url . '#breadcrumb')]),
]);
partial('site-header');
?>
  <main class="mx-auto max-w-7xl px-6 py-12 sm:py-16 lg:px-8">
    <?php partial('breadcrumbs', ['items' => [['Forside', '/'], ['Badge', null]]]); ?>

    <div class="mt-10 grid grid-cols-1 items-center gap-x-16 gap-y-10 lg:grid-cols-2">
      <div>
        <h1 class="text-4xl font-semibold tracking-tight text-pretty text-gray-900 sm:text-5xl dark:text-white">Vis at I er dansk software</h1>
        <p class="mt-6 text-lg/8 text-gray-600 dark:text-gray-400">Er jeres produkt på Den danske tech stack? Sæt et badge på jeres website og vis kunderne, at I er et dansk alternativ. Badget er gratis og helt frivilligt – det er ikke et krav for at være på listen.</p>
      </div>
      <div class="flex flex-col items-start gap-4 rounded-2xl bg-gray-50 p-8 sm:items-center dark:bg-gray-800" aria-hidden="true">
        <?php echo badge_svg($example, 'lys', 'kompakt'); ?>
        <?php echo badge_svg($example, 'lys', 'stor'); ?>
      </div>
    </div>

    <section class="mt-16">
      <h2 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">1. Vælg jeres produkt</h2>
      <form method="get" action="/badge" class="mt-4 flex max-w-xl gap-3">
        <label for="produkt" class="sr-only">Produkt</label>
        <select id="produkt" name="produkt" onchange="this.form.submit()" class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-base text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
          <option value="">Vælg produkt …</option>
          <?php foreach ($products as $item): ?>
          <option value="<?php echo e(product_slug($item)); ?>"<?php echo $product && product_slug($item) === product_slug($product) ? ' selected' : ''; ?>><?php echo e($item['name']); ?></option>
          <?php endforeach; ?>
        </select>
        <noscript><button type="submit" class="rounded-lg bg-gray-900 px-4 py-3 text-sm font-semibold text-white">Vis</button></noscript>
      </form>
      <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">Er jeres produkt ikke på listen endnu? <a href="https://github.com/Boligforeningsweb/dansk-tech#-hvordan-bidrager-du" class="font-semibold text-gray-900 hover:underline dark:text-white">Foreslå det på GitHub</a>.</p>
    </section>

    <?php if ($product && $badge): ?>
    <section class="mt-14">
      <h2 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">2. Vælg badge</h2>
      <p class="mt-2 text-sm/6 text-gray-600 dark:text-gray-400"><span class="font-semibold text-gray-900 dark:text-white">Anbefalet:</span> "Dansk alternativ til …" linker til jeres side på listen.</p>
      <div class="mt-5 flex flex-wrap gap-2">
        <?php foreach ($badges as $item): $active = $item['slug'] === $badge['slug']; ?>
        <a href="/badge?<?php echo e(http_build_query(['produkt' => product_slug($product), 'badge' => $item['slug']])); ?>#udseende"<?php echo $active ? ' aria-current="true"' : ''; ?> class="rounded-full px-3.5 py-1.5 text-sm font-medium <?php echo $active ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'border border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800'; ?>"><?php echo e($item['slug'] === BADGE_GENERIC ? 'Generelt badge' : $item['name']); ?></a>
        <?php endforeach; ?>
      </div>
    </section>

    <section id="udseende" class="mt-14 scroll-mt-6">
      <h2 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">3. Vælg udseende og kopiér koden</h2>
      <p class="mt-2 text-sm/6 text-gray-600 dark:text-gray-400">Badget linker til <a href="<?php echo e($badge['link']); ?>" class="font-semibold text-gray-900 hover:underline dark:text-white"><?php echo e(preg_replace('#^https://#', '', rtrim($badge['link'], '/'))); ?></a>. Brug Markdown-koden i fx en README på GitHub.</p>
      <ul role="list" class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2">
        <?php foreach ($variants as [$format, $theme, $label]): ?>
        <li class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
          <div class="flex h-32 items-center justify-center px-4 <?php echo $theme === 'moerk' ? 'bg-gray-900' : 'bg-gray-50'; ?>"><?php echo badge_svg($badge, $theme, $format); ?></div>
          <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-200 bg-white px-4 py-3 dark:border-gray-700 dark:bg-gray-800">
            <span class="text-sm font-medium text-gray-900 dark:text-white"><?php echo e($label); ?></span>
            <span class="flex gap-2">
              <button type="button" data-copy="<?php echo e(badge_html($product, $badge, $theme, $format)); ?>" class="copy rounded-md bg-gray-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-gray-800 dark:bg-white dark:text-gray-900">Kopiér HTML</button>
              <button type="button" data-copy="<?php echo e(badge_markdown($product, $badge, $theme, $format)); ?>" class="copy rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">Kopiér Markdown</button>
            </span>
          </div>
        </li>
        <?php endforeach; ?>
      </ul>
      <p class="mt-6 text-sm text-gray-600 dark:text-gray-400">HTML for "Kompakt, lys":</p>
      <pre class="mt-2 whitespace-pre-wrap break-all rounded-lg bg-gray-900 p-4 text-xs/5 text-gray-100"><code><?php echo e(badge_html($product, $badge)); ?></code></pre>
    </section>
    <?php endif; ?>

    <section class="mt-16 grid grid-cols-1 gap-8 rounded-2xl bg-gray-50 px-6 py-10 sm:px-10 lg:grid-cols-3 dark:bg-gray-800">
      <div>
        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Koster det noget?</h2>
        <p class="mt-2 text-sm/6 text-gray-600 dark:text-gray-400">Nej. Badget er gratis, og ingen produkter betaler for at være på listen.</p>
      </div>
      <div>
        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Skal vi have badget for at være med?</h2>
        <p class="mt-2 text-sm/6 text-gray-600 dark:text-gray-400">Nej. Det er helt frivilligt og påvirker ikke jeres plads på listen.</p>
      </div>
      <div>
        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Hvor skal det stå?</h2>
        <p class="mt-2 text-sm/6 text-gray-600 dark:text-gray-400">Typisk i footeren, på "Om os"-siden, på en side om datasikkerhed og GDPR eller i en README på GitHub.</p>
      </div>
    </section>
  </main>
<?php partial('footer'); ?>
  <script>
    document.querySelectorAll('.copy').forEach(function(button) {
      button.addEventListener('click', function() {
        navigator.clipboard.writeText(button.dataset.copy).then(function() {
          const text = button.textContent;
          button.textContent = 'Kopieret ✓';
          setTimeout(function() { button.textContent = text; }, 2000);
        });
      });
    });
  </script>
</body>
</html>
