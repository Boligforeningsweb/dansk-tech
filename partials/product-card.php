<?php
if (!defined('APP_ROOT')) { http_response_code(404); exit; }

// Forventer: $product, $originalProducts. Valgfrit: $headingTag (h3 på forsiden, h2 på alternativsiderne)
$headingTag = $headingTag ?? 'h3';
$name = $product['name'];
$url = $product['url'];
$description = $product['description'];
$alternatives = isset($product['alternatives']) && is_array($product['alternatives'])
  ? $product['alternatives']
  : [];

// Vis så mange alternativer, der er plads til (ca. 60 tegn), resten som "+N"
$shownAlternatives = [];
$length = 0;
foreach ($alternatives as $alternative) {
  $add = mb_strlen($alternative) + ($shownAlternatives ? 2 : 0);
  if ($shownAlternatives && $length + $add > 60) {
    break;
  }
  $shownAlternatives[] = $alternative;
  $length += $add;
}
$hiddenAlternatives = count($alternatives) - count($shownAlternatives);

$faviconUrl = favicon_url($url);

$isOriginal = in_array($url, $originalProducts);
$productDataAttributes = 'data-product data-name="' . e(mb_strtolower($name)) . '" data-description="' . e(mb_strtolower($description)) . '" data-alternatives="' . e(mb_strtolower(implode(' ', $alternatives))) . '"';
?>
        <li <?php echo $productDataAttributes; ?>>
          <div class="group relative block p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-gray-300 dark:hover:border-gray-600 hover:shadow-md transition-all duration-200">
            <div class="flex items-start gap-3">
              <div class="flex-shrink-0 mt-0.5 w-8 h-8 rounded bg-gray-200 dark:bg-gray-700 flex items-center justify-center relative overflow-hidden">
                <?php if ($faviconUrl): ?>
                <img src="<?php echo $faviconUrl; ?>" alt="" width="32" height="32" loading="lazy" decoding="async" class="rounded w-full h-full object-contain" onerror="this.style.display='none'; this.parentElement.querySelector('.favicon-fallback').style.display='flex';" />
                <?php endif; ?>
                <span class="favicon-fallback text-sm font-bold text-gray-500 dark:text-gray-400" style="<?php echo $faviconUrl ? 'display: none;' : 'display: flex;'; ?>"><?php echo e(mb_strtoupper(mb_substr($name, 0, 1))); ?></span>
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1 flex-wrap">
                  <<?php echo $headingTag; ?> class="text-base font-semibold text-gray-900 dark:text-white group-hover:text-gray-700 dark:group-hover:text-gray-200 transition-colors truncate">
                    <?php // Linket dækker hele kortet (after:inset-0); alternativ-linksene ligger ovenpå ?>
                    <a href="<?php echo e(outbound_url($url)); ?>" target="_blank" rel="noopener" title="<?php echo e($description); ?>" class="after:absolute after:inset-0 after:rounded-lg focus:outline-none focus-visible:after:ring-2 focus-visible:after:ring-gray-900 dark:focus-visible:after:ring-white"><?php echo e($name); ?></a>
                  </<?php echo $headingTag; ?>>
                  <?php if ($isOriginal): ?>
                  <span class="inline-flex items-center rounded-full bg-blue-600 px-2 py-0.5 text-xs font-medium text-white flex-shrink-0">Original</span>
                  <?php endif; ?>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2 mb-2">
                  <?php echo e($description); ?>
                </p>
                <?php if ($shownAlternatives): ?>
                <p class="text-xs text-gray-500 dark:text-gray-500">
                  <span class="font-medium text-gray-600 dark:text-gray-400">Alternativ til:</span>
                  <span class="text-gray-500 dark:text-gray-400"><?php
                    echo implode(', ', array_map(function($alternative) {
                      return '<a href="' . e(alternative_url($alternative)) . '" class="relative z-10 hover:text-gray-900 hover:underline dark:hover:text-white">' . e($alternative) . '</a>';
                    }, $shownAlternatives));
                    if ($hiddenAlternatives) {
                      echo ' +' . $hiddenAlternatives;
                    }
                  ?></span>
                </p>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </li>
