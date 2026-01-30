<div id="produkter" class="bg-white py-24 sm:py-32 dark:bg-gray-900">
  <div class="mx-auto max-w-7xl px-6 lg:px-8">
    <div class="mx-auto max-w-3xl text-center">
      <h2 class="text-3xl font-semibold tracking-tight text-gray-900 sm:text-4xl dark:text-white">Tech fra 🇩🇰</h2>
      <p class="mt-6 text-base text-gray-600 dark:text-gray-400">
        Oversigt over danske systemer til din tech stack
      </p>
    </div>
    <div class="mt-8">
      <div class="max-w-md mx-auto">
        <input type="text" id="product-search" placeholder="Søg efter international software, dansk software eller kategori..."
               class="w-full px-4 py-3 text-base border border-gray-300 rounded-lg dark:bg-gray-800 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-white" />
        <div class="mt-2 flex flex-wrap items-center justify-center gap-2">
          <span class="text-xs text-gray-500 dark:text-gray-400">Eksempel:</span>
          <button type="button" class="search-example text-xs px-2.5 py-1 rounded-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors" data-search="Stripe">Stripe</button>
          <button type="button" class="search-example text-xs px-2.5 py-1 rounded-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors" data-search="DownDetector">DownDetector</button>
          <button type="button" class="search-example text-xs px-2.5 py-1 rounded-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors" data-search="Mailchimp">Mailchimp</button>
          <button type="button" class="search-example text-xs px-2.5 py-1 rounded-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors" data-search="Zendesk">Zendesk</button>
        </div>
      </div>
    </div>
    <div id="products-container">
      <ul role="list" class="mx-auto mt-10 grid max-w-5xl grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2 lg:mx-0 lg:max-w-none lg:grid-cols-3 xl:grid-cols-4 [&>li]:h-full">
      <?php foreach ($products as $product): ?>
        <?php include __DIR__ . '/product-card.php'; ?>
      <?php endforeach; ?>
      </ul>
      <div id="no-results" class="mt-10 text-center text-gray-500 dark:text-gray-400 hidden">
        <p>Ingen produkter fundet. Prøv at søge efter et andet navn eller international software.</p>
      </div>
    </div>
  </div>
</div>

<div class="mx-auto max-w-7xl px-6 lg:px-8">
  <div class="border-t border-gray-300 dark:border-white/15"></div>
</div>

<div class="mx-auto max-w-7xl px-6 lg:px-8">
  <div class="border-t border-gray-300 dark:border-white/15"></div>
</div>
