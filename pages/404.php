<?php
if (!defined('APP_ROOT')) { http_response_code(404); exit; }

partial('head', [
  'title' => '404: Der mangler noget i stacken. · Den danske tech stack',
  'description' => 'Vi har danske alternativer til næsten alt. Bare ikke til den side, du leder efter. Find resten af den danske tech stack her.',
  'robots' => 'noindex, follow',
]);
?>
  <main class="not-found" id="main-content">
    <div class="not-found-layout">
      <div class="not-found-scene" aria-hidden="true">
        <svg class="not-found-illustration" viewBox="0 0 520 460" fill="none" xmlns="http://www.w3.org/2000/svg">
          <defs>
            <filter id="stack-shadow" x="-50%" y="-50%" width="200%" height="220%">
              <feGaussianBlur stdDeviation="12" />
            </filter>
          </defs>
          <text class="not-found-number" x="260" y="183" text-anchor="middle">404</text>
          <ellipse cx="262" cy="391" rx="130" ry="18" fill="#801326" opacity=".1" filter="url(#stack-shadow)" />
          <!-- Logoets nederste brik. Den har heldigvis ikke flyttet sig. -->
          <g class="not-found-base" stroke-linejoin="round">
            <path d="m133 320 120-63 120 63-120 63Z" fill="#f1bec7" />
            <path d="m133 320 120 63v32l-120-63Z" fill="#df8e9e" />
            <path d="m253 383 120-63v32l-120 63Z" fill="#cd7185" />
            <path d="m149 321 104 55 104-55" stroke="#ffe8ec" stroke-width="2" />
          </g>
          <!-- Her burde den manglende side have været. -->
          <g class="not-found-missing" stroke="#c8102e" stroke-opacity=".5" stroke-width="1.5" stroke-dasharray="5 6" stroke-linejoin="round">
            <path d="m133 264 120-63 120 63-120 63Z" />
            <path d="M133 264v30l120 63 120-63v-30M253 327v30" />
          </g>
          <text x="253" y="274" text-anchor="middle" fill="#bb5b6d" font-size="12" font-family="ui-monospace, monospace" transform="rotate(-28 253 274)">siden.findes.ikke</text>
          <!-- En levende udgave af sitets stack-logo, med Dannebrog på toppen. -->
          <g class="not-found-top" stroke-linejoin="round">
            <path d="m133 171 120-63 120 63-120 63Z" fill="#d7193f" />
            <path d="m133 171 120 63v42l-120-63Z" fill="#c8102e" />
            <path d="m253 234 120-63v42l-120 63Z" fill="#9e102a" />
            <path d="m289 127 13 7-120 63-13-7Z" fill="#fff5f4" />
            <path d="m211 130-15 8 120 63 15-8Z" fill="#fff5f4" />
            <path d="m145 172 108 57 108-57" stroke="#f15671" stroke-width="2" />
            <g class="not-found-face" transform="translate(0 -8)" stroke="#fff5f4" stroke-width="5" stroke-linecap="round">
              <path d="m176 213 0 6m35 12 0 6" />
              <path d="m185 242q8-6 16 8" stroke-width="3" />
            </g>
            <path d="m322 227 25-13m-25 22 12-6" stroke="#ef8094" stroke-width="3" stroke-linecap="round" />
          </g>
          <g class="not-found-spark" stroke="#c8102e" stroke-width="2" stroke-linecap="round">
            <path d="M106 129v10m-5-5h10M401 279v10m-5-5h10" />
            <path d="m386 129 5-8m5 20 8-2" opacity=".6" />
          </g>
          <circle cx="106" cy="299" r="3" fill="#df8e9e" />
          <circle cx="393" cy="327" r="2" fill="#c8102e" />
          <g class="not-found-annotation" fill="currentColor">
            <text x="318" y="394" font-size="17" transform="rotate(-7 318 394)">Den var her lige …</text>
            <path d="M410 367c18-50-3-88-37-95m0 0 10-3m-10 3 5 9" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
          </g>
        </svg>
      </div>
      <div class="not-found-copy">
        <h1>404: Der mangler noget i <span>stacken.</span></h1>
        <p class="not-found-description">Vi har danske alternativer til næsten alt. Bare ikke til den side, du leder efter.</p>
        <p class="not-found-description not-found-description-secondary">Den er måske flyttet. Måske slettet. Måske er den bare gået til fredagsbar.</p>
        <div class="not-found-actions">
          <a class="not-found-home" href="/#produkter">Find en dansk løsning <span aria-hidden="true">→</span></a>
          <button class="not-found-restart" type="button" data-restart hidden>
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 7v5h-5M20 12a8 8 0 1 0-2.3 5.7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" /></svg>
            <span>Prøv at genstarte</span>
          </button>
        </div>
        <p class="not-found-site"><a href="/">dansktechstack.dk</a></p>
      </div>
    </div>
  </main>
  <script src="<?php echo e(asset_url('assets/404.js')); ?>" defer></script>
</body>
</html>
