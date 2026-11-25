/* Polls this app once per endpoint. It never calls the cricket provider. */
(function () {
  'use strict';

  var roots = Array.prototype.slice.call(document.querySelectorAll('[data-cricket-market-live]'));

  if (!roots.length) {
    return;
  }

  function paint(root, match) {
    var score = root.querySelector('[data-cricket-score]');
    var status = root.querySelector('[data-cricket-status]');

    if (score && match) {
      var line = '';

      if (match.batting_team) {
        line += match.batting_team + ' ';
      }

      if (match.runs !== null && match.runs !== undefined) {
        line += match.runs + '/' + (match.wickets === null || match.wickets === undefined ? '—' : match.wickets);
      }

      if (match.overs) {
        line += ' (' + match.overs + ')';
      }

      if (match.stale) {
        line += (line ? ' · ' : '') + 'stale cache';
      }

      score.textContent = line || 'Cached score unavailable';
    }

    if (status && match) {
      status.textContent = match.stale ? 'Stale' : (match.status || 'Live');
    }

    (match && match.markets ? match.markets : []).forEach(function (market) {
      document.querySelectorAll('[data-market-status="' + market.id + '"]').forEach(function (badge) {
        badge.textContent = market.status;
      });

      if (market.status !== 'open') {
        document.querySelectorAll('[data-market-id="' + market.id + '"]').forEach(function (button) {
          button.disabled = true;
        });
      }
    });
  }

  function pull(endpoint, group) {
    fetch(endpoint, { headers: { 'Accept': 'application/json' }, cache: 'no-store' })
      .then(function (response) { return response.ok ? response.json() : null; })
      .then(function (body) {
        if (!body || !Array.isArray(body.matches)) {
          return;
        }

        group.forEach(function (root) {
          var matchId = String(root.getAttribute('data-match-id') || '');
          body.matches.forEach(function (match) {
            if (String(match.match_id) === matchId) {
              paint(root, match);
            }
          });
        });
      })
      .catch(function () {});
  }

  var groups = {};

  roots.forEach(function (root) {
    var endpoint = root.getAttribute('data-endpoint');

    if (!endpoint) {
      return;
    }

    if (!groups[endpoint]) {
      groups[endpoint] = [];
    }

    groups[endpoint].push(root);
  });

  Object.keys(groups).forEach(function (endpoint) {
    var group = groups[endpoint];
    pull(endpoint, group);
    setInterval(function () {
      if (!document.hidden) {
        pull(endpoint, group);
      }
    }, 10000);
  });
})();
