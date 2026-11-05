<?php
if (!defined('APP_ROOT')) { http_response_code(404); exit; }

// Forventer: $badge og $product (null for badges uden produkt)
$variants = [
  ['kompakt', 'lys', t('Kompakt, lys')],
  ['kompakt', 'moerk', t('Kompakt, mørk')],
  ['stor', 'lys', t('Stor, lys')],
  ['stor', 'moerk', t('Stor, mørk')],
];
?>
      <ul role="list" class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2">
        <?php foreach ($variants as [$format, $theme, $label]): ?>
        <li class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
          <div class="badge-preview flex h-32 items-center justify-center px-4 <?php echo $theme === 'moerk' ? 'bg-gray-900' : 'bg-gray-50'; ?>"><?php echo badge_svg($badge, $theme, $format); ?></div>
          <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-200 bg-white px-4 py-3 dark:border-gray-700 dark:bg-gray-800">
            <span class="text-sm font-medium text-gray-900 dark:text-white"><?php echo e($label); ?></span>
            <span class="flex flex-wrap gap-2">
              <button type="button" data-copy="<?php echo e(badge_html($product, $badge, $theme, $format)); ?>" class="copy rounded-md bg-gray-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-gray-800 dark:bg-white dark:text-gray-900"><?php echo e(t('Kopiér HTML')); ?></button>
              <button type="button" data-copy="<?php echo e(badge_markdown($product, $badge, $theme, $format)); ?>" class="copy rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"><?php echo e(t('Kopiér Markdown')); ?></button>
            </span>
          </div>
        </li>
        <?php endforeach; ?>
      </ul>
      <p class="mt-6 text-sm text-gray-700 dark:text-gray-300"><?php echo e(t('HTML for "Kompakt, lys":')); ?></p>
      <pre class="mt-2 whitespace-pre-wrap break-all rounded-lg bg-gray-900 p-4 text-xs/5 text-gray-100"><code><?php echo e(badge_html($product, $badge)); ?></code></pre>
