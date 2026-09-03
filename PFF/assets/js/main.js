// Sprylo — light interaction (no framework)
(function () {
  // Mobile drawer
  const drawer = document.getElementById('drawer');
  const toggle = document.querySelector('.nav-toggle');
  const close  = document.querySelector('.drawer-close');

  function setDrawer(open) {
    if (!drawer || !toggle) return;
    drawer.classList.toggle('is-open', open);
    drawer.setAttribute('aria-hidden', String(!open));
    toggle.setAttribute('aria-expanded', String(open));
    document.body.style.overflow = open ? 'hidden' : '';
  }
  toggle && toggle.addEventListener('click', () => setDrawer(!drawer.classList.contains('is-open')));
  close  && close.addEventListener('click', () => setDrawer(false));
  drawer && drawer.querySelectorAll('a').forEach((a) => a.addEventListener('click', () => setDrawer(false)));
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && drawer && drawer.classList.contains('is-open')) setDrawer(false);
  });

  // Category tabs (just visual switch)
  document.querySelectorAll('.tabs').forEach((tablist) => {
    tablist.querySelectorAll('.tab').forEach((tab) => {
      tab.addEventListener('click', () => {
        tablist.querySelectorAll('.tab').forEach((t) => {
          t.classList.remove('is-active');
          t.setAttribute('aria-selected', 'false');
        });
        tab.classList.add('is-active');
        tab.setAttribute('aria-selected', 'true');
      });
    });
  });

  // Product gallery thumbnails
  const thumbs = document.querySelectorAll('.gallery-thumbs button');
  const mainImg = document.querySelector('.gallery-main img');
  thumbs.forEach((t) => {
    t.addEventListener('click', () => {
      thumbs.forEach((x) => x.classList.remove('is-active'));
      t.classList.add('is-active');
      const src = t.querySelector('img')?.src;
      if (src && mainImg) mainImg.src = src.replace(/w=\d+/, 'w=900');
    });
  });

  // Qty +/- buttons
  document.querySelectorAll('.qty').forEach((q) => {
    const input = q.querySelector('input');
    const minus = q.querySelector('[data-act="-"]');
    const plus  = q.querySelector('[data-act="+"]');
    minus && minus.addEventListener('click', () => { input.value = Math.max(1, parseInt(input.value || 1) - 1); });
    plus  && plus.addEventListener('click', () => { input.value = parseInt(input.value || 1) + 1; });
  });

  // Color swatches & option pills (visual switch)
  document.querySelectorAll('.color-swatches, .option-pills').forEach((group) => {
    group.querySelectorAll('button').forEach((b) => {
      b.addEventListener('click', () => {
        group.querySelectorAll('button').forEach((x) => x.classList.remove('is-active'));
        b.classList.add('is-active');
      });
    });
  });
})();

// Recherche produits (filtre live sur les cartes .product-card)
(function () {
  const forms = document.querySelectorAll('form.search');
  forms.forEach((form) => {
    const input = form.querySelector('input[type="text"]');
    if (!input) return;

    function applyFilter() {
      const q = input.value.trim().toLowerCase();
      const sections = document.querySelectorAll('.section');

      sections.forEach((section) => {
        const cards = section.querySelectorAll('.product-card');
        if (!cards.length) return; // pas une section produits (ex: payment logos)

        let visibleCount = 0;
        cards.forEach((card) => {
          const nameEl = card.querySelector('.name');
          const name = nameEl ? nameEl.textContent.toLowerCase() : '';
          const match = q === '' || name.includes(q);
          card.style.display = match ? '' : 'none';
          if (match) visibleCount++;
        });

        // Cache la section entière si aucun produit ne correspond
        section.style.display = (q !== '' && visibleCount === 0) ? 'none' : '';
      });
    }

    form.addEventListener('submit', (e) => {
      e.preventDefault();
      applyFilter();
      // Scroll vers le premier résultat visible
      const firstMatch = document.querySelector('.product-card:not([style*="display: none"])');
      if (input.value.trim() !== '' && firstMatch) {
        firstMatch.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    });

    // Filtre live pendant la frappe
    input.addEventListener('input', applyFilter);
  });
})();
