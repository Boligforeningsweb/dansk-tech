<?php
if (!defined('APP_ROOT')) { http_response_code(404); exit; }
$shareUrl = locale_url();
$shareText = t('Tjek lige den danske tech stack ud. Man kan sagtens købe fed software i Danmark. Se dansktechstack.dk');
?>
  <div class="mx-auto max-w-7xl px-6 lg:px-8">
    <div class="border-t border-gray-300 dark:border-white/15"></div>
  </div>
  <div class="bg-white py-24 sm:py-32 dark:bg-gray-900">
    <div class="mx-auto max-w-7xl px-6 lg:px-8">
      <div class="mx-auto max-w-2xl text-center">
        <h2 class="text-3xl font-semibold tracking-tight text-gray-900 sm:text-4xl dark:text-white"><?php echo e(t('Del budskabet')); ?></h2>
        <p class="mt-6 text-lg/8 text-gray-600 dark:text-gray-400">
          <?php echo e(t('Hjælp os med at sprede budskabet om den danske tech stack. Del dette med andre danske virksomheder, der leder efter alternativer.')); ?>
        </p>
        <div class="mt-10 flex flex-wrap items-center justify-center gap-x-4 gap-y-3">
          <a href="https://x.com/intent/post?<?php echo e(http_build_query(['text' => $shareText, 'url' => $shareUrl])); ?>" target="_blank" rel="noopener noreferrer" class="rounded-md bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100">
            <?php echo e(t('Del på X')); ?>
          </a>
          <a href="https://www.linkedin.com/sharing/share-offsite/?<?php echo e(http_build_query(['url' => $shareUrl])); ?>" target="_blank" rel="noopener noreferrer" class="rounded-md bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
            <?php echo e(t('Del på LinkedIn')); ?>
          </a>
          <a href="https://www.facebook.com/sharer/sharer.php?<?php echo e(http_build_query(['u' => $shareUrl])); ?>" target="_blank" rel="noopener noreferrer" class="rounded-md bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">
            <?php echo e(t('Del på Facebook')); ?>
          </a>

        </div>
      </div>
    </div>
  </div>
