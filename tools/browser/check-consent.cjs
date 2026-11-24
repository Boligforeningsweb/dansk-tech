const assert = require('node:assert/strict');
const fs = require('node:fs');
const { AxeBuilder } = require('@axe-core/playwright');
const key = 'dansk-tech.cookie-consent.v1';
module.exports = async function ({ context, check, origin, root }) {
  const source = fs.readFileSync(root + '/assets/cookie-consent.js', 'utf8');
  const { readConsent, consentLifetime } = await import('data:text/javascript;base64,' + Buffer.from(source).toString('base64'));
  await check('consent validates version, booleans and 180-day lifetime', async () => {
    const now = Date.now(), valid = { version: 1, analytics: false, marketing: false, at: now };
    assert.deepEqual(readConsent(JSON.stringify(valid), now), valid);
    for (const value of [null, '', 'null', '{}', '[]', 'invalid', ...[
      { version: 2 }, { analytics: 'true' }, { marketing: 1 }, { at: 0 }, { at: -1 },
      { at: now + 1 }, { at: now - consentLifetime }, { at: 'yesterday' }, { at: null }
    ].map(change => JSON.stringify({ ...valid, ...change }))]) assert.equal(readConsent(value, now), null, String(value));
    assert(readConsent(JSON.stringify({ ...valid, at: now - consentLifetime + 1 }), now));
  });
  const fresh = options => context({ ...options, freshConsent: true });
  await check('fresh visit has denied defaults, equal buttons and no tracking resources', async () => {
    const c = await fresh(); const p = await c.newPage(); await p.goto(origin + '/');
    await p.locator('[data-cookie-banner]').waitFor({ state: 'visible' });
    assert.equal(await p.evaluate(() => localStorage.getItem('dansk-tech.cookie-consent.v1')), null);
    assert.deepEqual(await p.evaluate(() => [...dataLayer[0]]), ['consent', 'default', {analytics_storage:'denied', ad_storage:'denied', ad_user_data:'denied', ad_personalization:'denied'}]);
    assert.equal(await p.locator('[data-cookie-banner] [data-cookie-reject]').getAttribute('class'), await p.locator('[data-cookie-accept]').getAttribute('class'));
    await p.screenshot({path: root + '/.context/cookie-banner-mobile.png'});
    await p.setViewportSize({width:1440, height:960});
    await p.screenshot({path: root + '/.context/cookie-banner-desktop.png'});
    assert.equal(await p.locator('script[src*="googletagmanager"],iframe').count(), 0);
    const response = await p.request.get(origin + '/assets/cookie-consent.js');
    assert.match(response.headers()['content-type'], /javascript/);
    await c.close();
  });
  await check('saving banner choices preserves scroll position and keyboard focus', async () => {
    for (const action of ['accept', 'reject', 'save']) {
      const c = await fresh(); const p = await c.newPage(); await p.goto(origin + '/');
      await p.locator('[data-cookie-banner]').waitFor({state:'visible'});
      const before = await p.evaluate(() => scrollY);
      if (action === 'save') await p.locator('[data-cookie-banner] [data-cookie-open]').click();
      await p.locator(action === 'reject' ? '[data-cookie-banner] [data-cookie-reject]' : '[data-cookie-' + action + ']').click();
      await p.waitForTimeout(100);
      assert.equal(await p.evaluate(() => scrollY), before, action + ' jumped to footer');
      assert(await p.locator('footer [data-cookie-open]').evaluate(el => el === document.activeElement));
      await c.close();
    }
  });
  for (const [analytics, marketing] of [[false, false], [true, false], [false, true], [true, true]]) {
    await check(`consent choice analytics=${analytics}, marketing=${marketing} persists and withdraws`, async () => {
      const c = await fresh(); const p = await c.newPage(); await p.goto(origin + '/en');
      if (analytics === marketing) await p.locator(analytics ? '[data-cookie-accept]' : '[data-cookie-banner] [data-cookie-reject]').click();
      else {
        await p.locator('[data-cookie-banner] [data-cookie-open]').click();
        assert(!await p.locator('[name=cookie-analytics]').isChecked());
        assert(!await p.locator('[name=cookie-marketing]').isChecked());
        await p.locator('[name=cookie-analytics]').setChecked(analytics);
        await p.locator('[name=cookie-marketing]').setChecked(marketing);
        await p.locator('[data-cookie-save]').click();
      }
      const choice = await p.evaluate(k => JSON.parse(localStorage.getItem(k)), key);
      assert.equal(choice.analytics, analytics); assert.equal(choice.marketing, marketing);
      const signals = await p.evaluate(() => [...dataLayer].filter(x => x[0] === 'consent').at(-1)[2]);
      assert.equal(signals.analytics_storage, analytics ? 'granted' : 'denied');
      for (const name of ['ad_storage', 'ad_user_data', 'ad_personalization']) assert.equal(signals[name], marketing ? 'granted' : 'denied');
      await p.reload(); await p.locator('footer [data-cookie-open]').waitFor({ state: 'visible' });
      assert(await p.locator('[data-cookie-banner]').isHidden());
      await p.locator('footer [data-cookie-open]').click();
      assert.equal(await p.locator('[name=cookie-analytics]').isChecked(), analytics);
      await p.locator('dialog [data-cookie-reject]').click();
      assert.deepEqual(await p.evaluate(k => { const v = JSON.parse(localStorage.getItem(k)); return [v.analytics, v.marketing]; }, key), [false, false]);
      assert.equal(await p.locator('script[src*="googletagmanager"],iframe').count(), 0);
      await c.close();
    });
  }
  await check('blocked storage keeps choices usable only in the current document', async () => {
    const c = await fresh(); await c.addInitScript(() => Object.defineProperty(window, 'localStorage', { get() { throw Error('blocked'); } }));
    const p = await c.newPage(); await p.goto(origin + '/'); await p.locator('[data-cookie-accept]').click();
    assert(await p.locator('[data-cookie-banner]').isHidden());
    assert.match(await p.locator('[data-cookie-status]').textContent(), /kunne ikke/);
    await p.reload(); await p.locator('[data-cookie-banner]').waitFor({ state: 'visible' }); await c.close();
  });
  await check('failed withdrawal write removes an older saved acceptance', async () => {
    const c = await fresh(); const p = await c.newPage(); await p.goto(origin + '/');
    await p.locator('[data-cookie-accept]').click();
    await p.evaluate(() => { Storage.prototype.setItem = () => { throw new DOMException('Full', 'QuotaExceededError'); }; });
    await p.locator('footer [data-cookie-open]').click(); await p.locator('dialog [data-cookie-reject]').click();
    assert.equal(await p.evaluate(k => localStorage.getItem(k), key), null);
    assert.match(await p.locator('[data-cookie-status]').textContent(), /kunne ikke/);
    await p.reload(); await p.locator('[data-cookie-banner]').waitFor({state:'visible'});
    await c.close();
  });
  await check('invalid saved choice and cross-tab updates including storage clear', async () => {
    const c = await fresh(); const p = await c.newPage(); await p.goto(origin + '/');
    await p.evaluate(k => localStorage.setItem(k, '{"version":1}'), key); await p.reload();
    await p.locator('[data-cookie-banner]').waitFor({ state: 'visible' });
    const other = await c.newPage(); await other.goto(origin + '/en');
    await other.locator('[data-cookie-accept]').click(); await p.locator('[data-cookie-banner]').waitFor({ state: 'hidden' });
    await p.locator('footer [data-cookie-open]').click(); assert(await p.locator('[name=cookie-analytics]').isChecked());
    await other.evaluate(() => localStorage.clear());
    await p.waitForFunction(() => !document.querySelector('[name=cookie-analytics]').checked);
    await p.keyboard.press('Escape'); await p.locator('[data-cookie-banner]').waitFor({ state: 'visible' });
    await c.close();
  });
  await check('all page types and languages include cookie choices and privacy', async () => {
    const c = await fresh(); const p = await c.newPage();
    for (const prefix of ['', '/en']) for (const route of ['', prefix ? '/alternatives' : '/alternativer', prefix ? '/alternatives/stripe' : '/alternativer/stripe', '/badge', '/privatliv', '/missing-page']) {
      const response = await p.goto(origin + prefix + (route || (prefix ? '' : '/')));
      assert.equal(response.status(), route === '/missing-page' ? 404 : 200);
      await p.locator('footer [data-cookie-open]').waitFor({ state: 'visible' });
      assert.equal(await p.locator('[data-cookie-consent]').count(), 1);
      assert.equal(await p.locator('footer a[href$="/privatliv"]').count(), 1);
    }
    await c.close();
  });
  for (const lang of ['da', 'en']) for (const theme of ['light', 'dark']) {
    await check(`cookie keyboard, mobile overflow and Axe: ${lang}/${theme}`, async () => {
      const c = await fresh({ colorScheme: theme }); const p = await c.newPage(); await p.goto(origin + (lang === 'da' ? '/' : '/en'));
      await p.locator('[data-cookie-banner]').waitFor({state:'visible'});
      const bannerResults = await new AxeBuilder({page:p}).include('[data-cookie-consent]').withTags(['wcag2a','wcag2aa','wcag21aa']).analyze();
      assert.deepEqual(bannerResults.violations, []);
      await p.locator('[data-cookie-banner] [data-cookie-open]').click();
      await p.keyboard.press('Escape'); assert(await p.locator('[data-cookie-banner] [data-cookie-open]').evaluate(el => el === document.activeElement));
      await p.keyboard.press('Enter');
      assert(await p.locator('dialog').evaluate(el => el.open));
      for (let i = 0; i < 12; i++) { await p.keyboard.press('Tab'); assert(await p.locator('dialog').evaluate(el => el.contains(document.activeElement))); }
      const results = await new AxeBuilder({ page: p }).include('[data-cookie-consent]').withTags(['wcag2a','wcag2aa','wcag21aa']).analyze();
      assert.deepEqual(results.violations, []);
      await p.addStyleTag({ content: 'html { font-size:200% }' });
      await p.setViewportSize({ width: 320, height: 568 });
      const box = await p.locator('[data-cookie-save]').boundingBox(); assert(box.x >= 0 && box.x + box.width <= 321 && box.y + box.height <= 569);
      assert(await p.evaluate(() => document.documentElement.scrollWidth <= innerWidth));
      await p.locator('[data-cookie-save]').click();
      await p.addStyleTag({ content: 'html { font-size:100% }' });
      await p.setViewportSize({ width: 1440, height: 960 });
      await p.locator('footer [data-cookie-open]').click();
      if (lang === 'da') await p.screenshot({path: root + '/.context/cookie-dialog-' + theme + '.png'});
      await c.close();
    });
  }
  await check('without JavaScript privacy is readable and optional scripts never load', async () => {
    const c = await fresh({ javaScriptEnabled: false }); const p = await c.newPage(); await p.goto(origin + '/privatliv');
    assert(await p.locator('h1').isVisible()); assert(await p.locator('[data-cookie-banner]').isHidden());
    assert.equal(await p.locator('script[src^="http"],iframe').count(), 0); await c.close();
  });
};
