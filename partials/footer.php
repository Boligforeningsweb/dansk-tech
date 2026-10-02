<?php if (!defined('APP_ROOT')) { http_response_code(404); exit; } ?>
  <footer class="bg-white dark:bg-gray-900">
    <div class="mx-auto max-w-7xl px-6 py-12 md:flex md:items-center md:justify-between lg:px-8">
      <div class="flex flex-wrap justify-center gap-x-6 gap-y-2 md:order-2">
        <a href="/alternativer" class="text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white">
          Alle alternativer
        </a>
        <a href="mailto:kontakt@langsom.com" class="text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white">
          Kontakt os på kontakt@langsom.com
        </a>
      </div>
      <div class="mt-8 flex flex-col items-center gap-4 md:order-1 md:mt-0 md:flex-row md:gap-6">
        <?php $footerBadge = supporter_badges()['vi-stoetter-dansk-tech']; [$badgeWidth, $badgeHeight] = badge_size($footerBadge, 'kompakt'); ?>
        <a href="/badge" title="Hent badget til jeres website" class="flex-none"><img src="/badge/<?php echo $footerBadge['slug']; ?>.svg" alt="<?php echo e($footerBadge['title']); ?>" width="<?php echo $badgeWidth; ?>" height="<?php echo $badgeHeight; ?>" loading="lazy" /></a>
      <p class="text-center text-sm/6 text-gray-600 md:text-left dark:text-gray-400">Bygget i København af folkene fra <a href="https://langsom.com">langsom.com</a> + venner fra branchen.</p>
      </div>
    </div>
  </footer>
