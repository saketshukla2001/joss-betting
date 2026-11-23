(function () {
  'use strict';

  const B = window.BETZONE || {};
  const $ = (selector, scope) => (scope || document).querySelector(selector);
  const $$ = (selector, scope) => Array.from((scope || document).querySelectorAll(selector));
  const currency = B.currency || '₹';
  const minStake = Number(B.minStake || 100);
  const maxStake = Number(B.maxStake || 100000);
  const storageKey = 'betzone_slip_v1';

  const fmt = (value) => Number(value || 0).toLocaleString('en-IN', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });

  function toast(message, type) {
    if (!message) return;
    let stack = $('#toast-stack');
    if (!stack) {
      stack = document.createElement('div');
      stack.id = 'toast-stack';
      stack.className = 'toast-stack';
      document.body.appendChild(stack);
    }
    const el = document.createElement('div');
    el.className = 'toast toast--' + (type || 'success');
    el.innerHTML = '<span class="toast__ico">' + (type === 'error' ? '!' : '✓') + '</span>' +
      '<span>' + String(message).replace(/[<>&]/g, (c) => ({ '<': '&lt;', '>': '&gt;', '&': '&amp;' }[c])) + '</span>' +
      '<button type="button" aria-label="Dismiss">×</button>';
    el.querySelector('button').addEventListener('click', () => el.remove());
    stack.appendChild(el);
    setTimeout(() => {
      el.style.opacity = '0';
      el.style.transform = 'translateY(-5px)';
      setTimeout(() => el.remove(), 220);
    }, 5200);
  }

  window.betzoneToast = toast;

  /* Flash toasts rendered by Blade */
  $$('[data-flash-toast]').forEach((el) => {
    setTimeout(() => el.remove(), 6000);
    const close = el.querySelector('button');
    if (close) close.addEventListener('click', () => el.remove());
  });

  /* User dropdown */
  $$('[data-user-menu]').forEach((menu) => {
    const trigger = $('[data-user-menu-trigger]', menu);
    if (!trigger) return;
    trigger.addEventListener('click', (event) => {
      event.stopPropagation();
      menu.classList.toggle('open');
    });
    document.addEventListener('click', (event) => {
      if (!menu.contains(event.target)) menu.classList.remove('open');
    });
  });

  /* Admin sidebar */
  const sidebar = $('#admin-sidebar');
  const overlay = $('#admin-overlay');
  const sidebarToggle = $('#admin-sidebar-toggle');
  const sidebarClose = $('#admin-sidebar-close');

  function setSidebar(open) {
    if (!sidebar) return;

    if (window.innerWidth > 960) {
      document.body.classList.toggle('sidebar-collapsed', !open);
      sidebar.classList.remove('open');
      if (overlay) overlay.classList.remove('show');
      document.body.classList.remove('no-scroll');
      return;
    }

    sidebar.classList.toggle('open', open);
    if (overlay) overlay.classList.toggle('show', open);
    document.body.classList.toggle('no-scroll', open);
  }

  if (sidebarToggle) sidebarToggle.addEventListener('click', () => {
    if (window.innerWidth > 960) {
      setSidebar(document.body.classList.contains('sidebar-collapsed'));
    } else {
      setSidebar(true);
    }
  });
  if (sidebarClose) sidebarClose.addEventListener('click', () => setSidebar(false));
  if (overlay) overlay.addEventListener('click', () => setSidebar(false));

  /* Confirm forms */
  document.addEventListener('submit', (event) => {
    const form = event.target.closest('form[data-confirm]');
    if (form && !window.confirm(form.dataset.confirm)) {
      event.preventDefault();
    }
  });

  /* Copy helpers */
  $$('[data-copy]').forEach((button) => {
    button.addEventListener('click', async () => {
      const value = button.dataset.copy;
      try {
        await navigator.clipboard.writeText(value);
        toast('Copied to clipboard');
      } catch (error) {
        toast('Could not copy', 'error');
      }
    });
  });

  /* Wallet method fields */
  $$('[data-method-form]').forEach((form) => {
    const fields = $$('[data-method-field]', form);
    const update = () => {
      const selected = $('input[name="method"]:checked', form);
      const method = selected ? selected.value : '';
      fields.forEach((field) => {
        field.classList.toggle('show', field.dataset.methodField === method);
      });
    };
    $$('input[name="method"]', form).forEach((input) => input.addEventListener('change', update));
    update();
  });

  /* Password reveal */
  $$('[data-toggle-password]').forEach((button) => {
    button.addEventListener('click', () => {
      const input = $('#' + button.dataset.togglePassword);
      if (!input) return;
      input.type = input.type === 'password' ? 'text' : 'password';
      button.textContent = input.type === 'password' ? 'Show' : 'Hide';
    });
  });

  /* Demo credential fill */
  $$('[data-fill-username]').forEach((button) => {
    button.addEventListener('click', () => {
      const username = $('input[name="username"]');
      const password = $('input[name="password"]');
      if (username) username.value = button.dataset.fillUsername;
      if (password) password.value = button.dataset.fillPassword;
      if (username) username.focus();
      toast('Demo credentials filled');
    });
  });

  /* =========================================================
     Bet slip
     ========================================================= */
  const slipEl = $('#bet-slip');
  const slipFab = $('#slip-fab');
  const slipOverlay = $('#slip-overlay');
  const slipBody = $('#slip-body');
  const slipEmpty = $('#slip-empty');
  const slipError = $('#slip-error');
  const slipStake = $('#slip-stake');
  const slipReturn = $('#slip-return');
  const slipPlace = $('#slip-place');

  let slip = null;
  let slipOpen = false;

  try {
    slip = JSON.parse(localStorage.getItem(storageKey) || 'null');
  } catch (error) {
    slip = null;
  }

  const isSmall = () => window.innerWidth <= 760;

  function saveSlip() {
    if (slip) localStorage.setItem(storageKey, JSON.stringify(slip));
    else localStorage.removeItem(storageKey);
  }

  function markSelected() {
    $$('[data-selection-id]').forEach((button) => {
      button.classList.toggle('selected', !!slip && String(button.dataset.selectionId) === String(slip.selectionId));
    });
    $$('[data-slip-count]').forEach((el) => { el.textContent = slip ? '1' : '0'; });
  }

  function openSlip() {
    if (!slipEl) return;
    slipEl.classList.add('open');
    slipEl.setAttribute('aria-hidden', 'false');
    slipOpen = true;
    if (slipOverlay && isSmall()) slipOverlay.classList.add('show');
    if (slipFab) slipFab.classList.add('hidden');
    if (isSmall()) document.body.classList.add('no-scroll');
    if (slipStake && !slipStake.value) slipStake.value = slip ? (slip.stake || minStake) : minStake;
    if (slipStake) slipStake.focus({ preventScroll: true });
  }

  function closeSlip() {
    if (!slipEl) return;
    slipEl.classList.remove('open');
    slipEl.setAttribute('aria-hidden', 'true');
    slipOpen = false;
    if (slipOverlay) slipOverlay.classList.remove('show');
    if (slipFab) slipFab.classList.toggle('hidden', !slip);
    document.body.classList.remove('no-scroll');
  }

  function renderSlip() {
    if (!slipEl) return;
    markSelected();

    if (!slip) {
      if (slipBody) slipBody.hidden = true;
      if (slipEmpty) slipEmpty.hidden = false;
      if (slipFab) slipFab.classList.add('hidden');
      if (slipError) slipError.classList.remove('show');
      closeSlip();
      return;
    }

    if (slipBody) slipBody.hidden = false;
    if (slipEmpty) slipEmpty.hidden = true;
    if (slipFab) slipFab.classList.remove('hidden');

    const set = (selector, value) => {
      const el = $(selector);
      if (el) el.textContent = value;
    };
    set('#slip-match', slip.matchLabel || '');
    set('#slip-selection-name', slip.selectionName || '');
    set('#slip-market-name', slip.marketName || '');
    set('#slip-odds', Number(slip.odds || 0).toFixed(2));

    if (slipStake) {
      if (!slipStake.value) slipStake.value = slip.stake || minStake;
      slipStake.min = minStake;
      slipStake.max = maxStake;
    }

    updateReturn();
    saveSlip();
  }

  function updateReturn() {
    if (!slip || !slipReturn) return;
    const stake = Number(slipStake ? slipStake.value : 0) || 0;
    const odds = Number(slip.odds) || 0;
    slip.stake = stake;
    slipReturn.textContent = currency + fmt(stake * odds);
    if (slipPlace) slipPlace.disabled = !slip.available;
    saveSlip();
  }

  function selectSelection(data) {
    slip = {
      selectionId: data.selectionId,
      odds: Number(data.odds),
      selectionName: data.selectionName,
      marketName: data.marketName,
      matchLabel: data.matchLabel,
      marketId: data.marketId,
      matchId: data.matchId,
      available: true,
      stake: slip && String(slip.selectionId) === String(data.selectionId) ? slip.stake : minStake,
    };
    if (slipStake) slipStake.value = slip.stake;
    if (slipError) slipError.classList.remove('show');
    renderSlip();
    openSlip();
  }

  $$('[data-selection-id]').forEach((button) => {
    button.addEventListener('click', () => {
      if (!B.authenticated) {
        window.location.href = B.loginUrl || '/login';
        return;
      }
      if (!B.canBet) {
        toast('Administrators cannot place bets. Use a client account.', 'error');
        return;
      }
      if (button.disabled) return;
      selectSelection({
        selectionId: button.dataset.selectionId,
        odds: button.dataset.odds,
        selectionName: button.dataset.selectionName,
        marketName: button.dataset.marketName,
        matchLabel: button.dataset.matchLabel,
        marketId: button.dataset.marketId,
        matchId: button.dataset.matchId,
      });
    });
  });

  if (slipFab) slipFab.addEventListener('click', openSlip);
  $$('[data-slip-close]').forEach((button) => button.addEventListener('click', closeSlip));
  if (slipOverlay) slipOverlay.addEventListener('click', closeSlip);

  if (slipStake) {
    slipStake.addEventListener('input', updateReturn);
    slipStake.addEventListener('change', updateReturn);
  }

  $$('[data-quick-stake]').forEach((button) => {
    button.addEventListener('click', () => {
      if (!slipStake) return;
      slipStake.value = button.dataset.quickStake;
      updateReturn();
    });
  });

  if (slipPlace) {
    slipPlace.addEventListener('click', async () => {
      if (!slip) return;
      const stake = Number(slipStake ? slipStake.value : 0);

      if (!Number.isFinite(stake) || stake < minStake) {
        if (slipError) {
          slipError.textContent = 'Minimum stake is ' + currency + fmt(minStake) + '.';
          slipError.classList.add('show');
        }
        return;
      }
      if (stake > maxStake) {
        if (slipError) {
          slipError.textContent = 'Maximum stake is ' + currency + fmt(maxStake) + '.';
          slipError.classList.add('show');
        }
        return;
      }

      const original = slipPlace.textContent;
      slipPlace.disabled = true;
      slipPlace.textContent = 'Placing bet…';
      if (slipError) slipError.classList.remove('show');

      try {
        const response = await fetch(B.betUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': B.csrf,
          },
          body: JSON.stringify({ selection_id: slip.selectionId, stake: stake }),
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
          let message = data.message || 'Could not place the bet.';
          if (data.errors) {
            const first = Object.values(data.errors)[0];
            if (Array.isArray(first) && first[0]) message = first[0];
          }
          if (slipError) {
            slipError.textContent = message;
            slipError.classList.add('show');
          }
          toast(message, 'error');
          return;
        }

        toast(data.message || 'Bet placed successfully');
        $$('[data-balance]').forEach((el) => { el.textContent = currency + fmt(data.balance); });
        $$('[data-exposure]').forEach((el) => { el.textContent = currency + fmt(data.exposure); });
        slip = null;
        saveSlip();
        renderSlip();
        closeSlip();
      } catch (error) {
        toast('Network error. Please try again.', 'error');
        if (slipError) {
          slipError.textContent = 'Network error. Please check your connection and try again.';
          slipError.classList.add('show');
        }
      } finally {
        slipPlace.disabled = false;
        slipPlace.textContent = original;
      }
    });
  }

  renderSlip();

  /* =========================================================
     Live odds polling
     ========================================================= */
  const oddsButtons = $$('[data-odds-id]');

  async function refreshOdds() {
    if (!B.canBet || !oddsButtons.length || document.hidden) return;

    const ids = Array.from(new Set(oddsButtons.map((el) => el.dataset.oddsId))).filter(Boolean).slice(0, 150);
    if (!ids.length) return;

    try {
      const response = await fetch((B.oddsUrl || '/api/odds') + '?ids=' + encodeURIComponent(ids.join(',')), {
        headers: { 'Accept': 'application/json' },
        cache: 'no-store',
      });
      if (!response.ok) return;
      const data = await response.json();
      const selections = data.selections || {};

      oddsButtons.forEach((button) => {
        const info = selections[button.dataset.oddsId];
        if (!info) return;

        const valueEl = $('[data-odds-value]', button);
        const newOdds = Number(info.odds).toFixed(2);
        if (valueEl && valueEl.textContent.trim() !== newOdds) {
          valueEl.textContent = newOdds;
          button.dataset.odds = newOdds;
          button.style.transition = 'background .2s';
          button.style.background = 'rgba(201,255,69,.15)';
          setTimeout(() => { button.style.background = ''; }, 700);
        }

        const unavailable = info.status !== 'active' || info.market_status !== 'open';
        button.disabled = unavailable;

        if (slip && String(slip.selectionId) === String(button.dataset.oddsId)) {
          slip.odds = Number(info.odds);
          slip.available = !unavailable;
          renderSlip();
          if (unavailable && slipError) {
            slipError.textContent = 'This market is suspended. Please choose another selection.';
            slipError.classList.add('show');
          }
        }
      });
    } catch (error) {
      /* silent — polling should never break the page */
    }
  }

  if (B.canBet && oddsButtons.length) {
    setInterval(refreshOdds, 10000);
    setTimeout(refreshOdds, 3500);
  }

})();
