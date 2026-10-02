<?php
if (!defined('APP_ROOT')) { http_response_code(404); exit; }

// "Om listen": hvem står bag, kriterier, bidragydere og omtale. Bruges på undersiderne.
$people = load_json('data/people.json');
$backers = $people['backers'] ?? [];
$contributors = load_contributors();
$shownContributors = array_slice($contributors, 0, 16);
$criteria = [
  ['Kun dansk software', 'Virksomheden skal have hovedkontor i Danmark, en dansk stifter eller medstifter, eller være primært dansk ejet.'],
  ['Gennemgået før det kommer med', 'Nye produkter foreslås som offentlige pull requests på GitHub og gennemgås af vedligeholderne, før de kommer på listen.'],
  ['Helt åbent', 'Hele listen og alle ændringer kan ses på GitHub – også hvem der har foreslået hvad.'],
];
?>
    <section id="om-listen" aria-labelledby="om-listen-titel" class="mt-24 scroll-mt-6 border-t border-gray-200 pt-16 dark:border-white/10">
      <div class="grid grid-cols-1 gap-x-20 gap-y-12 lg:grid-cols-2">
        <div>
          <p class="text-sm font-semibold text-gray-500 dark:text-gray-400">Om listen</p>
          <h2 id="om-listen-titel" class="mt-2 text-3xl font-semibold tracking-tight text-pretty text-gray-900 dark:text-white">Et åbent fællesprojekt fra dansk tech</h2>
          <p class="mt-6 text-base/7 text-gray-600 dark:text-gray-400">
            Den danske tech stack er startet af danske iværksættere – bl.a. folkene bag Dinero, Clerk.io, Sleeknote, GrowPanel, Morningscore og Timelog – for at gøre det nemmere at vælge dansk software.
            <?php if ($contributors): ?>Listen vedligeholdes åbent på GitHub, hvor <?php echo count($contributors); ?> bidragydere har foreslået og rettet produkter.<?php endif; ?>
          </p>
          <div class="mt-8 flex flex-wrap gap-x-6 gap-y-3 text-sm font-semibold">
            <a href="/#iværksættere" class="whitespace-nowrap text-gray-900 hover:text-gray-700 dark:text-white">Mød folkene bag <span aria-hidden="true">→</span></a>
            <a href="https://github.com/Boligforeningsweb/dansk-tech#-retningslinjer-for-bidrag" class="whitespace-nowrap text-gray-900 hover:text-gray-700 dark:text-white">Kriterierne på GitHub <span aria-hidden="true">→</span></a>
          </div>
        </div>

        <ul role="list" class="space-y-6 lg:pt-8">
          <?php foreach ($criteria as [$term, $text]): ?>
          <li class="flex gap-x-3">
            <svg class="mt-0.5 size-5 flex-none text-gray-900 dark:text-white" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" /></svg>
            <div>
              <p class="text-sm/6 font-semibold text-gray-900 dark:text-white"><?php echo e($term); ?></p>
              <p class="text-sm/6 text-gray-600 dark:text-gray-400"><?php echo e($text); ?></p>
            </div>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <h3 class="mt-16 text-sm font-semibold text-gray-900 dark:text-white">Anbefalet af <?php echo count($backers); ?> danske iværksættere</h3>
      <ul role="list" class="mt-6 grid grid-cols-1 gap-x-8 gap-y-6 sm:grid-cols-2 lg:grid-cols-4">
        <?php foreach ($backers as $person): ?>
        <li class="flex items-start gap-x-3">
          <img src="<?php echo e($person['image']); ?>" alt="" width="40" height="40" loading="lazy" class="size-10 flex-none rounded-full object-cover outline-1 -outline-offset-1 outline-black/5 dark:outline-white/10" />
          <div class="min-w-0">
            <p class="text-sm/6 font-semibold text-gray-900 dark:text-white"><?php echo e($person['name']); ?></p>
            <p class="text-xs/5 text-gray-600 dark:text-gray-400"><?php echo e($person['role']); ?></p>
          </div>
        </li>
        <?php endforeach; ?>
      </ul>

      <?php if ($contributors): ?>
      <div class="mt-14 flex flex-col gap-x-6 gap-y-4 sm:flex-row sm:items-center">
        <div class="flex items-center">
          <?php foreach ($shownContributors as $i => $contributor): ?>
          <?php // Overlappende stak; på mobil vises de første 9 ?>
          <a href="<?php echo e($contributor['url']); ?>" target="_blank" rel="noopener noreferrer" title="<?php echo e($contributor['login']); ?>" class="<?php echo $i > 0 ? '-ml-2' : ''; ?><?php echo $i >= 9 ? ' hidden sm:block' : ' block'; ?>"><img src="<?php echo e(contributor_avatar_url($contributor, 36)); ?>" alt="<?php echo e($contributor['login']); ?>" width="36" height="36" loading="lazy" decoding="async" class="size-9 rounded-full object-cover ring-2 ring-white dark:ring-gray-900"></a>
          <?php endforeach; ?>
          <?php if (count($contributors) > count($shownContributors)): ?>
          <a href="https://github.com/Boligforeningsweb/dansk-tech/graphs/contributors" class="-ml-2 inline-flex h-9 min-w-9 items-center justify-center rounded-full bg-gray-100 px-2 text-xs font-semibold text-gray-700 ring-2 ring-white hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-900">+<?php echo count($contributors) - count($shownContributors); ?></a>
          <?php endif; ?>
        </div>
        <p class="text-sm/6 text-gray-600 dark:text-gray-400"><span class="font-semibold text-gray-900 dark:text-white"><?php echo count($contributors); ?> bidragydere</span> har foreslået og rettet produkter. <a href="https://github.com/Boligforeningsweb/dansk-tech/graphs/contributors" class="whitespace-nowrap font-semibold text-gray-900 hover:underline dark:text-white">Se dem på GitHub <span aria-hidden="true">→</span></a></p>
      </div>
      <?php endif; ?>

      <div class="mt-14 flex flex-wrap items-center gap-x-10 gap-y-6 border-t border-gray-200 pt-10 dark:border-white/10">
        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Omtalt i bl.a.</p>
        <?php foreach (load_json('data/press.json') as $press): ?>
        <img src="<?php echo e($press['image']); ?>" width="<?php echo (int) $press['width']; ?>" height="<?php echo (int) $press['height']; ?>" alt="<?php echo e($press['name']); ?>" loading="lazy" class="h-8 w-auto max-w-[120px] object-contain opacity-80" />
        <?php endforeach; ?>
      </div>
    </section>
