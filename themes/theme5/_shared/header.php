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
  <title><?= htmlspecialchars(($pageTitle ?? 'Banking') . ' | ' . ($site['name'] ?? 'Bank')) ?></title>
  <?php if ($siteFaviconUrl !== ''): ?>
  <link rel="icon" href="<?= htmlspecialchars($siteFaviconUrl) ?>" type="image/png">
  <?php endif; ?>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: { brand: ['<?= addslashes($themeMeta['font_family'] ?? 'Outfit') ?>', 'ui-sans-serif', 'system-ui'] }
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
    body { font-family: <?= htmlspecialchars($themeMeta['font_family']) ?>, ui-sans-serif, system-ui; background: linear-gradient(180deg, color-mix(in srgb, var(--primary2) 16%, var(--bg)) 0%, var(--bg) 55%); color: var(--ink); }
    .legacy-wrap { line-height: 1.7; font-size: 0.98rem; color: var(--ink); }
    .legacy-wrap > * + * { margin-top: 1rem; }
    .legacy-wrap h1,.legacy-wrap h2,.legacy-wrap h3,.legacy-wrap h4 { color: var(--primary2); font-weight: 800; line-height: 1.25; margin-top: 1.4rem; margin-bottom: 0.7rem; }
    .legacy-wrap p,.legacy-wrap li { color: color-mix(in srgb, var(--ink) 88%, #fff); }
    .legacy-wrap ul,.legacy-wrap ol { padding-left: 1.2rem; margin: 0.7rem 0; }
    .legacy-wrap a { color: var(--primary); text-decoration: none; font-weight: 600; }
    .legacy-wrap a:hover { text-decoration: underline; }
    .legacy-wrap img { display: block; width: auto !important; max-width: min(100%, 760px) !important; height: auto !important; margin: 1.25rem auto; border-radius: 0.85rem; box-shadow: 0 12px 26px rgba(15, 23, 42, 0.12); }
    .legacy-wrap table { width: 100% !important; border-collapse: collapse; display: block; overflow-x: auto; border: 1px solid var(--line); border-radius: 0.75rem; }
    .legacy-wrap td, .legacy-wrap th { padding: 0.6rem 0.7rem; border: 1px solid var(--line); vertical-align: top; }
    .legacy-wrap [style*='float'] { float: none !important; margin-left: auto !important; margin-right: auto !important; }
    .legacy-wrap iframe { max-width: 100%; border: 0; border-radius: 0.75rem; }
    .legacy-wrap #contact-stripe { margin-top: 1.25rem; }
    .legacy-wrap #contact-stripe .inner-content {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 0.9rem;
      align-items: stretch;
    }
    .legacy-wrap #contact-stripe .inner-content > div {
      border: 1px solid var(--line);
      background: color-mix(in srgb, var(--primary) 8%, #fff);
      border-radius: 0.9rem;
      padding: 0.85rem 1rem;
    }
    .legacy-wrap #contact-stripe .inner-content > div > a {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      text-decoration: none;
    }
    .legacy-wrap #contact-stripe .inner-content > div img {
      width: 44px !important;
      height: 44px !important;
      margin: 0 !important;
      border-radius: 0 !important;
      box-shadow: none !important;
      flex: 0 0 auto;
    }
    .legacy-wrap #contact-stripe .inner-content > div h3 {
      margin: 0;
      color: var(--primary2);
      font-size: 1rem;
      line-height: 1.2;
    }
    .legacy-wrap #contact-stripe .inner-content > div p {
      margin: 0.2rem 0 0;
      color: var(--muted);
      font-size: 0.9rem;
    }
    .legacy-wrap #contact-stripe .clear { display: none !important; }
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
    html, body {
      top: 0 !important;
      margin-top: 0 !important;
    }
    .theme5-translator .gts-select {
      color: #0f172a !important;
      border-color: color-mix(in srgb, var(--line) 88%, #ffffff) !important;
      background-color: #ffffff !important;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none'%3E%3Cpath stroke='rgba(15,23,42,0.72)' stroke-linecap='round' stroke-linejoin='round' stroke-width='2.5' d='M6 9l6 6 6-6'/%3E%3C/svg%3E") !important;
    }
    .theme5-translator .gts-select option {
      color: #0f172a !important;
      background: #ffffff !important;
    }
    /* Dropdown nav */
    .nav-dropdown {
      visibility: hidden;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.12s ease, visibility 0s linear 0.12s;
    }
    .nav-dropdown.nav-open {
      visibility: visible;
      opacity: 1;
      pointer-events: auto;
      transition-delay: 0s;
    }
    .nav-group .nav-dropdown a:hover {
      background: color-mix(in srgb, var(--accent) 10%, #fff) !important;
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
<body class="min-h-screen font-brand">
  <div class="text-[11px] border-b" style="background:color-mix(in srgb, var(--primary2) 88%, #0f172a); border-color:color-mix(in srgb, var(--line) 35%, #0f172a); color:#e2e8f0">
    <div class="max-w-7xl mx-auto px-4 py-2 flex items-center justify-between gap-3">
      <p class="truncate">Client Advisory Desk: <?= htmlspecialchars((string)($site['phone'] ?? '')) ?></p>
      <div class="hidden sm:flex items-center gap-4">
        <a href="Contact-Us.php" class="hover:underline">Contact</a>
        <a href="Locations.php" class="hover:underline">Locations</a>
        <a href="Security-Center.php" class="hover:underline">Security</a>
      </div>
    </div>
  </div>

  <header class="sticky top-0 z-40 border-b backdrop-blur" style="background:color-mix(in srgb, var(--surface) 90%, transparent); border-color:var(--line)">
    <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between gap-4">
      <a href="index.php" class="flex items-center gap-3">
        <img src="<?= htmlspecialchars($siteLogoUrl) ?>" alt="logo" class="h-10 w-auto rounded-lg">
      </a>
      <div class="hidden lg:flex items-center gap-2 text-xs">
        <span class="px-2 py-1 rounded-full" style="background:color-mix(in srgb,var(--accent) 22%, transparent)">Fast Payments</span>
        <span class="px-2 py-1 rounded-full" style="background:color-mix(in srgb,var(--primary) 22%, transparent)">Smart Insights</span>
      </div>
      <div class="flex items-center gap-2">
        <div class="theme5-translator"><?php include_once dirname(__DIR__, 3) . '/private/shared-translator.php'; ?></div>
        <a href="<?= htmlspecialchars(($site['login'] ?? 'user')) ?>/login.php" class="px-4 py-2 rounded-xl text-sm font-semibold text-white" style="background:linear-gradient(135deg,var(--primary2),var(--primary))">Sign In</a>
      </div>
    </div>
    <nav class="hidden md:block border-t" style="border-color:var(--line)">
      <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-end gap-2 text-sm">
        <a href="index.php" class="px-3 py-1.5 rounded-lg transition-colors <?= nav_is_active('index.php', $currentPage) ? 'font-semibold' : '' ?>" style="background:<?= nav_is_active('index.php', $currentPage) ? 'color-mix(in srgb, var(--accent) 18%, #fff)' : 'transparent' ?>; color:var(--ink)">Home</a>
        <?php foreach ($navColumns as $group => $links): ?>
          <?php $groupActive = nav_group_active($links, $currentPage); ?>
          <div class="relative nav-group">
            <button class="px-3 py-1.5 rounded-lg font-semibold" style="background:<?= $groupActive ? 'color-mix(in srgb, var(--accent) 18%, #fff)' : 'transparent' ?>; color:var(--ink)">
              <?= htmlspecialchars($group) ?>
            </button>
            <div class="nav-dropdown absolute left-0 mt-1 min-w-[280px] rounded-xl border p-2 shadow-xl z-50" style="background:var(--surface); border-color:var(--line)">
              <?php foreach ($links as $link): ?>
                <a href="<?= htmlspecialchars($link[1]) ?>" class="block rounded-lg px-3 py-2 text-sm <?= nav_is_active((string)$link[1], $currentPage) ? 'font-semibold' : '' ?>" style="background:<?= nav_is_active((string)$link[1], $currentPage) ? 'color-mix(in srgb,var(--accent) 16%, #fff)' : 'transparent' ?>; color:var(--ink)"><?= htmlspecialchars($link[0]) ?></a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </nav>

    <details class="md:hidden border-t" style="border-color:var(--line)">
      <summary class="list-none cursor-pointer px-4 py-3 text-sm font-semibold" style="color:var(--ink)">Menu</summary>
      <nav class="px-4 pb-4 space-y-2">
        <a href="index.php" class="block rounded-lg px-3 py-2 text-sm font-semibold" style="color:var(--ink)">Home</a>
        <?php foreach ($navColumns as $group => $links): ?>
          <p class="text-xs font-bold uppercase tracking-[0.15em] mt-2 mb-1" style="color:var(--muted)"><?= htmlspecialchars($group) ?></p>
          <?php foreach ($links as $link): ?>
            <a class="block rounded-md px-2 py-1.5 text-sm" href="<?= htmlspecialchars($link[1]) ?>" style="color:var(--ink)"><?= htmlspecialchars($link[0]) ?></a>
          <?php endforeach; ?>
        <?php endforeach; ?>
      </nav>
    </details>
  </header>

  <?php if (($sourcePage ?? basename($_SERVER['SCRIPT_NAME'] ?? 'index.php')) !== 'index.php'): ?>
  <section class="relative overflow-hidden text-white bg-gradient-to-r <?= htmlspecialchars($tweak['hero']) ?>">
    <div class="absolute inset-0 opacity-30" style="background-image:linear-gradient(90deg, rgba(255,255,255,.1) 1px, transparent 1px), linear-gradient(rgba(255,255,255,.08) 1px, transparent 1px); background-size:28px 28px;"></div>
    <div class="relative max-w-7xl mx-auto px-4 py-12 md:py-16 grid lg:grid-cols-2 gap-8 items-center">
      <div>
        <p class="text-xs uppercase tracking-[0.2em] opacity-80">Intelligent Banking Layer</p>
        <h1 class="text-3xl md:text-5xl font-black mt-2"><?= htmlspecialchars($pageTitle ?? 'Welcome') ?></h1>
        <p class="text-sm md:text-base mt-3 max-w-2xl opacity-95">A future-ready interface built for secure transfers, instant visibility, and confident financial control.</p>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div class="rounded-xl border border-white/25 bg-white/10 backdrop-blur p-4"><p class="text-xs uppercase opacity-80">Live Support</p><p class="text-2xl font-black mt-2">24/7</p></div>
        <div class="rounded-xl border border-white/25 bg-white/10 backdrop-blur p-4"><p class="text-xs uppercase opacity-80">Performance</p><p class="text-2xl font-black mt-2">99.9%</p></div>
        <div class="rounded-xl border border-white/25 bg-white/10 backdrop-blur p-4"><p class="text-xs uppercase opacity-80">Encryption</p><p class="text-2xl font-black mt-2">256-bit</p></div>
        <div class="rounded-xl border border-white/25 bg-white/10 backdrop-blur p-4"><p class="text-xs uppercase opacity-80">Fraud Shield</p><p class="text-2xl font-black mt-2">Active</p></div>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <main class="max-w-7xl mx-auto px-4 py-8 md:py-12">
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
    group.addEventListener('focusin', function () {
      clearTimeout(timer);
      dd.classList.add('nav-open');
    });
    group.addEventListener('focusout', function () {
      timer = setTimeout(function () { dd.classList.remove('nav-open'); }, 160);
    });
  });
});
</script>
