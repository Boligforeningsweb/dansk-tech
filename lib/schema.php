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
    'parentOrganization' => [
      '@type' => 'Organization',
      'name' => 'langsom.com',
      'url' => 'https://langsom.com',
    ],
  ];
}

function schema_website($description) {
  return [
    '@type' => 'WebSite',
    '@id' => SITE_URL . '#website',
    'name' => 'Den danske tech stack',
    'alternateName' => 'Dansk Tech Stack',
    'url' => SITE_URL,
    'description' => $description,
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

// $items = [['Forside', '/'], ['Alternativer', '/alternativer'], ['Stripe', '/alternativer/stripe']]
function schema_breadcrumbs(array $items, $id) {
  $list = [];
  foreach (array_values($items) as $i => [$name, $path]) {
    $list[] = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $name, 'item' => SITE_URL . ltrim($path, '/')];
  }
  return ['@type' => 'BreadcrumbList', '@id' => $id, 'itemListElement' => $list];
}
