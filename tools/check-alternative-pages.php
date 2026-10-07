<?php
// Indeksering, sitemap og produktforklaringer. Kør: php tools/check-alternative-pages.php
if (PHP_SAPI !== 'cli') {
  http_response_code(404);
  exit;
}

// Isolerede fil-fixtures køres i en ny proces, så den statiske guide-cache er tom.
if (($argv[1] ?? '') === '--guide-fixture') {
  define('APP_ROOT', $argv[2]);
  require APP_ROOT . '/lib/bootstrap.php';
  $product = load_json('products.json')[0];
  check(product_guide($product) === load_json('expected.json'), 'Produktguide eller fallback afviger i fil-fixture.');
  exit;
}

define('APP_ROOT', dirname(__DIR__));

// Render uden netværkskald eller ændringer i sitets normale cache.
$cacheDir = sys_get_temp_dir() . '/dts-alternative-check-' . bin2hex(random_bytes(8));
mkdir($cacheDir);
file_put_contents($cacheDir . '/contributors.json', '[]');
$_SERVER['DTS_CACHE_DIR'] = $cacheDir;
register_shutdown_function(function() use ($cacheDir) {
  $fixtureRoot = $cacheDir . '/fixture';
  foreach (['data/product-guides.json', 'products.json', 'expected.json', 'lib'] as $file) {
    if (file_exists($fixtureRoot . '/' . $file)) {
      unlink($fixtureRoot . '/' . $file);
    }
  }
  if (is_dir($fixtureRoot)) {
    rmdir($fixtureRoot . '/data');
    rmdir($fixtureRoot);
  }
  unlink($cacheDir . '/contributors.json');
  rmdir($cacheDir);
});
require APP_ROOT . '/lib/bootstrap.php';

function check($condition, $message) {
  if (!$condition) {
    fwrite(STDERR, "FEJL: $message\n");
    exit(1);
  }
}

function page_html($page, array $vars = [], $status = 200) {
  ob_start();
  render($page, $vars, $status);
  return ob_get_clean();
}

function html_xpath($html) {
  $previous = libxml_use_internal_errors(true);
  $document = new DOMDocument();
  $document->loadHTML($html, LIBXML_NONET);
  libxml_clear_errors();
  libxml_use_internal_errors($previous);
  return new DOMXPath($document);
}

function schema_graph($html) {
  preg_match('#<script type="application/ld\+json">\s*(.*?)\s*</script>#s', $html, $match);
  check(isset($match[1]), 'JSON-LD mangler.');
  $schema = json_decode($match[1], true);
  check(is_array($schema) && ($schema['@context'] ?? '') === 'https://schema.org', 'JSON-LD har ugyldigt format.');
  return array_column($schema['@graph'], null, '@type');
}

function normalized_text($text) {
  return trim(preg_replace('/\s+/u', ' ', $text));
}

function check_page_ids($html, $label) {
  $xpath = html_xpath($html);
  $ids = array_map(fn($node) => $node->nodeValue, iterator_to_array($xpath->query('//@id')));
  check(count(array_unique($ids)) === count($ids), "$label har dublerede element-ID'er.");
  foreach ($xpath->query('//label/@for') as $for) {
    check(in_array($for->nodeValue, $ids, true), "$label har en formularlabel uden et tilhørende felt.");
  }
}

$products = load_products();
$alternatives = load_alternatives();
$guides = load_json('data/product-guides.json');
check(count($alternatives) > 0, 'Listen over alternativsider er tom.');
foreach ($guides as $url => $guide) {
  check(is_array($guide), "Guiden til $url har ugyldigt format.");
  foreach (['sourceHash', 'explanation', 'consideration'] as $field) {
    check(is_string($guide[$field] ?? null) && trim($guide[$field]) !== '', "Guiden til $url mangler $field.");
  }
  check(preg_match('/^[a-f0-9]{64}$/', $guide['sourceHash']) === 1, "Guiden til $url har ugyldig sourceHash.");
}
$activeGuides = 0;
$fallbackNames = [];
foreach ($products as $product) {
  $guide = product_guide($product);
  if (isset($guide['sourceHash'])) {
    $activeGuides++;
  } else {
    $fallbackNames[] = $product['name'];
  }
}
if ($fallbackNames) {
  fwrite(STDERR, 'ADVARSEL: Følgende produkter bruger deres aktuelle beskrivelse, indtil AI-guiden er opdateret: ' . implode(', ', $fallbackNames) . "\n");
}

// Nye produkter og ændrede kildedata må aldrig få en gammel AI-forklaring.
$fixture = ['name' => 'Nyt produkt', 'url' => 'https://example.invalid', 'description' => 'Ny produktbeskrivelse.'];
check(product_guide($fixture) === ['explanation' => $fixture['description'], 'consideration' => null], 'Fallback for nyt produkt fejler.');
$withGuide = array_values(array_filter($products, fn($p) => isset($guides[$p['url']])));
check(count($withGuide) > 0, 'Ingen produktguides blev indlæst.');
$fixture = $withGuide[0];
$fixture['description'] = 'Opdateret beskrivelse med <script> og & tegn.';
check(product_guide($fixture) === ['explanation' => $fixture['description'], 'consideration' => null], 'En ændret beskrivelse bruger stadig gammel AI-tekst.');
$fixture = $withGuide[0];
$fixture['name'] .= ' nyt navn';
check(product_guide($fixture)['explanation'] === $fixture['description'], 'Et ændret navn bruger stadig gammel AI-tekst.');
$fixture = $withGuide[0];
$fixture['url'] = 'https://example.invalid/ny-adresse';
check(product_guide($fixture)['explanation'] === $fixture['description'], 'En ændret URL bruger stadig gammel AI-tekst.');

// Manglende, ugyldige og forældede guidefiler skal give den aktuelle beskrivelse.
$fixtureRoot = $cacheDir . '/fixture';
mkdir($fixtureRoot . '/data', 0777, true);
symlink(APP_ROOT . '/lib', $fixtureRoot . '/lib');
$fixtureProduct = $withGuide[0];
file_put_contents($fixtureRoot . '/products.json', json_encode([$fixtureProduct]));
$fallback = ['explanation' => $fixtureProduct['description'], 'consideration' => null];
$validGuide = $guides[$fixtureProduct['url']];
$validGuide['sourceHash'] = hash('sha256', $fixtureProduct['name'] . "\n" . $fixtureProduct['description']);
$guideFixtures = [
  [null, $fallback],
  ['{ ugyldig JSON', $fallback],
  ['"forkert rodtype"', $fallback],
  [json_encode([$fixtureProduct['url'] => null]), $fallback],
  [json_encode([$fixtureProduct['url'] => array_replace($validGuide, ['explanation' => []])]), $fallback],
  [json_encode([$fixtureProduct['url'] => array_replace($validGuide, ['consideration' => '  '])]), $fallback],
  [json_encode([$fixtureProduct['url'] => array_replace($validGuide, ['sourceHash' => str_repeat('0', 64)])]), $fallback],
  [json_encode([$fixtureProduct['url'] => $validGuide]), $validGuide],
];
foreach ($guideFixtures as [$contents, $expected]) {
  $guideFile = $fixtureRoot . '/data/product-guides.json';
  if ($contents === null) {
    if (file_exists($guideFile)) unlink($guideFile);
  } else {
    file_put_contents($guideFile, $contents);
  }
  file_put_contents($fixtureRoot . '/expected.json', json_encode($expected));
  $process = proc_open([PHP_BINARY, __FILE__, '--guide-fixture', $fixtureRoot], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
  check(is_resource($process), 'Kunne ikke starte guide-fixture.');
  $output = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
  fclose($pipes[1]);
  fclose($pipes[2]);
  check(proc_close($process) === 0 && $output === '', 'Guide-fixture fejlede: ' . $output);
}

$sitemap = simplexml_load_string(page_html('sitemap'));
check($sitemap !== false, 'Sitemap er ikke gyldig XML.');
$sitemapUrls = array_map(fn($node) => (string) $node->loc, iterator_to_array($sitemap->url, false));
check(count($sitemapUrls) === count($alternatives) + 3, 'Sitemap mangler sider eller indeholder ekstra sider.');
check(count(array_unique($sitemapUrls)) === count($sitemapUrls), 'Sitemap indeholder dubletter.');
check(count($sitemap->xpath('//*[local-name()="lastmod"]')) === 0, 'Sitemap bruger igen upålidelige filændringsdatoer.');

$titles = [];
$descriptions = [];

foreach ($alternatives as $alternative) {
  $url = SITE_URL . ltrim(alternative_url($alternative['name']), '/');
  check(in_array($url, $sitemapUrls, true), "$url mangler i sitemap.");
  $html = page_html('alternative', ['alternative' => $alternative]);
  check(str_contains($html, '<meta name="robots" content="index, follow,'), "$url tillader ikke indeksering.");
  check(!str_contains($html, 'noindex'), "$url indeholder noindex.");
  check(str_contains($html, '<link rel="canonical" href="' . e($url) . '"'), "$url mangler korrekt canonical.");
  check(str_contains($html, 'Skrevet med AI.'), "$url mangler AI-deklaration.");
  check(substr_count($html, '<dt ') === count($alternative['products']) + 2, "$url mangler FAQ-spørgsmål.");
  foreach ($alternative['products'] as $product) {
    check(str_contains($html, e(product_guide($product)['explanation'])), "$url mangler forklaring af {$product['name']}.");
  }
  $xpath = html_xpath($html);
  check_page_ids($html, $url);
  check($xpath->query('//h1')->length === 1, "$url skal have præcis én H1.");
  $title = $xpath->evaluate('string(//head/title)');
  $description = $xpath->evaluate('string(//head/meta[@name="description"]/@content)');
  check($title !== '' && str_contains($title, $alternative['name']), "$url har en mangelfuld sidetitel.");
  check($description !== '' && str_contains($description, $alternative['name']), "$url har en mangelfuld meta description.");
  check($xpath->evaluate('string(//html/@lang)') === 'da', "$url mangler korrekt sprogkode.");
  check($xpath->query('//head/link[@rel="canonical"]')->length === 1, "$url har flere canonical-tags.");
  check($xpath->evaluate('string(//head/meta[@property="og:url"]/@content)') === $url, "$url har forkert Open Graph-URL.");
  check($xpath->evaluate('string(//head/meta[@property="og:title"]/@content)') === $title, "$url har en afvigende Open Graph-titel.");
  check($xpath->evaluate('string(//head/meta[@name="twitter:card"]/@content)') === 'summary_large_image', "$url mangler Twitter-kort.");
  $titles[] = $title;
  $descriptions[] = $description;

  $graph = schema_graph($html);
  foreach (['Organization', 'WebSite', 'CollectionPage', 'BreadcrumbList', 'ItemList', 'FAQPage'] as $type) {
    check(isset($graph[$type]), "$url mangler $type-schema.");
  }
  check($graph['WebSite'] === schema_website(), "$url bruger en afvigende WebSite-identitet.");
  check($graph['CollectionPage']['url'] === $url, "$url har forkert URL i side-schema.");
  check($graph['CollectionPage']['name'] === $title && $graph['CollectionPage']['description'] === $description, "$url har metadata, der afviger fra side-schema.");
  check($graph['CollectionPage']['mainEntity']['@id'] === $graph['ItemList']['@id'], "$url forbinder ikke side og produktliste.");
  check($graph['CollectionPage']['hasPart']['@id'] === $graph['FAQPage']['@id'], "$url forbinder ikke side og FAQ.");
  check($graph['CollectionPage']['breadcrumb']['@id'] === $graph['BreadcrumbList']['@id'], "$url forbinder ikke side og brødkrummer.");
  check(!isset($graph['CollectionPage']['dateModified']), "$url bruger en upålidelig ændringsdato.");
  foreach ($graph['BreadcrumbList']['itemListElement'] as $i => $item) {
    check($item['position'] === $i + 1, "$url har forkert rækkefølge i brødkrummer.");
  }
  check($graph['BreadcrumbList']['itemListElement'][2]['item'] === $url, "$url har forkert URL i sidste brødkrumme.");
  check($graph['ItemList']['numberOfItems'] === count($alternative['products']), "$url har forkert antal produkter i schema.");
  foreach ($graph['ItemList']['itemListElement'] as $i => $item) {
    $product = $alternative['products'][$i];
    check($item['position'] === $i + 1 && $item['item']['@type'] === 'SoftwareApplication', "$url har ugyldigt produkt-schema.");
    foreach (['name', 'url', 'description'] as $field) {
      check($item['item'][$field] === $product[$field], "$url har afvigende $field i produkt-schema.");
    }
  }

  // Sammenlign med den renderede FAQ, ikke kun med PHP-inputdataene.
  $questions = $xpath->query('//section[@id="faq"]/dl/div/dt');
  $answers = $xpath->query('//section[@id="faq"]/dl/div/dd');
  $schemaQuestions = $graph['FAQPage']['mainEntity'];
  check(count($schemaQuestions) === $questions->length, "$url har afvigende antal FAQ-spørgsmål i schema.");
  check($graph['FAQPage']['name'] === $xpath->evaluate('string(//*[@id="faq-heading"])'), "$url har en afvigende FAQ-overskrift i schema.");
  check($graph['FAQPage']['description'] === normalized_text($xpath->evaluate('string(//section[@id="faq"]/p[1])')), "$url mangler AI-deklarationen i schema.");
  foreach ($schemaQuestions as $i => $question) {
    check($question['@type'] === 'Question' && $question['acceptedAnswer']['@type'] === 'Answer', "$url har ugyldige FAQ-typer.");
    check($question['name'] === $questions->item($i)->textContent, "$url har et spørgsmål, der afviger fra den synlige FAQ.");
    $answerNode = $answers->item($i);
    foreach (iterator_to_array($xpath->query('.//span[@aria-hidden="true" or contains(concat(" ", normalize-space(@class), " "), " sr-only ")]', $answerNode)) as $span) {
      $span->parentNode->removeChild($span);
    }
    check(normalized_text($question['acceptedAnswer']['text']) === normalized_text($answerNode->textContent), "$url har et svar, der afviger fra den synlige FAQ.");
    if ($i < count($alternative['products'])) {
      check($question['acceptedAnswer']['citation']['url'] === $alternative['products'][$i]['url'], "$url har forkert leverandørhenvisning i FAQ-schema.");
    }
  }
}
check(count(array_unique($titles)) === count($titles), 'Alternativsiderne har dublerede sidetitler.');
check(count(array_unique($descriptions)) === count($descriptions), 'Alternativsiderne har dublerede meta descriptions.');

// Samme WebSite-entity på forside, oversigt og badge-side.
foreach (['home', 'alternatives', 'badge'] as $page) {
  $html = page_html($page);
  check(schema_graph($html)['WebSite'] === schema_website(), "$page bruger en afvigende WebSite-identitet.");
  check_page_ids($html, $page);
}

$fixture['description'] = 'Beskrivelse med <script> og & tegn.';
$escaped = page_html('alternative', ['alternative' => ['name' => 'Test <script>', 'slug' => 'test-script', 'products' => [$fixture]]]);
check(!str_contains($escaped, 'Test <script>'), 'Produktnavne escapes ikke i FAQ.');
check(str_contains($escaped, '<p>' . e($fixture['description']) . '</p>'), 'Fallback-beskrivelsen escapes ikke i FAQ.');
check(str_contains(page_html('404', [], 404), 'noindex, follow'), '404-siden har mistet noindex.');
check(http_response_code() === 404, '404-siden returnerer ikke HTTP 404.');

echo 'OK: ' . count($alternatives) . ' alternativsider tillader indeksering, er med i sitemap og har unikke metadata. Side-, produkt-, brødkrumme- og FAQ-schema matcher indholdet. ' . $activeGuides . '/' . count($products) . " produkter bruger en aktuel AI-guide. Fallback for nye/ændrede produkter og manglende/ugyldige guidefiler samt 404 kontrolleret.\n";
