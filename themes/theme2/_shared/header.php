<?php if (!isset($site) || !isset($palette)) { require __DIR__ . '/bootstrap.php'; } ?>
<?php
$siteLogoUrl = theme_asset_url('images/logo.png');
if (!empty($site['image'])) {
  $logoFile = basename((string)$site['image']);
  $logoAbs = dirname(__DIR__, 3) . '/user/admin/site/' . $logoFile;
  if ($logoFile !== '' && is_file($logoAbs)) {
    $siteLogoUrl = function_exists('app_url')
      ? app_url('user/admin/site/' . rawurlencode($logoFile))
      : '../../user/admin/site/' . rawurlencode($logoFile);
  }
}

include_once dirname(__DIR__, 3) . '/private/shared-favicon-url.php';
$siteFaviconUrl = $sharedFaviconUrl;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= htmlspecialchars($isHomePage ? ($site['name'] ?? 'Bank') : (($pageTitle ?? 'Banking') . ' | ' . ($site['name'] ?? 'Bank'))) ?></title>
  <?php if ($siteFaviconUrl !== ''): ?>
  <link rel="icon" href="<?= htmlspecialchars($siteFaviconUrl) ?>" type="image/png">
  <?php endif; ?>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=<?= rawurlencode((string)($themeMeta['font_family'] ?? 'Public Sans')) ?>:wght@300;400;500;600;700;800&family=<?= rawurlencode((string)($themeMeta['display_font'] ?? 'Merriweather')) ?>:wght@700;900&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            brand: ['<?= addslashes($themeMeta['font_family'] ?? 'Public Sans') ?>', 'ui-sans-serif', 'system-ui'],
            display: ['<?= addslashes($themeMeta['display_font'] ?? 'Merriweather') ?>', 'Georgia', 'serif']
          },
          boxShadow: {
            editorial: '0 36px 90px -44px rgba(15, 23, 42, 0.46)',
            panel: '0 22px 64px -38px rgba(15, 23, 42, 0.3)'
          }
        }
      }
    }
  </script>
  <style>
    :root {
      --bg: <?= htmlspecialchars($palette['bg']) ?>;
      --surface: <?= htmlspecialchars($palette['surface']) ?>;
      --ink: <?= htmlspecialchars($palette['ink']) ?>;
      --muted: <?= htmlspecialchars($palette['muted']) ?>;
      --primary: <?= htmlspecialchars($palette['primary']) ?>;
      --primary2: <?= htmlspecialchars($palette['primary2']) ?>;
      --accent: <?= htmlspecialchars($palette['accent']) ?>;
      --line: <?= htmlspecialchars($palette['line']) ?>;
    }
    * { box-sizing: border-box; }
    body {
      font-family: <?= htmlspecialchars($themeMeta['font_family'] ?? 'Public Sans') ?>, ui-sans-serif, system-ui;
      background:
        radial-gradient(circle at top right, color-mix(in srgb, var(--accent) 16%, transparent), transparent 30%),
        linear-gradient(180deg, #f8fafc 0%, var(--bg) 45%, #eef2f7 100%);
      color: var(--ink);
    }
    .surface-card {
      background: color-mix(in srgb, var(--surface) 95%, rgba(255, 255, 255, 0.94));
      border: 1px solid color-mix(in srgb, var(--line) 92%, white);
      box-shadow: 0 30px 72px -52px rgba(15, 23, 42, 0.34);
    }
    .nav-group .nav-dropdown {
      opacity: 0;
      visibility: hidden;
      pointer-events: none;
      transform: translateY(10px);
      transition: opacity 180ms ease, transform 180ms ease, visibility 0s linear 180ms;
    }
    .nav-group .nav-dropdown.nav-open {
      opacity: 1;
      visibility: visible;
      pointer-events: auto;
      transform: translateY(0);
      transition-delay: 0s;
    }
    .nav-group .nav-dropdown a:hover {
      background: color-mix(in srgb, var(--accent) 12%, white) !important;
    }
    .menu-summary::-webkit-details-marker { display: none; }
    .home-slider { position: relative; overflow: hidden; }
    .home-slide {
      position: absolute;
      inset: 0;
      opacity: 0;
      transition: opacity 700ms ease;
    }
    .home-slide.is-active { opacity: 1; }
    .home-slide::after {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(90deg, rgba(9, 21, 35, 0.82) 0%, rgba(9, 21, 35, 0.52) 40%, rgba(9, 21, 35, 0.18) 100%);
    }
    .home-slide-description {
      color: #ffffff !important;
    }
    .home-slider-dot {
      width: 0.7rem;
      height: 0.7rem;
      border-radius: 999px;
      background: rgba(255, 255, 255, 0.36);
      border: 1px solid rgba(255, 255, 255, 0.6);
      transition: transform 180ms ease, background 180ms ease;
    }
    .home-slider-dot.is-active {
      background: #ffffff;
      transform: scale(1.15);
    }
    .section-kicker {
      font-size: 0.72rem;
      letter-spacing: 0.22em;
      text-transform: uppercase;
      color: var(--muted);
    }
    .feature-art {
      position: relative;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 11rem;
      border-bottom: 1px solid color-mix(in srgb, var(--line) 90%, white);
      overflow: hidden;
      background: linear-gradient(180deg, color-mix(in srgb, var(--bg) 90%, white), color-mix(in srgb, var(--surface) 96%, white));
    }
    .feature-art-chip {
      position: relative;
      z-index: 2;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 4.6rem;
      height: 2rem;
      padding: 0 0.9rem;
      border-radius: 999px;
      font-size: 0.72rem;
      font-weight: 700;
      letter-spacing: 0.14em;
      color: #ffffff;
      text-transform: uppercase;
      background: linear-gradient(135deg, var(--primary), var(--primary2));
      box-shadow: 0 10px 26px -16px rgba(15, 23, 42, 0.7);
    }
    .feature-art::before,
    .feature-art::after {
      content: '';
      position: absolute;
      border-radius: 1rem;
    }
    .feature-art-card::before {
      width: 9rem;
      height: 5.6rem;
      background: linear-gradient(135deg, color-mix(in srgb, var(--primary) 65%, #1e293b), color-mix(in srgb, var(--primary2) 72%, #0f172a));
      transform: rotate(-6deg);
      box-shadow: 0 18px 34px -22px rgba(15, 23, 42, 0.8);
    }
    .feature-art-card::after {
      width: 6.8rem;
      height: 4.2rem;
      background: rgba(255, 255, 255, 0.22);
      transform: translate(1.7rem, 1.1rem) rotate(-6deg);
    }
    .feature-art-atm::before {
      width: 6.4rem;
      height: 7.6rem;
      border-radius: 0.9rem;
      background: linear-gradient(180deg, color-mix(in srgb, var(--primary2) 65%, #0f172a), color-mix(in srgb, var(--primary) 55%, #111827));
      box-shadow: 0 18px 30px -22px rgba(15, 23, 42, 0.75);
    }
    .feature-art-atm::after {
      width: 3.6rem;
      height: 0.5rem;
      border-radius: 999px;
      background: rgba(255, 255, 255, 0.75);
      transform: translateY(-1.25rem);
    }
    .feature-art-rdc::before {
      width: 7.4rem;
      height: 5.2rem;
      border-radius: 0.9rem;
      background: linear-gradient(180deg, color-mix(in srgb, var(--primary) 54%, #0f172a), color-mix(in srgb, var(--primary2) 74%, #111827));
      box-shadow: 0 18px 30px -20px rgba(15, 23, 42, 0.72);
    }
    .feature-art-rdc::after {
      width: 2.2rem;
      height: 2.2rem;
      border-radius: 0.55rem;
      border: 3px solid rgba(255, 255, 255, 0.72);
      transform: translate(2.1rem, -1.25rem);
    }
    .legacy-wrap {
      line-height: 1.8;
      font-size: 1rem;
      color: var(--ink);
    }
    .legacy-wrap > * + * { margin-top: 1rem; }
    .legacy-wrap h1,
    .legacy-wrap h2,
    .legacy-wrap h3,
    .legacy-wrap h4 {
      font-family: <?= htmlspecialchars($themeMeta['display_font'] ?? 'Merriweather') ?>, Georgia, serif;
      color: var(--primary2);
      font-weight: 800;
      line-height: 1.22;
      margin-top: 1.55rem;
      margin-bottom: 0.7rem;
    }
    .legacy-wrap h1:first-child,
    .legacy-wrap h2:first-child,
    .legacy-wrap h3:first-child,
    .legacy-wrap h4:first-child { margin-top: 0; }
    .legacy-wrap p,
    .legacy-wrap li,
    .legacy-wrap td,
    .legacy-wrap th { color: color-mix(in srgb, var(--ink) 90%, white); }
    .legacy-wrap ul,
    .legacy-wrap ol { padding-left: 1.25rem; margin: 0.85rem 0; }
    .legacy-wrap a {
      color: var(--primary);
      text-decoration: none;
      font-weight: 600;
    }
    .legacy-wrap a:hover { text-decoration: underline; }
    .legacy-wrap img {
      display: block;
      width: 100% !important;
      max-width: 100% !important;
      height: auto !important;
      margin: 1.35rem 0;
      border-radius: 1rem;
      box-shadow: 0 18px 40px rgba(15, 23, 42, 0.12);
    }
    .legacy-wrap img[src*='eNews-icon.png'],
    .legacy-wrap img[src*='contact-icon.png'] {
      display: inline-block;
      width: 84px !important;
      max-width: 84px !important;
      margin: 0.35rem 0.55rem 0.35rem 0;
      border-radius: 0.45rem;
      box-shadow: none;
      vertical-align: middle;
    }
    .legacy-wrap #contact-stripe {
      margin: 1.4rem 0 0.5rem;
      padding: 0;
      background: transparent !important;
    }
    .legacy-wrap #contact-stripe .inner-content {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 0.9rem;
      max-width: 100%;
      margin: 0;
      padding: 0;
    }
    .legacy-wrap #contact-stripe .inner-content > div {
      margin: 0;
      padding: 0;
    }
    .legacy-wrap #contact-stripe .inner-content > div > a {
      display: flex;
      align-items: center;
      gap: 0.9rem;
      height: 100%;
      padding: 1rem;
      border-radius: 0.95rem;
      border: 1px solid var(--line);
      background: color-mix(in srgb, var(--surface) 95%, white);
      text-decoration: none;
      box-shadow: 0 16px 30px -24px rgba(15, 23, 42, 0.36);
    }
    .legacy-wrap #contact-stripe .inner-content > div > a img[src*='eNews-icon.png'],
    .legacy-wrap #contact-stripe .inner-content > div > a img[src*='contact-icon.png'] {
      width: 58px !important;
      max-width: 58px !important;
      margin: 0;
      border-radius: 0.6rem;
      background: color-mix(in srgb, var(--bg) 80%, white);
      padding: 0.2rem;
      flex-shrink: 0;
    }
    .legacy-wrap #contact-stripe .inner-content > div > a h3 {
      margin: 0;
      font-size: 1rem;
      line-height: 1.25;
      color: var(--primary2);
    }
    .legacy-wrap #contact-stripe .inner-content > div > a p {
      margin: 0.22rem 0 0;
      font-size: 0.9rem;
      color: var(--muted);
    }
    .legacy-wrap #contact-stripe .clear {
      display: none !important;
    }
    .legacy-wrap table {
      width: 100% !important;
      display: block;
      overflow-x: auto;
      border-collapse: collapse;
      border: 1px solid var(--line);
      border-radius: 0.9rem;
      background: color-mix(in srgb, var(--surface) 97%, white);
    }
    .legacy-wrap td,
    .legacy-wrap th {
      padding: 0.8rem 0.9rem;
      border: 1px solid var(--line);
      vertical-align: top;
    }
    .legacy-wrap [style*='float'] {
      float: none !important;
      margin-left: 0 !important;
      margin-right: 0 !important;
    }
    .legacy-wrap iframe {
      max-width: 100%;
      border: 0;
      border-radius: 0.85rem;
    }
    /* Google Translate banner suppression */
    .goog-te-banner-frame,
    .goog-te-banner-frame.skiptranslate,
    iframe.skiptranslate,
    iframe.goog-te-banner-frame,
    iframe[src*="translate.google.com/translate"],
    iframe[src*="translate.googleapis.com"],
    .VIpgJd-ZVi9od-ORHb,
    .VIpgJd-ZVi9od-aZ2wEe-wOHMyf,
    body > .skiptranslate {
      display: none !important;
      visibility: hidden !important;
      height: 0 !important;
      min-height: 0 !important;
    }
    html,
    body {
      top: 0 !important;
      margin-top: 0 !important;
    }
  </style>
  <script type="text/javascript">
    (function() {
      function hideGoogleTranslateBanner() {
        var selectors = [
          '.goog-te-banner-frame',
          '.goog-te-banner-frame.skiptranslate',
          'iframe.skiptranslate',
          'iframe.goog-te-banner-frame',
          'iframe[src*="translate.google.com/translate"]',
          'iframe[src*="translate.googleapis.com"]',
          '.VIpgJd-ZVi9od-ORHb',
          '.VIpgJd-ZVi9od-aZ2wEe-wOHMyf',
          'body > .skiptranslate'
        ];
        for (var i = 0; i < selectors.length; i++) {
          var nodes = document.querySelectorAll(selectors[i]);
          for (var j = 0; j < nodes.length; j++) {
            nodes[j].style.setProperty('display', 'none', 'important');
            nodes[j].style.setProperty('visibility', 'hidden', 'important');
            nodes[j].style.setProperty('height', '0', 'important');
            nodes[j].style.setProperty('min-height', '0', 'important');
          }
        }
        document.documentElement.style.setProperty('top', '0', 'important');
        if (document.body) {
          document.body.style.setProperty('top', '0', 'important');
          document.body.style.setProperty('margin-top', '0', 'important');
        }
      }
      document.addEventListener('DOMContentLoaded', hideGoogleTranslateBanner);
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', hideGoogleTranslateBanner);
      } else {
        hideGoogleTranslateBanner();
      }
      var observer = new MutationObserver(hideGoogleTranslateBanner);
      observer.observe(document.documentElement, { childList: true, subtree: true, attributes: true });
      setInterval(hideGoogleTranslateBanner, 500);
    }());
  </script>
</head>
<body class="min-h-screen font-brand antialiased">
  <header class="sticky top-0 z-50 border-b backdrop-blur-xl" style="background:linear-gradient(135deg, color-mix(in srgb, var(--primary2) 86%, #020617) 0%, color-mix(in srgb, var(--primary) 74%, #0f172a) 52%, color-mix(in srgb, var(--primary2) 62%, #111827) 100%); border-color:rgba(255,255,255,0.18)">
    <div class="border-b" style="border-color:var(--line); background:#ffffff">
      <div class="max-w-7xl mx-auto px-4 py-2.5 text-[11px] uppercase tracking-[0.18em] flex items-center justify-between gap-4" style="color:var(--muted)">
        <div class="flex flex-wrap items-center gap-4">
          <?php if (!empty($site['phone'])): ?><span><?= htmlspecialchars((string)$site['phone']) ?></span><?php endif; ?>
          <?php if (!empty($site['email'])): ?><span><?= htmlspecialchars((string)$site['email']) ?></span><?php endif; ?>
        </div>
        <div class="hidden md:flex items-center gap-5">
          <a href="<?= htmlspecialchars(theme_page_url('Contact-Us.php')) ?>" class="transition hover:opacity-80" style="color:var(--ink)">Contact</a>
          <a href="<?= htmlspecialchars(theme_page_url('Locations.php')) ?>" class="transition hover:opacity-80" style="color:var(--ink)">Locations</a>
          <a href="<?= htmlspecialchars(theme_page_url('Security-Center.php')) ?>" class="transition hover:opacity-80" style="color:var(--ink)">Security</a>
        </div>
      </div>
    </div>
    <div class="max-w-7xl mx-auto px-4 py-4 lg:py-5 flex items-center justify-between gap-6">
      <a href="<?= htmlspecialchars(theme_page_url('index.php')) ?>" class="shrink-0">
        <img src="<?= htmlspecialchars($siteLogoUrl) ?>" alt="<?= htmlspecialchars((string)($site['name'] ?? 'Bank')) ?> logo" class="h-12 md:h-14 w-auto max-w-[220px] md:max-w-[300px] object-contain">
      </a>

      <nav class="hidden xl:flex items-center gap-2 flex-1 justify-center">
        <?php foreach ($navColumns as $group => $links): ?>
          <?php $groupActive = nav_group_active($links, $currentPage); ?>
          <div class="nav-group relative">
            <button class="rounded-full px-4 py-2 text-[12px] font-semibold uppercase tracking-[0.16em] transition" style="color:<?= $groupActive ? '#ffffff' : 'rgba(255,255,255,0.86)' ?>; background:<?= $groupActive ? 'rgba(255,255,255,0.18)' : 'transparent' ?>">
              <?= htmlspecialchars($group) ?>
            </button>
            <div class="nav-dropdown absolute left-1/2 top-full z-50 mt-1 w-[320px] -translate-x-1/2 rounded-[1.75rem] p-4 surface-card">
              <div class="space-y-1.5">
                <?php foreach ($links as $link): ?>
                  <?php $isActive = nav_is_active((string)$link[1], $currentPage); ?>
                  <a href="<?= htmlspecialchars(theme_page_url((string)$link[1])) ?>" class="block rounded-2xl px-4 py-3 text-sm transition <?= $isActive ? 'font-semibold' : 'font-medium' ?>" style="background:<?= $isActive ? 'color-mix(in srgb, var(--accent) 14%, white)' : 'color-mix(in srgb, var(--bg) 42%, white)' ?>; color:var(--ink)">
                    <?= htmlspecialchars((string)$link[0]) ?>
                  </a>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </nav>

      <?php include_once dirname(__DIR__, 3) . '/private/shared-translator.php'; ?>
      <div class="hidden md:flex items-center gap-3 shrink-0">
        <?php foreach ($clientActions as $action): ?>
          <a href="<?= htmlspecialchars((string)$action['href']) ?>" class="inline-flex items-center rounded-full px-4 py-2.5 text-sm font-semibold transition" style="background:<?= $action['variant'] === 'primary' ? 'linear-gradient(135deg, var(--primary), var(--primary2))' : 'rgba(255,255,255,0.08)' ?>; color:#ffffff; border:1px solid <?= $action['variant'] === 'primary' ? 'transparent' : 'rgba(255,255,255,0.35)' ?>;">
            <?= htmlspecialchars((string)$action['label']) ?>
          </a>
        <?php endforeach; ?>
      </div>

      <details class="xl:hidden relative shrink-0">
        <summary class="menu-summary list-none cursor-pointer rounded-full px-4 py-2 text-sm font-semibold border" style="border-color:rgba(255,255,255,0.26); color:#ffffff">Menu</summary>
        <div class="absolute right-0 mt-3 w-[min(22rem,calc(100vw-2rem))] rounded-[1.75rem] p-5 surface-card">
          <div class="grid grid-cols-2 gap-3 mb-5">
            <?php foreach ($clientActions as $action): ?>
              <a href="<?= htmlspecialchars((string)$action['href']) ?>" class="inline-flex justify-center rounded-full px-4 py-2.5 text-sm font-semibold transition" style="background:<?= $action['variant'] === 'primary' ? 'linear-gradient(135deg, var(--primary), var(--primary2))' : 'transparent' ?>; color:<?= $action['variant'] === 'primary' ? '#ffffff' : 'var(--primary2)' ?>; border:1px solid <?= $action['variant'] === 'primary' ? 'transparent' : 'color-mix(in srgb, var(--line) 92%, white)' ?>;">
                <?= htmlspecialchars((string)$action['label']) ?>
              </a>
            <?php endforeach; ?>
          </div>
          <div class="space-y-5">
            <?php foreach ($navColumns as $group => $links): ?>
              <div>
                <p class="section-kicker mb-2"><?= htmlspecialchars($group) ?></p>
                <div class="space-y-1.5">
                  <?php foreach ($links as $link): ?>
                    <a href="<?= htmlspecialchars(theme_page_url((string)$link[1])) ?>" class="block rounded-2xl px-4 py-3 text-sm <?= nav_is_active((string)$link[1], $currentPage) ? 'font-semibold' : 'font-medium' ?>" style="background:<?= nav_is_active((string)$link[1], $currentPage) ? 'color-mix(in srgb, var(--accent) 14%, white)' : 'color-mix(in srgb, var(--bg) 42%, white)' ?>; color:var(--ink)">
                      <?= htmlspecialchars((string)$link[0]) ?>
                    </a>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </details>
    </div>
  </header>
<script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.nav-group').forEach(function (group) {
    var timer;
    var dd = group.querySelector('.nav-dropdown');
    if (!dd) return;
    group.addEventListener('mouseenter', function () {
      clearTimeout(timer);
      dd.classList.add('nav-open');
    });
    group.addEventListener('mouseleave', function () {
      timer = setTimeout(function () { dd.classList.remove('nav-open'); }, 200);
    });
  });
});
</script>

