<?php
// Validerer products.json. Køres af GitHub Actions på PR'er og kan køres lokalt:
//   php tools/validate-products.php
// Fejl stopper PR'en; advarsler vises kun.

if (PHP_SAPI !== 'cli') {
  http_response_code(404);
  exit;
}

$root = dirname(__DIR__);
$file = 'products.json';
$raw = file_get_contents("$root/$file");
$errors = [];
$warnings = [];

function line_of($raw, $needle) {
  $pos = strpos($raw, $needle);
  return $pos === false ? 1 : substr_count($raw, "\n", 0, $pos) + 1;
}

function report($level, $message, $line = 1) {
  global $file;
  // GitHub Actions-annotation, så fejlen vises direkte på linjen i PR'en
  if (getenv('GITHUB_ACTIONS')) {
    echo "::$level file=$file,line=$line::$message\n";
  } else {
    echo strtoupper($level) . " (linje $line): $message\n";
  }
}

// Navne skrives ens på tværs af produkter: "MailChimp" og "Mailchimp" må ikke begge findes
function normalize($name) {
  return preg_replace('/[^a-z0-9]/', '', mb_strtolower($name));
}

$products = json_decode($raw, true);
if (!is_array($products) || !array_is_list($products)) {
  report('error', 'products.json er ikke gyldig JSON (' . json_last_error_msg() . '). Tjek især komma mellem produkterne, intet komma efter det sidste produkt, og at alle tekster står i "anførselstegn". En JSON-validator som jsonlint.com viser præcis hvor fejlen er.');
  exit(1);
}

$urls = [];
$names = [];
$spellings = [];
$productNames = [];

foreach ($products as $i => $product) {
  $label = is_array($product) && isset($product['name']) && is_string($product['name']) ? $product['name'] : "produkt nr. " . ($i + 1);
  $line = line_of($raw, '"name": ' . json_encode($label, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

  if (!is_array($product)) {
    $errors[] = ["$label: skal være et objekt", $line];
    continue;
  }

  foreach (['name', 'url', 'description'] as $field) {
    if (!isset($product[$field]) || !is_string($product[$field]) || trim($product[$field]) === '') {
      $errors[] = ["$label: mangler \"$field\"", $line];
    }
  }

  $unknown = array_diff(array_keys($product), ['name', 'url', 'description', 'alternatives', 'image']);
  if ($unknown) {
    $warnings[] = ["$label: ukendte felter: " . implode(', ', $unknown), $line];
  }

  if (isset($product['url']) && is_string($product['url'])) {
    $host = parse_url($product['url'], PHP_URL_HOST);
    if (!preg_match('#^https?://#', $product['url']) || !$host) {
      $errors[] = ["$label: \"url\" skal være en fuld adresse, fx https://eksempel.dk", $line];
    } else {
      $key = preg_replace('/^www\./', '', strtolower($host)) . rtrim((string) parse_url($product['url'], PHP_URL_PATH), '/');
      if (isset($urls[$key])) {
        $errors[] = ["$label: samme url som \"{$urls[$key]}\"", $line];
      }
      $urls[$key] = $label;
    }
  }

  if (isset($product['name']) && is_string($product['name'])) {
    $key = normalize($product['name']);
    if (isset($names[$key])) {
      $errors[] = ["$label: produktet findes allerede som \"{$names[$key]}\"", $line];
    }
    $names[$key] = $label;
    $productNames[$key] = $label;
  }

  if (!isset($product['alternatives']) || !is_array($product['alternatives']) || !array_is_list($product['alternatives']) || !$product['alternatives']) {
    $errors[] = ["$label: \"alternatives\" skal være en liste med mindst ét udenlandsk produkt, fx [\"Stripe\", \"Paddle\"]", $line];
  } else {
    $seen = [];
    foreach ($product['alternatives'] as $alternative) {
      if (!is_string($alternative) || trim($alternative) === '') {
        $errors[] = ["$label: alle \"alternatives\" skal være tekst", $line];
        continue;
      }
      if (preg_match('/,| og | and | \/ |;/i', $alternative)) {
        $errors[] = ["$label: \"$alternative\" ser ud til at være flere produkter – skriv ét produkt pr. element, fx [\"Wix\", \"WordPress\"]", $line];
      }
      if ($alternative !== trim($alternative)) {
        $errors[] = ["$label: \"$alternative\" har mellemrum i starten eller slutningen", $line];
      }
      $key = normalize($alternative);
      if (isset($seen[$key])) {
        $errors[] = ["$label: \"$alternative\" står flere gange", $line];
      }
      $seen[$key] = true;
      $spellings[$key][$alternative][] = [$label, $line];
    }
  }

  if (isset($product['image'])) {
    if (!is_string($product['image']) || !is_file("$root/" . ltrim($product['image'], '/'))) {
      $errors[] = ["$label: billedet \"" . (is_string($product['image']) ? $product['image'] : '') . "\" findes ikke", $line];
    }
  }
}

// Samme udenlandske produkt stavet på flere måder, fx "MailChimp" og "Mailchimp"
foreach ($spellings as $variants) {
  if (count($variants) < 2) {
    continue;
  }
  $all = [];
  foreach ($variants as $spelling => $uses) {
    $all[] = "\"$spelling\" (" . implode(', ', array_column($uses, 0)) . ')';
  }
  // Fejlen vises ved den mindst brugte stavemåde
  uasort($variants, function($a, $b) { return count($a) - count($b); });
  $line = reset($variants)[0][1];
  $errors[] = ['Samme produkt staves forskelligt: ' . implode(' og ', $all) . ' – brug én stavemåde', $line];
}

// Danske produkter fra listen, der også står som "alternativ"
foreach ($spellings as $key => $variants) {
  if (isset($productNames[$key]) || isset($productNames[$key . 'com']) || isset($productNames[$key . 'dk'])) {
    foreach ($variants as $spelling => $uses) {
      foreach ($uses as [$label, $line]) {
        $warnings[] = ["$label: \"$spelling\" står selv på listen som dansk produkt", $line];
      }
    }
  }
}

// original-products.json skal pege på produkter, der findes
$originals = json_decode((string) @file_get_contents("$root/original-products.json"), true) ?: [];
$productUrls = array_column($products, 'url');
foreach ($originals as $url) {
  if (!in_array($url, $productUrls, true)) {
    $warnings[] = ["original-products.json: $url findes ikke i products.json", 1];
  }
}

foreach ($errors as [$message, $line]) {
  report('error', $message, $line);
}
foreach ($warnings as [$message, $line]) {
  report('warning', $message, $line);
}

echo count($products) . ' produkter, ' . count($errors) . ' fejl, ' . count($warnings) . " advarsler\n";
exit($errors ? 1 : 0);
