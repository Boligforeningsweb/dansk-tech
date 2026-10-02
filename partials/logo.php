<?php
if (!defined('APP_ROOT')) { http_response_code(404); exit; }

// Logoet inline. Valgfrit: $size (px), $class, $color
$size = $size ?? 24;
$color = $color ?? '#c8102e';
// De to nederste lag i faste, lysere toner (ser ens ud på lys og mørk baggrund)
$mid = $mid ?? '#de7082';
$low = $low ?? '#ecabb6';
?>
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="<?php echo (int) $size; ?>" height="<?php echo (int) $size; ?>" class="<?php echo e($class ?? ''); ?>" aria-hidden="true" focusable="false"><polygon points="16,1.3 31.4,9.6 16,17.9 0.6,9.6" fill="<?php echo e($color); ?>"/><polyline points="1.9,16 16,23.7 30.1,16" fill="none" stroke="<?php echo e($mid); ?>" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/><polyline points="1.9,22.4 16,30.1 30.1,22.4" fill="none" stroke="<?php echo e($low); ?>" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
