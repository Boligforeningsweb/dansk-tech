<?php
if (!defined('APP_ROOT')) { http_response_code(404); exit; }

// Kort troværdighedslinje under indledningen på undersider
$people = load_json('data/people.json');
$backers = $people['backers'] ?? [];
$contributorCount = count(load_contributors());
?>
    <div class="mt-8 flex flex-col gap-x-4 gap-y-3 sm:flex-row sm:items-center">
      <div class="flex flex-none -space-x-2">
        <?php foreach (array_slice($backers, 0, 6) as $person): ?>
        <img src="<?php echo e($person['image']); ?>" alt="<?php echo e($person['name']); ?>" title="<?php echo e($person['name'] . ' – ' . $person['role']); ?>" width="36" height="36" class="size-9 rounded-full object-cover ring-2 ring-white dark:ring-gray-900" />
        <?php endforeach; ?>
      </div>
      <div class="min-w-0 text-sm/6 text-gray-600 sm:flex-1 dark:text-gray-400">
        <p>
          Kurateret af <a href="#om-listen" class="font-semibold text-gray-900 hover:underline dark:text-white"><?php echo count($backers); ?> danske iværksættere</a><?php if ($contributorCount): ?>
          og <a href="https://github.com/Boligforeningsweb/dansk-tech/graphs/contributors" class="font-semibold text-gray-900 hover:underline dark:text-white"><?php echo $contributorCount; ?> bidragydere</a><?php endif; ?>
        </p>
        <p>
          Åben på <a href="https://github.com/Boligforeningsweb/dansk-tech" class="font-semibold text-gray-900 hover:underline dark:text-white">GitHub</a>
          <span aria-hidden="true" class="mx-1 text-gray-300 dark:text-gray-600">·</span>
          Ingen betalte placeringer
        </p>
      </div>
    </div>
