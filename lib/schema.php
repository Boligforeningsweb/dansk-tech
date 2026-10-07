<?php
if (!defined('APP_ROOT')) { http_response_code(404); exit; }

// Byggeklodser til Schema.org JSON-LD, som deles mellem siderne

function schema_organization() {
  return [
    '@type' => 'Organization',
    '@id' => SITE_URL . '#organization',
    'name' => 'Den danske tech stack',
    'alternateName' => 'Dansk Tech Stack',
    'url' => SITE_URL,
    'logo' => SITE_URL . 'web-app-manifest-512x512.png',
    'email' => 'kontakt@langsom.com',
    'sameAs' => ['https://github.com/Boligforeningsweb/dansk-tech'],
    'founder' => array_map(function($person) {
      return ['@type' => 'Person', 'name' => $person['name'], 'description' => $person['role']];
    }, load_json('data/people.json')['founders'] ?? []),
    'parentOrganization' => [
      '@type' => 'Organization',
      'name' => 'langsom.com',
      'url' => 'https://langsom.com',
    ],
  ];
}

function schema_website() {
  return [
    '@type' => 'WebSite',
    '@id' => SITE_URL . '#website',
    'name' => 'Den danske tech stack',
    'alternateName' => 'Dansk Tech Stack',
    'url' => SITE_URL,
    'description' => 'En åben, kurateret liste over danske software-produkter som alternativer til internationale systemer.',
    'inLanguage' => 'da-DK',
    'publisher' => ['@id' => SITE_URL . '#organization'],
  ];
}

function schema_product_list_items(array $products) {
  $items = [];
  foreach (array_values($products) as $i => $product) {
    $item = [
      '@type' => 'SoftwareApplication',
      'name' => $product['name'],
      'url' => $product['url'],
      'description' => $product['description'],
      'applicationCategory' => 'BusinessApplication',
    ];
    if (!empty($product['alternatives']) && is_array($product['alternatives'])) {
      $item['keywords'] = 'Alternativ til ' . implode(', ', $product['alternatives']);
    }
    $items[] = ['@type' => 'ListItem', 'position' => $i + 1, 'item' => $item];
  }
  return $items;
}

function schema_json(array $graph) {
  return json_encode(['@context' => 'https://schema.org', '@graph' => $graph],
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_TAG);
}

// De samme spørgsmål, svar og afklaringspunkter som i den synlige FAQ.
// FAQPage beskriver indholdet; Google viser ikke længere FAQ rich results.
function schema_faq(array $items, $id) {
  return [
    '@type' => 'FAQPage',
    '@id' => $id,
    'mainEntity' => array_map(function($item) {
      $text = $item['a'];
      if (!empty($item['consideration'])) {
        $text .= "\n\nInden du vælger: " . $item['consideration'];
      }
      $answer = ['@type' => 'Answer', 'text' => $text];
      if (isset($item['source'])) {
        $answer['text'] .= "\n\nLæs mere hos " . $item['source']['name'];
        $answer['citation'] = ['@type' => 'WebPage', 'name' => $item['source']['name'], 'url' => $item['source']['url']];
      }
      return ['@type' => 'Question', 'name' => $item['q'], 'acceptedAnswer' => $answer];
    }, $items),
  ];
}

// $items = [['Forside', '/'], ['Alternativer', '/alternativer'], ['Stripe', '/alternativer/stripe']]
function schema_breadcrumbs(array $items, $id) {
  $list = [];
  foreach (array_values($items) as $i => [$name, $path]) {
    $list[] = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $name, 'item' => SITE_URL . ltrim($path, '/')];
  }
  return ['@type' => 'BreadcrumbList', '@id' => $id, 'itemListElement' => $list];
}
