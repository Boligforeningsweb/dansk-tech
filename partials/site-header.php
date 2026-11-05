<?php if (!defined('APP_ROOT')) { http_response_code(404); exit; } ?>
  <header class="border-b border-gray-200 dark:border-white/10">
    <div class="site-header-inner mx-auto flex min-h-16 max-w-7xl items-center justify-between gap-6 px-6 lg:px-8">
      <a href="<?php echo e(locale_path('/')); ?>" class="site-brand flex items-center gap-2.5 text-base font-semibold tracking-tight text-gray-900 hover:text-gray-700 dark:text-white"><?php partial('logo', ['size' => 24]); ?><span><?php echo e(t('Den danske tech stack')); ?></span></a>
      <nav aria-label="<?php echo e(t('Navigation')); ?>" class="site-navigation flex items-center gap-x-6 text-sm font-medium text-gray-600 dark:text-gray-400">
        <a href="<?php echo e(locale_path('/alternativer')); ?>" class="hover:text-gray-900 dark:hover:text-white"><?php echo e(t('Alternativer')); ?></a>
        <a href="<?php echo e(locale_path('/#produkter')); ?>" class="hidden hover:text-gray-900 sm:inline dark:hover:text-white"><?php echo e(t('Alle produkter')); ?></a>
      </nav>
      <div class="site-preferences">
        <?php partial('theme-toggle'); ?>
        <?php partial('language-switcher'); ?>
      </div>
    </div>
  </header>
