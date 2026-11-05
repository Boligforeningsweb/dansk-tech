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

// Use the reviewed local artwork before asking an external favicon service.
function product_image_url(array $product) {
  $image = $product['image'] ?? null;
  if (!is_string($image) || $image === '') {
    return null;
  }
  $relative = ltrim($image, '/');
  $path = realpath(APP_ROOT . '/' . $relative);
  $imageRoot = realpath(APP_ROOT . '/images');
  return $path && $imageRoot && str_starts_with($path, $imageRoot . '/') && is_file($path)
    ? '/' . $relative : null;
}

function products_modified_at() {
  return filemtime(APP_ROOT . '/products.json');
}
