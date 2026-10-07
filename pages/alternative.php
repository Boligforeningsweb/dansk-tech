<?php
if (!defined('APP_ROOT')) { http_response_code(404); exit; }

// Forventer: $alternative fra load_alternatives()
$name = $alternative['name'];
$products = $alternative['products'];
$count = count($products);
$path = alternative_url($name);
$url = SITE_URL . ltrim($path, '/');
$productNames = array_column($products, 'name');
$originalProducts = load_original_products();
$related = related_alternatives($alternative);
$faq = alternative_faq($alternative);
$faqHeading = "Spørgsmål om alternativer til $name";
$faqDisclosure = 'Forklaringerne og vejledningen er udarbejdet med AI ud fra produktbeskrivelserne i vores åbne liste. De er ikke baseret på en selvstændig test af produkterne. Bekræft aktuelle funktioner og vilkår hos leverandøren.';

if ($count === 1) {
  $title = "Dansk alternativ til $name: {$productNames[0]} | Den danske tech stack";
  $heading = "Dansk alternativ til $name";
  $intro = "Leder du efter et dansk alternativ til $name? {$productNames[0]} er foreslået på vores liste til lignende behov. Her kan du læse om produktet og få spørgsmål, der hjælper dig med at vurdere et skifte.";
} else {
  $title = "$count danske alternativer til $name | Den danske tech stack";
  $heading = "Danske alternativer til $name";
  $named = count($productNames) > 5 ? list_names(array_merge(array_slice($productNames, 0, 5), ['flere'])) : list_names($productNames);
  $intro = "Leder du efter et dansk alternativ til $name? Disse $count produkter er foreslået på vores liste til lignende behov: $named. Læs om produkterne og få spørgsmål, der hjælper dig med at vurdere et skifte.";
}
$intro .= ' Produkterne har hovedkontor i Danmark, en dansk stifter eller medstifter, eller er primært dansk ejet.';

// Meta description på maks. ca. 160 tegn: så mange produktnavne, der er plads til
$description = '';
for ($n = min(3, $count); $n >= 1; $n--) {
  $names = list_names($n < $count ? array_merge(array_slice($productNames, 0, $n), ['flere']) : array_slice($productNames, 0, $n));
  $description = "Find danske alternativer til $name: $names. Læs om produkterne og få hjælp til at vurdere et skifte.";
  if (mb_strlen($description) <= 160) {
    break;
  }
}

$breadcrumbs = [['Forside', '/'], ['Alternativer', '/alternativer'], [$name, $path]];

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
    'about' => ['@type' => 'Thing', 'name' => $name],
    'mainEntity' => ['@id' => $url . '#produkter'],
    'hasPart' => ['@id' => $url . '#faq'],
    'publisher' => ['@id' => SITE_URL . '#organization'],
  ],
  schema_breadcrumbs($breadcrumbs, $url . '#breadcrumb'),
  [
    '@type' => 'ItemList',
    '@id' => $url . '#produkter',
    'name' => $heading,
    'numberOfItems' => $count,
    'itemListElement' => schema_product_list_items($products),
  ],
  schema_faq($faq, $url . '#faq') + [
    'url' => $url . '#faq',
    'name' => $faqHeading,
    'description' => 'Skrevet med AI. ' . $faqDisclosure,
    'inLanguage' => 'da-DK',
    'isPartOf' => ['@id' => $url . '#webpage'],
    'publisher' => ['@id' => SITE_URL . '#organization'],
    'isBasedOn' => 'https://github.com/Boligforeningsweb/dansk-tech/blob/main/products.json',
  ],
]);

partial('head', [
  'title' => $title,
  'description' => $description,
  'canonical' => $url,
  'ogImageAlt' => $heading,
  'schema' => $schema,
]);
partial('site-header');
?>
  <main class="mx-auto max-w-7xl px-6 py-12 sm:py-16 lg:px-8">
    <?php partial('breadcrumbs', ['items' => [['Forside', '/'], ['Alternativer', '/alternativer'], [$name, null]]]); ?>

    <div class="mt-10 max-w-3xl">
      <h1 class="text-4xl font-semibold tracking-tight text-pretty text-gray-900 sm:text-5xl dark:text-white"><?php echo e($heading); ?></h1>
      <p class="mt-6 text-lg/8 text-gray-600 dark:text-gray-400"><?php echo e($intro); ?></p>
      <?php partial('trust-bar'); ?>
    </div>

    <ul role="list" class="mt-12 grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
      <?php foreach ($products as $product) {
        partial('product-card', ['product' => $product, 'originalProducts' => $originalProducts, 'headingTag' => 'h2']);
      } ?>
    </ul>

    <section id="faq" aria-labelledby="faq-heading" class="mt-16 max-w-prose sm:mt-20">
      <h2 id="faq-heading" class="text-2xl font-semibold tracking-tight text-pretty text-gray-900 dark:text-white"><?php echo e($faqHeading); ?></h2>
      <p class="mt-4 text-sm/6 text-gray-600 dark:text-gray-400"><strong class="font-semibold text-gray-900 dark:text-white">Skrevet med AI.</strong> <?php echo e($faqDisclosure); ?></p>

      <dl class="mt-8 space-y-8">
        <?php foreach ($faq as $item): ?>
        <div class="border-t border-gray-200 pt-6 dark:border-gray-700">
          <dt class="text-lg/7 font-semibold text-pretty text-gray-900 dark:text-white"><?php echo e($item['q']); ?></dt>
          <dd class="mt-3 space-y-3 text-base/7 text-pretty text-gray-600 dark:text-gray-400">
            <p><?php echo e($item['a']); ?></p>
            <?php if (!empty($item['consideration'])): ?>
            <p><strong class="font-medium text-gray-900 dark:text-white">Inden du vælger:</strong> <?php echo e($item['consideration']); ?></p>
            <?php endif; ?>
            <?php if (isset($item['source'])): ?>
            <p><a href="<?php echo e(outbound_url($item['source']['url'])); ?>" target="_blank" rel="noopener" class="font-medium text-gray-900 underline underline-offset-4 hover:text-gray-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 dark:text-white dark:hover:text-gray-300">Læs mere hos <?php echo e($item['source']['name']); ?> <span aria-hidden="true">↗</span><span class="sr-only"> (åbner i en ny fane)</span></a></p>
            <?php endif; ?>
          </dd>
        </div>
        <?php endforeach; ?>
      </dl>
      <p class="mt-8 text-sm/6 text-gray-600 dark:text-gray-400">Grundlag: <a href="https://github.com/Boligforeningsweb/dansk-tech/blob/main/products.json" class="font-medium text-gray-900 underline underline-offset-4 dark:text-white">produktbeskrivelserne på GitHub</a>. Har du fundet en fejl? <a href="https://github.com/Boligforeningsweb/dansk-tech/issues/new" class="font-medium text-gray-900 underline underline-offset-4 dark:text-white">Foreslå en rettelse</a>.</p>
    </section>

    <?php if ($related): ?>
    <section class="mt-20">
      <h2 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">Danske alternativer til lignende systemer</h2>
      <ul role="list" class="mt-6 flex flex-wrap gap-2">
        <?php foreach ($related as $item): ?>
        <li>
          <a href="<?php echo e(alternative_url($item['name'])); ?>" class="inline-flex items-center gap-2 rounded-full border border-gray-300 bg-white px-3.5 py-1.5 text-sm font-medium text-gray-700 hover:border-gray-400 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
            <?php echo e($item['name']); ?>
            <span class="text-xs text-gray-500 dark:text-gray-400"><?php echo count($item['products']); ?></span>
          </a>
        </li>
        <?php endforeach; ?>
      </ul>
    </section>
    <?php endif; ?>

    <?php partial('about-list'); ?>

    <section class="mt-20 rounded-2xl bg-gray-50 px-6 py-10 sm:px-10 dark:bg-gray-800">
      <h2 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">Kender du et dansk alternativ til <?php echo e($name); ?>?</h2>
      <p class="mt-4 max-w-2xl text-base/7 text-gray-600 dark:text-gray-400">Listen er åben og vedligeholdes på GitHub. Mangler der et dansk produkt, kan du foreslå det med en pull request – det tager et par minutter.</p>
      <div class="mt-6 flex flex-wrap items-center gap-x-6 gap-y-4">
        <a href="https://github.com/Boligforeningsweb/dansk-tech#-hvordan-bidrager-du" class="rounded-md bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100">Foreslå et produkt på GitHub</a>
        <a href="/alternativer" class="text-sm font-semibold text-gray-900 hover:text-gray-700 dark:text-white">Se alle alternativer <span aria-hidden="true">→</span></a>
        <a href="/badge" class="text-sm font-semibold text-gray-900 hover:text-gray-700 dark:text-white">Er I på listen? Hent et badge <span aria-hidden="true">→</span></a>
      </div>
    </section>
  </main>
<?php partial('footer'); ?>
</body>
</html>
