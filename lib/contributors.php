<?php
if (!defined('APP_ROOT')) { http_response_code(404); exit; }

// Bidragydere fra GitHub, cachet i CACHE_DIR/contributors.json. Er cachen forældet, vises den
// gamle version med det samme, og den opdateres først, når svaret er sendt til den besøgende.

const CONTRIBUTORS_CACHE = CACHE_DIR . '/contributors.json';
const CONTRIBUTORS_TTL = 21600;
const CONTRIBUTORS_API = 'https://api.github.com/repos/Boligforeningsweb/dansk-tech/contributors?per_page=100';

function load_contributors() {
  $cached = is_file(CONTRIBUTORS_CACHE) ? json_decode(file_get_contents(CONTRIBUTORS_CACHE), true) : null;

  if (!is_array($cached)) {
    return refresh_contributors() ?? [];
  }
  if (time() - filemtime(CONTRIBUTORS_CACHE) > CONTRIBUTORS_TTL) {
    register_shutdown_function(function() {
      if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
      }
      refresh_contributors();
    });
  }
  return $cached;
}

function refresh_contributors() {
  $headers = ['Accept: application/vnd.github+json'];
  if ($token = getenv('GITHUB_TOKEN')) {
    $headers[] = 'Authorization: Bearer ' . $token;
  }
  $response = http_get(CONTRIBUTORS_API, $headers);
  $data = $response && $response[0] === 200 ? json_decode($response[1], true) : null;
  if (!is_array($data)) {
    // Behold den gamle cache, men prøv først igen om en time
    if (is_file(CONTRIBUTORS_CACHE)) {
      @touch(CONTRIBUTORS_CACHE, time() - CONTRIBUTORS_TTL + 3600);
    }
    return null;
  }

  $contributors = [];
  foreach ($data as $contributor) {
    if (($contributor['type'] ?? '') !== 'User') {
      continue;
    }
    $contributors[] = [
      'login' => $contributor['login'],
      'url' => $contributor['html_url'],
      'avatar' => $contributor['avatar_url'],
    ];
  }
  write_cache_file(CONTRIBUTORS_CACHE, json_encode($contributors, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
  return $contributors;
}

// Avatar i den størrelse, den vises i (2x til retina)
function contributor_avatar_url(array $contributor, $size) {
  return $contributor['avatar'] . (str_contains($contributor['avatar'], '?') ? '&' : '?') . 's=' . ($size * 2);
}
