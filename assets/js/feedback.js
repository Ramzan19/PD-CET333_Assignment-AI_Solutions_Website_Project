(function () {
  const form = document.querySelector('[data-feedback-form]');
  const wall = document.querySelector('[data-feedback-wall]');
  const alertSlot = document.querySelector('[data-feedback-alert]');
  const averageNode = document.querySelector('[data-feedback-average]');
  const totalNode = document.querySelector('[data-feedback-total]');
  const starsNode = document.querySelector('[data-feedback-stars]');
  const reviewAvatarUrls = [
    'assets/images/review-card-avatar.jpg',
    'assets/images/review-card-avatar-2.jpg',
    'assets/images/review-card-avatar-3.jpg',
    'assets/images/review-card-avatar-4.jpg',
  ];

  if (!form && !wall) {
    return;
  }

  function scheduleAlertDismiss(alert) {
    window.setTimeout(() => {
      if (!alert.isConnected) {
        return;
      }
      alert.classList.add('is-hiding');
      window.setTimeout(() => alert.remove(), 260);
    }, 5000);
  }

  function showAlert(message, type) {
    if (!alertSlot) {
      return;
    }

    alertSlot.innerHTML = '';
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.textContent = message;
    alertSlot.appendChild(alert);
    scheduleAlertDismiss(alert);
  }

  function starText(rating) {
    const value = Math.max(1, Math.min(5, Number(rating) || 5));
    return `${'\u2605 '.repeat(value)}${'\u2606 '.repeat(5 - value)}`.trim();
  }

  function createReviewCard(review) {
    const card = document.createElement('article');
    card.className = 'feedback-card live-feedback is-new-feedback';
    card.dataset.feedbackCard = '';

    const top = document.createElement('div');
    top.className = 'feedback-card-top';

    const stars = document.createElement('span');
    stars.className = 'star-row';
    stars.setAttribute('aria-label', `${review.rating} out of 5 stars`);
    stars.textContent = starText(review.rating);

    const tag = document.createElement('span');
    tag.className = 'feedback-tag';
    tag.textContent = review.tag || 'Visitor review';

    top.append(stars, tag);

    const message = document.createElement('p');
    message.textContent = `"${review.message}"`;

    const identity = document.createElement('div');
    identity.className = 'feedback-author';

    const avatar = document.createElement('img');
    avatar.className = 'feedback-avatar';
    const nextAvatarIndex = wall ? wall.querySelectorAll('[data-feedback-card]').length % reviewAvatarUrls.length : 0;
    avatar.src = review.avatar_url || reviewAvatarUrls[nextAvatarIndex];
    avatar.alt = `${review.display_name || 'Visitor'} reviewer photo`;
    avatar.loading = 'lazy';
    avatar.width = 58;
    avatar.height = 58;

    const identityText = document.createElement('div');
    identityText.className = 'feedback-author-text';

    const name = document.createElement('strong');
    name.textContent = review.display_name || 'Visitor';
    const role = document.createElement('span');
    role.textContent = review.role_title || 'Visitor';
    identityText.append(name, role);
    identity.append(avatar, identityText);

    card.append(top, message, identity);
    return card;
  }

  function syncCardAvatars() {
    const cards = Array.from(wall.querySelectorAll('[data-feedback-card]'));
    cards.forEach((card, index) => {
      const avatar = card.querySelector('.feedback-avatar');
      if (avatar) {
        avatar.src = reviewAvatarUrls[index % reviewAvatarUrls.length];
      }
    });
  }

  function normalizeCards() {
    const cards = Array.from(wall.querySelectorAll('[data-feedback-card]'));
    cards.forEach((card) => card.classList.remove('featured-feedback'));
    cards.slice(3).forEach((card) => card.remove());

    const visibleCards = Array.from(wall.querySelectorAll('[data-feedback-card]'));
    if (visibleCards[2]) {
      visibleCards[2].classList.add('featured-feedback');
    }
    syncCardAvatars();
  }

  function updateStats(stats) {
    if (!stats) {
      return;
    }
    if (averageNode) {
      averageNode.textContent = stats.average;
    }
    if (totalNode) {
      totalNode.textContent = stats.summary;
    }
    if (starsNode) {
      starsNode.setAttribute('aria-label', `${stats.average} out of 5 stars`);
    }
  }

  function getPageNumber(url) {
    const target = new URL(url, window.location.href);
    return Math.max(1, Number(target.searchParams.get('ratings_page')) || 1);
  }

  function getCurrentPage() {
    const activeLink = document.querySelector('.feedback-pagination [aria-current="page"]');
    if (activeLink) {
      return getPageNumber(activeLink.href);
    }
    return getPageNumber(window.location.href);
  }

  function updatePagination(targetDoc, section) {
    const currentPagination = section.querySelector('.feedback-pagination');
    const nextPagination = targetDoc.querySelector('.feedback-pagination');

    if (currentPagination && nextPagination) {
      currentPagination.replaceWith(nextPagination);
      return;
    }

    if (currentPagination) {
      currentPagination.remove();
      return;
    }

    if (nextPagination && wall) {
      wall.insertAdjacentElement('afterend', nextPagination);
    }
  }

  async function loadFeedbackPage(url, pushState = true) {
    if (!wall || wall.dataset.paging === 'true') {
      return;
    }

    const section = wall.closest('#visitor-feedback') || wall.parentElement;
    const targetUrl = new URL(url, window.location.href);
    const direction = getPageNumber(targetUrl.href) >= getCurrentPage() ? 'next' : 'previous';
    wall.dataset.paging = 'true';
    section.classList.add('is-feedback-paging');
    wall.classList.remove('feedback-slide-in-left', 'feedback-slide-in-right');
    wall.classList.add(direction === 'next' ? 'feedback-slide-out-left' : 'feedback-slide-out-right');

    try {
      const slideOut = new Promise((resolve) => window.setTimeout(resolve, 220));
      const response = await fetch(targetUrl.href, {
        headers: {
          Accept: 'text/html',
          'X-Requested-With': 'XMLHttpRequest',
        },
      });
      const html = await response.text();
      await slideOut;

      const targetDoc = new DOMParser().parseFromString(html, 'text/html');
      const nextWall = targetDoc.querySelector('[data-feedback-wall]');

      if (!response.ok || !nextWall) {
        throw new Error('Feedback page unavailable');
      }

      wall.innerHTML = nextWall.innerHTML;
      syncCardAvatars();
      updatePagination(targetDoc, section);

      wall.classList.remove('feedback-slide-out-left', 'feedback-slide-out-right');
      wall.classList.add(direction === 'next' ? 'feedback-slide-in-right' : 'feedback-slide-in-left');
      window.setTimeout(() => {
        wall.classList.remove('feedback-slide-in-left', 'feedback-slide-in-right');
      }, 320);

      if (pushState) {
        window.history.pushState({ ratingsPage: getPageNumber(targetUrl.href) }, '', targetUrl.href);
      }
    } catch (error) {
      window.location.href = targetUrl.href;
    } finally {
      window.setTimeout(() => {
        wall.dataset.paging = 'false';
        section.classList.remove('is-feedback-paging');
      }, 320);
    }
  }

  if (wall) {
    document.addEventListener('click', (event) => {
      const link = event.target.closest('.feedback-pagination a[href]');
      const section = wall.closest('#visitor-feedback');
      if (!link || !section || !section.contains(link)) {
        return;
      }

      event.preventDefault();
      if (link.getAttribute('aria-current') === 'page') {
        return;
      }

      loadFeedbackPage(link.href);
    });

    window.addEventListener('popstate', () => {
      loadFeedbackPage(window.location.href, false);
    });
  }

  if (!form || !wall) {
    return;
  }

  const existingAlert = alertSlot ? alertSlot.querySelector('.alert') : null;
  if (existingAlert) {
    scheduleAlertDismiss(existingAlert);
  }

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (!form.reportValidity()) {
      return;
    }

    const submitButton = form.querySelector('button[type="submit"]');
    const originalLabel = submitButton ? submitButton.textContent : '';
    if (submitButton) {
      submitButton.disabled = true;
      submitButton.textContent = 'Submitting...';
    }
    form.classList.add('is-submitting');

    try {
      const response = await fetch(new URL(form.action, window.location.href), {
        method: 'POST',
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: new FormData(form),
      });
      const payload = await response.json();

      if (!response.ok || !payload.success) {
        const errors = payload.errors || ['We could not save your rating right now.'];
        showAlert(errors.join(' '), 'error');
        return;
      }

      const newCard = createReviewCard(payload.review);
      wall.prepend(newCard);
      normalizeCards();
      updateStats(payload.stats);
      showAlert(payload.message, 'success');
      form.reset();
      window.setTimeout(() => newCard.classList.remove('is-new-feedback'), 900);
    } catch (error) {
      showAlert('Connection problem. Please check the page and submit again.', 'error');
    } finally {
      if (submitButton) {
        submitButton.disabled = false;
        submitButton.textContent = originalLabel;
      }
      form.classList.remove('is-submitting');
    }
  });
})();
