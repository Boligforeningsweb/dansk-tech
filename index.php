<?php
// Front controller: nginx på Forge sender alle forespørgsler, der ikke rammer en fil, hertil
define('APP_ROOT', __DIR__);
require APP_ROOT . '/lib/bootstrap.php';

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$query = $_SERVER['QUERY_STRING'] ?? '';
$queryString = $query !== '' ? '?' . $query : '';

// Samme side må kun findes på én adresse
if ($path === '/index.php') {
  redirect('/' . $queryString);
}
if ($path !== '/' && str_ends_with($path, '/')) {
  redirect(rtrim($path, '/') . $queryString);
}

if (preg_match('#^/cache/favicons/([a-z0-9.-]+)\.png$#', $path, $match)) {
  serve_favicon($match[1]);
  exit;
}

switch ($path) {
  case '/':
    render('home');
    break;

  default:
    render('404', [], 404);
}
