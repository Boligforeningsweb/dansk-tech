<?php
// Unit and real HTTP checks. Run: php tools/check-language-preferences.php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('APP_ROOT', dirname(__DIR__));
$cache = sys_get_temp_dir() . '/dts-preferences-' . bin2hex(random_bytes(8));
mkdir($cache);
file_put_contents($cache . '/contributors.json', '[]');
$_SERVER['DTS_CACHE_DIR'] = $cache;
require APP_ROOT . '/lib/bootstrap.php';
function check($condition, $message) {
  if (!$condition) { throw new RuntimeException($message); }
}
$server = null;
register_shutdown_function(function() use (&$server, $cache) {
  if (is_resource($server)) { proc_terminate($server); proc_close($server); }
  foreach (glob($cache . '/*') as $file) { if (is_file($file)) unlink($file); }
  rmdir($cache);
});
try {
  $headers = [
    '' => 'da', 'da-DK,da;q=0.9,en;q=0.8' => 'da', 'en-US,en;q=0.9' => 'en',
    'de-DE,de;q=0.9' => 'da', 'de-DE,en-GB;q=0.8,da;q=0.5' => 'en',
    'en;q=0.4,da;q=0.9' => 'da', 'da;q=0,en;q=0.5' => 'en',
    'en;q=0' => 'da', '*' => 'da', 'EN-gb;Q=0.9,da;q=0.2' => 'en',
    'en;q=1.5,da;q=0.5' => 'da', 'en;q=wat,da;q=0.3' => 'da',
    'en;q=0.8,da;q=0.8' => 'en', 'da;q=0.8,en;q=0.8' => 'da',
  ];
  foreach ($headers as $header => $language) {
    check(browser_language($header) === $language, 'Forkert browserprioritering: ' . $header);
  }
  foreach (['da', 'en', 'invalid', ['en']] as $cookie) {
    $_COOKIE[LANGUAGE_COOKIE] = $cookie;
    $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US';
    check(preferred_language() === ($cookie === 'da' ? 'da' : 'en'), 'Ugyldig cookieprioritering.');
  }
  // Ask the OS for an available local port, then start the actual front controller.
  $socket = stream_socket_server('tcp://127.0.0.1:0', $errorNumber, $errorMessage);
  check($socket !== false, 'Kunne ikke finde en ledig testport.');
  $port = (int) substr(strrchr(stream_socket_get_name($socket, false), ':'), 1);
  fclose($socket);
  $server = proc_open([PHP_BINARY, '-S', '127.0.0.1:' . $port, APP_ROOT . '/tools/dev-router.php'],
    [['pipe', 'r'], ['file', $cache . '/server.log', 'a'], ['file', $cache . '/server.log', 'a']], $pipes,
    APP_ROOT, array_merge(getenv(), ['DTS_CACHE_DIR' => $cache, 'PHP_CLI_SERVER_WORKERS' => '']));
  check(is_resource($server), 'Kunne ikke starte den lokale testserver.');
  fclose($pipes[0]);
  $ready = false;
  for ($i = 0; $i < 100; $i++) {
    $connection = @fsockopen('127.0.0.1', $port, $errorNumber, $errorMessage, 0.05);
    if ($connection) { fclose($connection); $ready = true; break; }
    usleep(20000);
  }
  check($ready, 'Testserveren blev ikke klar.');
  $request = function($path, $accept = '', $cookie = '', $agent = 'DTS-Preference-Review') use ($port) {
    $headers = ['User-Agent: ' . $agent];
    if ($accept !== '') $headers[] = 'Accept-Language: ' . $accept;
    if ($cookie !== '') $headers[] = 'Cookie: ' . LANGUAGE_COOKIE . '=' . $cookie;
    $context = stream_context_create(['http' => ['header' => implode("\r\n", $headers), 'ignore_errors' => true, 'follow_location' => 0, 'timeout' => 10]]);
    $body = file_get_contents('http://127.0.0.1:' . $port . $path, false, $context);
    check($body !== false, 'HTTP-request fejlede: ' . $path);
    $response = [];
    foreach ($http_response_header as $line) {
      if (preg_match('#^HTTP/\S+ (\d+)#', $line, $match)) $response['status'] = (int) $match[1];
      elseif (str_contains($line, ':')) { [$key, $value] = explode(':', $line, 2); $response[strtolower($key)] = trim($value); }
    }
    return [$response, $body];
  };
  $cases = [
    ['/', '', '', 200, null, 'da'],
    ['/', 'en-US,en;q=0.9', '', 302, '/en', null],
    ['/', 'de-DE,de;q=0.9', '', 200, null, 'da'],
    ['/', 'da-DK', 'en', 302, '/en', null],
    ['/', 'en-US', 'da', 200, null, 'da'],
    ['/', 'en-US', 'invalid', 302, '/en', null],
    ['/?q=Stripe&utm_source=test', 'en-GB', '', 302, '/en?q=Stripe&utm_source=test', null],
    ['/?site_language=da', 'en-US', 'en', 200, null, 'da'],
    ['/?site_language=da', 'en-US', '', 200, null, 'da'],
    ['/en?site_language=en', 'da-DK', 'da', 200, null, 'en'],
    ['/en?site_language=da', 'en-US', 'en', 302, '/?site_language=da', null],
    ['/alternativer/stripe', 'en-US', 'en', 200, null, 'da'],
    ['/en/alternatives/stripe', 'da-DK', 'da', 200, null, 'en'],
    ['/en/badge?produkt=alunta-com&sprog=da', 'da-DK', 'da', 200, null, 'en'],
    ['/', 'en-US', 'en', 200, null, 'da', 'Googlebot'],
    ['/', 'en-US', '', 200, null, 'da', 'facebookexternalhit/1.1'],
    ['/en', 'da-DK', '', 200, null, 'en', 'Googlebot'],
  ];
  foreach ($cases as $case) {
    [$path, $accept, $cookie, $status, $location, $language] = $case;
    [$response, $body] = $request($path, $accept, $cookie, $case[6] ?? 'DTS-Preference-Review');
    check($response['status'] === $status, 'Forkert HTTP-status: ' . $path . ' / ' . $accept);
    check(($response['location'] ?? null) === $location, 'Forkert redirect: ' . $path);
    if ($language) {
      check(str_contains($body, '<html lang="' . $language . '"'), 'Forkert HTML-sprog: ' . $path);
      check(($response['content-language'] ?? '') === language_tag($language), 'Forkert Content-Language.');
    }
    if ($path === '/') {
      check(str_contains($response['vary'] ?? '', 'Cookie') && str_contains($response['vary'] ?? '', 'Accept-Language'), 'Forsiden mangler Vary.');
    }
    check(str_contains($response['cache-control'] ?? '', 'no-store'), 'Sprogvalg kan caches på tværs af brugere.');
  }
  [$response] = $request('/en', 'da-DK');
  foreach (['dts_language=en', 'Max-Age=31536000', 'HttpOnly', 'SameSite=Lax', 'path=/'] as $attribute) {
    check(str_contains($response['set-cookie'] ?? '', $attribute), 'Sprog-cookien mangler ' . $attribute);
  }
  foreach (['/sitemap.xml', '/badge/vi-stoetter-dansk-tech.svg', '/en/badge/vi-stoetter-dansk-tech.svg'] as $path) {
    [$response] = $request($path, 'en-US', 'da');
    check($response['status'] === 200 && !isset($response['set-cookie']), 'En ressource ændrer sprogpræferencen: ' . $path);
  }
  echo 'OK: ' . count($headers) . ' browserprioriteringer, cookieprioritet, ' . count($cases) . " HTTP-forløb, direkte links, bots, cookie-attributter og ressourcer.\n";
} catch (Throwable $error) {
  fwrite(STDERR, 'FEJL: ' . $error->getMessage() . "\n");
  exit(1);
}
