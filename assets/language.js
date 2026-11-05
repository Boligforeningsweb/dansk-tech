// Preference markers also work without cookies/JavaScript; keep normal URLs clean.
const currentUrl = new URL(window.location.href);
if (currentUrl.searchParams.has('site_language')) {
  currentUrl.searchParams.delete('site_language');
  window.history.replaceState(window.history.state, '', currentUrl.pathname + currentUrl.search + currentUrl.hash);
}

// Keep real link destinations current for clicks, middle-clicks and context menus.
const languageLinks = document.querySelectorAll('[data-language-link]');
const productSearch = document.getElementById('product-search');
function updateLanguageLinks() {
  const current = new URL(window.location.href);
  languageLinks.forEach((link) => {
    const url = new URL(link.href);
    url.search = current.search;
    url.searchParams.set('site_language', link.lang);
    if (productSearch) {
      const query = productSearch.value.trim();
      if (query) url.searchParams.set('q', query);
      else url.searchParams.delete('q');
    }
    url.hash = current.hash;
    link.href = url.href;
  });
}
updateLanguageLinks();
productSearch?.addEventListener('input', updateLanguageLinks);
window.addEventListener('hashchange', updateLanguageLinks);
window.addEventListener('pageshow', updateLanguageLinks);
window.addEventListener('popstate', updateLanguageLinks);
languageLinks.forEach((link) => link.addEventListener('click', updateLanguageLinks));
