<?php
if (!defined('APP_ROOT')) { http_response_code(404); exit; }

$products = load_products();
$product = isset($_GET['produkt']) ? find_product_by_slug((string) $_GET['produkt']) : null;
$badges = $product ? product_badges($product) : [];
$badge = $badges[(string) ($_GET['badge'] ?? '')] ?? ($badges ? reset($badges) : null);
$supporter = supporter_badges()['vi-stoetter-dansk-tech'];

$url = SITE_URL . 'badge';
$title = 'Badges til jeres website | Den danske tech stack';
$description = 'Gratis badges til jeres website: "Dansk alternativ til Stripe" til produkterne på listen, og "Vi støtter dansk tech" til alle andre.';
$example = ['label' => 'Dansk alternativ til', 'name' => 'Stripe'];

partial('head', [
  'title' => $title,
  'description' => $description,
  'canonical' => $url,
  // Varianter med valgt produkt er værktøjssider, ikke indhold
  'robots' => $_GET ? 'noindex, follow' : null,
  'schema' => schema_json([schema_organization(), schema_website(), schema_breadcrumbs([['Forside', '/'], ['Badge', '/badge']], $url . '#breadcrumb')]),
]);
partial('site-header');
?>
  <main class="mx-auto max-w-7xl px-6 py-12 sm:py-16 lg:px-8">
    <?php partial('breadcrumbs', ['items' => [['Forside', '/'], ['Badge', null]]]); ?>

    <div class="mt-10 max-w-3xl">
      <h1 class="text-4xl font-semibold tracking-tight text-pretty text-gray-900 sm:text-5xl dark:text-white">Badges til jeres website</h1>
      <p class="mt-6 text-lg/8 text-gray-600 dark:text-gray-400">Sæt et gratis badge på jeres website. Badgesene er helt frivillige – ingen betaler for at være på listen, og ingen skal have et badge for at komme med.</p>
    </div>

    <div class="mt-10 grid grid-cols-1 gap-6 lg:grid-cols-2">
      <a href="#produkt" class="group rounded-2xl border border-gray-200 p-6 hover:border-gray-300 hover:shadow-md transition-all duration-200 dark:border-gray-700">
        <p class="text-base font-semibold text-gray-900 dark:text-white">Er jeres produkt på listen?</p>
        <p class="mt-1 text-sm/6 text-gray-600 dark:text-gray-400">Vis kunderne, at I er et dansk alternativ.</p>
        <div class="mt-5" aria-hidden="true"><?php echo badge_svg(['label' => 'Dansk alternativ til', 'name' => 'Stripe'], 'lys', 'kompakt'); ?></div>
      </a>
      <a href="#stoette" class="group rounded-2xl border border-gray-200 p-6 hover:border-gray-300 hover:shadow-md transition-all duration-200 dark:border-gray-700">
        <p class="text-base font-semibold text-gray-900 dark:text-white">Vil I støtte dansk tech?</p>
        <p class="mt-1 text-sm/6 text-gray-600 dark:text-gray-400">Til alle virksomheder – også jer, der ikke selv laver software.</p>
        <div class="mt-5" aria-hidden="true"><?php echo badge_svg($supporter, 'lys', 'kompakt'); ?></div>
      </a>
    </div>

    <section id="produkt" class="mt-20 scroll-mt-6 border-t border-gray-200 pt-16 dark:border-white/10">
      <p class="text-sm font-semibold text-gray-500 dark:text-gray-400">Til produkterne på listen</p>
      <h2 class="mt-2 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">Er jeres produkt på listen?</h2>
      <h3 class="mt-8 text-lg font-semibold text-gray-900 dark:text-white">1. Vælg jeres produkt</h3>
      <form method="get" action="/badge#produkt" class="mt-4 flex max-w-xl gap-3">
        <label for="produkt-valg" class="sr-only">Produkt</label>
        <select id="produkt-valg" name="produkt" onchange="this.form.submit()" class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-base text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
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
    <section class="mt-10">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white">2. Vælg badge</h3>
      <p class="mt-2 text-sm/6 text-gray-600 dark:text-gray-400"><span class="font-semibold text-gray-900 dark:text-white">Anbefalet:</span> "Dansk alternativ til …" linker til jeres side på listen.</p>
      <div class="mt-5 flex flex-wrap gap-2">
        <?php foreach ($badges as $item): $active = $item['slug'] === $badge['slug']; ?>
        <a href="/badge?<?php echo e(http_build_query(['produkt' => product_slug($product), 'badge' => $item['slug']])); ?>#udseende"<?php echo $active ? ' aria-current="true"' : ''; ?> class="rounded-full px-3.5 py-1.5 text-sm font-medium <?php echo $active ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'border border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800'; ?>"><?php echo e($item['slug'] === BADGE_GENERIC ? 'Generelt badge' : $item['name']); ?></a>
        <?php endforeach; ?>
      </div>
    </section>

    <section id="udseende" class="mt-10 scroll-mt-6">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white">3. Vælg udseende og kopiér koden</h3>
      <p class="mt-2 text-sm/6 text-gray-600 dark:text-gray-400">Badget linker til <a href="<?php echo e($badge['link']); ?>" class="font-semibold text-gray-900 hover:underline dark:text-white"><?php echo e(preg_replace('#^https://#', '', rtrim($badge['link'], '/'))); ?></a>. Brug Markdown-koden i fx en README på GitHub.</p>
      <?php partial('badge-variants', ['product' => $product, 'badge' => $badge]); ?>
    </section>
    <?php endif; ?>

    <section id="stoette" class="mt-20 scroll-mt-6 border-t border-gray-200 pt-16 dark:border-white/10">
      <p class="text-sm font-semibold text-gray-500 dark:text-gray-400">Til alle virksomheder</p>
      <h2 class="mt-2 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">Vil I støtte dansk tech?</h2>
      <p class="mt-4 max-w-2xl text-base/7 text-gray-600 dark:text-gray-400">Badget linker til listen, så andre også kan finde gode danske systemer – uanset om I selv laver software eller ej.</p>
      <?php partial('badge-variants', ['product' => null, 'badge' => $supporter]); ?>
    </section>

    <section class="mt-16 grid grid-cols-1 gap-8 rounded-2xl bg-gray-50 px-6 py-10 sm:px-10 lg:grid-cols-3 dark:bg-gray-800">
      <div>
        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Koster det noget?</h2>
        <p class="mt-2 text-sm/6 text-gray-600 dark:text-gray-400">Nej. Badget er gratis, og ingen produkter betaler for at være på listen.</p>
      </div>
      <div>
        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Skal vi have badget for at være med?</h2>
        <p class="mt-2 text-sm/6 text-gray-600 dark:text-gray-400">Nej. Det er helt frivilligt og påvirker ikke jeres plads på listen. "Vi støtter dansk tech" kan alle bruge.</p>
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
