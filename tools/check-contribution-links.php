<?php
// Keep website links and existing bookmarks pointing at the contribution guide.
// Run: php tools/check-contribution-links.php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root = dirname(__DIR__);
$readme = file_get_contents($root . '/readme.md');
$document = new DOMDocument();
$previous = libxml_use_internal_errors(true);
$document->loadHTML($readme, LIBXML_NONET);
libxml_clear_errors();
libxml_use_internal_errors($previous);
$xpath = new DOMXPath($document);
$anchors = [];
foreach ($xpath->query('//*[@id]') as $node) {
  $id = $node->getAttribute('id');
  if (isset($anchors[$id])) {
    fwrite(STDERR, "FEJL: Dubleret README-anker: $id\n");
    exit(1);
  }
  $anchors[$id] = true;
}
foreach (['bidrag', 'kriterier', '-hvordan-bidrager-du', '-retningslinjer-for-bidrag'] as $id) {
  if (!isset($anchors[$id])) {
    fwrite(STDERR, "FEJL: README mangler det stabile eller tidligere bidragsanker: $id\n");
    exit(1);
  }
}
$pattern = <<<'REGEX'
~https://github\.com/Boligforeningsweb/dansk-tech#([^\s"'<>]+)~u
REGEX;
$checked = 0;
foreach (array_merge(glob($root . '/pages/*.php'), glob($root . '/partials/*.php')) as $file) {
  preg_match_all($pattern, file_get_contents($file), $matches);
  foreach ($matches[1] as $fragment) {
    $id = rawurldecode(html_entity_decode($fragment, ENT_QUOTES, 'UTF-8'));
    if (!isset($anchors[$id])) {
      fwrite(STDERR, 'FEJL: ' . substr($file, strlen($root) + 1) . " linker til et manglende README-anker: #$id\n");
      exit(1);
    }
    $checked++;
  }
}
if ($checked === 0) {
  fwrite(STDERR, "FEJL: Ingen links fra sitet til bidragsvejledningen blev fundet.\n");
  exit(1);
}
echo "OK: $checked README-fragmentlinks fra sitet samt stabile og tidligere bidragsankre.\n";
