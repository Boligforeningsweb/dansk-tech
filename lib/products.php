<?php
if (!defined('APP_ROOT')) { http_response_code(404); exit; }

// Alle produkter fra products.json, sorteret alfabetisk. Produkter uden de påkrævede felter udelades.
function load_products() {
  static $products = null;
  if ($products !== null) {
    return $products;
  }
  $products = array_values(array_filter(load_json('products.json'), function($product) {
    return isset($product['name'], $product['url'], $product['description']);
  }));
  usort($products, function($a, $b) {
    return strcasecmp($a['name'], $b['name']);
  });
  return $products;
}

// URL'er på de produkter, der har fået "Original"-labelen
function load_original_products() {
  return load_json('original-products.json');
}

function products_modified_at() {
  return filemtime(APP_ROOT . '/products.json');
}

function favicon_url($url) {
  $domain = parse_url($url, PHP_URL_HOST);
  if (!$domain) {
    return '';
  }
  $domain = str_replace('www.', '', $domain);
  return 'https://www.google.com/s2/favicons?domain=' . urlencode($domain) . '&sz=32';
}
