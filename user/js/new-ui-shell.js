(function () {
  function ensureTopbar() {
    if (document.getElementById('new-shell-topbar')) {
      return;
    }
    if (document.body) {
      document.body.classList.add('has-new-shell');
    }
    syncShellColorsFromPageTheme();

    var topbar = document.createElement('header');
    topbar.id = 'new-shell-topbar';
    topbar.className = 'new-shell-topbar';
    topbar.innerHTML = '' +
      '<div class="new-shell-left">' +
      '  <button id="newShellSidebarToggle" class="new-shell-btn" aria-label="Toggle menu">' +
      '    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h16M4 17h16"/></svg>' +
      '  </button>' +
      '  <a class="new-shell-logo-link" href="index.php" aria-label="Dashboard">' +
      '    <img class="new-shell-logo" src="img/logo.png" alt="Logo" onerror="this.onerror=null;this.src=\'assets/img/logo.png\';">' +
      '  </a>' +
      '</div>' +
      '<div class="new-shell-right">' +
      '  <div class="new-shell-translator">' +
      '    <select id="newShellLang" class="new-shell-select">' +
      '      <option value="en">EN</option>' +
      '    </select>' +
      '    <div id="google_translate_element2" class="new-shell-gt"></div>' +
      '  </div>' +
      '  <div class="new-shell-dropdown-wrap">' +
      '    <button id="newShellMessagesBtn" class="new-shell-btn new-shell-icon-btn" aria-label="Messages">' +
      '      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 7h18v10H3z"/><path d="M3 8l9 6 9-6"/></svg>' +
      '      <span id="newShellMsgBadge" class="new-shell-badge">0</span>' +
      '    </button>' +
      '    <div id="newShellMessagesDropdown" class="new-shell-dropdown"></div>' +
      '  </div>' +
      '  <div class="new-shell-dropdown-wrap">' +
      '    <button id="newShellProfileBtn" class="new-shell-profile">' +
      '      <img id="newShellProfileImg" src="admin/foto/user.png" alt="Profile">' +
      '      <span id="newShellProfileName">Customer</span>' +
      '    </button>' +
      '    <div id="newShellProfileDropdown" class="new-shell-dropdown">' +
      '      <a href="profile.php">My Profile</a>' +
      '      <a href="editpass.php">Change Password</a>' +
      '      <a href="statement.php">Statements</a>' +
      '      <a href="logout.php" class="danger">Logout</a>' +
      '    </div>' +
      '  </div>' +
      '</div>';

    document.body.insertBefore(topbar, document.body.firstChild);

    var openSidebar = document.getElementById('sidebar-open');
    var sidebar = document.getElementById('app-sidebar');
    var btnToggle = document.getElementById('newShellSidebarToggle');
    if (!sidebar && btnToggle) {
      btnToggle.style.display = 'none';
    }

    function isMobileViewport() {
      return window.matchMedia('(max-width: 1023px)').matches;
    }

    function closeMobileSidebar() {
      document.body.classList.remove('new-shell-sidebar-open');
    }

    function syncSidebarMode() {
      if (!sidebar) {
        return;
      }
      if (isMobileViewport()) {
        closeMobileSidebar();
      } else {
        document.body.classList.remove('new-shell-sidebar-open');
      }
    }

    syncSidebarMode();
    window.addEventListener('resize', syncSidebarMode);

    if (btnToggle) {
      btnToggle.addEventListener('click', function () {
        if (!sidebar) {
          return;
        }
        if (isMobileViewport()) {
          document.body.classList.toggle('new-shell-sidebar-open');
        } else {
          document.body.classList.remove('new-shell-sidebar-open');
        }
      });
    }

    if (openSidebar) {
      openSidebar.style.display = 'none';
    }

    var closeBtn = document.getElementById('sidebar-close');
    if (closeBtn) {
      closeBtn.addEventListener('click', function () {
        closeMobileSidebar();
      });
    }

    if (sidebar) {
      var links = sidebar.querySelectorAll('a[href]');
      links.forEach(function (link) {
        link.addEventListener('click', function () {
          if (isMobileViewport()) {
            closeMobileSidebar();
          }
        });
      });
    }

    wireDropdown('newShellMessagesBtn', 'newShellMessagesDropdown');
    wireDropdown('newShellProfileBtn', 'newShellProfileDropdown');
    loadMessageData();
    setupTranslator();
  }

  function wireDropdown(btnId, dropdownId) {
    var btn = document.getElementById(btnId);
    var menu = document.getElementById(dropdownId);
    if (!btn || !menu) {
      return;
    }
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      var isOpen = menu.classList.contains('open');
      closeAllDropdowns();
      if (!isOpen) {
        menu.classList.add('open');
      }
    });
  }

  function closeAllDropdowns() {
    var all = document.querySelectorAll('.new-shell-dropdown.open');
    all.forEach(function (el) { el.classList.remove('open'); });
  }

  function loadMessageData() {
    fetch('messages-feed.php', { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data || !data.ok) {
          return;
        }
        var badge = document.getElementById('newShellMsgBadge');
        if (badge) {
          badge.textContent = String(data.messageCount || 0);
        }
        var nameEl = document.getElementById('newShellProfileName');
        if (nameEl) {
          nameEl.textContent = data.fullName || 'Customer';
        }
        var img = document.getElementById('newShellProfileImg');
        if (img) {
          img.src = 'admin/foto/' + (data.photo || 'user.png');
          img.onerror = function () { this.src = 'admin/foto/user.png'; };
        }
        var msgDrop = document.getElementById('newShellMessagesDropdown');
        if (!msgDrop) {
          return;
        }
        hydrateTranslatorOptions(data.translatorOptions || []);

        var html = '<div class="new-shell-dropdown-title">Messages</div>';
        if (!data.messages || !data.messages.length) {
          html += '<div class="new-shell-empty">No messages</div>';
        } else {
          data.messages.forEach(function (m) {
            var unread = m.isRead ? '' : ' new';
            var rel = escapeHtml(m.relativeDate || '');
            html += '<div class="new-shell-msg-row' + unread + '">';
            html += '<a href="message_view.php?id=' + Number(m.id || 0) + '" class="new-shell-msg-link" data-message-id="' + Number(m.id || 0) + '"><strong>' + escapeHtml(m.sender || 'System') + '</strong><span>' + escapeHtml(m.subject || 'Notification') + '</span>' + (rel ? '<small class="new-shell-msg-time">' + rel + '</small>' : '') + '</a>';
            html += '<button type="button" class="new-shell-mark-one" data-message-id="' + Number(m.id || 0) + '" aria-label="Mark as read">';
            html += '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>';
            html += '</button>';
            html += '</div>';
          });
        }
        html += '<button id="newShellMarkAllRead" type="button" class="new-shell-mark-read">Mark all as read</button>';
        html += '<a href="inbox.php" class="new-shell-viewall">See all</a>';
        msgDrop.innerHTML = html;
        bindMessageReadActions();
      })
      .catch(function () {});
  }

  function setupTranslator() {
    var sel = document.getElementById('newShellLang');
    if (!sel) {
      return;
    }

    window.googleTranslateElementInit2 = function () {
      new google.translate.TranslateElement({ pageLanguage: 'en', autoDisplay: false, includedLanguages: getIncludedLanguages() }, 'google_translate_element2');
      hideGoogleBanner();
    };

    if (!document.querySelector('script[data-new-shell-translate="1"]')) {
      var s = document.createElement('script');
      s.src = 'https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit2';
      s.async = true;
      s.setAttribute('data-new-shell-translate', '1');
      document.body.appendChild(s);
    }

    var current = readGoogleLang();
    if (current) {
      sel.value = current;
    }

    if (current && current !== 'en') {
      applyGoogleLanguage(current);
    }

    sel.addEventListener('change', function () {
      var lang = sel.value || 'en';
      setGoogleLang(lang);
      if (lang === 'en') {
        clearGoogleLang();
        window.location.reload();
        return;
      }
      applyGoogleLanguage(lang, function () {
        window.location.reload();
      });
    });

    hideGoogleBanner();
    setInterval(hideGoogleBanner, 800);
  }

  function hydrateTranslatorOptions(options) {
    var sel = document.getElementById('newShellLang');
    if (!sel || !Array.isArray(options) || !options.length) {
      return;
    }

    var current = sel.value || 'en';
    sel.innerHTML = '';
    options.forEach(function (opt) {
      if (!opt || !opt.code) {
        return;
      }
      var o = document.createElement('option');
      o.value = String(opt.code);
      o.textContent = String(opt.label || opt.code).toUpperCase();
      sel.appendChild(o);
    });

    var desired = readGoogleLang() || current;
    var exists = Array.prototype.some.call(sel.options, function (o) { return o.value === desired; });
    sel.value = exists ? desired : 'en';
  }

  function bindMessageReadActions() {
    var markAll = document.getElementById('newShellMarkAllRead');
    if (markAll) {
      markAll.addEventListener('click', function () {
        markRead(0);
      });
    }

    var links = document.querySelectorAll('.new-shell-msg-link[data-message-id]');
    links.forEach(function (link) {
      link.addEventListener('click', function (e) {
        e.preventDefault();
        var id = Number(link.getAttribute('data-message-id') || 0);
        if (id > 0) {
          markRead(id);
        }
        var href = link.getAttribute('href') || 'inbox.php';
        window.location.href = href;
      });
    });

    var oneButtons = document.querySelectorAll('.new-shell-mark-one[data-message-id]');
    oneButtons.forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var id = Number(btn.getAttribute('data-message-id') || 0);
        if (id > 0) {
          markRead(id);
        }
      });
    });
  }

  function markRead(messageId) {
    var body = new URLSearchParams();
    if (messageId > 0) {
      body.set('message_id', String(messageId));
    }

    fetch('messages-mark-read.php', {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
      body: body.toString(),
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data || !data.ok) {
          return;
        }
        var badge = document.getElementById('newShellMsgBadge');
        if (badge) {
          badge.textContent = String(data.remaining || 0);
        }
        loadMessageData();
      })
      .catch(function () {});
  }

  function getIncludedLanguages() {
    var sel = document.getElementById('newShellLang');
    if (!sel || !sel.options || !sel.options.length) {
      return 'en,es,fr,de,it,pt,ru,zh-CN';
    }
    var codes = [];
    for (var i = 0; i < sel.options.length; i += 1) {
      var code = (sel.options[i].value || '').trim();
      if (code && codes.indexOf(code) === -1) {
        codes.push(code);
      }
    }
    return codes.join(',');
  }

  function hideGoogleBanner() {
    var frame = document.querySelector('iframe.goog-te-banner-frame.skiptranslate');
    if (frame) {
      frame.style.display = 'none';
      frame.style.visibility = 'hidden';
      frame.style.height = '0';
    }
    if (document.body) {
      document.body.style.top = '0px';
    }
  }

  function syncShellColorsFromPageTheme() {
    var root = document.documentElement;
    if (!root) {
      return;
    }
    if (root.style.getPropertyValue('--shell-navy')) {
      return;
    }

    var source = document.querySelector('.menu-w') || document.querySelector('.top-bar') || document.querySelector('.header') || document.body;
    if (!source) {
      return;
    }

    var bg = window.getComputedStyle(source).backgroundColor;
    if (bg && bg !== 'rgba(0, 0, 0, 0)' && bg !== 'transparent') {
      root.style.setProperty('--shell-navy', bg);
      root.style.setProperty('--shell-navy2', bg);
    }
  }

  function setGoogleLang(lang) {
    var cookieValue = '/en/' + lang;
    var paths = getCookiePaths();
    persistPreferredLanguage(lang);
    for (var i = 0; i < paths.length; i += 1) {
      writeGoogtransCookie(cookieValue, paths[i]);
    }
  }

  function clearGoogleLang() {
    clearPreferredLanguage();
    var paths = getCookiePaths();
    for (var i = 0; i < paths.length; i += 1) {
      var path = paths[i];
      document.cookie = 'googtrans=;path=' + path + ';expires=Thu, 01 Jan 1970 00:00:00 GMT;SameSite=Lax';
      try {
        document.cookie = 'googtrans=;path=' + path + ';domain=' + window.location.hostname + ';expires=Thu, 01 Jan 1970 00:00:00 GMT;SameSite=Lax';
      } catch (e) {}
      try {
        document.cookie = 'googtrans=;path=' + path + ';domain=.' + window.location.hostname + ';expires=Thu, 01 Jan 1970 00:00:00 GMT;SameSite=Lax';
      } catch (e2) {}
    }
  }

  function readGoogleLang() {
    var match = document.cookie.match(/(?:^|;\s*)googtrans=\/[^/]+\/([^;]+)/);
    if (match && match[1]) {
      return decodeURIComponent(match[1]);
    }
    return readPreferredLanguage();
  }

  function persistPreferredLanguage(lang) {
    try {
      window.localStorage.setItem('preferred_lang', String(lang || 'en'));
    } catch (e) {}
    try {
      window.sessionStorage.setItem('preferred_lang', String(lang || 'en'));
    } catch (e2) {}
  }

  function clearPreferredLanguage() {
    try {
      window.localStorage.removeItem('preferred_lang');
    } catch (e) {}
    try {
      window.sessionStorage.removeItem('preferred_lang');
    } catch (e2) {}
  }

  function readPreferredLanguage() {
    var lang = '';
    try {
      lang = window.localStorage.getItem('preferred_lang') || '';
    } catch (e) {}
    if (!lang) {
      try {
        lang = window.sessionStorage.getItem('preferred_lang') || '';
      } catch (e2) {}
    }
    return lang;
  }

  function getCookiePaths() {
    var paths = ['/'];
    var parts = (window.location.pathname || '').split('/').filter(Boolean);
    var current = '';
    for (var i = 0; i < parts.length; i += 1) {
      current += '/' + parts[i];
      paths.push(current);
    }
    return paths;
  }

  function writeGoogtransCookie(cookieValue, path) {
    document.cookie = 'googtrans=' + cookieValue + ';path=' + path + ';SameSite=Lax';
    try {
      document.cookie = 'googtrans=' + cookieValue + ';path=' + path + ';domain=' + window.location.hostname + ';SameSite=Lax';
    } catch (e) {}
    try {
      document.cookie = 'googtrans=' + cookieValue + ';path=' + path + ';domain=.' + window.location.hostname + ';SameSite=Lax';
    } catch (e2) {}
  }

  function applyGoogleLanguage(lang, onFailure) {
    var tries = 0;
    var maxTries = 18;

    function attempt() {
      var combo = document.querySelector('select.goog-te-combo');
      if (combo && combo.options && combo.options.length > 0) {
        combo.value = lang;
        try {
          var ev = document.createEvent('HTMLEvents');
          ev.initEvent('change', true, true);
          combo.dispatchEvent(ev);
          combo.dispatchEvent(ev);
        } catch (e) {}
        hideGoogleBanner();
        return;
      }
      tries += 1;
      if (tries < maxTries) {
        setTimeout(attempt, 120);
      } else if (typeof onFailure === 'function') {
        onFailure();
      }
    }

    attempt();
  }

  function escapeHtml(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  document.addEventListener('click', function () { closeAllDropdowns(); });
  document.addEventListener('DOMContentLoaded', ensureTopbar);
})();
