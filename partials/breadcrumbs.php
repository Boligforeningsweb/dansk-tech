<?php
if (!defined('APP_ROOT')) { http_response_code(404); exit; }

// Forventer: $items = [['Forside', '/'], ['Alternativer', '/alternativer'], ['Stripe', null]]
?>
    <nav aria-label="Brødkrummer">
      <ol role="list" class="flex flex-wrap items-center gap-x-2 text-sm text-gray-500 dark:text-gray-400">
        <?php foreach ($items as $i => [$label, $url]): ?>
        <li class="flex items-center gap-x-2">
          <?php if ($i > 0): ?><span aria-hidden="true" class="text-gray-300 dark:text-gray-600">/</span><?php endif; ?>
          <?php if ($url): ?>
          <a href="<?php echo e($url); ?>" class="hover:text-gray-900 dark:hover:text-white"><?php echo e($label); ?></a>
          <?php else: ?>
          <span aria-current="page" class="font-medium text-gray-900 dark:text-white"><?php echo e($label); ?></span>
          <?php endif; ?>
        </li>
        <?php endforeach; ?>
      </ol>
    </nav>
