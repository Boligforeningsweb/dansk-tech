<?php
// Kør: php tools/check-localization.php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('APP_ROOT', dirname(__DIR__));
$cache = sys_get_temp_dir() . '/dts-languages-' . bin2hex(random_bytes(8));
mkdir($cache);
file_put_contents($cache . '/contributors.json', '[]');
$_SERVER['DTS_CACHE_DIR'] = $cache;
register_shutdown_function(function() use ($cache) {
  unlink($cache . '/contributors.json');
  rmdir($cache);
});
require APP_ROOT . '/lib/bootstrap.php';
function check($condition, $message) {
  if (!$condition) { fwrite(STDERR, "FEJL: $message\n"); exit(1); }
}
function page_document($page, $path, array $vars = [], array $query = []) {
  $_SERVER['REQUEST_URI'] = $path;
  $_GET = $query;
  ob_start(); render($page, $vars); $html = ob_get_clean();
  $document = new DOMDocument();
  $previous = libxml_use_internal_errors(true);
  $document->loadHTML($html, LIBXML_NONET);
  libxml_clear_errors(); libxml_use_internal_errors($previous);
  return [$html, new DOMXPath($document)];
}
function check_page($page, $path, array $vars = []) {
  [$html, $xpath] = page_document($page, $path, $vars);
  $language = str_starts_with($path, '/en') ? 'en' : 'da';
  check($xpath->evaluate('string(/html/@lang)') === $language, "$path: forkert HTML-sprog.");
  check($xpath->evaluate('string(//link[@rel="canonical"]/@href)') === SITE_URL . ltrim($path, '/'), "$path: forkert canonical.");
  foreach (['da', 'en'] as $target) {
    check($xpath->query('//head/link[@rel="alternate" and @hreflang="' . $target . '"]')->length === 1, "$path: manglende eller dubleret hreflang for $target.");
    check($xpath->evaluate('string(//link[@hreflang="' . $target . '"]/@href)') === locale_url($path, $target), "$path: forkert hreflang for $target.");
  }
  check($xpath->evaluate('string(//head/link[@hreflang="x-default"]/@href)') === locale_url($path, 'da'), "$path: forkert x-default.");
  check(!str_contains($xpath->evaluate('string(//meta[@name="robots"]/@content)'), 'noindex'), "$path: siden kan ikke indekseres.");
  $title = $xpath->evaluate('string(//head/title)');
  $description = $xpath->evaluate('string(//meta[@name="description"]/@content)');
  check(trim($title) !== '' && trim($description) !== '', "$path: tom titel eller meta description.");
  foreach (['og:title' => $title, 'og:url' => locale_url($path), 'og:type' => 'website',
      'og:locale' => $language === 'en' ? 'en_GB' : 'da_DK',
      'og:locale:alternate' => $language === 'en' ? 'da_DK' : 'en_GB',
      'og:site_name' => t('Den danske tech stack')] as $property => $expected) {
    check($xpath->query('//head/meta[@property="' . $property . '"]')->length === 1, "$path: manglende eller dubleret $property.");
    check($xpath->evaluate('string(//meta[@property="' . $property . '"]/@content)') === $expected, "$path: forkert $property.");
  }
  $socialDescription = $xpath->evaluate('string(//meta[@property="og:description"]/@content)');
  check(trim($socialDescription) !== '', "$path: tom Open Graph-beskrivelse.");
  check($xpath->evaluate('string(//meta[@name="twitter:description"]/@content)') === $socialDescription, "$path: Twitter-beskrivelsen afviger fra Open Graph.");
  check($xpath->evaluate('string(//meta[@name="twitter:title"]/@content)') === $title, "$path: forkert Twitter-titel.");
  check($xpath->evaluate('string(//meta[@name="twitter:card"]/@content)') === 'summary_large_image', "$path: forkert Twitter-kort.");
  $image = $xpath->evaluate('string(//meta[@property="og:image"]/@content)');
  $imageFile = $language === 'en' ? 'og-image-en.jpg' : 'og-image.jpg';
  check($image === SITE_URL . ltrim(asset_url($imageFile), '/'), "$path: forkert delingsbillede eller manglende versionshash.");
  check($xpath->evaluate('string(//meta[@name="twitter:image"]/@content)') === $image, "$path: forkert Twitter-billede.");
  $imageAlt = $xpath->evaluate('string(//meta[@property="og:image:alt"]/@content)');
  check(trim($imageAlt) !== '' && $xpath->evaluate('string(//meta[@name="twitter:image:alt"]/@content)') === $imageAlt, "$path: manglende eller afvigende billedtekst.");
  check($xpath->evaluate('string(//meta[@property="og:image:type"]/@content)') === 'image/jpeg'
    && $xpath->evaluate('string(//meta[@property="og:image:width"]/@content)') === '1200'
    && $xpath->evaluate('string(//meta[@property="og:image:height"]/@content)') === '630', "$path: forkerte billedmetadata.");
  $manifestFile = $language === 'en' ? 'site-en.webmanifest' : 'site.webmanifest';
  check($xpath->evaluate('string(//link[@rel="manifest"]/@href)') === '/' . $manifestFile, "$path: forkert manifest-sprog.");
  check($xpath->query('//nav[contains(@class,"language-switcher") and @aria-label]//a[@aria-current="true"]')->length === 1, "$path: sprogvælgeren mangler valgt sprog.");
  foreach ($xpath->query('//a[starts-with(@href,"/") and not(@data-language-link)]/@href') as $href) {
    check(locale_path($href->nodeValue, $language) === $href->nodeValue, "$path: link skifter utilsigtet sprog: $href->nodeValue");
  }
  $visibleText = $title . $description;
  foreach ($xpath->query('//body//text()[not(ancestor::script) and not(ancestor::style)]') as $textNode) {
    $visibleText .= $textNode->textContent;
  }
  check(!str_contains($visibleText, '{count}') && !str_contains($visibleText, '{products}'), "$path: uerstattede parametre.");
  check($xpath->query('//main[@id="main-content"]')->length === 1, "$path: hovedindhold mangler et entydigt landmark.");
  if ($language === 'en') {
    foreach ($xpath->query('//h1 | //h2 | //nav | //strong') as $node) {
      check(!preg_match('/\b(?:Anbefalet af|danske alternativer|jeres produkt|Inden du vælger|Skrevet med AI)\b/u', $node->textContent), "$path: uoversat grænseflade.");
    }
  }
  preg_match('#<script type="application/ld\+json">\s*(.*?)\s*</script>#s', $html, $match);
  $schema = json_decode($match[1] ?? '', true);
  check(is_array($schema), "$path: ugyldig JSON-LD.");
  foreach ($schema['@graph'] as $node) {
    if (($node['@type'] ?? '') === 'CollectionPage') {
      check($node['url'] === locale_url($path), "$path: forkert URL i sideschema.");
      check($node['inLanguage'] === language_tag(), "$path: forkert sprog i sideschema.");
    }
  }
}
$products = load_products();
$translations = load_json('data/translations/products.en.json');
$guides = load_json('data/product-guides.json');
$englishGuides = load_json('data/translations/product-guides.en.json');
$_SERVER['REQUEST_URI'] = '/en';
$translatedCount = 0;
$warnings = [];
foreach ($products as $product) {
  $translation = $translations[$product['url']] ?? [];
  if (product_translation($product) === null) {
    check(product_description($product) === $product['description'], "Forældet oversættelse vises: {$product['name']}");
    $warnings[] = $product['name'];
    continue;
  }
  $translatedCount++;
  check(trim(product_description($product)) !== '', "Tom oversættelse: {$product['name']}");
  $guide = $guides[$product['url']] ?? [];
  if (($guide['sourceHash'] ?? '') === ($translation['sourceHash'] ?? null)) {
    $english = $englishGuides[$product['url']] ?? [];
    if (($english['guideHash'] ?? '') === hash('sha256', $guide['explanation'] . "\n" . $guide['consideration']) && ($english['sourceHash'] ?? '') === $guide['sourceHash']) {
      check(product_guide($product) === $english, "Engelsk FAQ vises ikke: {$product['name']}");
    } else {
      check(product_guide($product) === ['explanation' => product_description($product), 'consideration' => null], "Forældet engelsk FAQ vises: {$product['name']}");
    }
  }
}
$changed = $products[0];
$changed['description'] .= ' changed source';
check(product_description($changed) === $changed['description'], 'En ændret beskrivelse genbruger gammel engelsk tekst.');
check(product_guide($changed) === ['explanation' => $changed['description'], 'consideration' => null], 'En ændret beskrivelse genbruger gammel engelsk FAQ.');
check(product_description_language($changed) === 'da', 'Dansk fallback får forkert sprogmærkning.');
$originalProducts = [];
ob_start(); partial('product-card', ['product' => $changed, 'originalProducts' => $originalProducts]); $fallbackCard = ob_get_clean();
check(str_contains($fallbackCard, '<p lang="da"') && str_contains($fallbackCard, 'This description is in Danish.'), 'Dansk fallback mangler synlig forklaring eller lang-attribut.');
check(schema_product_list_items([$changed])[0]['item']['description'] === ['@value' => $changed['description'], '@language' => 'da'], 'Dansk fallback er forkert mærket i JSON-LD.');
$fallbackFaq = alternative_faq(['name' => 'Fixture', 'products' => [$changed]]);
check($fallbackFaq[0]['language'] === 'da' && schema_faq($fallbackFaq, '#fixture')['mainEntity'][0]['acceptedAnswer']['inLanguage'] === 'da-DK', 'Dansk FAQ-fallback er forkert sprogmærket.');
$missing = $changed;
$missing['url'] = 'https://translation-fixture.invalid';
check(product_description_language($missing) === 'da' && product_description($missing) === $missing['description'], 'Nyt produkt uden oversættelse har forkert fallback.');
$alternatives = load_alternatives();
foreach (['da' => 'og-image.jpg', 'en' => 'og-image-en.jpg'] as $language => $file) {
  $imageSize = getimagesize(APP_ROOT . '/' . $file);
  check($imageSize !== false && $imageSize[0] === 1200 && $imageSize[1] === 630 && $imageSize['mime'] === 'image/jpeg', "$file: filen matcher ikke billedmetadata.");
  $manifest = load_json($language === 'en' ? 'site-en.webmanifest' : 'site.webmanifest');
  check(($manifest['lang'] ?? '') === ($language === 'en' ? 'en-GB' : 'da-DK'), 'Forkert sprog i manifest.');
  check(($manifest['start_url'] ?? '') === ($language === 'en' ? '/en' : '/'), 'Manifest åbner den forkerte forside.');
}
// Every sitemap entry lists the same complete, reciprocal language pair as HTML.
[$sitemap] = page_document('sitemap', '/sitemap.xml');
$xml = simplexml_load_string($sitemap);
check($xml !== false, 'Ugyldig XML i sitemap.');
$sitemapEntries = [];
foreach ($xml->url as $entry) {
  $links = [];
  foreach ($entry->children('http://www.w3.org/1999/xhtml')->link as $link) {
    $attributes = $link->attributes();
    check((string) $attributes['rel'] === 'alternate', 'Forkert rel i sitemap.');
    $links[(string) $attributes['hreflang']] = (string) $attributes['href'];
  }
  check(count($links) === 3 && isset($links['da'], $links['en'], $links['x-default']), 'Sitemap mangler sprogvarianter.');
  $url = (string) $entry->loc;
  check(!isset($sitemapEntries[$url]), 'Dubleret URL i sitemap.');
  check(in_array($url, [$links['da'], $links['en']], true), 'Sitemap mangler reference til siden selv.');
  check($links['x-default'] === $links['da'], 'Sitemap har forkert standardsprog.');
  $sitemapEntries[$url] = $links;
}
check(count($sitemapEntries) === (count($alternatives) + 3) * 2, 'Sitemap mangler sider.');
foreach ($sitemapEntries as $links) {
  check(($sitemapEntries[$links['da']] ?? null) === $links && ($sitemapEntries[$links['en']] ?? null) === $links, 'Sitemap har ikke gensidige sprogreferencer.');
}
foreach (['da', 'en'] as $language) {
  foreach (['home' => '/', 'alternatives' => '/alternativer', 'badge' => '/badge'] as $page => $path) {
    check_page($page, locale_path($path, $language));
  }
  foreach ($alternatives as $alternative) {
    check_page('alternative', locale_path('/alternativer/' . $alternative['slug'], $language), ['alternative' => $alternative]);
  }
}
// Badge language is independent of page language, including URLs and accessibility text.
foreach (['da', 'en'] as $language) {
  $_SERVER['REQUEST_URI'] = $language === 'da' ? '/en/badge' : '/badge';
  foreach ($products as $product) {
    foreach (product_badges($product, $language) as $badge) {
      check(str_starts_with(badge_image_url($product, $badge), locale_url('/badge/', $language)), 'Forkert sprog i badge-URL.');
      check($badge['link'] === locale_url(parse_url($badge['link'], PHP_URL_PATH) ?: '/', $language), 'Forkert sprog i badge-link.');
      foreach (BADGE_FORMATS as $format) {
        foreach (array_keys(BADGE_THEMES) as $theme) {
          $svg = simplexml_load_string(badge_svg($badge, $theme, $format));
          check($svg !== false && (int) $svg['width'] > 0, 'Ugyldigt badge.');
          check((string) $svg['lang'] === $language && (string) $svg->attributes('http://www.w3.org/XML/1998/namespace')['lang'] === $language, 'SVG mangler badgets eget sprog.');
          check(str_contains((string) $svg->title, $language === 'en' ? 'The Danish Tech Stack' : 'Den danske tech stack'), 'Forkert sprog i SVG-titel.');
        }
      }
    }
  }
}
// Both page/badge language combinations, including HTML copied to another website.
foreach (['da', 'en'] as $pageLanguage) {
  foreach (['da', 'en'] as $badgeLanguage) {
    [, $xpath] = page_document('badge', locale_path('/badge', $pageLanguage), [],
      ['produkt' => 'alunta-com', 'sprog' => $badgeLanguage]);
    foreach ($xpath->query('//div[contains(@class,"badge-preview")]/svg') as $svg) {
      check($svg->getAttribute('lang') === $badgeLanguage, 'Badgepreview arver sidens sprog.');
    }
    foreach ($xpath->query('//button[@data-copy]') as $button) {
      $code = $button->getAttribute('data-copy');
      if (!str_starts_with($code, '<a ')) continue; // Markdown embeds use host markup.
      $embed = new DOMDocument();
      $embed->loadHTML($code, LIBXML_NONET);
      $embedXpath = new DOMXPath($embed);
      check($embedXpath->evaluate('string(//img/ancestor::*[@lang][1]/@lang)') === $badgeLanguage,
        'HTML-embedets alt-tekst har forkert sprog.');
      check($embedXpath->evaluate('string(//a[@title]/@lang)') === $badgeLanguage,
        'HTML-embedets titel har forkert sprog.');
    }
  }
  $_SERVER['REQUEST_URI'] = locale_path('/badge', $pageLanguage);
  $defaultBadge = ['label' => t('Dansk alternativ til'), 'name' => 'Stripe'];
  check((string) simplexml_load_string(badge_svg($defaultBadge, 'lys', 'kompakt'))['lang'] === $pageLanguage,
    'Et badge uden eksplicit sprog følger ikke sidens sprog.');
}
// Language switching preserves selections and cannot create an external redirect.
$_SERVER['REQUEST_URI'] = '/en/badge';
$_GET = ['produkt' => 'alunta-com', 'badge' => BADGE_GENERIC, 'sprog' => 'en'];
check(switch_language_url('da') === '/badge?' . http_build_query(array_merge($_GET, ['site_language' => 'da'])), 'Sprogskift mister badgevalget.');
check(locale_path('/en/alternatives/stripe', 'da') === '/alternativer/stripe', 'Forkert dansk alternativsti.');
check(locale_path('/#produkter', 'en') === '/en#produkter', 'Forkert link til sektion på engelsk forside.');
// 404s keep their language, including links and the restart control.
[$html, $xpath] = page_document('404', '/en/not-found');
check($xpath->evaluate('string(/html/@lang)') === 'en' && str_contains($html, 'Try restarting'), '404 er ikke oversat.');
check($xpath->evaluate('string(//a[@class="not-found-home"]/@href)') === '/en#produkter', '404 skifter sprog.');
check(str_contains($xpath->evaluate('string(//meta[@name="robots"]/@content)'), 'noindex'), '404 kan indekseres.');
foreach (['/', '/en'] as $prefix) {
  [, $xpath] = page_document('badge', $prefix . '/badge', [], ['produkt' => 'alunta-com', 'sprog' => 'en']);
  check(str_contains($xpath->evaluate('string(//meta[@name="robots"]/@content)'), 'noindex'), 'Badgevalg kan indekseres.');
  check($xpath->evaluate('string(//link[@rel="canonical"]/@href)') === locale_url('/badge'), 'Badgevalg har forkert canonical.');
}
if ($warnings) {
  fwrite(STDERR, 'ADVARSEL: Vedligeholderne skal oversætte: ' . implode(', ', $warnings) . ". Den aktuelle danske kildebeskrivelse bruges indtil da.\n");
  check(!in_array('--strict-translations', $argv, true), 'Udgivelseskontrollen kræver aktuelle engelske produktoversættelser.');
}
echo 'OK: Begge sprog, ' . count($alternatives) . ' alternativsider pr. sprog, ' . $translatedCount . '/' . count($products) . " produktoversættelser, FAQ, metadata, interne links og alle produktbadges i begge sprog og fire udseender.\n";
