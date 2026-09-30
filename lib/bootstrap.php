<?php
if (!defined('APP_ROOT')) { http_response_code(404); exit; }

const SITE_URL = 'https://dansktechstack.dk/';

header('Strict-Transport-Security: max-age=31536000');

require APP_ROOT . '/lib/products.php';
require APP_ROOT . '/lib/schema.php';

function e($value) {
  return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function load_json($file) {
  $path = APP_ROOT . '/' . $file;
  if (!file_exists($path)) {
    return [];
  }
  $decoded = json_decode(file_get_contents($path), true);
  return json_last_error() === JSON_ERROR_NONE && is_array($decoded) ? $decoded : [];
}

// Rolle-tekst, evt. med et link på en del af teksten
function person_role(array $person) {
  $role = e($person['role']);
  if (!empty($person['link'])) {
    $text = e($person['link']['text']);
    $link = '<a target="_blank" href="' . e($person['link']['url']) . '">' . $text . '</a>';
    $role = preg_replace('/' . preg_quote($text, '/') . '/', $link, $role, 1);
  }
  return $role;
}

// URL med indholds-hash, så filen kan caches længe og alligevel opdateres ved deploy
function asset_url($file) {
  $path = APP_ROOT . '/' . $file;
  $version = file_exists($path) ? substr(md5_file($path), 0, 10) : '0';
  return '/' . $file . '?v=' . $version;
}

function redirect($location, $status = 301) {
  header('Location: ' . $location, true, $status);
  exit;
}

// Render en side med et sæt variabler i eget scope
function render($page, array $vars = [], $status = 200) {
  http_response_code($status);
  extract($vars);
  require APP_ROOT . '/pages/' . $page . '.php';
}

function partial($name, array $vars = []) {
  extract($vars);
  require APP_ROOT . '/partials/' . $name . '.php';
}

// Tilføj kilde-parametre til udgående produktlinks, så produkterne kan se trafikken fra os
function outbound_url($url) {
  $params = ['ref' => 'dansktechstack.dk', 'utm_source' => 'dansktechstack.dk', 'utm_medium' => 'referral'];
  $fragment = '';
  if (($hash = strpos($url, '#')) !== false) {
    $fragment = substr($url, $hash);
    $url = substr($url, 0, $hash);
  }
  parse_str(parse_url($url, PHP_URL_QUERY) ?? '', $existing);
  $params = array_diff_key($params, $existing);
  if (!$params) {
    return $url . $fragment;
  }
  return $url . (str_contains($url, '?') ? '&' : '?') . http_build_query($params) . $fragment;
}
