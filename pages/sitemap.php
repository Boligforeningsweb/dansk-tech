<?php
if (!defined('APP_ROOT')) { http_response_code(404); exit; }

// Alle eksisterende alternativsider er med, også dem med ét dansk produkt.
// Filernes mtime ændres ved deploy og er ikke en pålidelig indholdsdato.
// lastmod er valgfri og udelades, indtil reelle ændringsdatoer vedligeholdes.
$urls = [SITE_URL, SITE_URL . 'alternativer', SITE_URL . 'badge'];
foreach (load_alternatives() as $alternative) {
  $urls[] = SITE_URL . ltrim(alternative_url($alternative['name']), '/');
}

header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as $url): ?>
  <url>
    <loc><?php echo e($url); ?></loc>
  </url>
<?php endforeach; ?>
</urlset>
