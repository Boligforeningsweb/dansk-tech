<?php
if (!defined('APP_ROOT')) { http_response_code(404); exit; }

// Alle eksisterende alternativsider er med, også dem med ét dansk produkt.
// Filernes mtime ændres ved deploy og er ikke en pålidelig indholdsdato.
// lastmod er valgfri og udelades, indtil reelle ændringsdatoer vedligeholdes.
$paths = ['/', '/alternativer', '/badge'];
foreach (load_alternatives() as $alternative) {
  $paths[] = '/alternativer/' . $alternative['slug'];
}

header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
<?php foreach ($paths as $path): foreach (['da', 'en'] as $language): ?>
  <url>
    <loc><?php echo e(locale_url($path, $language)); ?></loc>
    <xhtml:link rel="alternate" hreflang="da" href="<?php echo e(locale_url($path, 'da')); ?>" />
    <xhtml:link rel="alternate" hreflang="en" href="<?php echo e(locale_url($path, 'en')); ?>" />
    <xhtml:link rel="alternate" hreflang="x-default" href="<?php echo e(locale_url($path, 'da')); ?>" />
  </url>
<?php endforeach; endforeach; ?>
</urlset>
