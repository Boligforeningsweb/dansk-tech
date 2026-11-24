<?php if (!defined('APP_ROOT')) { http_response_code(404); exit; } ?>
<div data-cookie-consent data-saved="<?php echo e(t('Dit cookievalg er gemt.')); ?>" data-session-saved="<?php echo e(t('Dit valg gælder denne sidevisning. Browseren kunne ikke gemme det til næste gang.')); ?>">
  <aside class="cookie-banner" data-cookie-banner aria-labelledby="cookie-banner-title" hidden>
    <div class="cookie-banner-copy"><h2 id="cookie-banner-title"><?php echo e(t('Du bestemmer over cookies.')); ?></h2><p><?php echo e(t('Vi husker dit sprog, tema og cookievalg. Vi bruger i øjeblikket ingen statistik- eller marketingcookies. Du kan altid ændre dit valg i footeren.')); ?></p><button class="cookie-text-button" type="button" data-cookie-open aria-haspopup="dialog" aria-controls="cookie-settings"><?php echo e(t('Vælg cookies')); ?></button></div>
    <div class="cookie-banner-actions"><button class="cookie-button" type="button" data-cookie-reject><?php echo e(t('Afvis valgfrie')); ?></button><button class="cookie-button" type="button" data-cookie-accept><?php echo e(t('Tillad alle')); ?></button></div>
  </aside>
  <dialog class="cookie-dialog" id="cookie-settings" aria-labelledby="cookie-settings-title" aria-describedby="cookie-controller">
    <button class="cookie-close" type="button" data-cookie-close aria-label="<?php echo e(t('Luk cookievalg')); ?>">×</button>
    <div class="cookie-dialog-content">
      <h2 id="cookie-settings-title"><?php echo e(t('Vælg, hvad vi må bruge.')); ?></h2><p id="cookie-controller"><?php echo e(t('Langsom.com står bag Den danske tech stack. Vi gemmer dit cookievalg i denne browser i højst 180 dage. Du kan ændre det eller trække det tilbage under Cookievalg i footeren. Kontakt: kontakt@langsom.com.')); ?></p>
      <div class="cookie-categories">
        <label><span><strong><?php echo e(t('Nødvendige indstillinger')); ?></strong><span id="cookie-necessary-description"><?php echo e(t('Husker dit sprog (op til ét år), dit tema (indtil du sletter det) og dit cookievalg (180 dage). Disse funktioner er altid aktive.')); ?></span></span><input type="checkbox" checked disabled aria-describedby="cookie-necessary-description" /></label>
        <label><span><strong><?php echo e(t('Statistik')); ?></strong><span id="cookie-analytics-description"><?php echo e(t('Ikke i brug. Der er ingen statistikleverandører på sitet. Dit valg gemmes, men aktiverer ingen måling.')); ?></span></span><input type="checkbox" name="cookie-analytics" aria-describedby="cookie-analytics-description" /></label>
        <label><span><strong><?php echo e(t('Markedsføring')); ?></strong><span id="cookie-marketing-description"><?php echo e(t('Ikke i brug. Der er ingen marketingpixels eller annonceleverandører på sitet. Dit valg gemmes, men aktiverer ingen annoncering.')); ?></span></span><input type="checkbox" name="cookie-marketing" aria-describedby="cookie-marketing-description" /></label>
      </div>
      <a class="cookie-privacy" href="<?php echo e(locale_path('/privatliv')); ?>"><?php echo e(t('Læs om privatliv og cookies')); ?></a>
    </div>
    <div class="cookie-dialog-actions"><button class="cookie-button" type="button" data-cookie-reject><?php echo e(t('Afvis valgfrie')); ?></button><button class="cookie-button" type="button" data-cookie-save><?php echo e(t('Gem mit valg')); ?></button></div>
  </dialog>
  <span class="sr-only" role="status" data-cookie-status></span>
</div>
<script type="module" src="<?php echo e(asset_url('assets/cookie-consent.js')); ?>"></script>
