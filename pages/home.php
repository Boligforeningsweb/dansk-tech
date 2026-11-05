<?php
if (!defined('APP_ROOT')) { http_response_code(404); exit; }

$products = load_products();
$originalProducts = load_original_products();
$people = load_json('data/people.json');
$contributors = load_contributors();

$productCount = count($products);
$title = t('Den danske tech stack – {count} danske alternativer til Big Tech', ['{count}' => $productCount]);
$description = t('Find danske alternativer til Stripe, Shopify, Mailchimp, Zendesk og andre udenlandske systemer. {count} danske SaaS-produkter, anbefalet af danske iværksættere.', ['{count}' => $productCount]);
$socialDescription = t("Betalinger, e-mail, support, regnskab, monitoring og meget mere – bygget i Danmark. Find danske alternativer til de udenlandske giganter.");
// Versions-hash i URL'en, så LinkedIn m.fl. henter billedet igen, når det opdateres
$ogImage = SITE_URL . ltrim(asset_url(locale() === 'en' ? 'og-image-en.jpg' : 'og-image.jpg'), '/');
$ogImageAlt = t('Den danske tech stack: {count} danske alternativer til Stripe, Shopify, Mailchimp, Zendesk og co.', ['{count}' => at_least($productCount)]);

// Schema.org JSON-LD, built from the same data as the page
$faq = [
  [
    'q' => t('Hvad er den danske tech stack?'),
    'a' => t('Den danske tech stack er en åben, kurateret liste over {count} danske software-produkter, som kan bruges som alternativer til store internationale spillere som Stripe, Shopify, Mailchimp og Zendesk. Listen er startet af en gruppe danske iværksættere og vedligeholdes på GitHub.', ['{count}' => $productCount]),
  ],
  [
    'q' => t('Hvornår tæller et produkt som dansk?'),
    'a' => t('Et produkt kommer på listen, hvis virksomheden har hovedkontor i Danmark, har en dansk stifter eller medstifter, eller primært er dansk ejet. Produktet skal samtidig kunne indgå i en tech stack hos SaaS-, e-commerce- eller andre tech-virksomheder.'),
  ],
  [
    'q' => t('Hvorfor vælge dansk software?'),
    'a' => t('Dansk software kan være et valg, hvis du vil støtte det danske tech-miljø og undersøge leverandører med tilknytning til Danmark. Dansk tilknytning garanterer ikke dansk support, datalagring i Danmark eller overholdelse af GDPR. Afklar support, databehandling og kontraktvilkår hos den enkelte leverandør.'),
  ],
  [
    'q' => t('Betaler produkterne for at være på listen?'),
    'a' => t('Nej. Ingen produkter betaler for at være med, og vi tjener ikke penge på links. Et produkt kommer på listen, fordi det opfylder kriterierne – ikke fordi det har betalt.'),
  ],
  [
    'q' => t('Hvordan foreslår jeg et produkt til listen?'),
    'a' => t('Send en pull request på GitHub, hvor du tilføjer produktet til filen products.json med navn, URL, en kort beskrivelse og de internationale produkter, det er et alternativ til. Vi gennemgår forslaget og tilføjer det til listen.'),
  ],
];

$schema = schema_json([
  schema_organization(),
  schema_website(),
  [
    '@type' => 'CollectionPage',
    '@id' => locale_url() . '#webpage',
    'url' => locale_url(),
    'name' => $title,
    'description' => $description,
    'inLanguage' => language_tag(),
    'isPartOf' => ['@id' => SITE_URL . '#website'],
    'about' => ['@id' => SITE_URL . '#organization'],
    'primaryImageOfPage' => [
      '@type' => 'ImageObject',
      'url' => $ogImage,
      'width' => 1200,
      'height' => 630,
    ],
    'mainEntity' => ['@id' => locale_url() . '#produkter'],
  ],
  [
    '@type' => 'ItemList',
    '@id' => locale_url() . '#produkter',
    'name' => t('Danske alternativer til udenlandsk software'),
    'description' => t('Liste over danske tech-systemer foreslået som alternativer til internationale SaaS-produkter'),
    'numberOfItems' => $productCount,
    'itemListOrder' => 'https://schema.org/ItemListOrderAscending',
    'itemListElement' => schema_product_list_items($products),
  ],
  [
    '@type' => 'FAQPage',
    '@id' => locale_url() . '#faq',
    'mainEntity' => array_map(function($item) {
      return [
        '@type' => 'Question',
        'name' => $item['q'],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['a']],
      ];
    }, $faq),
  ],
]);

partial('head', [
  'title' => $title,
  'description' => $description,
  'canonical' => locale_url(),
  'socialDescription' => $socialDescription,
  'ogImage' => $ogImage,
  'ogImageAlt' => $ogImageAlt,
  'schema' => $schema,
]);
?>
  <main id="main-content" tabindex="-1">
  <div class="home-hero min-h-screen flex items-center justify-center relative" style="background-image: url('/images/danmark.webp'); background-size: cover; background-position: center; background-repeat: no-repeat;">
    <div class="absolute inset-0 bg-white/70 dark:bg-gray-900/90"></div>
    <div class="home-language-switcher"><?php partial('theme-toggle'); partial('language-switcher'); ?></div>
    <div class="mx-auto max-w-7xl px-6 lg:px-8 w-full relative z-10">
      <div class="mx-auto max-w-3xl text-center">
        <?php partial('logo', ['size' => 64, 'class' => 'mx-auto mb-7 size-12 sm:size-16']); ?>
        <h1 class="text-5xl font-semibold tracking-tight text-pretty text-gray-900 sm:text-6xl lg:text-7xl dark:text-white">
          <?php echo e(t('Den danske tech stack')); ?>
        </h1>
        <p class="mx-auto mt-7 max-w-[600px] text-xl/8 text-gray-600 dark:text-gray-200">
          <?php echo e(t('Vi vil sætte fokus på dansk software og gøre det lettere at finde danske alternativer til din tech stack. Selve websitet er non-profit og open source. Du er velkommen til at foreslå produkter til listen.')); ?>
        </p>
        <div class="mt-8 flex flex-wrap items-center justify-center gap-x-5 gap-y-3">
          <a href="#produkter" class="inline-flex items-center justify-center rounded-xl bg-gray-900 px-7 py-3.5 text-base font-semibold text-white transition-all hover:-translate-y-0.5 hover:bg-gray-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100">
            <?php echo e(t('Se produkterne')); ?>
          </a>
          <a href="https://github.com/Boligforeningsweb/dansk-tech" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 rounded-xl px-3 py-3.5 text-base font-medium text-gray-900 transition-colors hover:text-gray-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900 dark:text-white dark:hover:text-gray-300 dark:focus-visible:outline-white">
            <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.942.359.31.678.921.678 1.856 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/></svg>
            <?php echo e(t('Tilføj produkt')); ?>
          </a>
        </div>
        <p class="mt-5 text-sm/6 text-gray-600 dark:text-gray-400">
          <?php echo e(t('Et initiativ fra')); ?> <a href="#bag-initiativet" class="underline decoration-gray-400 underline-offset-4 hover:text-gray-900 dark:hover:text-white"><?php echo e(t('iværksættere og tech-folk')); ?></a>.
        </p>

        <!-- Media mentions section -->
        <div class="mt-16 pt-12 border-t border-gray-200/50 dark:border-gray-700/50">
          <p class="text-xs font-medium uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-8"><?php echo e(t('Omtalt i bl.a.')); ?></p>
          <div class="flex flex-wrap items-center justify-center gap-10 sm:gap-16">
            <?php foreach (load_json('data/press.json') as $press): ?>
            <div class="group flex items-center justify-center h-12 opacity-90 hover:opacity-100 transition-opacity duration-300">
              <img src="<?php echo e($press['image']); ?>" width="<?php echo (int) $press['width']; ?>" height="<?php echo (int) $press['height']; ?>" alt="<?php echo e($press['name']); ?>" class="h-full w-auto max-w-[140px] object-contain transition-all duration-300" />
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div id="produkter" class="bg-white py-24 sm:py-32 dark:bg-gray-900">
    <div class="mx-auto max-w-7xl px-6 lg:px-8">
      <div class="mx-auto max-w-3xl text-center">
        <h2 class="flex items-center justify-center gap-3 text-3xl font-semibold tracking-tight text-gray-900 sm:text-4xl dark:text-white"><?php partial('logo', ['size' => 36]); ?><?php echo e(t('Tech fra Danmark')); ?></h2>
        <p class="mt-6 text-base text-gray-600 dark:text-gray-400">
          <?php echo e(t('{count} danske systemer til din tech stack – søg på det udenlandske produkt, du gerne vil erstatte', ['{count}' => $productCount])); ?>
        </p>
      </div>
      <div class="mt-8">
        <div class="max-w-md mx-auto">
          <label class="sr-only" for="product-search"><?php echo e(t('Søg efter international software, dansk software eller kategori...')); ?></label>
          <input type="search" id="product-search" value="<?php echo e(query_value('q')); ?>" placeholder="<?php echo e(t('Søg efter international software, dansk software eller kategori...')); ?>"
                 class="w-full px-4 py-3 text-base border border-gray-300 rounded-lg placeholder:text-gray-600 dark:placeholder:text-gray-400 dark:bg-gray-800 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-white" />
          <p id="search-status" role="status" aria-live="polite" aria-atomic="true" class="sr-only"></p>
          <div class="mt-2 flex flex-wrap items-center justify-center gap-2">
            <span class="text-xs text-gray-500 dark:text-gray-400"><?php echo e(t('Eksempel:')); ?></span>
            <button type="button" class="search-example text-xs px-2.5 py-1 rounded-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors" data-search="Stripe">Stripe</button>
            <button type="button" class="search-example text-xs px-2.5 py-1 rounded-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors" data-search="DownDetector">DownDetector</button>
            <button type="button" class="search-example text-xs px-2.5 py-1 rounded-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors" data-search="Mailchimp">Mailchimp</button>
            <button type="button" class="search-example text-xs px-2.5 py-1 rounded-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors" data-search="Zendesk">Zendesk</button>
          </div>
          <p id="alternative-suggestion" class="mt-4 hidden text-center text-sm">
            <a href="#" class="font-semibold text-gray-900 hover:underline dark:text-white"></a>
          </p>
        </div>
      </div>
      <div id="products-container">
        <ul role="list" class="mx-auto mt-10 grid max-w-5xl grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2 lg:mx-0 lg:max-w-none lg:grid-cols-3 xl:grid-cols-4">
        <?php foreach ($products as $product) {
          partial('product-card', ['product' => $product, 'originalProducts' => $originalProducts]);
        } ?>
      </ul>
      <div id="no-results" class="mt-10 text-center text-gray-500 dark:text-gray-400 hidden">
        <p><?php echo e(t('Ingen produkter fundet. Prøv at søge efter et andet navn eller international software.')); ?></p>
      </div>
      </div>
      <p class="mt-12 text-center">
        <a href="<?php echo e(locale_path('/alternativer')); ?>" class="text-base font-semibold text-gray-900 hover:text-gray-700 dark:text-white dark:hover:text-gray-300"><?php echo e(t('Se danske alternativer til alle {count} udenlandske systemer', ['{count}' => count(load_alternatives())])); ?> <span aria-hidden="true">→</span></a>
      </p>
    </div>
  </div>

  <div class="mx-auto max-w-7xl px-6 lg:px-8">
    <div class="border-t border-gray-300 dark:border-white/15"></div>
  </div>

  <div class="mx-auto max-w-7xl px-6 lg:px-8">
    <div class="border-t border-gray-300 dark:border-white/15"></div>
  </div>
  <div id="iværksættere" class="bg-white py-24 sm:py-32 dark:bg-gray-900">
    <div class="mx-auto max-w-7xl px-6 lg:px-8">
      <div class="mx-auto max-w-3xl text-center">
        <h2 class="text-4xl font-semibold tracking-tight text-pretty text-gray-900 sm:text-5xl dark:text-white"><?php echo e(t('Tusind tak til dem der støttede projektet helt fra start')); ?></h2>
        <p class="mt-6 text-lg/8 text-gray-600 dark:text-gray-400"><?php echo e(t('Vi har talt med bekendte i tech-miljøet og fået dem med til også at lægge navn til anbefalingerne. Disse personer støtter initiativet og bygger og investerer selv i de danske systemer. Alle på listen her har fået lov at udnævne et stykke software til listen, som så har fået')); ?> <span class="inline-flex items-center rounded-full bg-blue-600 px-2 py-0.5 text-xs font-medium text-white">Original</span> <?php echo e(t('label\'en i oversigten over produkter.')); ?></p>
      </div>
      <ul role="list" class="backer-grid mx-auto mt-20 grid max-w-2xl gap-x-8 gap-y-16 text-center lg:mx-0 lg:max-w-none">
        <?php foreach ($people['backers'] as $person): ?>
        <li>
          <img src="<?php echo e($person['image']); ?>" alt="<?php echo e($person['name']); ?>" width="96" height="96" loading="lazy" class="mx-auto size-24 rounded-full object-cover outline-1 -outline-offset-1 outline-black/5 dark:outline-white/10" />
          <h3 class="mt-6 text-base/7 font-semibold tracking-tight text-gray-900 dark:text-white"><?php echo e($person['name']); ?></h3>
          <p class="text-sm/6 text-gray-600 dark:text-gray-400"><?php echo person_role($person); ?></p>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
  <div class="mx-auto max-w-7xl px-6 lg:px-8">
    <div class="border-t border-gray-300 dark:border-white/15"></div>
  </div>

  <div id="bag-initiativet" class="bg-white py-24 sm:py-32 dark:bg-gray-900">
    <div class="mx-auto max-w-7xl px-6 lg:px-8">
      <div class="mx-auto max-w-3xl text-center">
        <h2 class="text-4xl font-semibold tracking-tight text-pretty text-gray-900 sm:text-5xl dark:text-white"><?php echo e(t('Hvem står bag initiativet?')); ?></h2>
        <p class="mt-6 text-lg/8 text-gray-600 dark:text-gray-400"><?php echo e(t('Vi er fire glade iværksættere og udviklere fra Danmark som elsker tech, business og at bygge virksomheder. Vi har fulgt debatten omkring at Danmark måske "falder bagud" internationalt og vi er nok desværre enige. Derfor vil vi gerne prøve at kaste lidt lys på danske tech-firmaer som vi mener er gode alternativer til de store internationale spillere, når man leder efter produkter.')); ?></p>
      </div>
      <ul role="list" class="mx-auto mt-20 grid max-w-2xl grid-cols-1 gap-x-8 gap-y-16 text-center sm:grid-cols-2 lg:mx-0 lg:max-w-none lg:grid-cols-4 lg:justify-items-center">
        <?php foreach ($people['founders'] as $person): ?>
        <li>
          <img src="<?php echo e($person['image']); ?>" alt="<?php echo e($person['name']); ?>" width="96" height="96" loading="lazy" class="mx-auto size-24 rounded-full object-cover outline-1 -outline-offset-1 outline-black/5 dark:outline-white/10" />
          <h3 class="mt-6 text-base/7 font-semibold tracking-tight text-gray-900 dark:text-white"><?php echo e($person['name']); ?></h3>
          <p class="text-sm/6 text-gray-600 dark:text-gray-400"><?php echo person_role($person); ?></p>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>


  <div class="mx-auto max-w-7xl px-6 lg:px-8">
    <div class="border-t border-gray-300 dark:border-white/15"></div>
  </div>
  <div id="forslag" class="bg-white py-24 sm:py-32 dark:bg-gray-900">
    <div class="mx-auto max-w-7xl px-6 lg:px-8">
      <div class="mx-auto max-w-2xl text-center">
        <h2 class="text-3xl font-semibold tracking-tight text-gray-900 sm:text-4xl dark:text-white"><?php echo e(t('Send dit forslag via')); ?> <a href="https://github.com/Boligforeningsweb/dansk-tech" class="underline">GitHub</a></h2>
        <p class="mt-6 text-lg/8 text-gray-600 dark:text-gray-400">
          <?php echo e(t('Er du selv udvikler og kender lige præcis dét stykke software fra Danmark som mangler på listen? Så kan du sende os en pull request og foreslå til listen af produkter fra eksterne bidragydere. Herunder kan se de')); ?> <b><?php echo count($contributors); ?> <?php echo e(t('bidragsydere')); ?></b> <?php echo e(t('der har budt ind med forslag indtil videre. Det er hhv.')); ?> <a href="https://github.com/BoMoellerDK" class="underline">Bo Møller</a> <?php echo e(t('og')); ?> <a href="https://github.com/jonasdev" class="underline">Jonas Kaas Kristensen</a> <?php echo e(t('der styrer repo\'et.')); ?>
        </p>
        <div id="contributors" class="mt-10 flex flex-wrap items-center justify-center gap-x-4 gap-y-2">
          <?php foreach ($contributors as $contributor): ?>
          <a href="<?php echo e($contributor['url']); ?>" target="_blank" rel="noopener noreferrer"><img src="<?php echo e(contributor_avatar_url($contributor, 48)); ?>" alt="<?php echo e($contributor['login']); ?>" width="48" height="48" loading="lazy" decoding="async" class="rounded-full object-cover outline-1 -outline-offset-1 outline-black/5 dark:outline-white/10"></a>
          <?php endforeach; ?>
        </div>
        <div class="mt-12 flex flex-col items-center gap-4 rounded-2xl bg-gray-50 px-6 py-6 sm:flex-row sm:justify-center sm:gap-6 dark:bg-gray-800">
          <a href="<?php echo e(locale_path('/badge')); ?>" class="badge-preview flex-none" aria-hidden="true" tabindex="-1"><?php echo badge_svg(['label' => t('Dansk alternativ til'), 'name' => 'Stripe'], 'lys', 'kompakt'); ?></a>
          <p class="text-sm/6 text-gray-600 sm:text-left dark:text-gray-400"><span class="font-semibold text-gray-900 dark:text-white"><?php echo e(t('Er jeres produkt på listen?')); ?></span> <?php echo e(t('Vis det på jeres website med et gratis badge.')); ?> <a href="<?php echo e(locale_path('/badge')); ?>" class="whitespace-nowrap font-semibold text-gray-900 hover:underline dark:text-white"><?php echo e(t('Hent et badge')); ?> <span aria-hidden="true">→</span></a></p>
        </div>
      </div>
    </div>
  </div>

  <div class="mx-auto max-w-7xl px-6 lg:px-8">
    <div class="border-t border-gray-300 dark:border-white/15"></div>
  </div>
  <div class="bg-white py-24 sm:py-32 dark:bg-gray-900">
    <div class="mx-auto max-w-7xl px-6 lg:px-8">
      <div class="mx-auto max-w-3xl text-center">
        <img src="/images/dhh.webp" alt="David Heinemeier Hansson" width="288" height="288" loading="lazy" class="mx-auto w-28 h-28 md:w-32 md:h-32 rounded-full object-cover ring-4 ring-white dark:ring-gray-900 shadow-lg" />
        <figure class="mt-8 inline-block bg-gray-50 dark:bg-gray-800 rounded-lg p-6 md:p-8 text-center">
          <blockquote class="text-lg md:text-xl text-gray-700 dark:text-gray-300 leading-relaxed italic"><?php echo e(t('“Danmark kan og skal være langt mere selvforsynende med software. Vi kan ikke bygge et resistent samfund på en digital infrastruktur, der er næsten udelukkende leveret af Amerikanerne eller Kineserne. Så et fokus på hvad der allerede findes i dag og danske virksomheder og institutioner kan komme igang med i morgen er super”')); ?></blockquote>
          <figcaption class="mt-4 text-sm text-gray-600 dark:text-gray-400 font-medium">David Heinemeier Hansson <br/> <?php echo e(t('Stifter af Ruby on Rails, Bestseller-forfatter og medejer af flere danske startups')); ?></figcaption>
        </figure>
      </div>
    </div>
  </div>

  <div class="mx-auto max-w-7xl px-6 lg:px-8">
    <div class="border-t border-gray-300 dark:border-white/15"></div>
  </div>
  <div id="faq" class="bg-white py-24 sm:py-32 dark:bg-gray-900">
    <div class="mx-auto max-w-3xl px-6 lg:px-8">
      <h2 class="text-3xl font-semibold tracking-tight text-gray-900 sm:text-4xl dark:text-white text-center"><?php echo e(t('Ofte stillede spørgsmål')); ?></h2>
      <dl class="mt-12 divide-y divide-gray-200 dark:divide-white/10">
        <?php foreach ($faq as $item): ?>
        <div class="py-6">
          <dt class="text-base/7 font-semibold text-gray-900 dark:text-white"><?php echo e($item['q']); ?></dt>
          <dd class="mt-2 text-base/7 text-gray-600 dark:text-gray-400"><?php echo e($item['a']); ?></dd>
        </div>
        <?php endforeach; ?>
      </dl>
    </div>
  </div>

<?php partial('share'); ?>
  </main>
<?php partial('footer'); ?>
  <script type="text/javascript">
    // Product search functionality
    (function() {
      const searchInput = document.getElementById('product-search');
      const productItems = document.querySelectorAll('[data-product]');
      const productsList = document.querySelector('#products-container > ul');
      const noResults = document.getElementById('no-results');
      const searchStatus = document.getElementById('search-status');
      let statusTimer;

      if (!searchInput || !productItems.length) return;

      // Function to perform search
      // Udenlandske produkter med egen side: slug => navn
      const alternativePages = <?php echo json_encode(array_map(function($alternative) { return $alternative['name']; }, load_alternatives()), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP); ?>;
      const suggestion = document.getElementById('alternative-suggestion');

      // Samme regler som alternative_slug() i lib/alternatives.php
      function slugify(text) {
        const map = { 'æ': 'ae', 'ø': 'oe', 'å': 'aa', 'ä': 'ae', 'ö': 'oe', 'ü': 'ue', 'é': 'e', '&': ' og ', '+': ' plus ' };
        return text.toLowerCase().trim().replace(/[æøåäöüé&+]/g, c => map[c]).replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
      }

      function showSuggestion(query) {
        const slug = slugify(query);
        const name = slug && alternativePages[slug];
        suggestion.classList.toggle('hidden', !name);
        if (name) {
          const link = suggestion.querySelector('a');
          link.href = <?php echo json_encode(locale_path('/alternativer/')); ?> + slug;
          link.textContent = <?php echo json_encode(t('Danske alternativer til {name}')); ?>.replace('{name}', name) + ' →';
        }
      }

      function performSearch(query) {
        const searchQuery = query.toLowerCase().trim();
        let visibleCount = 0;
        showSuggestion(query);

        productItems.forEach(function(item) {
          const name = item.getAttribute('data-name') || '';
          const description = item.getAttribute('data-description') || '';
          const alternatives = item.getAttribute('data-alternatives') || '';

          const matches = !searchQuery ||
            name.includes(searchQuery) ||
            description.includes(searchQuery) ||
            alternatives.includes(searchQuery);

          if (matches) {
            item.style.display = '';
            visibleCount++;
          } else {
            item.style.display = 'none';
          }
        });

        // Show/hide no results message
        if (searchQuery && visibleCount === 0) {
          noResults.classList.remove('hidden');
          productsList.classList.add('hidden');
        } else {
          noResults.classList.add('hidden');
          productsList.classList.remove('hidden');
        }
        clearTimeout(statusTimer);
        statusTimer = setTimeout(function() {
          searchStatus.textContent = !searchQuery ? '' : visibleCount === 0
            ? <?php echo json_encode(t('Ingen produkter fundet. Prøv at søge efter et andet navn eller international software.')); ?>
            : visibleCount === 1 ? <?php echo json_encode(t('Der blev fundet 1 produkt.')); ?>
            : <?php echo json_encode(t('Der blev fundet {count} produkter.')); ?>.replace('{count}', visibleCount);
        }, 200);
        const url = new URL(window.location.href);
        if (searchQuery) url.searchParams.set('q', query.trim());
        else url.searchParams.delete('q');
        window.history.replaceState(window.history.state, '', url.pathname + url.search + url.hash);
      }

      // Search input handler
      searchInput.addEventListener('input', function(e) {
        performSearch(e.target.value);
      });

      // Clickable search examples
      const searchExamples = document.querySelectorAll('.search-example');
      searchExamples.forEach(function(button) {
        button.addEventListener('click', function() {
          const searchTerm = this.getAttribute('data-search');
          searchInput.value = searchTerm;
          searchInput.focus();
          searchInput.dispatchEvent(new Event('input', { bubbles: true }));
          // Scroll to products
          document.getElementById('produkter').scrollIntoView({ behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth', block: 'start' });
        });
      });
      if (searchInput.value) performSearch(searchInput.value);
    })();
  </script>
</body>
</html>
