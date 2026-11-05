<?php if (!defined('APP_ROOT')) { http_response_code(404); exit; } ?>
      <nav class="language-switcher" aria-label="<?php echo e(t('Vælg sprog')); ?>">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a17 17 0 0 1 0 18 17 17 0 0 1 0-18Z"/></svg>
        <?php foreach (['da' => 'Dansk', 'en' => 'English'] as $language => $label): ?>
        <a href="<?php echo e(switch_language_url($language)); ?>" lang="<?php echo $language; ?>" hreflang="<?php echo $language; ?>" data-language-link<?php echo locale() === $language ? ' aria-current="true"' : ''; ?>><?php echo $label; ?></a>
        <?php endforeach; ?>
      </nav>
