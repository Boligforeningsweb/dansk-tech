<?php
if (!defined('APP_ROOT')) { http_response_code(404); exit; }

// Simpelt GET-kald med timeout og maks. størrelse. Returnerer [status, body] eller null ved fejl.
function http_get($url, array $headers = [], $timeout = 4, $maxBytes = 1048576) {
  $ch = curl_init($url);
  $body = '';
  curl_setopt_array($ch, [
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_MAXREDIRS => 3,
    CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
    CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTPS,
    CURLOPT_CONNECTTIMEOUT => 2,
    CURLOPT_TIMEOUT => $timeout,
    CURLOPT_USERAGENT => 'dansktechstack.dk',
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_WRITEFUNCTION => function($ch, $chunk) use (&$body, $maxBytes) {
      $body .= $chunk;
      return strlen($body) > $maxBytes ? 0 : strlen($chunk);
    },
  ]);
  $ok = curl_exec($ch);
  $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
  curl_close($ch);
  return $ok === false ? null : [$status, $body];
}

// Skriv filen atomisk, så en samtidig læsning aldrig ser en halv fil
function write_cache_file($path, $contents) {
  $dir = dirname($path);
  if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
    return false;
  }
  $tmp = $path . '.' . bin2hex(random_bytes(4)) . '.tmp';
  if (@file_put_contents($tmp, $contents) === false) {
    return false;
  }
  return @rename($tmp, $path);
}
