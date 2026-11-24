// No tracking providers are configured on this website. Consent updates stay local.
export const consentKey = 'dansk-tech.cookie-consent.v1';
export const consentLifetime = 180 * 24 * 60 * 60 * 1000;

export function readConsent(value, now = Date.now()) {
  try {
    const choice = JSON.parse(value);
    return choice?.version === 1 && typeof choice.analytics === 'boolean' && typeof choice.marketing === 'boolean'
      && Number.isFinite(choice.at) && choice.at > 0 && choice.at <= now && now - choice.at < consentLifetime ? choice : null;
  } catch { return null; }
}

export function consentSignals(choice) {
  const ads = choice?.marketing ? 'granted' : 'denied';
  return {analytics_storage: choice?.analytics ? 'granted' : 'denied', ad_storage: ads, ad_user_data: ads, ad_personalization: ads};
}

export function mountConsent(doc = document, win = window) {
  const root = doc.querySelector('[data-cookie-consent]');
  if (!root) return;
  const banner = root.querySelector('[data-cookie-banner]');
  const dialog = root.querySelector('dialog');
  const analytics = root.querySelector('[name="cookie-analytics"]');
  const marketing = root.querySelector('[name="cookie-marketing"]');
  let choice = null, returnFocus = null;
  try { choice = readConsent(win.localStorage.getItem(consentKey)); } catch { /* Storage may be unavailable. */ }
  function publish() {
    win.gtag('consent', 'update', consentSignals(choice));
    win.dataLayer.push({event: 'dansk_tech_consent_update', dansk_tech_consent: {analytics: choice?.analytics || false, marketing: choice?.marketing || false}});
  }
  if (choice) publish();
  banner.hidden = Boolean(choice);

  function close() {
    dialog.close();
    if (returnFocus?.isConnected && !returnFocus.closest('[hidden]')) returnFocus.focus({preventScroll: true});
  }
  function save(next) {
    choice = {version: 1, analytics: next.analytics, marketing: next.marketing, at: Date.now()};
    let persisted = false;
    try { win.localStorage.setItem(consentKey, JSON.stringify(choice)); persisted = true; } catch {
      // A full storage quota may reject writes while still allowing deletion.
      // Do not restore an older acceptance after a failed withdrawal write.
      try { win.localStorage.removeItem(consentKey); } catch { /* Storage may be entirely unavailable. */ }
    }
    banner.hidden = true;
    if (dialog.open) close();
    if (returnFocus?.closest('[hidden]') || doc.activeElement?.closest('[hidden]')) {
      doc.querySelector('footer [data-cookie-open]')?.focus({preventScroll: true});
    }
    publish();
    const status = root.querySelector('[data-cookie-status]');
    status.textContent = persisted ? root.dataset.saved : root.dataset.sessionSaved;
  }
  doc.querySelectorAll('[data-cookie-open]').forEach(button => { button.hidden = false; button.addEventListener('click', () => {
    returnFocus = button;
    analytics.checked = choice?.analytics || false;
    marketing.checked = choice?.marketing || false;
    if (!dialog.open) dialog.showModal();
  }); });
  root.querySelectorAll('[data-cookie-accept]').forEach(button => button.addEventListener('click', () => save({analytics: true, marketing: true})));
  root.querySelectorAll('[data-cookie-reject]').forEach(button => button.addEventListener('click', () => save({analytics: false, marketing: false})));
  root.querySelector('[data-cookie-save]').addEventListener('click', () => save({analytics: analytics.checked, marketing: marketing.checked}));
  root.querySelector('[data-cookie-close]').addEventListener('click', close);
  // Keep Tab inside the modal, including browsers that briefly focus the body
  // between the last and first native dialog controls.
  dialog.addEventListener('keydown', event => {
    if (event.key !== 'Tab') return;
    const controls = [...dialog.querySelectorAll('button, a[href], input:not(:disabled)')];
    const first = controls[0], last = controls.at(-1);
    if (event.shiftKey && doc.activeElement === first) { event.preventDefault(); last.focus(); }
    else if (!event.shiftKey && doc.activeElement === last) { event.preventDefault(); first.focus(); }
  });
  dialog.addEventListener('cancel', event => { event.preventDefault(); close(); });
  win.addEventListener('storage', event => {
    if (event.key !== consentKey && event.key !== null) return;
    const updated = event.key === null ? null : readConsent(event.newValue);
    if (JSON.stringify(updated) === JSON.stringify(choice)) return;
    choice = updated;
    banner.hidden = Boolean(choice);
    analytics.checked = choice?.analytics || false;
    marketing.checked = choice?.marketing || false;
    publish();
  });
}

if (typeof document !== 'undefined') mountConsent();
