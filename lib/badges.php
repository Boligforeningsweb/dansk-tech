<?php
if (!defined('APP_ROOT')) { http_response_code(404); exit; }

// Badges til produkternes egne websites: "Dansk alternativ til Stripe" eller "Med på Den danske
// tech stack". Teksten tegnes som vektorer ud fra skriften Inter (data/badge-font.json, lavet af
// tools/badge/build-font.py), så badget ser ens ud overalt, og bredden altid passer til teksten.

const BADGE_GENERIC = 'den-danske-tech-stack';
const BADGE_THEMES = [
  'lys' => ['bg' => '#ffffff', 'border' => '#d1d5db', 'muted' => '#6b7280', 'strong' => '#111827'],
  'moerk' => ['bg' => '#111827', 'border' => '#374151', 'muted' => '#9ca3af', 'strong' => '#ffffff'],
];
const BADGE_FORMATS = ['kompakt', 'stor'];

function product_slug(array $product) {
  return alternative_slug($product['name']);
}

function find_product_by_slug($slug) {
  foreach (load_products() as $product) {
    if (product_slug($product) === $slug) {
      return $product;
    }
  }
  return null;
}

// De badges, et produkt kan bruge: ét pr. udenlandsk alternativ + det generelle
function product_badges(array $product) {
  $badges = [];
  foreach ($product['alternatives'] ?? [] as $name) {
    $badges[alternative_slug($name)] = [
      'slug' => alternative_slug($name),
      'label' => 'Dansk alternativ til',
      'name' => $name,
      'link' => SITE_URL . ltrim(alternative_url($name), '/'),
    ];
  }
  $badges[BADGE_GENERIC] = [
    'slug' => BADGE_GENERIC,
    'label' => 'Med på',
    'name' => 'Den danske tech stack',
    'link' => SITE_URL,
  ];
  return $badges;
}

function badge_image_url(array $product, array $badge, $theme = 'lys', $format = 'kompakt') {
  $query = array_filter(['tema' => $theme !== 'lys' ? $theme : null, 'format' => $format !== 'kompakt' ? $format : null]);
  return SITE_URL . 'badge/' . product_slug($product) . '/' . $badge['slug'] . '.svg' . ($query ? '?' . http_build_query($query) : '');
}

function badge_html(array $product, array $badge, $theme = 'lys', $format = 'kompakt') {
  [$width, $height] = badge_size($badge, $format);
  return '<a href="' . e($badge['link']) . '" title="' . e($badge['label'] . ' ' . $badge['name']) . ' – Den danske tech stack">'
    . '<img src="' . e(badge_image_url($product, $badge, $theme, $format)) . '" alt="' . e($badge['label'] . ' ' . $badge['name']) . ' – Den danske tech stack" width="' . $width . '" height="' . $height . '"></a>';
}

function badge_markdown(array $product, array $badge, $theme = 'lys', $format = 'kompakt') {
  return '[![' . $badge['label'] . ' ' . $badge['name'] . '](' . badge_image_url($product, $badge, $theme, $format) . ')](' . $badge['link'] . ')';
}

function badge_font() {
  static $font = null;
  return $font ??= load_json('data/badge-font.json');
}

// Tekst som SVG-stier. Returnerer [svg, bredde i px, bredde til sidste bogstavs synlige kant i px].
function badge_text($text, $weight, $size, $x, $baseline, $fill) {
  $font = badge_font();
  $face = $font['weights'][(string) $weight];
  $scale = $size / $font['unitsPerEm'];
  $cursor = 0;
  $previous = null;
  $paths = '';
  foreach (mb_str_split($text) as $char) {
    $glyph = $face['glyphs'][$char] ?? $face['glyphs']['?'];
    if ($previous !== null) {
      $cursor += $face['kern'][$previous . $char] ?? 0;
    }
    if ($glyph['d'] !== '') {
      $paths .= '<path transform="translate(' . round($x + $cursor * $scale, 2) . ' ' . $baseline . ') scale(' . round($scale, 5) . ' -' . round($scale, 5) . ')" d="' . $glyph['d'] . '"/>';
    }
    $inkRight = $cursor + $glyph['xmax'];
    $cursor += $glyph['adv'];
    $previous = $char;
  }
  return ['<g fill="' . $fill . '">' . $paths . '</g>', $cursor * $scale, ($inkRight ?? 0) * $scale];
}

function badge_flag($x, $y, $width) {
  $height = round($width * 28 / 37, 2);
  $bar = round($height * 4 / 28, 2);
  return '<g transform="translate(' . $x . ' ' . $y . ')"><rect width="' . $width . '" height="' . $height . '" rx="' . round($width / 10, 1) . '" fill="#c8102e"/>'
    . '<rect x="' . round($width * 12 / 37, 2) . '" width="' . $bar . '" height="' . $height . '" fill="#fff"/>'
    . '<rect y="' . round($height * 12 / 28, 2) . '" width="' . $width . '" height="' . $bar . '" fill="#fff"/></g>';
}

// Layout pr. format. Venstre og højre margin er ens.
function badge_layout($format) {
  return $format === 'stor'
    ? ['height' => 48, 'pad' => 16, 'flag' => 24, 'gap' => 10, 'radius' => 10]
    : ['height' => 32, 'pad' => 12, 'flag' => 18, 'gap' => 8, 'radius' => 8];
}

function badge_size(array $badge, $format) {
  $l = badge_layout($format);
  // Bredden måles til den synlige kant af sidste bogstav, så højre margin = venstre margin
  if ($format === 'stor') {
    $textWidth = max(badge_text($badge['label'], 500, 11, 0, 0, '')[2], badge_text($badge['name'], 700, 15, 0, 0, '')[2]);
  } else {
    $textWidth = badge_text($badge['label'] . ' ', 500, 13, 0, 0, '')[1] + badge_text($badge['name'], 700, 13, 0, 0, '')[2];
  }
  return [(int) round($l['pad'] + $l['flag'] + $l['gap'] + $textWidth + $l['pad']), $l['height']];
}

function badge_svg(array $badge, $theme, $format) {
  $t = BADGE_THEMES[$theme];
  $l = badge_layout($format);
  [$width, $height] = badge_size($badge, $format);
  $x = $l['pad'] + $l['flag'] + $l['gap'];
  $flag = badge_flag($l['pad'], round(($height - $l['flag'] * 28 / 37) / 2, 2), $l['flag']);

  if ($format === 'stor') {
    $text = badge_text($badge['label'], 500, 11, $x, 20, $t['muted'])[0]
      . badge_text($badge['name'], 700, 15, $x, 36.5, $t['strong'])[0];
  } else {
    [$label, $labelWidth] = badge_text($badge['label'] . ' ', 500, 13, $x, 20.5, $t['muted']);
    $text = $label . badge_text($badge['name'], 700, 13, $x + $labelWidth, 20.5, $t['strong'])[0];
  }

  $title = e($badge['label'] . ' ' . $badge['name'] . ' – Den danske tech stack');
  return '<svg xmlns="http://www.w3.org/2000/svg" width="' . $width . '" height="' . $height . '" viewBox="0 0 ' . $width . ' ' . $height . '" role="img" aria-label="' . $title . '">'
    . '<title>' . $title . '</title>'
    . '<rect x="0.5" y="0.5" width="' . ($width - 1) . '" height="' . ($height - 1) . '" rx="' . $l['radius'] . '" fill="' . $t['bg'] . '" stroke="' . $t['border'] . '"/>'
    . $flag . $text . '</svg>';
}

function serve_badge($productSlug, $badgeSlug) {
  $product = find_product_by_slug($productSlug);
  $badges = $product ? product_badges($product) : [];
  $theme = $_GET['tema'] ?? 'lys';
  $format = $_GET['format'] ?? 'kompakt';
  if (!isset($badges[$badgeSlug]) || !isset(BADGE_THEMES[$theme]) || !in_array($format, BADGE_FORMATS, true)) {
    http_response_code(404);
    return;
  }
  header('Content-Type: image/svg+xml; charset=utf-8');
  header('Cache-Control: public, max-age=86400');
  header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'");
  echo badge_svg($badges[$badgeSlug], $theme, $format);
}
