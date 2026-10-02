<?php
if (!defined('APP_ROOT')) { http_response_code(404); exit; }

// Kun sider, der må indekseres (alternativsider med færre end ALTERNATIVE_MIN_INDEXED produkter udelades)
$lastmod = date('Y-m-d', products_modified_at());
$urls = [SITE_URL, SITE_URL . 'alternativer'];
foreach (load_alternatives() as $alternative) {
  if ($alternative['indexed']) {
    $urls[] = SITE_URL . ltrim(alternative_url($alternative['name']), '/');
  }
}

header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as $url): ?>
  <url>
    <loc><?php echo e($url); ?></loc>
    <lastmod><?php echo $lastmod; ?></lastmod>
  </url>
<?php endforeach; ?>
</urlset>
