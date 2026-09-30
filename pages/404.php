<?php
if (!defined('APP_ROOT')) { http_response_code(404); exit; }

partial('head', [
  'title' => 'Siden findes ikke – Den danske tech stack',
  'description' => 'Siden du leder efter findes ikke. Se hele listen over danske alternativer til udenlandsk software.',
  'robots' => 'noindex, follow',
]);
?>
  <main class="min-h-[70vh] flex items-center justify-center px-6 py-24">
    <div class="text-center max-w-xl">
      <p class="text-base font-semibold text-gray-500 dark:text-gray-400">404</p>
      <h1 class="mt-4 text-4xl font-semibold tracking-tight text-gray-900 sm:text-5xl dark:text-white">Siden findes ikke</h1>
      <p class="mt-6 text-lg/8 text-gray-600 dark:text-gray-400">Vi kunne ikke finde siden, du leder efter. Men der er masser af dansk software på forsiden.</p>
      <div class="mt-10 flex items-center justify-center gap-x-6">
        <a href="/#produkter" class="rounded-md bg-gray-900 px-6 py-3.5 text-base font-semibold text-white shadow-sm hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100">
          Se produkter fra 🇩🇰
        </a>
        <a href="/" class="text-base font-semibold text-gray-900 hover:text-gray-700 dark:text-white dark:hover:text-gray-300">
          Til forsiden <span aria-hidden="true">→</span>
        </a>
      </div>
    </div>
  </main>
<?php partial('footer'); ?>
</body>
</html>
