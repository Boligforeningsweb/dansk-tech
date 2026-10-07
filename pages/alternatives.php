<?php
if (!defined('APP_ROOT')) { http_response_code(404); exit; }

$alternatives = load_alternatives();
$total = count($alternatives);
$url = SITE_URL . 'alternativer';

$popular = $alternatives;
uasort($popular, function($a, $b) {
  return [count($b['products']), $a['name']] <=> [count($a['products']), $b['name']];
});
$popular = array_slice($popular, 0, 12, true);

// A–Å, tal og tegn samles under "#"
$letters = [];
foreach ($alternatives as $alternative) {
  $first = mb_strtoupper(mb_substr($alternative['name'], 0, 1));
  $letters[preg_match('/\p{L}/u', $first) ? $first : '#'][] = $alternative;
}
uksort($letters, function($a, $b) {
  return [$a === '#', $a] <=> [$b === '#', $b];
});

$title = 'Danske alternativer til udenlandsk software A–Å | Den danske tech stack';
$description = "Find danske alternativer til $total udenlandske systemer – fra Stripe og Shopify til Mailchimp og Zendesk. Dansk software, kurateret af danske iværksættere.";
$breadcrumbs = [['Forside', '/'], ['Alternativer', '/alternativer']];

$schema = schema_json([
  schema_organization(),
  schema_website(),
  [
    '@type' => 'CollectionPage',
    '@id' => $url . '#webpage',
    'url' => $url,
    'name' => $title,
    'description' => $description,
    'inLanguage' => 'da-DK',
    'isPartOf' => ['@id' => SITE_URL . '#website'],
    'breadcrumb' => ['@id' => $url . '#breadcrumb'],
  ],
  schema_breadcrumbs($breadcrumbs, $url . '#breadcrumb'),
]);

partial('head', [
  'title' => $title,
  'description' => $description,
  'canonical' => $url,
  'schema' => $schema,
]);
partial('site-header');
?>
  <main class="mx-auto max-w-7xl px-6 py-12 sm:py-16 lg:px-8">
    <?php partial('breadcrumbs', ['items' => [['Forside', '/'], ['Alternativer', null]]]); ?>

    <div class="mt-10 max-w-3xl">
      <h1 class="text-4xl font-semibold tracking-tight text-pretty text-gray-900 sm:text-5xl dark:text-white">Danske alternativer til udenlandsk software</h1>
      <p class="mt-6 text-lg/8 text-gray-600 dark:text-gray-400">Find det system, du bruger i dag, og se hvilke danske produkter der er foreslået som alternativer. Vi har forslag til <?php echo $total; ?> systemer. Undersøg funktioner og integrationer hos leverandøren, før du vælger.</p>
      <?php partial('trust-bar'); ?>
    </div>

    <section class="mt-14">
      <h2 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">Flest danske alternativer</h2>
      <ul role="list" class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
        <?php foreach ($popular as $alternative): ?>
        <li>
          <a href="<?php echo e(alternative_url($alternative['name'])); ?>" class="block rounded-lg border border-gray-200 bg-white p-4 hover:border-gray-300 hover:shadow-md transition-all duration-200 dark:border-gray-700 dark:bg-gray-800 dark:hover:border-gray-600">
            <span class="block truncate text-base font-semibold text-gray-900 dark:text-white"><?php echo e($alternative['name']); ?></span>
            <span class="mt-1 block text-sm text-gray-600 dark:text-gray-400"><?php echo count($alternative['products']); ?> danske alternativer</span>
          </a>
        </li>
        <?php endforeach; ?>
      </ul>
    </section>

    <section class="mt-16">
      <h2 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">Alle udenlandske systemer A–Å</h2>
      <nav aria-label="Spring til bogstav" class="mt-6 flex flex-wrap gap-1.5">
        <?php foreach (array_keys($letters) as $letter): ?>
        <a href="#bogstav-<?php echo e($letter === '#' ? 'tal' : mb_strtolower($letter)); ?>" class="inline-flex size-9 items-center justify-center rounded-md border border-gray-200 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"><?php echo e($letter); ?></a>
        <?php endforeach; ?>
      </nav>

      <div class="mt-10 space-y-10">
        <?php foreach ($letters as $letter => $items): ?>
        <div id="bogstav-<?php echo e($letter === '#' ? 'tal' : mb_strtolower($letter)); ?>" class="scroll-mt-6">
          <h3 class="border-b border-gray-200 pb-2 text-lg font-semibold text-gray-900 dark:border-white/10 dark:text-white"><?php echo e($letter); ?></h3>
          <ul role="list" class="mt-4 grid grid-cols-1 gap-x-8 gap-y-2 sm:grid-cols-2 lg:grid-cols-4">
            <?php foreach ($items as $alternative): ?>
            <li>
              <a href="<?php echo e(alternative_url($alternative['name'])); ?>" class="group flex items-baseline justify-between gap-3 text-sm text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                <span class="truncate group-hover:underline"><?php echo e($alternative['name']); ?></span>
                <span class="shrink-0 text-xs text-gray-500 dark:text-gray-400"><?php echo count($alternative['products']); ?></span>
              </a>
            </li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php endforeach; ?>
      </div>
    </section>

    <?php partial('about-list'); ?>
  </main>
<?php partial('footer'); ?>
</body>
</html>
