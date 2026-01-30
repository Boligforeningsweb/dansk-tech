(function() {
  var searchInput = document.getElementById('product-search');
  var productItems = document.querySelectorAll('[data-product]');
  var productsList = document.querySelector('[role="list"]');
  var noResults = document.getElementById('no-results');

  if (!searchInput || !productItems.length) return;

  function performSearch(query) {
    var searchQuery = query.toLowerCase().trim();
    var visibleCount = 0;

    productItems.forEach(function(item) {
      var name = item.getAttribute('data-name') || '';
      var description = item.getAttribute('data-description') || '';
      var alternatives = item.getAttribute('data-alternatives') || '';

      var matches = !searchQuery ||
        name.includes(searchQuery) ||
        description.includes(searchQuery) ||
        alternatives.includes(searchQuery);

      if (matches) {
        item.style.display = '';
        visibleCount++;
      } else {
        item.style.display = 'none';
      }
    });

    if (searchQuery && visibleCount === 0) {
      noResults.classList.remove('hidden');
      productsList.classList.add('hidden');
    } else {
      noResults.classList.add('hidden');
      productsList.classList.remove('hidden');
    }
  }

  searchInput.addEventListener('input', function(e) {
    performSearch(e.target.value);
  });

  var searchExamples = document.querySelectorAll('.search-example');
  searchExamples.forEach(function(button) {
    button.addEventListener('click', function() {
      var searchTerm = this.getAttribute('data-search');
      searchInput.value = searchTerm;
      searchInput.focus();
      performSearch(searchTerm);
      document.getElementById('produkter').scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });
})();

(async function() {
  var response = await fetch('https://api.github.com/repos/Boligforeningsweb/dansk-tech/contributors?per_page=500&page=1');
  var contributors = await response.json();
  var container = document.getElementById('contributors');

  contributors.forEach(function(contributor) {
    var link = document.createElement('a');
    link.href = contributor.html_url;
    link.target = '_blank';
    link.rel = 'noopener noreferrer';

    var img = document.createElement('img');
    img.src = contributor.avatar_url;
    img.alt = contributor.login;
    img.width = 48;
    img.height = 48;
    img.className = 'rounded-full object-cover outline-1 -outline-offset-1 outline-black/5 dark:outline-white/10';

    link.appendChild(img);
    container.appendChild(link);
  });
})();
