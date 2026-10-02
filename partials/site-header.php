<?php if (!defined('APP_ROOT')) { http_response_code(404); exit; } ?>
  <header class="border-b border-gray-200 dark:border-white/10">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-6 px-6 lg:px-8">
      <a href="/" class="flex items-center gap-2.5 text-base font-semibold tracking-tight text-gray-900 hover:text-gray-700 dark:text-white"><?php partial('logo', ['size' => 24]); ?>Den danske tech stack</a>
      <nav class="flex items-center gap-x-6 text-sm font-medium text-gray-600 dark:text-gray-400">
        <a href="/alternativer" class="hover:text-gray-900 dark:hover:text-white">Alternativer</a>
        <a href="/#produkter" class="hidden hover:text-gray-900 sm:inline dark:hover:text-white">Alle produkter</a>
      </nav>
    </div>
  </header>
