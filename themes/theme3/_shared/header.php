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
          fontFamily: { brand: ['<?= addslashes($themeMeta['font_family'] ?? 'Sora') ?>', 'ui-sans-serif', 'system-ui'] }
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
    body { font-family: <?= htmlspecialchars($themeMeta['font_family']) ?>, ui-sans-serif, system-ui; background: linear-gradient(180deg,#020617 0%,color-mix(in srgb,var(--primary2) 45%,#020617) 42%, var(--bg) 100%); color: #e5e7eb; }
    .legacy-wrap { line-height: 1.7; font-size: 0.98rem; color: #334155; }
    .legacy-wrap > * + * { margin-top: 1rem; }
    .legacy-wrap h1,.legacy-wrap h2,.legacy-wrap h3,.legacy-wrap h4 { color: #0f172a; font-weight: 800; line-height: 1.25; margin-top: 1.4rem; margin-bottom: 0.7rem; }
    .legacy-wrap p,.legacy-wrap li { color: #334155; }
    .legacy-wrap ul,.legacy-wrap ol { padding-left: 1.2rem; margin: 0.7rem 0; }
    .legacy-wrap a { color: #0b4a7d; text-decoration: none; font-weight: 600; }
    .legacy-wrap a:hover { text-decoration: underline; }
    .legacy-wrap img { display: block; width: auto !important; max-width: min(100%, 760px) !important; height: auto !important; margin: 1.25rem auto; border-radius: 0.85rem; box-shadow: 0 12px 24px rgba(15, 23, 42, 0.12); }
    .legacy-wrap table { width: 100% !important; border-collapse: collapse; display: block; overflow-x: auto; border: 1px solid #dbe3ef; border-radius: 0.75rem; }
    .legacy-wrap td, .legacy-wrap th { padding: 0.6rem 0.7rem; border: 1px solid #dbe3ef; vertical-align: top; }
    .legacy-wrap [style*='float'] { float: none !important; margin-left: auto !important; margin-right: auto !important; }
    .legacy-wrap iframe { max-width: 100%; border: 0; border-radius: 0.75rem; }

    /* Theme3 interior utility strip: Join our newsletter / Let us help */
    .legacy-wrap #contact-stripe { margin-top: 1.5rem; }
    .legacy-wrap #contact-stripe .inner-content {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 0.9rem;
      align-items: stretch;
    }
    .legacy-wrap #contact-stripe .inner-content > div {
      border: 1px solid #d8e3f2;
      background: #f8fbff;
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
      color: #0f172a;
      font-size: 1rem;
      line-height: 1.2;
    }
    .legacy-wrap #contact-stripe .inner-content > div p {
      margin: 0.2rem 0 0;
      color: #334155;
      font-size: 0.9rem;
    }
    .legacy-wrap #contact-stripe .clear { display: none !important; }
    @media (max-width: 760px) {
      .legacy-wrap #contact-stripe .inner-content { grid-template-columns: 1fr; }
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
    html, body {
      top: 0 !important;
      margin-top: 0 !important;
    }
    /* Dropdown nav */
    .nav-group .nav-dropdown {
      visibility: hidden;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.12s ease, visibility 0s linear 0.12s;
    }
    .nav-group .nav-dropdown.nav-open {
      visibility: visible;
      opacity: 1;
      pointer-events: auto;
      transition-delay: 0s;
    }
    .nav-group .nav-dropdown a:hover {
      background: rgba(255,255,255,0.1) !important;
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
  <?php
    $primaryNav = [
      ['Home', 'index.php'],
      ['Checking', 'Checking.php'],
      ['Savings', 'Savings.php'],
      ['Business', 'Business-Checking.php'],
      ['Digital', 'Online-Banking.php'],
      ['Security', 'Security-Center.php'],
      ['Contact', 'Contact-Us.php'],
    ];
  ?>

  <div class="text-[11px] border-b border-white/10 bg-black/35 text-slate-300">
    <div class="max-w-7xl mx-auto px-4 py-2 flex items-center justify-between gap-3">
      <p class="truncate">Private Client Desk</p>
      <p class="shrink-0">Secure Channel</p>
    </div>
  </div>

  <header class="sticky top-0 z-40 backdrop-blur border-b border-white/10 bg-slate-950/80">
    <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between gap-4">
      <a href="index.php" class="flex items-center gap-3">
        <img src="<?= htmlspecialchars($siteLogoUrl) ?>" alt="logo" class="h-11 w-auto rounded-md">
      </a>

      <div class="hidden md:flex items-center gap-3">
        <?php include_once dirname(__DIR__, 3) . '/private/shared-translator.php'; ?>
        <a href="Contact-Us.php" class="px-4 py-2 rounded-lg text-sm border border-white/20 text-slate-200 hover:bg-white/5 transition-colors">Advisory Desk</a>
        <a href="<?= htmlspecialchars(($site['login'] ?? 'user')) ?>/login.php" class="px-4 py-2 rounded-lg text-sm font-semibold text-slate-950" style="background:var(--accent)">Client Sign-In</a>
      </div>

      <details class="md:hidden relative">
        <summary class="list-none cursor-pointer px-3 py-2 rounded-lg text-sm font-semibold border border-white/20 text-slate-100">Menu</summary>
        <nav class="absolute right-0 mt-2 w-80 max-w-[88vw] rounded-xl border border-white/15 p-4 bg-slate-950 text-slate-100 shadow-2xl">
          <?php foreach ($primaryNav as $link): ?>
            <a class="block rounded-lg px-3 py-2 text-sm <?= nav_is_active((string)$link[1], $currentPage) ? 'font-semibold text-white bg-white/10' : 'text-slate-200 hover:bg-white/5' ?>" href="<?= htmlspecialchars($link[1]) ?>"><?= htmlspecialchars($link[0]) ?></a>
          <?php endforeach; ?>
          <hr class="my-3 border-white/10">
          <?php foreach ($navColumns as $group => $links): ?>
            <p class="text-[11px] font-bold uppercase tracking-[0.16em] mt-2 mb-1 text-slate-400"><?= htmlspecialchars($group) ?></p>
            <?php foreach ($links as $link): ?>
              <a class="block rounded-md px-2 py-1.5 text-sm text-slate-300 hover:bg-white/5" href="<?= htmlspecialchars($link[1]) ?>"><?= htmlspecialchars($link[0]) ?></a>
            <?php endforeach; ?>
          <?php endforeach; ?>
        </nav>
      </details>
    </div>

    <nav class="hidden md:block border-t border-white/10">
      <div class="max-w-7xl mx-auto px-4 py-3 flex items-center gap-2 text-sm text-slate-200">
        <a href="index.php" class="px-3 py-1.5 rounded-lg transition-colors <?= nav_is_active('index.php', $currentPage) ? 'bg-white/10 text-white font-semibold' : 'text-slate-300 hover:text-white hover:bg-white/5' ?>">Home</a>
        <?php foreach ($navColumns as $group => $links): ?>
          <?php $groupActive = nav_group_active($links, $currentPage); ?>
          <div class="relative nav-group">
            <button class="px-3 py-1.5 rounded-lg transition-colors <?= $groupActive ? 'bg-white/10 text-white font-semibold' : 'text-slate-300 hover:text-white hover:bg-white/5' ?>">
              <?= htmlspecialchars($group) ?>
            </button>
            <div class="nav-dropdown absolute left-0 mt-1 min-w-[280px] rounded-xl border border-white/15 p-2 shadow-xl z-50 bg-slate-950/95">
              <?php foreach ($links as $link): ?>
                <a class="block rounded-lg px-3 py-2 text-sm <?= nav_is_active((string)$link[1], $currentPage) ? 'font-semibold text-white bg-white/10' : 'text-slate-200 hover:bg-white/5' ?>" href="<?= htmlspecialchars($link[1]) ?>"><?= htmlspecialchars($link[0]) ?></a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </nav>
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
  <main class="max-w-7xl mx-auto px-4 py-10 md:py-12">
