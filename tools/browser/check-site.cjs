// Isolated browser regressions. No external services or production data are changed.
const { chromium, firefox, webkit } = require('playwright');
const { AxeBuilder } = require('@axe-core/playwright');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const os = require('node:os');
const path = require('node:path');
const net = require('node:net');
const { spawn } = require('node:child_process');
const root = path.resolve(__dirname, '../..');
const pixel = Buffer.from('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII=', 'base64');

async function main() {
  const cache = fs.mkdtempSync(path.join(os.tmpdir(), 'dts-browser-'));
  fs.writeFileSync(path.join(cache, 'contributors.json'), '[]');
  const port = await new Promise(resolve => {
    const socket = net.createServer().listen(0, '127.0.0.1', () => {
      const value = socket.address().port;
      socket.close(() => resolve(value));
    });
  });
  const origin = `http://127.0.0.1:${port}`;
  const server = spawn('php', ['-S', `127.0.0.1:${port}`, 'tools/dev-router.php'], {
    cwd: root, env: { ...process.env, DTS_CACHE_DIR: cache, PHP_CLI_SERVER_WORKERS: '' }, stdio: 'ignore'
  });
  let browser;
  let passed = 0;
  async function context(options = {}) {
    const result = await browser.newContext({ viewport: { width: 390, height: 844 }, reducedMotion: 'reduce', ...options });
    // Test only our UI: portrait/favicons must not depend on remote availability.
    await result.route('**/*', route => {
      const url = new URL(route.request().url());
      if (url.origin !== origin) return route.abort();
      if (url.pathname.startsWith('/cache/favicons/')) return route.fulfill({ contentType: 'image/png', body: pixel });
      return route.continue();
    });
    return result;
  }
  async function check(name, run) { await run(); passed++; console.log(`PASS ${name}`); }
  try {
    let ready = false;
    for (let attempt = 0; attempt < 100; attempt++) {
      try { await fetch(origin + '/site.webmanifest'); ready = true; break; } catch (_) {}
      if (server.exitCode !== null) throw Error('PHP server exited before it was ready.');
      await new Promise(resolve => setTimeout(resolve, 50));
    }
    assert(ready, 'PHP server did not start.');
    const engines = { chromium, firefox, webkit };
    const engine = process.env.DTS_BROWSER || 'chromium';
    assert(engines[engine], 'Unsupported DTS_BROWSER.');
    browser = await engines[engine].launch({ headless: true, ...(process.env.DTS_CHROME_PATH ? { executablePath: process.env.DTS_CHROME_PATH } : {}) });

    for (const [locale, expected] of [['en-US', 'en'], ['da-DK', 'da'], ['de-DE', 'da']]) {
      await check(`fresh ${locale} visitor selects ${expected}`, async () => {
        const c = await context({ locale }); const p = await c.newPage();
        await p.goto(origin + '/'); assert.equal(await p.locator('html').getAttribute('lang'), expected);
        const cookie = (await c.cookies()).find(x => x.name === 'dts_language');
        assert.equal(cookie.value, expected); assert(cookie.httpOnly); assert.equal(cookie.sameSite, 'Lax');
        assert(cookie.expires > Date.now() / 1000 + 300 * 86400);
        await c.close();
      });
    }
    await check('explicit selection overrides browser, persists, and keeps search/fragment', async () => {
      const c = await context({ locale: 'en-US' }); const p = await c.newPage();
      await p.goto(origin + '/en#produkter'); await p.locator('#product-search').fill('Stripe');
      const count = await p.locator('[data-product]:visible').count();
      await p.locator('[data-language-link][lang=da]').click();
      await p.waitForURL(url => url.pathname === '/' && !url.searchParams.has('site_language'));
      assert.equal(new URL(p.url()).hash, '#produkter'); assert.equal(new URL(p.url()).searchParams.get('q'), 'Stripe');
      assert.equal(await p.locator('[data-product]:visible').count(), count);
      await p.goto(origin + '/'); assert.equal(await p.locator('html').getAttribute('lang'), 'da');
      await p.goto(origin + '/en/alternatives/stripe'); assert.equal(await p.locator('html').getAttribute('lang'), 'en');
      await p.goto(origin + '/'); assert.equal(new URL(p.url()).pathname, '/en');
      await p.goto(origin + '/alternativer/stripe'); assert.equal(await p.locator('html').getAttribute('lang'), 'da');
      await c.close();
    });
    await check('language links preserve search and section when opened in a new tab', async () => {
      for (const language of ['da', 'en']) {
        const c = await context(); const p = await c.newPage();
        const target = language === 'da' ? 'en' : 'da';
        const home = language === 'da' ? '/' : '/en';
        await p.goto(origin + home + '?site_language=' + language + '&utm_source=review#produkter');
        await p.locator('#product-search').fill('Stripe');
        const count = await p.locator('[data-product]:visible').count();
        const link = p.locator('[data-language-link][lang=' + target + ']');
        const destination = new URL(await link.getAttribute('href'), origin);
        assert.equal(destination.searchParams.get('q'), 'Stripe');
        assert.equal(destination.searchParams.get('utm_source'), 'review');
        assert.equal(destination.hash, '#produkter');
        let popup;
        if (engine === 'webkit') {
          // Headless WebKit has no browser UI for middle-click tab creation.
          // Use the actual href directly, as opening via a context menu does.
          popup = await c.newPage(); await popup.goto(destination.href);
        } else {
          const newTab = c.waitForEvent('page'); await link.click({ button: 'middle' });
          popup = await newTab; await popup.waitForLoadState();
        }
        assert.equal(await popup.locator('html').getAttribute('lang'), target);
        assert.equal(new URL(popup.url()).searchParams.get('q'), 'Stripe');
        assert.equal(new URL(popup.url()).hash, '#produkter');
        assert.equal(await popup.locator('[data-product]:visible').count(), count);
        await popup.close();
        await p.locator('.search-example[data-search=Mailchimp]').click();
        assert.equal(new URL(await link.getAttribute('href'), origin).searchParams.get('q'), 'Mailchimp');
        await p.evaluate(() => { window.location.hash = 'faq'; });
        await p.waitForFunction(() => [...document.querySelectorAll('[data-language-link]')].every(a => a.hash === '#faq'));
        await p.locator('#product-search').fill('');
        const cleared = new URL(await link.getAttribute('href'), origin);
        assert.equal(cleared.searchParams.has('q'), false);
        assert.equal(cleared.hash, '#faq');
        assert.equal(cleared.searchParams.get('utm_source'), 'review');
        await c.close();
      }
    });
    await check('short tech badges can be found and copied without selecting a product', async () => {
      const c = await context(); const p = await c.newPage();
      for (const pageLanguage of ['da', 'en']) for (const badgeLanguage of ['da', 'en']) {
        const prefix = pageLanguage === 'en' ? '/en' : '';
        const label = badgeLanguage === 'en' ? 'Nordic tech' : 'Dansk tech';
        await p.goto(origin + prefix + '/badge?sprog=' + badgeLanguage);
        assert.equal(await p.locator('#produkt-valg').inputValue(), '');
        const card = p.locator('a[href="#tech"]');
        assert((await card.innerText()).includes(label)); await card.click();
        assert.equal(new URL(p.url()).hash, '#tech');
        assert.equal(await p.locator('#tech-heading').innerText(), label);
        assert.equal(await p.locator('#tech .badge-preview svg').count(), 4);
        await p.evaluate(() => Object.defineProperty(navigator, 'clipboard', { configurable: true, value: { writeText: async text => { window.copied = text; } } }));
        await p.locator('#tech button.copy').first().click();
        const html = await p.evaluate(() => window.copied);
        assert(html.includes(label) && !html.includes('We support') && !html.includes('Vi støtter'));
        assert(html.includes('/badge/listet-paa-dansktechstack.svg'));
        const response = await c.request.get(origin + (badgeLanguage === 'en' ? '/en' : '') + '/badge/listet-paa-dansktechstack.svg');
        assert.equal(response.status(), 200); assert((await response.text()).includes(label));
        const supporter = await p.locator('#stoette svg title').first().textContent();
        assert(supporter.includes(badgeLanguage === 'en' ? 'We support Nordic tech' : 'Vi støtter dansk tech'));
      }
      await c.close();
    });
    await check('badge previews, SVG files and embeds keep their own language', async () => {
      const c = await context(); const p = await c.newPage();
      for (const pageLanguage of ['da', 'en']) for (const badgeLanguage of ['da', 'en']) {
        const prefix = pageLanguage === 'en' ? '/en' : '';
        await p.goto(origin + prefix + '/badge?produkt=alunta-com&sprog=' + badgeLanguage);
        assert.equal(await p.locator('html').getAttribute('lang'), pageLanguage);
        const previews = await p.locator('.badge-preview svg').evaluateAll(items => items.map(e => ({
          lang: e.getAttribute('lang'), xmlLang: e.getAttribute('xml:lang'), inherited: e.closest('[lang]').getAttribute('lang')
        })));
        assert(previews.length > 0);
        assert(previews.every(e => e.lang === badgeLanguage && e.xmlLang === badgeLanguage && e.inherited === badgeLanguage));
        const embeds = await p.locator('button[data-copy]').evaluateAll(items => items.map(e => e.dataset.copy).filter(code => code.startsWith('<a ')).map(code => {
          const doc = new DOMParser().parseFromString(code, 'text/html');
          return { title: doc.querySelector('a').getAttribute('lang'), alt: doc.querySelector('img').closest('[lang]').getAttribute('lang') };
        }));
        assert(embeds.length > 0 && embeds.every(e => e.title === badgeLanguage && e.alt === badgeLanguage));
        const response = await c.request.get(origin + (badgeLanguage === 'en' ? '/en' : '') + '/badge/alunta-com/stripe.svg');
        assert.equal(response.status(), 200);
        const svg = await p.evaluate(code => {
          const root = new DOMParser().parseFromString(code, 'image/svg+xml').documentElement;
          return { lang: root.getAttribute('lang'), xmlLang: root.getAttributeNS('http://www.w3.org/XML/1998/namespace', 'lang') };
        }, await response.text());
        assert.deepEqual(svg, { lang: badgeLanguage, xmlLang: badgeLanguage });
      }
      await c.close();
    });
    await check('cookie-free/no-JavaScript explicit Danish selection has no redirect loop', async () => {
      const c = await context({ javaScriptEnabled: false, locale: 'en-US' }); const p = await c.newPage();
      await p.goto(origin + '/en'); await c.clearCookies();
      await p.locator('[data-language-link][lang=da]').click();
      assert.equal(await p.locator('html').getAttribute('lang'), 'da'); assert(new URL(p.url()).searchParams.has('site_language'));
      await c.close();
    });
    await check('system theme, manual override, and override persistence', async () => {
      const c = await context({ colorScheme: 'dark' }); const p = await c.newPage();
      await p.goto(origin + '/en'); assert.equal(await p.locator('html').evaluate(e => e.classList.contains('dark')), true);
      await p.emulateMedia({ colorScheme: 'light' }); assert.equal(await p.locator('html').evaluate(e => e.classList.contains('dark')), false);
      await p.locator('[data-theme-toggle]').click(); assert.equal(await p.locator('[data-theme-toggle]').getAttribute('aria-pressed'), 'true');
      await p.goto(origin + '/en/badge'); assert.equal(await p.locator('html').evaluate(e => e.classList.contains('dark')), true);
      await p.emulateMedia({ colorScheme: 'light' }); assert.equal(await p.locator('html').evaluate(e => e.classList.contains('dark')), true);
      await c.close();
    });
    await check('theme works when localStorage is blocked', async () => {
      const c = await context({ colorScheme: 'dark' });
      await c.addInitScript(() => Object.defineProperty(window, 'localStorage', { get() { throw Error('blocked'); } }));
      const p = await c.newPage(); const errors = []; p.on('pageerror', e => errors.push(e.message));
      await p.goto(origin + '/en'); assert.equal(await p.locator('[data-theme-toggle]').getAttribute('aria-pressed'), 'true');
      await p.locator('[data-theme-toggle]').click(); assert.equal(await p.locator('[data-theme-toggle]').getAttribute('aria-pressed'), 'false');
      assert.deepEqual(errors, []); await c.close();
    });
    await check('live search announces results and no results', async () => {
      const c = await context(); const p = await c.newPage(); await p.goto(origin + '/en');
      await p.locator('#product-search').fill('Stripe');
      await p.waitForFunction(() => document.querySelector('#search-status').textContent.includes('products'));
      await p.locator('#product-search').fill('no-product-9f4fe');
      await p.waitForFunction(() => document.querySelector('#search-status').textContent.toLowerCase().includes('no products'));
      assert(await p.locator('#no-results').isVisible()); await c.close();
    });
    await check('repeated clipboard feedback resets', async () => {
      const c = await context(); const p = await c.newPage(); await p.goto(origin + '/en/badge?produkt=alunta-com');
      await p.evaluate(() => Object.defineProperty(navigator, 'clipboard', { value: { writeText: async text => { window.copied = text; } } }));
      const button = p.locator('#udseende button.copy').first(); await button.click(); await button.click();
      assert.equal(await button.innerText(), 'Copied ✓'); await p.waitForTimeout(2100);
      assert.equal(await button.innerText(), 'Copy HTML'); assert((await p.evaluate(() => window.copied)).includes('<a href='));
      await c.close();
    });
    await check('local footer SVG and curated product image load', async () => {
      const c = await context(); const p = await c.newPage(); await p.goto(origin + '/en');
      const logo = p.locator('li[data-name=ethnote] img'); await logo.scrollIntoViewIfNeeded();
      await p.waitForFunction(() => document.querySelector('li[data-name=ethnote] img').naturalWidth > 0);
      assert((await logo.getAttribute('src')).startsWith('/images/'));
      const footer = p.locator('footer img'); await footer.scrollIntoViewIfNeeded();
      await p.waitForFunction(() => document.querySelector('footer img').naturalWidth > 0);
      assert.equal(new URL(await footer.getAttribute('src'), origin).origin, origin); await c.close();
    });
    await check('broken curated image falls back to favicon then initial', async () => {
      const c = await context(); await c.route('**/images/ethnote_logo_light.svg', r => r.abort());
      await c.route('**/cache/favicons/*', r => r.abort());
      const p = await c.newPage(); await p.goto(origin + '/en'); await p.locator('li[data-name=ethnote]').scrollIntoViewIfNeeded();
      await p.locator('li[data-name=ethnote] .favicon-fallback').waitFor({ state: 'visible' });
      assert.equal(await p.locator('li[data-name=ethnote] .favicon-fallback').innerText(), 'E'); await c.close();
    });
    const paths = ['/', '/alternativer', '/alternativer/stripe', '/alternativer/adobe-acrobat', '/alternativer/microsoft-dynamics-365-field-service', '/badge', '/badge?produkt=alunta-com', '/badge?produkt=alunta-com&badge=listet-paa-dansktechstack', '/missing-browser-check'];
    for (const lang of ['da', 'en']) for (const theme of ['light', 'dark']) {
      await check(`${lang}/${theme}: 9 templates, text resize and accessibility`, async () => {
        const c = await context({ colorScheme: theme }); await c.addCookies([{ name: 'dts_language', value: lang, url: origin }]);
        const p = await c.newPage(); const errors = []; p.on('pageerror', e => errors.push(e.message));
        for (const pathname of paths) {
          const target = lang === 'en' ? '/en' + (pathname === '/' ? '' : pathname.replace('/alternativer', '/alternatives')) : pathname;
          await p.goto(origin + target);
          for (const width of [320, 390, 768, 1440]) {
            await p.setViewportSize({ width, height: 844 });
            for (const size of ['100%', '200%']) {
              await p.evaluate(value => document.documentElement.style.fontSize = value, size);
              await p.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
              const overflow = await p.evaluate(() => document.documentElement.scrollWidth > innerWidth);
              assert(!overflow, `${target}: overflow at ${width}px / ${size}`);
            }
          }
          await p.evaluate(() => document.documentElement.style.fontSize = '');
          await p.setViewportSize({ width: 390, height: 844 });
          assert.equal(await p.locator('main#main-content').count(), 1);
          const axe = await new AxeBuilder({ page: p }).withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa', 'best-practice']).analyze();
          assert.deepEqual(axe.violations.map(v => ({ id: v.id, nodes: v.nodes.map(n => n.target) })), [], target);
          const targets = await p.locator('[data-language-link],[data-theme-toggle],button.copy').evaluateAll(items => items.filter(e => !e.hidden).map(e => e.getBoundingClientRect().height));
          assert(targets.every(height => height >= 44), `${target}: small controls`);
        }
        assert.deepEqual(errors, []); await c.close();
      });
    }
    console.log(`OK: ${passed} browser regression groups (${engine}).`);
  } finally {
    if (browser) await browser.close();
    server.kill();
    await new Promise(resolve => { if (server.exitCode !== null) resolve(); else server.once('exit', resolve); });
    fs.rmSync(cache, { recursive: true, force: true });
  }
}
main().catch(error => { console.error(error); process.exitCode = 1; });
