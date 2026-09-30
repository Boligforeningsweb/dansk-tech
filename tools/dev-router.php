<?php
// Lokal udvikling: php -S localhost:8000 tools/dev-router.php
// Efterligner Forge' nginx: filer serveres direkte, alt andet går til index.php
if (PHP_SAPI !== 'cli-server') {
  http_response_code(404);
  exit;
}
$root = dirname(__DIR__);
$file = realpath($root . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
if ($file && str_starts_with($file, $root . '/') && is_file($file) && !str_ends_with($file, '.php')) {
  return false;
}
require $root . '/index.php';
