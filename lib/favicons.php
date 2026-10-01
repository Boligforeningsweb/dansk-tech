<?php
if (!defined('APP_ROOT')) { http_response_code(404); exit; }

// Favicons hentes én gang fra Google og gemmes i cache/favicons/. Derefter serverer nginx
// filen direkte; kun første forespørgsel (filen findes ikke endnu) rammer PHP.

const FAVICON_DIR = APP_ROOT . '/cache/favicons';
const FAVICON_MISS_TTL = 86400;

function favicon_domain($url) {
  $host = strtolower((string) parse_url($url, PHP_URL_HOST));
  return preg_replace('/^www\./', '', $host);
}

function favicon_url($url) {
  $domain = favicon_domain($url);
  return $domain ? '/cache/favicons/' . $domain . '.png' : '';
}

function serve_favicon($domain) {
  // Kun domæner fra products.json - ellers kunne siden bruges til at hente vilkårlige URL'er
  $allowed = array_map(function($product) { return favicon_domain($product['url']); }, load_products());
  if (!in_array($domain, $allowed, true)) {
    http_response_code(404);
    return;
  }

  $file = FAVICON_DIR . '/' . $domain . '.png';
  $miss = FAVICON_DIR . '/' . $domain . '.miss';

  if (!is_file($file)) {
    if (is_file($miss) && time() - filemtime($miss) < FAVICON_MISS_TTL) {
      http_response_code(404);
      return;
    }
    $response = http_get('https://www.google.com/s2/favicons?domain=' . urlencode($domain) . '&sz=64', [], 3, 200000);
    $info = $response && $response[0] === 200 ? @getimagesizefromstring($response[1]) : false;
    if (!$info || !in_array($info['mime'], ['image/png', 'image/jpeg', 'image/gif', 'image/webp'], true)) {
      write_cache_file($miss, '');
      http_response_code(404);
      return;
    }
    $png = $info['mime'] === 'image/png' ? $response[1] : favicon_to_png($response[1]);
    if (!write_cache_file($file, $png)) {
      // Kan vi ikke gemme, serverer vi alligevel billedet
      header('Content-Type: image/png');
      header('Cache-Control: public, max-age=3600');
      echo $png;
      return;
    }
    @unlink($miss);
  }

  header('Content-Type: image/png');
  header('Content-Length: ' . filesize($file));
  header('Cache-Control: public, max-age=2592000');
  readfile($file);
}

// Google leverer nogle favicons som JPEG; vi gemmer altid PNG, så filtypen passer til .png-URL'en
function favicon_to_png($data) {
  if (!function_exists('imagecreatefromstring') || !($image = @imagecreatefromstring($data))) {
    return $data;
  }
  ob_start();
  imagepng($image, null, 9);
  imagedestroy($image);
  return ob_get_clean();
}
