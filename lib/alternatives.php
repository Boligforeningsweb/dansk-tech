<?php
if (!defined('APP_ROOT')) { http_response_code(404); exit; }

// Udenlandske produkter fra "alternatives" i products.json, hver med sin egen side på
// /alternativer/{slug}. Kun sider med mindst ALTERNATIVE_MIN_INDEXED danske produkter
// indekseres - de øvrige findes og linkes til, men får noindex, indtil listen vokser.

const ALTERNATIVE_MIN_INDEXED = 2;

function alternative_slug($name) {
  $slug = mb_strtolower(trim($name));
  $slug = strtr($slug, ['æ' => 'ae', 'ø' => 'oe', 'å' => 'aa', 'ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'é' => 'e', '&' => ' og ', '+' => ' plus ']);
  $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
  return trim($slug, '-');
}

function alternative_url($name) {
  return '/alternativer/' . alternative_slug($name);
}

// slug => ['name' => ..., 'slug' => ..., 'products' => [...], 'indexed' => bool]
function load_alternatives() {
  static $alternatives = null;
  if ($alternatives !== null) {
    return $alternatives;
  }
  $alternatives = [];
  foreach (load_products() as $product) {
    foreach ($product['alternatives'] ?? [] as $name) {
      $slug = alternative_slug($name);
      if ($slug === '') {
        continue;
      }
      $alternatives[$slug] ??= ['name' => $name, 'slug' => $slug, 'products' => []];
      $alternatives[$slug]['products'][] = $product;
    }
  }
  foreach ($alternatives as &$alternative) {
    $alternative['indexed'] = count($alternative['products']) >= ALTERNATIVE_MIN_INDEXED;
  }
  unset($alternative);
  uasort($alternatives, function($a, $b) {
    return strcasecmp($a['name'], $b['name']);
  });
  return $alternatives;
}

// Andre udenlandske produkter, som de samme danske produkter også erstatter
function related_alternatives(array $alternative, $limit = 12) {
  $all = load_alternatives();
  $scores = [];
  foreach ($alternative['products'] as $product) {
    foreach ($product['alternatives'] ?? [] as $name) {
      $slug = alternative_slug($name);
      if ($slug !== $alternative['slug'] && isset($all[$slug])) {
        $scores[$slug] = ($scores[$slug] ?? 0) + 1;
      }
    }
  }
  // Flest fælles produkter først, derefter flest danske alternativer i alt
  uksort($scores, function($a, $b) use ($scores, $all) {
    return [$scores[$b], count($all[$b]['products'])] <=> [$scores[$a], count($all[$a]['products'])];
  });
  return array_map(function($slug) use ($all) { return $all[$slug]; }, array_slice(array_keys($scores), 0, $limit));
}

// Opremsning på dansk: "A, B og C"
function list_names(array $names) {
  if (count($names) < 2) {
    return implode('', $names);
  }
  $last = array_pop($names);
  return implode(', ', $names) . ' og ' . $last;
}
