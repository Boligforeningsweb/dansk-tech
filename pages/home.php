<?php
if (!defined('APP_ROOT')) { http_response_code(404); exit; }

$products = load_products();
$originalProducts = load_original_products();
$people = load_json('data/people.json');

$productCount = count($products);
$title = "Den danske tech stack – $productCount danske alternativer til Big Tech";
$description = "Find danske alternativer til Stripe, Shopify, Mailchimp, Zendesk og andre udenlandske systemer. $productCount danske SaaS-produkter, anbefalet af danske iværksættere.";
$socialDescription = "Betalinger, e-mail, support, regnskab, monitoring og meget mere – bygget i Danmark. Find danske alternativer til de udenlandske giganter.";
$ogImage = SITE_URL . 'og-image.jpg';
$ogImageAlt = "Den danske tech stack: $productCount danske alternativer til Stripe, Shopify, Mailchimp, Zendesk og co.";

// Schema.org JSON-LD, built from the same data as the page
$faq = [
  [
    'q' => 'Hvad er den danske tech stack?',
    'a' => "Den danske tech stack er en åben, kurateret liste over $productCount danske software-produkter, som kan bruges som alternativer til store internationale spillere som Stripe, Shopify, Mailchimp og Zendesk. Listen er startet af en gruppe danske iværksættere og vedligeholdes på GitHub.",
  ],
  [
    'q' => 'Hvornår tæller et produkt som dansk?',
    'a' => 'Et produkt kommer på listen, hvis virksomheden har hovedkontor i Danmark, har en dansk stifter eller medstifter, eller primært er dansk ejet. Produktet skal samtidig kunne indgå i en tech stack hos SaaS-, e-commerce- eller andre tech-virksomheder.',
  ],
  [
    'q' => 'Hvorfor vælge dansk software?',
    'a' => 'Danske systemer giver dansktalende support, data og kontrakter under dansk og europæisk lovgivning (GDPR) og mindre afhængighed af software fra lande uden for EU. Samtidig styrker du det danske tech-miljø.',
  ],
  [
    'q' => 'Hvordan foreslår jeg et produkt til listen?',
    'a' => 'Send en pull request på GitHub, hvor du tilføjer produktet til filen products.json med navn, URL, en kort beskrivelse og de internationale produkter, det er et alternativ til. Vi gennemgår forslaget og tilføjer det til listen.',
  ],
];

$schema = schema_json([
  schema_organization(),
  schema_website($description),
  [
    '@type' => 'CollectionPage',
    '@id' => SITE_URL . '#webpage',
    'url' => SITE_URL,
    'name' => $title,
    'description' => $description,
    'inLanguage' => 'da-DK',
    'isPartOf' => ['@id' => SITE_URL . '#website'],
    'about' => ['@id' => SITE_URL . '#organization'],
    'dateModified' => date('c', products_modified_at()),
    'primaryImageOfPage' => [
      '@type' => 'ImageObject',
      'url' => $ogImage,
      'width' => 1200,
      'height' => 630,
    ],
    'mainEntity' => ['@id' => SITE_URL . '#produkter'],
  ],
  [
    '@type' => 'ItemList',
    '@id' => SITE_URL . '#produkter',
    'name' => 'Danske alternativer til udenlandsk software',
    'description' => 'Liste over danske tech-systemer, der kan erstatte internationale SaaS-produkter',
    'numberOfItems' => $productCount,
    'itemListOrder' => 'https://schema.org/ItemListOrderAscending',
    'itemListElement' => schema_product_list_items($products),
  ],
  [
    '@type' => 'FAQPage',
    '@id' => SITE_URL . '#faq',
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
  'canonical' => SITE_URL,
  'socialDescription' => $socialDescription,
  'ogImage' => $ogImage,
  'ogImageAlt' => $ogImageAlt,
  'schema' => $schema,
]);
?>
  <div class="min-h-screen flex items-center justify-center relative" style="background-image: url('/images/danmark.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;">
    <div class="absolute inset-0 bg-white/70 dark:bg-gray-900/70"></div>
    <div class="mx-auto max-w-7xl px-6 lg:px-8 w-full relative z-10">
      <div class="mx-auto max-w-3xl text-center">
        <h1 class="text-5xl font-semibold tracking-tight text-pretty text-gray-900 sm:text-6xl lg:text-7xl dark:text-white">
          Den danske tech stack
        </h1>
        <p class="mt-8 text-xl/8 text-gray-600 dark:text-gray-400 max-[450px]:hidden">
          Vi er en gruppe iværksættere, der ønsker at sætte fokus på dansk software. I en tid hvor der ofte tales om Danmarks afhængighed af udenlandsk software, har vi udgivet en liste af danske software-virksomheder, man kan vælge som alternativ til dem udenfor EU. 
          Danmark har nemlig en stolt tradition inden for softwareudvikling. Teknologier som Ruby on Rails, C++ og PHP har danske rødder, og nedenfor har vi samlet en liste over stærke danske alternativer til software, der ellers typisk købes i udlandet.
        </p>
        <p class="mt-8 text-xl/8 text-gray-600 dark:text-gray-400 hidden max-[450px]:block">
          Vi er en gruppe iværksættere, der ønsker at sætte fokus på danske produkter, man kan bruge i sin tech stack. I en tid hvor der ofte tales om Danmarks afhængighed af udenlandsk software, vil vi fremhæve danske systemer, vi selv har tillid til.
        </p>
        <div class="mt-10 flex items-center justify-center gap-x-6">
          <a href="#produkter" class="rounded-md bg-gray-900 px-6 py-3.5 text-base font-semibold text-white shadow-sm hover:bg-gray-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100">
            Produkter fra 🇩🇰
          </a>
          <a href="#iværksættere" class="text-base font-semibold text-gray-900 hover:text-gray-700 dark:text-white dark:hover:text-gray-300">
            Hvem står bag? <span aria-hidden="true">→</span>
          </a>
        </div>
        
        <!-- Media mentions section -->
        <div class="mt-16 pt-12 border-t border-gray-200/50 dark:border-gray-700/50">
          <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-8">Omtalt i bl.a.</p>
          <div class="flex flex-wrap items-center justify-center gap-10 sm:gap-16">
            <!-- TechSavvy logo -->
            <div class="group flex items-center justify-center h-12 opacity-90 hover:opacity-100 transition-opacity duration-300">
              <img src="/techsavvy.png" alt="TechSavvy" class="h-full w-auto max-w-[140px] object-contain transition-all duration-300" />
            </div>
            <!-- Berlingske logo -->
            <div class="group flex items-center justify-center h-12 opacity-90 hover:opacity-100 transition-opacity duration-300">
              <img src="/berlingske.png" alt="Berlingske" class="h-full w-auto max-w-[140px] object-contain transition-all duration-300" />
            </div>
            <!-- Zetland logo -->
            <div class="group flex items-center justify-center h-12 opacity-90 hover:opacity-100 transition-opacity duration-300">
              <img src="/zetland.png" alt="Zetland" class="h-full w-auto max-w-[140px] object-contain transition-all duration-300" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div id="produkter" class="bg-white py-24 sm:py-32 dark:bg-gray-900">
    <div class="mx-auto max-w-7xl px-6 lg:px-8">
      <div class="mx-auto max-w-3xl text-center">
        <h2 class="text-3xl font-semibold tracking-tight text-gray-900 sm:text-4xl dark:text-white">Tech fra 🇩🇰</h2>
        <p class="mt-6 text-base text-gray-600 dark:text-gray-400">
          <?php echo $productCount; ?> danske systemer til din tech stack – søg på det udenlandske produkt, du gerne vil erstatte
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
        <ul role="list" class="mx-auto mt-10 grid max-w-5xl grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2 lg:mx-0 lg:max-w-none lg:grid-cols-3 xl:grid-cols-4">
        <?php foreach ($products as $product) {
          partial('product-card', ['product' => $product, 'originalProducts' => $originalProducts]);
        } ?>
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
  <div id="iværksættere" class="bg-white py-24 sm:py-32 dark:bg-gray-900">
    <div class="mx-auto max-w-7xl px-6 lg:px-8">
      <div class="mx-auto max-w-3xl text-center">
        <h2 class="text-4xl font-semibold tracking-tight text-pretty text-gray-900 sm:text-5xl dark:text-white">Tusind tak til dem der støttede projektet helt fra start</h2>
        <p class="mt-6 text-lg/8 text-gray-600 dark:text-gray-400">Vi har talt med bekendte i tech-miljøet og fået dem med til også at lægge navn til anbefalingerne. Disse personer støtter initiativet og bygger og investerer selv i de danske systemer. Alle på listen her har fået lov at udnævne et stykke software til listen, som så har fået <span class="inline-flex items-center rounded-full bg-blue-600 px-2 py-0.5 text-xs font-medium text-white">Original</span> label'en i oversigten over produkter.</p>
      </div>
      <ul role="list" class="mx-auto mt-20 grid max-w-2xl grid-cols-2 gap-x-8 gap-y-16 text-center sm:grid-cols-3 md:grid-cols-4 lg:mx-0 lg:max-w-none lg:grid-cols-7">
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

  <div class="bg-white py-24 sm:py-32 dark:bg-gray-900">
    <div class="mx-auto max-w-7xl px-6 lg:px-8">
      <div class="mx-auto max-w-3xl text-center">
        <h2 class="text-4xl font-semibold tracking-tight text-pretty text-gray-900 sm:text-5xl dark:text-white">De 4 oprindelige bagmænd</h2>
        <p class="mt-6 text-lg/8 text-gray-600 dark:text-gray-400">Vi er fire glade iværksættere og udviklere fra Danmark som elsker tech, business og at bygge virksomheder. Vi har fulgt debatten omkring at Danmark måske "falder bagud" internationalt og vi er nok desværre enige. Derfor vil vi gerne prøve at kaste lidt lys på danske tech-firmaer som vi mener er gode alternativer til de store internationale spillere, når man leder efter produkter.</p>
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
        <h2 class="text-3xl font-semibold tracking-tight text-gray-900 sm:text-4xl dark:text-white">Send dit forslag via <a href="https://github.com/Boligforeningsweb/dansk-tech" class="underline">GitHub</a></h2>
        <p class="mt-6 text-lg/8 text-gray-600 dark:text-gray-400">
          Er du selv udvikler og kender lige præcis dét stykke software fra Danmark som mangler på listen? Så kan du sende os en pull request og foreslå til listen af produkter fra eksterne bidragydere. Herunder kan du se dem der har bidraget indtil videre. Det er hhv. <a href="https://github.com/BoMoellerDK" class="underline">Bo Møller</a> og <a href="https://github.com/jonasdev" class="underline">Jonas Kaas Kristensen</a> der styrer repo'et.
        </p>
        <div id="contributors" class="mt-10 flex flex-wrap items-center justify-center gap-x-4 gap-y-2">
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
          <blockquote class="text-lg md:text-xl text-gray-700 dark:text-gray-300 leading-relaxed italic">“Danmark kan og skal være langt mere selvforsynende med software. Vi kan ikke bygge et resistent samfund på en digital infrastruktur, der er næsten udelukkende leveret af Amerikanerne eller Kineserne. Så et fokus på hvad der allerede findes i dag og danske virksomheder og institutioner kan komme igang med i morgen er super”</blockquote>
          <figcaption class="mt-4 text-sm text-gray-600 dark:text-gray-400 font-medium">David Heinemeier Hansson <br/> Stifter af Ruby on Rails, Bestseller-forfatter og medejer af flere danske startups</figcaption>
        </figure>
      </div>
    </div>
  </div>

  <div class="mx-auto max-w-7xl px-6 lg:px-8">
    <div class="border-t border-gray-300 dark:border-white/15"></div>
  </div>
  <div id="faq" class="bg-white py-24 sm:py-32 dark:bg-gray-900">
    <div class="mx-auto max-w-3xl px-6 lg:px-8">
      <h2 class="text-3xl font-semibold tracking-tight text-gray-900 sm:text-4xl dark:text-white text-center">Ofte stillede spørgsmål</h2>
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
<?php partial('footer'); ?>
  <script type="text/javascript">
    // Product search functionality
    (function() {
      const searchInput = document.getElementById('product-search');
      const productItems = document.querySelectorAll('[data-product]');
      const productsList = document.querySelector('[role="list"]');
      const noResults = document.getElementById('no-results');
      
      if (!searchInput || !productItems.length) return;
      
      // Function to perform search
      function performSearch(query) {
        const searchQuery = query.toLowerCase().trim();
        let visibleCount = 0;
        
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
          performSearch(searchTerm);
          // Scroll to products
          document.getElementById('produkter').scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
      });
    })();
    
    // GitHub contributors
    (async () => {
      const response = await fetch('https://api.github.com/repos/Boligforeningsweb/dansk-tech/contributors?per_page=500&page=1');
      const contributors = await response.json();
      const container = document.getElementById('contributors');

      contributors.forEach(contributor => {
        const link = document.createElement('a');
        link.href = contributor.html_url;
        link.target = '_blank';
        link.rel = 'noopener noreferrer';

        const img = document.createElement('img');
        img.src = contributor.avatar_url;
        img.alt = contributor.login;
        img.width = 48;
        img.height = 48;
        img.className = 'rounded-full object-cover outline-1 -outline-offset-1 outline-black/5 dark:outline-white/10';

        link.appendChild(img);
        container.appendChild(link);
      });
    })();
  </script>
</body>
</html>
