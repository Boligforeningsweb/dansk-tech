<?php
if (!defined('APP_ROOT')) { http_response_code(404); exit; }

// Danish keeps its established URLs. English has explicit, shareable URLs.
function locale() {
  $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
  return $path === '/en' || str_starts_with($path, '/en/') ? 'en' : 'da';
}

function language_tag($language = null) {
  return ($language ?? locale()) === 'en' ? 'en-GB' : 'da-DK';
}

const LANGUAGE_COOKIE = 'dts_language';

function browser_language($header) {
  $best = 'da';
  $bestWeight = 0;
  foreach (explode(',', $header) as $entry) {
    $parts = array_map('trim', explode(';', $entry));
    if (!preg_match('/^(da|en)(?:-[a-z0-9]{1,8})*$/i', $parts[0], $match)) {
      continue;
    }
    $weight = 1;
    foreach (array_slice($parts, 1) as $parameter) {
      if (preg_match('/^q=/i', $parameter)) {
        $weight = preg_match('/^q=(0(?:\.\d{0,3})?|1(?:\.0{0,3})?)$/i', $parameter, $quality) ? (float) $quality[1] : 0;
      }
    }
    if ($weight > $bestWeight) {
      $best = strtolower($match[1]);
      $bestWeight = $weight;
    }
  }
  return $best;
}

function preferred_language() {
  $saved = $_COOKIE[LANGUAGE_COOKIE] ?? null;
  return in_array($saved, ['da', 'en'], true) ? $saved : browser_language($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '');
}

function is_crawler() {
  return (bool) preg_match('/bot|crawler|spider|slurp|facebookexternalhit|whatsapp/i', $_SERVER['HTTP_USER_AGENT'] ?? '');
}

function remember_language($language) {
  if (is_crawler() || ($_COOKIE[LANGUAGE_COOKIE] ?? null) === $language) {
    return;
  }
  setcookie(LANGUAGE_COOKIE, $language, [
    'expires' => time() + 31536000,
    'path' => '/',
    'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') == 443),
    'httponly' => true,
    'samesite' => 'Lax',
  ]);
  header('Cache-Control: private, no-store');
  $_COOKIE[LANGUAGE_COOKIE] = $language;
}

function handle_language_preference($path) {
  $selected = query_value('site_language');
  if (in_array($selected, ['da', 'en'], true)) {
    remember_language($selected);
    $target = locale_path($path, $selected);
    if ($target !== $path) {
      header('Cache-Control: private, no-store');
      redirect($target . '?' . http_build_query($_GET), 302);
    }
    // Keep an explicit choice usable even when cookies or JavaScript are disabled.
    // JavaScript removes this preference marker from the visible URL after loading.
    unset($_GET['site_language']);
    return;
  }
  if ($path === '/') {
    header('Vary: Cookie, Accept-Language');
    header('Cache-Control: private, no-store');
    if (!is_crawler() && preferred_language() === 'en') {
      $query = $_SERVER['QUERY_STRING'] ?? '';
      redirect('/en' . ($query !== '' ? '?' . $query : ''), 302);
    }
  }
}

function t($message, array $parameters = []) {
  static $english = null;
  if (locale() === 'en') {
    $english ??= load_json('data/translations/en.json');
    $message = $english[$message] ?? $message;
  }
  return strtr($message, $parameters);
}

function locale_path($path = '/', $language = null) {
  $language ??= locale();
  // Keep fragments outside path translation, including links to the homepage.
  $fragment = '';
  if (($hash = strpos($path, '#')) !== false) {
    $fragment = substr($path, $hash);
    $path = substr($path, 0, $hash);
  }
  if ($path === '/en' || str_starts_with($path, '/en/')) {
    $path = substr($path, 3) ?: '/';
    $path = preg_replace('#^/alternatives(?=/|$)#', '/alternativer', $path);
  }
  if ($language === 'en') {
    $path = preg_replace('#^/alternativer(?=/|$)#', '/alternatives', $path);
    return '/en' . ($path === '/' ? '' : $path) . $fragment;
  }
  return $path . $fragment;
}

function badge_language() {
  return isset($_GET['sprog']) && in_array($_GET['sprog'], ['da', 'en'], true) ? $_GET['sprog'] : locale();
}

function query_value($key, $default = '') {
  return is_string($_GET[$key] ?? null) ? $_GET[$key] : $default;
}

function locale_url($path = '/', $language = null) {
  return SITE_URL . ltrim(locale_path($path, $language), '/');
}

function switch_language_url($language) {
  $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
  return locale_path($path, $language) . '?' . http_build_query(array_merge($_GET, ['site_language' => $language]));
}

// Translation files stay separate from the community-maintained product list.
// A changed source description invalidates its translation until reviewed.
function product_translation(array $product) {
  static $translations = null;
  $translations ??= load_json('data/translations/products.en.json');
  $translation = $translations[$product['url']] ?? [];
  $hash = hash('sha256', $product['name'] . "\n" . $product['description']);
  return ($translation['sourceHash'] ?? '') === $hash && is_string($translation['description'] ?? null)
    && trim($translation['description']) !== '' ? $translation['description'] : null;
}

function product_description(array $product) {
  return locale() === 'en' ? (product_translation($product) ?? $product['description']) : $product['description'];
}

function product_description_language(array $product) {
  return locale() === 'en' && product_translation($product) !== null ? 'en' : 'da';
}
