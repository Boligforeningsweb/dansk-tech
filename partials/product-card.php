<?php
if (!defined('APP_ROOT')) { http_response_code(404); exit; }

// Forventer: $product, $originalProducts
$name = $product['name'];
$url = $product['url'];
$description = $product['description'];
$alternatives = isset($product['alternatives']) && is_array($product['alternatives']) 
  ? $product['alternatives'] 
  : [];
$alternativesText = implode(', ', $alternatives);
if (mb_strlen($alternativesText) > 60) {
  $alternativesText = mb_substr($alternativesText, 0, 60) . '...';
}

$faviconUrl = favicon_url($url);

$isOriginal = in_array($url, $originalProducts);
$productDataAttributes = 'data-product data-name="' . e(mb_strtolower($name)) . '" data-description="' . e(mb_strtolower($description)) . '" data-alternatives="' . e(mb_strtolower(implode(' ', $alternatives))) . '"';
?>
        <li <?php echo $productDataAttributes; ?>>
          <a href="<?php echo e(outbound_url($url)); ?>" target="_blank" rel="noopener" class="group block p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-gray-300 dark:hover:border-gray-600 hover:shadow-md transition-all duration-200">
            <div class="flex items-start gap-3">
              <div class="flex-shrink-0 mt-0.5 w-8 h-8 rounded bg-gray-200 dark:bg-gray-700 flex items-center justify-center relative overflow-hidden">
                <?php if ($faviconUrl): ?>
                <img src="<?php echo $faviconUrl; ?>" alt="" width="32" height="32" loading="lazy" decoding="async" class="rounded w-full h-full object-contain" onerror="this.style.display='none'; this.parentElement.querySelector('.favicon-fallback').style.display='flex';" />
                <?php endif; ?>
                <span class="favicon-fallback text-sm font-bold text-gray-500 dark:text-gray-400" style="<?php echo $faviconUrl ? 'display: none;' : 'display: flex;'; ?>"><?php echo e(mb_strtoupper(mb_substr($name, 0, 1))); ?></span>
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1 flex-wrap">
                  <h3 class="text-base font-semibold text-gray-900 dark:text-white group-hover:text-gray-700 dark:group-hover:text-gray-200 transition-colors truncate">
                    <?php echo e($name); ?>
                  </h3>
                  <?php if ($isOriginal): ?>
                  <span class="inline-flex items-center rounded-full bg-blue-600 px-2 py-0.5 text-xs font-medium text-white flex-shrink-0">Original</span>
                  <?php endif; ?>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2 mb-2" title="<?php echo e($description); ?>">
                  <?php echo e($description); ?>
                </p>
                <?php if (!empty($alternativesText)): ?>
                <p class="text-xs text-gray-500 dark:text-gray-500">
                  <span class="font-medium">Alternativ til:</span> <span class="text-gray-400 dark:text-gray-500"><?php echo e($alternativesText); ?></span>
                </p>
                <?php endif; ?>
              </div>
            </div>
          </a>
        </li>
