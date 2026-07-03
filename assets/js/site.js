(function () {
  const consentName = 'ai_solutions_cookie_consent';
  const banner = document.querySelector('[data-cookie-banner]');

  function getCookie(name) {
    return document.cookie
      .split(';')
      .map((item) => item.trim())
      .find((item) => item.startsWith(`${name}=`));
  }

  function setConsent(value) {
    const maxAge = 60 * 60 * 24 * 180;
    document.cookie = `${consentName}=${value}; Max-Age=${maxAge}; Path=/; SameSite=Lax`;
    if (banner) {
      banner.hidden = true;
    }
  }

  if (banner && !getCookie(consentName)) {
    banner.hidden = false;
    const accept = banner.querySelector('[data-cookie-accept]');
    const decline = banner.querySelector('[data-cookie-decline]');
    if (accept) {
      accept.addEventListener('click', () => setConsent('accepted'));
    }
    if (decline) {
      decline.addEventListener('click', () => setConsent('declined'));
    }
  }

  const menuButton = document.querySelector('.mobile-menu-btn');
  const mainNav = document.querySelector('#mainNav');
  if (menuButton && mainNav) {
    const setMenuOpen = (open) => {
      document.body.classList.toggle('nav-open', open);
      menuButton.setAttribute('aria-expanded', String(open));
      menuButton.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
    };

    menuButton.addEventListener('click', () => {
      setMenuOpen(!document.body.classList.contains('nav-open'));
    });

    mainNav.addEventListener('click', (event) => {
      if (event.target.closest('a')) {
        setMenuOpen(false);
      }
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') {
        setMenuOpen(false);
      }
    });

    window.addEventListener('resize', () => {
      if (window.innerWidth > 820) {
        setMenuOpen(false);
      }
    });
  }

  document.querySelectorAll('[data-live-validate]').forEach((form) => {
    const controls = form.querySelectorAll('input, select, textarea');
    controls.forEach((control) => {
      const update = () => {
        if (control.type === 'hidden' || control.closest('.field-trap')) {
          return;
        }
        control.setAttribute('aria-invalid', String(!control.checkValidity()));
      };
      control.addEventListener('blur', update);
      control.addEventListener('input', update);
      control.addEventListener('change', update);
    });
  });

  const filterWrap = document.querySelector('[data-solution-filters]');
  const solutionCards = Array.from(document.querySelectorAll('[data-solution-card]'));
  if (filterWrap && solutionCards.length) {
    filterWrap.addEventListener('click', (event) => {
      const button = event.target.closest('[data-solution-filter]');
      if (!button) {
        return;
      }

      const filter = button.dataset.solutionFilter;
      filterWrap.querySelectorAll('[data-solution-filter]').forEach((item) => {
        item.classList.toggle('active', item === button);
        item.setAttribute('aria-pressed', String(item === button));
      });

      solutionCards.forEach((card) => {
        const matches = filter === 'all'
          || card.dataset.industry === filter
          || card.dataset.category === filter;
        card.hidden = !matches;
      });
    });
  }

  const eventFilterWrap = document.querySelector('[data-event-filters]');
  const eventCards = Array.from(document.querySelectorAll('[data-event-card]'));
  const eventEmpty = document.querySelector('[data-event-empty]');
  if (eventFilterWrap && eventCards.length) {
    eventFilterWrap.addEventListener('click', (event) => {
      const button = event.target.closest('[data-event-filter]');
      if (!button) {
        return;
      }

      const filter = button.dataset.eventFilter;
      let visibleCount = 0;
      eventFilterWrap.querySelectorAll('[data-event-filter]').forEach((item) => {
        const active = item === button;
        item.classList.toggle('active', active);
        item.setAttribute('aria-pressed', String(active));
      });

      eventCards.forEach((card) => {
        const matches = filter === 'all' || card.dataset.eventInterest === filter;
        card.hidden = !matches;
        if (matches) {
          visibleCount += 1;
        }
      });

      if (eventEmpty) {
        eventEmpty.hidden = visibleCount > 0;
      }
    });
  }

  document.querySelectorAll('[data-event-interest-sync]').forEach((form) => {
    const eventSelect = form.querySelector('[data-event-select]');
    const interestSelect = form.querySelector('[data-interest-select]');
    const summary = form.querySelector('[data-event-summary]');
    if (!eventSelect || !interestSelect) {
      return;
    }

    const updateEventDetails = () => {
      const option = eventSelect.selectedOptions[0];
      const interest = option ? option.dataset.interest : '';
      if (interest) {
        interestSelect.value = interest;
      }

      if (summary) {
        if (option && option.value) {
          const title = document.createElement('strong');
          const meta = document.createElement('span');
          const copy = document.createElement('p');
          title.textContent = option.value;
          meta.textContent = `${option.dataset.date || ''} - ${option.dataset.time || ''}`;
          copy.textContent = option.dataset.summary || '';
          summary.hidden = false;
          summary.replaceChildren(title, meta, copy);
        } else {
          summary.hidden = true;
          summary.replaceChildren();
        }
      }
    };

    eventSelect.addEventListener('change', updateEventDetails);
    updateEventDetails();
  });

  // --- Dynamic country code suggestion for phone fields ---
  const phoneFields = Array.from(document.querySelectorAll('input[type="tel"], input[name="phone"]'));
  if (phoneFields.length) {
    const dialCodes = {
      GB: '+44', US: '+1', CA: '+1', IE: '+353', IN: '+91', NP: '+977', AU: '+61',
      NZ: '+64', DE: '+49', FR: '+33', ES: '+34', IT: '+39', NL: '+31', SE: '+46',
      NO: '+47', DK: '+45', AE: '+971', SA: '+966', SG: '+65', MY: '+60', JP: '+81',
      CN: '+86', ZA: '+27', BR: '+55', PK: '+92', BD: '+880', LK: '+94',
    };

    const applyDialCode = (code) => {
      if (!code) {
        return;
      }
      phoneFields.forEach((field) => {
        // Only suggest when the user has not typed anything yet.
        if (field.value.trim() === '') {
          field.value = code + ' ';
          field.dataset.dialPrefilled = '1';
        }
      });
    };

    // Clear the suggestion if the user focuses but does not use it, so it never blocks input.
    phoneFields.forEach((field) => {
      field.addEventListener('input', () => {
        if (field.dataset.dialPrefilled === '1') {
          delete field.dataset.dialPrefilled;
        }
      });
    });

    fetch('https://ipapi.co/json/')
      .then((response) => (response.ok ? response.json() : null))
      .then((data) => {
        if (data && data.country_code && dialCodes[data.country_code]) {
          applyDialCode(dialCodes[data.country_code]);
          // Also help the Country text field if it is still empty.
          const countryField = document.querySelector('input[name="country"]');
          if (countryField && countryField.value.trim() === '' && data.country_name) {
            countryField.value = data.country_name;
          }
        }
      })
      .catch(() => { /* offline or blocked: leave fields untouched */ });
  }

  // --- Gallery lightbox (progressive enhancement) ---
  const galleryCards = Array.from(document.querySelectorAll('.event-gallery-grid .gallery-card'));
  if (galleryCards.length) {
    const items = galleryCards
      .map((card) => {
        const img = card.querySelector('img');
        if (!img) {
          return null;
        }
        const heading = card.querySelector('h3');
        const copy = card.querySelector('p');
        return {
          card,
          src: img.getAttribute('src'),
          alt: img.getAttribute('alt') || '',
          title: heading ? heading.textContent.trim() : '',
          caption: copy ? copy.textContent.trim() : '',
        };
      })
      .filter(Boolean);

    if (items.length) {
      let activeIndex = 0;
      let lastFocused = null;

      const overlay = document.createElement('div');
      overlay.className = 'lightbox';
      overlay.setAttribute('role', 'dialog');
      overlay.setAttribute('aria-modal', 'true');
      overlay.setAttribute('aria-label', 'Gallery image viewer');
      overlay.hidden = true;
      overlay.innerHTML =
        '<div class="lightbox-backdrop" data-lightbox-close></div>' +
        '<figure class="lightbox-figure">' +
        '<button class="lightbox-btn lightbox-close" type="button" data-lightbox-close aria-label="Close image viewer">&times;</button>' +
        '<button class="lightbox-btn lightbox-prev" type="button" data-lightbox-prev aria-label="Previous image">&#8249;</button>' +
        '<img class="lightbox-image" alt="">' +
        '<button class="lightbox-btn lightbox-next" type="button" data-lightbox-next aria-label="Next image">&#8250;</button>' +
        '<figcaption class="lightbox-caption"><strong></strong><span></span></figcaption>' +
        '</figure>';
      document.body.appendChild(overlay);

      const lbImage = overlay.querySelector('.lightbox-image');
      const lbTitle = overlay.querySelector('.lightbox-caption strong');
      const lbCaption = overlay.querySelector('.lightbox-caption span');
      const closeBtn = overlay.querySelector('.lightbox-close');
      const prevBtn = overlay.querySelector('[data-lightbox-prev]');
      const nextBtn = overlay.querySelector('[data-lightbox-next]');
      const showNav = items.length > 1;
      prevBtn.hidden = !showNav;
      nextBtn.hidden = !showNav;

      const renderItem = (index) => {
        activeIndex = (index + items.length) % items.length;
        const item = items[activeIndex];
        lbImage.setAttribute('src', item.src);
        lbImage.setAttribute('alt', item.alt);
        lbTitle.textContent = item.title;
        lbCaption.textContent = item.caption;
      };

      const openLightbox = (index) => {
        lastFocused = document.activeElement;
        renderItem(index);
        overlay.hidden = false;
        document.body.classList.add('lightbox-open');
        closeBtn.focus();
      };

      const closeLightbox = () => {
        overlay.hidden = true;
        document.body.classList.remove('lightbox-open');
        if (lastFocused && typeof lastFocused.focus === 'function') {
          lastFocused.focus();
        }
      };

      items.forEach((item, index) => {
        const { card } = item;
        card.classList.add('gallery-card-interactive');
        card.setAttribute('role', 'button');
        card.setAttribute('tabindex', '0');
        card.setAttribute('aria-label', 'View image: ' + (item.title || item.alt || 'gallery image'));
        card.addEventListener('click', () => openLightbox(index));
        card.addEventListener('keydown', (event) => {
          if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            openLightbox(index);
          }
        });
      });

      overlay.addEventListener('click', (event) => {
        if (event.target.closest('[data-lightbox-close]')) {
          closeLightbox();
        } else if (event.target.closest('[data-lightbox-prev]')) {
          renderItem(activeIndex - 1);
        } else if (event.target.closest('[data-lightbox-next]')) {
          renderItem(activeIndex + 1);
        }
      });

      document.addEventListener('keydown', (event) => {
        if (overlay.hidden) {
          return;
        }
        if (event.key === 'Escape') {
          closeLightbox();
        } else if (showNav && event.key === 'ArrowLeft') {
          renderItem(activeIndex - 1);
        } else if (showNav && event.key === 'ArrowRight') {
          renderItem(activeIndex + 1);
        }
      });
    }
  }

  // --- Scroll reveal (progressive enhancement) ---
  // Animates content blocks into view as the visitor scrolls, with a gentle
  // per-group stagger. Falls back to fully-visible content when JS or the
  // IntersectionObserver API is unavailable, and is skipped entirely for
  // visitors who prefer reduced motion.
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if ('IntersectionObserver' in window && !prefersReducedMotion) {
    const revealSelector = [
      '.feature-card',
      '.process-list > div',
      '.split-section > div:not(.process-list):not(.case-grid)',
      '.case-grid > .case-row',
      '.article-grid > .article-card',
      '.feedback-wall > .feedback-card',
      '.feedback-score',
      '.case-study',
      '.solution-card',
      '.event-card',
      '.gallery-card',
      '.section-header',
      '.cta-band > *',
    ].join(',');

    const revealEls = Array.from(document.querySelectorAll(revealSelector));
    if (revealEls.length) {
      revealEls.forEach((el) => el.classList.add('reveal'));
      revealEls.forEach((el) => {
        const group = el.parentElement
          ? Array.from(el.parentElement.children).filter((c) => c.classList.contains('reveal'))
          : [el];
        const index = Math.max(0, group.indexOf(el));
        el.style.setProperty('--reveal-i', String(Math.min(index, 7)));
      });

      const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) {
            return;
          }
          const el = entry.target;
          el.classList.add('reveal-in');
          const index = parseInt(el.style.getPropertyValue('--reveal-i'), 10) || 0;
          // Once the entrance transition settles, swap to a finished marker so
          // the base hover styles take over cleanly and the original load-time
          // rise animation is not re-triggered.
          window.setTimeout(() => {
            el.classList.remove('reveal', 'reveal-in');
            el.classList.add('revealed');
            el.style.removeProperty('--reveal-i');
          }, 900 + index * 90);
          observer.unobserve(el);
        });
      }, { threshold: 0.12, rootMargin: '0px 0px -6% 0px' });

      revealEls.forEach((el) => observer.observe(el));
    }
  }
})();
