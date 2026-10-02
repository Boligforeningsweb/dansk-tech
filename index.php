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

if (preg_match('#^/badge/([a-z0-9-]+)\.svg$#', $path, $match)) {
  serve_standalone_badge($match[1]);
  exit;
}

if (preg_match('#^/badge/([a-z0-9-]+)/([a-z0-9-]+)\.svg$#', $path, $match)) {
  serve_badge($match[1], $match[2]);
  exit;
}

if (preg_match('#^/cache/favicons/([a-z0-9.-]+)\.png$#', $path, $match)) {
  serve_favicon($match[1]);
  exit;
}

switch ($path) {
  case '/':
    render('home');
    break;

  case '/alternativer':
    render('alternatives');
    break;

  case '/badge':
    render('badge');
    break;

  case '/sitemap.xml':
    render('sitemap');
    break;

  default:
    if (preg_match('#^/alternativer/([^/]+)$#', $path, $match)) {
      $alternatives = load_alternatives();
      if (isset($alternatives[$match[1]])) {
        render('alternative', ['alternative' => $alternatives[$match[1]]]);
        break;
      }
      // Fx /alternativer/Stripe eller /alternativer/1Password -> kanonisk slug
      $slug = alternative_slug(rawurldecode($match[1]));
      if (isset($alternatives[$slug])) {
        redirect('/alternativer/' . $slug . $queryString);
      }
    }
    render('404', [], 404);
}
