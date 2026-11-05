<?php if (!defined('APP_ROOT')) { http_response_code(404); exit; } ?>
      <button type="button" class="theme-toggle" data-theme-toggle aria-label="<?php echo e(t('Mørkt tema')); ?>" aria-pressed="false" data-light-label="<?php echo e(t('Skift til lyst tema')); ?>" data-dark-label="<?php echo e(t('Skift til mørkt tema')); ?>" hidden>
        <svg class="theme-moon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M20.9 13a9 9 0 0 1-9.9-9.9A9 9 0 1 0 20.9 13Z"/></svg>
        <svg class="theme-sun" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M2 12h2m16 0h2M4.9 4.9l1.4 1.4m11.4 11.4 1.4 1.4M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
      </button>
