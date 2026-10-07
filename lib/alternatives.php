<?php
if (!defined('APP_ROOT')) { http_response_code(404); exit; }

// Udenlandske produkter fra "alternatives" i products.json, hver med sin egen side på
// /alternativer/{slug}. Alle eksisterende alternativsider må indekseres.

function alternative_slug($name) {
  $slug = mb_strtolower(trim($name));
  $slug = strtr($slug, ['æ' => 'ae', 'ø' => 'oe', 'å' => 'aa', 'ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'é' => 'e', '&' => ' og ', '+' => ' plus ']);
  $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
  return trim($slug, '-');
}

function alternative_url($name) {
  return '/alternativer/' . alternative_slug($name);
}

// slug => ['name' => ..., 'slug' => ..., 'products' => [...]]
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
  uasort($alternatives, function($a, $b) {
    return strcasecmp($a['name'], $b['name']);
  });
  return $alternatives;
}

// Andre produkter, som de samme danske produkter er foreslået som alternativer til
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

// AI-tekster skrives og gennemgås i repoet, ikke ved sidevisning.
// Ved ændrede kildedata bruges den aktuelle produktbeskrivelse i stedet.
function product_guide(array $product) {
  static $guides = null;
  $guides ??= load_json('data/product-guides.json');
  $guide = $guides[$product['url']] ?? null;
  $sourceHash = hash('sha256', $product['name'] . "\n" . $product['description']);
  if (!is_array($guide) || ($guide['sourceHash'] ?? '') !== $sourceHash
      || !is_string($guide['explanation'] ?? null) || trim($guide['explanation']) === ''
      || !is_string($guide['consideration'] ?? null) || trim($guide['consideration']) === '') {
    return ['explanation' => $product['description'], 'consideration' => null];
  }
  return $guide;
}

function alternative_faq(array $alternative) {
  $name = $alternative['name'];
  $faq = [];
  foreach ($alternative['products'] as $product) {
    $guide = product_guide($product);
    $faq[] = [
      'q' => "Hvad kan {$product['name']} bruges til?",
      'a' => $guide['explanation'],
      'consideration' => $guide['consideration'],
      'source' => ['name' => $product['name'], 'url' => $product['url']],
    ];
  }
  $faq[] = [
    'q' => "Kan jeg erstatte $name direkte med et af disse produkter?",
    'a' => "At et produkt er på listen som alternativ til $name betyder, at det er foreslået til lignende behov. Det er ikke en garanti for de samme funktioner eller en direkte udskiftning. Lav en liste over de funktioner, du bruger i $name, og afprøv dine vigtigste arbejdsgange hos den nye leverandør. Afklar også integrationer, flytning af data og den samlede pris før et skifte.",
  ];
  $faq[] = [
    'q' => 'Betyder et dansk produkt, at mine data opbevares i Danmark?',
    'a' => 'Nej. Dansk betyder her, at virksomheden har hovedkontor i Danmark, en dansk stifter eller medstifter, eller primært er dansk ejet. Det fortæller ikke i sig selv, hvor data opbevares, hvilke underleverandører der bruges, eller hvilket sprog supporten foregår på. Spørg leverandøren om de forhold, der er afgørende for jer.',
  ];
  return $faq;
}
