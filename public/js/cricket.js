/* Read-only cricket board — fetches Cricbuzz data from the Laravel endpoint. */
(function () {
  'use strict';

  var boards = Array.prototype.slice.call(document.querySelectorAll('[data-cricket-board]'));

  if (!boards.length) {
    return;
  }

  var timers = new WeakMap();
  var cycles = new WeakMap();

  function el(tag, className, text) {
    var node = document.createElement(tag);

    if (className) {
      node.className = className;
    }

    if (text !== undefined && text !== null) {
      node.textContent = String(text);
    }

    return node;
  }

  function scoreText(inning) {
    var runs = inning.runs === null || inning.runs === undefined ? '—' : inning.runs;
    return runs + (inning.wickets !== null && inning.wickets !== undefined ? '/' + inning.wickets : '');
  }

  function timeLabel(iso) {
    if (!iso) {
      return '';
    }

    var date = new Date(iso);

    if (isNaN(date.getTime())) {
      return '';
    }

    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  }

  function buildCard(match) {
    var kind = match.kind || 'live';
    var badgeClass = kind === 'result' ? 'badge--muted' : (kind === 'upcoming' ? 'badge--info' : 'badge--live');
    var badgeText = kind === 'result' ? 'Result' : (kind === 'upcoming' ? 'Upcoming' : 'Live');

    var card = el('article', 'card cricket-card');

    var top = el('div', 'cricket-card__top');
    top.appendChild(el('span', 'cricket-card__title', match.label));
    top.appendChild(el('span', 'badge ' + badgeClass, badgeText));
    card.appendChild(top);

    var body = el('div', 'cricket-card__body');
    var innings = Array.isArray(match.innings) ? match.innings : [];

    if (innings.length) {
      innings.forEach(function (inning) {
        var row = el('div', 'cricket-row');
        row.appendChild(el('span', 'cricket-row__team', inning.team || '—'));

        var score = el('span', 'cricket-row__score', scoreText(inning));

        if (inning.overs !== null && inning.overs !== undefined) {
          score.appendChild(el('span', 'cricket-row__overs', inning.overs + ' ov'));
        }

        row.appendChild(score);
        body.appendChild(row);
      });
    } else {
      body.appendChild(el('p', 'cricket-status', 'Score not started yet.'));
    }

    if (match.status || match.status_label) {
      var statusLine = el('p', 'cricket-status');
      statusLine.appendChild(el('strong', null, match.status_label || match.status || '—'));

      if (match.status_raw && match.status_raw !== (match.status_label || '')) {
        statusLine.appendChild(el('span', 'sub', ' · ' + match.status_raw));
      }

      body.appendChild(statusLine);
    }

    var meta = el('div', 'cricket-meta');

    [['CRR', match.crr], ['RRR', match.rrr], ['Target', match.target], ["P'ship", match.partnership]].forEach(function (item) {
      if (item[1] === null || item[1] === undefined || item[1] === '') {
        return;
      }

      var span = el('span', null, item[0] + ' ');
      span.appendChild(el('b', null, item[1]));
      meta.appendChild(span);
    });

    if (meta.childNodes.length) {
      body.appendChild(meta);
    }

    card.appendChild(body);

    var foot = el('div', 'cricket-card__foot');
    var updated = timeLabel(match.fetched_at);
    var metaLeft = el('span', null, updated ? 'updated ' + updated : 'live feed');

    if (match.stale) {
      metaLeft.appendChild(el('span', 'badge badge--warning', 'stale'));
    }

    foot.appendChild(metaLeft);

    var link = el('a', null, 'Scorecard →');
    link.href = match.url || '#';
    foot.appendChild(link);
    card.appendChild(foot);

    return card;
  }

  function buildGrid(items) {
    var grid = el('div', 'match-grid');

    items.forEach(function (match) {
      grid.appendChild(buildCard(match));
    });

    return grid;
  }

  function buildGroup(title, note, items) {
    var group = el('div', 'cricket-group');
    var head = el('div', 'cricket-group__head');
    head.appendChild(el('h3', null, title));
    head.appendChild(el('span', null, items.length ? note + ' · ' + items.length : note));
    group.appendChild(head);

    if (items.length) {
      group.appendChild(buildGrid(items));
    } else {
      group.appendChild(el('div', 'cricket-empty', 'No ' + title.toLowerCase() + ' right now.'));
    }

    return group;
  }

  function toolbar(board) {
    var bar = el('div', 'cricket-toolbar');
    var updated = timeLabel(board.generated_at);
    bar.appendChild(el('span', null, updated ? 'Updated ' + updated : 'Live feed'));
    bar.appendChild(el('span', null, board.cached ? 'cached' : 'fresh'));

    if (board.stale) {
      bar.appendChild(el('span', null, 'showing last snapshot'));
    }

    return bar;
  }

  function render(root, board) {
    var mode = root.dataset.mode || 'full';
    var live = Array.isArray(board.live) ? board.live : [];
    var upcoming = Array.isArray(board.upcoming) ? board.upcoming : [];
    var results = Array.isArray(board.results) ? board.results : [];

    root.textContent = '';

    if (mode === 'live') {
      root.appendChild(buildGroup('Live now', 'In play', live));
      root.appendChild(toolbar(board));

      return;
    }

    if (mode === 'home') {
      var pick = live.length ? live : (upcoming.length ? upcoming : results);

      if (pick.length) {
        root.appendChild(buildGrid(pick.slice(0, 3)));
      } else {
        root.appendChild(el('div', 'cricket-empty', 'No live cricket right now — check the cricket page for upcoming matches.'));
      }

      return;
    }

    root.appendChild(buildGroup('Live now', 'In play', live));
    root.appendChild(buildGroup('Upcoming', 'Scheduled', upcoming));
    root.appendChild(buildGroup('Results', 'Completed', results));
    root.appendChild(toolbar(board));

    var errors = Array.isArray(board.errors) ? board.errors : [];

    if (errors.length) {
      var note = el('div', 'cricket-toolbar');
      note.appendChild(el('span', null, errors.length + ' match feed(s) unavailable: ' + errors[0].message));
      root.appendChild(note);
    }
  }

  function renderError(root, message, retry) {
    root.textContent = '';

    var box = el('div', 'cricket-error');
    box.appendChild(el('span', null, message || 'Live cricket is temporarily unavailable.'));

    var button = el('button', 'btn btn--ghost btn--sm', 'Retry');
    button.type = 'button';
    button.addEventListener('click', retry);
    box.appendChild(button);

    root.appendChild(box);
  }

  function schedule(root, delay) {
    var current = timers.get(root);

    if (current) {
      clearTimeout(current);
      timers.delete(root);
    }

    if (document.hidden) {
      return;
    }

    timers.set(root, setTimeout(function () {
      load(root, false, true);
    }, delay));
  }

  function pausedNote(root) {
    var bar = root.querySelector('.cricket-toolbar');

    if (!bar) {
      bar = el('div', 'cricket-toolbar');
      root.appendChild(bar);
    }

    bar.appendChild(el('span', null, 'Auto-refresh paused to protect the API quota.'));

    var resume = el('button', 'btn btn--ghost btn--sm', 'Resume');
    resume.type = 'button';
    resume.addEventListener('click', function () {
      cycles.set(root, 0);
      bar.textContent = '';
      load(root, true, false);
    });
    bar.appendChild(resume);
  }

  function load(root, force, auto) {
    if (root.dataset.loading === '1') {
      return;
    }

    root.dataset.loading = '1';

    var url = root.dataset.endpoint || '/cricket/board';

    if (force) {
      url += (url.indexOf('?') === -1 ? '?' : '&') + 'refresh=1';
    }

    fetch(url, { headers: { Accept: 'application/json' }, cache: 'no-store' })
      .then(function (response) {
        return response.json().then(function (json) {
          return { ok: response.ok, json: json };
        });
      })
      .then(function (result) {
        if (!result.ok) {
          throw new Error((result.json && result.json.message) || 'Cricket feed request failed.');
        }

        render(root, result.json);

        if (result.json.refresh_limited) {
          var bar = root.querySelector('.cricket-toolbar');

          if (bar) {
            bar.appendChild(el('span', null, 'refresh limited — served from cache'));
          }
        }

        var count = (cycles.get(root) || 0) + (auto ? 1 : 0);
        cycles.set(root, count);

        if (count >= 10) {
          pausedNote(root);

          return;
        }

        var hasLive = Array.isArray(result.json.live) && result.json.live.length > 0;
        schedule(root, hasLive ? 30000 : 60000);
      })
      .catch(function (error) {
        renderError(root, error.message, function () {
          load(root, true, false);
        });
        schedule(root, 60000);
      })
      .finally(function () {
        root.dataset.loading = '0';
      });
  }

  document.querySelectorAll('[data-cricket-refresh]').forEach(function (button) {
    button.addEventListener('click', function () {
      boards.forEach(function (root) {
        cycles.set(root, 0);
        load(root, true, false);
      });
    });
  });

  document.addEventListener('visibilitychange', function () {
    if (!document.hidden) {
      boards.forEach(function (root) {
        load(root, false, false);
      });
    }
  });

  boards.forEach(function (root) {
    load(root, false, false);
  });
})();
