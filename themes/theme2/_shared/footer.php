  <section class="max-w-7xl mx-auto px-4 pb-12">
    <div class="rounded-[2rem] px-6 py-7 md:px-8 md:py-9 text-white shadow-editorial" style="background:linear-gradient(135deg, #0c2037 0%, var(--primary2) 42%, var(--primary) 100%)">
      <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center">
        <div>
          <p class="text-[11px] uppercase tracking-[0.22em] text-white/65">Need Assistance?</p>
          <h2 class="mt-3 font-display text-3xl leading-tight">Speak with our team or visit a nearby branch.</h2>
          <p class="mt-3 max-w-2xl text-sm md:text-base leading-7 text-white/78">Reach support, review service information, or connect with a banker through the channels below.</p>
        </div>
        <div class="flex flex-wrap gap-3">
          <a href="<?= htmlspecialchars(theme_page_url('Contact-Us.php')) ?>" class="inline-flex rounded-full bg-white px-5 py-3 text-sm font-semibold" style="color:var(--primary2)">Contact Us</a>
          <a href="<?= htmlspecialchars(theme_page_url('Locations.php')) ?>" class="inline-flex rounded-full border border-white/20 px-5 py-3 text-sm font-semibold text-white">Find a Branch</a>
        </div>
      </div>
    </div>
  </section>

  <footer class="border-t" style="border-color:var(--line); background:color-mix(in srgb, var(--surface) 94%, white)">
    <div class="max-w-7xl mx-auto px-4 py-10 grid gap-8 md:grid-cols-2 xl:grid-cols-4">
      <section>
        <p class="section-kicker">Banking</p>
        <div class="mt-3 space-y-2 text-sm">
          <a href="<?= htmlspecialchars(theme_page_url('Checking.php')) ?>" class="block">Checking</a>
          <a href="<?= htmlspecialchars(theme_page_url('Savings.php')) ?>" class="block">Savings</a>
          <a href="<?= htmlspecialchars(theme_page_url('Business-Checking.php')) ?>" class="block">Business Checking</a>
          <a href="<?= htmlspecialchars(theme_page_url('Online-Banking.php')) ?>" class="block">Online Banking</a>
        </div>
      </section>

      <section>
        <p class="section-kicker">Resources</p>
        <div class="mt-3 space-y-2 text-sm">
          <a href="<?= htmlspecialchars(theme_page_url('Security-Center.php')) ?>" class="block">Security Center</a>
          <a href="<?= htmlspecialchars(theme_page_url('News-and-Press-Releases.php')) ?>" class="block">News Room</a>
          <a href="<?= htmlspecialchars(theme_page_url('Financial-Results.php')) ?>" class="block">Financial Results</a>
          <a href="<?= htmlspecialchars(theme_page_url('Privacy.php')) ?>" class="block">Privacy</a>
        </div>
      </section>

      <section>
        <p class="section-kicker">Client Access</p>
        <div class="mt-4 grid gap-3">
          <?php foreach ($clientActions as $action): ?>
            <a href="<?= htmlspecialchars((string)$action['href']) ?>" class="inline-flex items-center justify-center rounded-full px-4 py-3 text-sm font-semibold" style="background:<?= $action['variant'] === 'primary' ? 'linear-gradient(135deg, var(--primary), var(--primary2))' : 'transparent' ?>; color:<?= $action['variant'] === 'primary' ? '#ffffff' : 'var(--primary2)' ?>; border:1px solid <?= $action['variant'] === 'primary' ? 'transparent' : 'color-mix(in srgb, var(--line) 92%, white)' ?>;">
              <?= htmlspecialchars((string)$action['label']) ?>
            </a>
          <?php endforeach; ?>
        </div>
      </section>

      <section>
        <p class="section-kicker">Contact</p>
        <div class="mt-3 space-y-2 text-sm leading-7" style="color:var(--muted)">
          <?php if (!empty($site['phone'])): ?><p><?= htmlspecialchars((string)$site['phone']) ?></p><?php endif; ?>
          <?php if (!empty($site['email'])): ?><p><?= htmlspecialchars((string)$site['email']) ?></p><?php endif; ?>
          <p><a href="<?= htmlspecialchars(theme_page_url('Contact-Us.php')) ?>">Send us a message</a></p>
        </div>
      </section>
    </div>

    <div class="border-t" style="border-color:var(--line)">
      <div class="max-w-7xl mx-auto px-4 py-5 text-xs md:text-sm flex flex-col md:flex-row gap-3 md:items-center md:justify-between" style="color:var(--muted)">
        <?php
          $siteYearLine = trim((string)($site['year'] ?? ''));
          if ($siteYearLine === '') {
            $siteYearLine = 'Copyright ' . html_entity_decode('&copy;', ENT_QUOTES, 'UTF-8') . ' ' . date('Y');
          }
          $siteYearLine = (string)preg_replace('/\b(19|20)\d{2}\b/', date('Y'), $siteYearLine);
          $siteYearLine = html_entity_decode($siteYearLine, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        ?>
        <p><?= htmlspecialchars($siteYearLine) ?></p>
        <div class="flex flex-wrap gap-4">
          <a href="<?= htmlspecialchars(theme_page_url('Privacy.php')) ?>">Privacy</a>
          <a href="<?= htmlspecialchars(theme_page_url('Security-Center.php')) ?>">Security</a>
          <a href="<?= htmlspecialchars(theme_page_url('Contact-Us.php')) ?>">Contact</a>
        </div>
      </div>
    </div>
  </footer>

  <?php if ($isHomePage): ?>
    <script>
      (() => {
        const slider = document.querySelector('[data-home-slider]');
        if (!slider) return;
        const slides = Array.from(slider.querySelectorAll('[data-home-slide]'));
        const dots = Array.from(slider.querySelectorAll('[data-home-slider-dot]'));
        if (!slides.length) return;

        let activeIndex = 0;
        const showSlide = (index) => {
          activeIndex = (index + slides.length) % slides.length;
          slides.forEach((slide, slideIndex) => {
            slide.classList.toggle('is-active', slideIndex === activeIndex);
          });
          dots.forEach((dot, dotIndex) => {
            dot.classList.toggle('is-active', dotIndex === activeIndex);
          });
        };

        dots.forEach((dot) => {
          dot.addEventListener('click', () => {
            const index = Number(dot.getAttribute('data-slide-index') || '0');
            showSlide(index);
          });
        });

        showSlide(0);
        window.setInterval(() => showSlide(activeIndex + 1), 5200);
      })();
    </script>
  <?php endif; ?>

  <?= $site['tawk'] ?? '' ?>

<?php
  $_promoEnabled   = ($settingMap['promo_enabled']        ?? '0') === '1';
  $_promoPopup     = ($settingMap['promo_popup_enabled']   ?? '0') === '1';
  $_promoCondition = $settingMap['promo_popup_condition']  ?? 'once_session';
  $_promoHeadline  = $settingMap['promo_headline']         ?? '';
  $_promoBody      = $settingMap['promo_body']             ?? '';
  $_promoBtnLabel  = $settingMap['promo_btn_label']        ?? '';
  $_promoBtnUrl    = $settingMap['promo_btn_url']          ?? '';
  $_promoImg       = $settingMap['promo_image_url']        ?? '';
  $_isGuest        = empty($_SESSION['acc_no']);
  $_showPopup      = $_promoEnabled && $_promoPopup && ($_promoCondition !== 'guest_only' || $_isGuest);
?>
<?php if ($_showPopup && ($_promoHeadline !== '' || $_promoImg !== '')): ?>
<div id="promo-popup-overlay" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/60" style="display:none">
  <div id="promo-popup-card" class="relative w-full max-w-md rounded-2xl bg-white shadow-2xl overflow-hidden animate-fade-in">
    <button id="promo-popup-close" type="button" aria-label="Close"
      class="absolute top-3 right-3 z-10 flex h-8 w-8 items-center justify-center rounded-full bg-white/90 text-gray-600 shadow hover:bg-gray-100 transition-colors">
      <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
    <?php if ($_promoImg !== ''): ?>
    <div class="w-full bg-gray-100 overflow-hidden" style="aspect-ratio:1/1">
      <img src="<?= htmlspecialchars($_promoImg) ?>" alt="" class="h-full w-full object-cover">
    </div>
    <?php endif; ?>
    <div class="p-6">
      <?php if ($_promoHeadline !== ''): ?>
      <h2 class="text-xl font-bold text-gray-900 leading-snug"><?= htmlspecialchars($_promoHeadline) ?></h2>
      <?php endif; ?>
      <?php if ($_promoBody !== ''): ?>
      <p class="mt-2 text-sm text-gray-600 leading-relaxed"><?= nl2br(htmlspecialchars($_promoBody)) ?></p>
      <?php endif; ?>
      <?php if ($_promoBtnLabel !== '' && $_promoBtnUrl !== ''): ?>
      <a href="<?= htmlspecialchars($_promoBtnUrl) ?>" target="_blank" rel="noopener noreferrer"
         class="mt-5 inline-flex w-full items-center justify-center rounded-xl px-5 py-3 text-sm font-semibold text-white shadow transition-opacity hover:opacity-90" style="background:linear-gradient(135deg,var(--primary),var(--primary2))">
        <?= htmlspecialchars($_promoBtnLabel) ?>
      </a>
      <?php endif; ?>
    </div>
  </div>
</div>
<script>
(function(){
  var CONDITION = <?= json_encode($_promoCondition) ?>;
  var KEY = 'promo_popup_v1';
  function shouldShow(){
    if(CONDITION==='always') return true;
    if(CONDITION==='once_session'){
      if(sessionStorage.getItem(KEY)) return false;
      sessionStorage.setItem(KEY,'1');
      return true;
    }
    if(CONDITION==='once_day'||CONDITION==='guest_only'){
      var stored=localStorage.getItem(KEY);
      var now=Date.now();
      if(stored && (now-parseInt(stored,10))<86400000) return false;
      localStorage.setItem(KEY,String(now));
      return true;
    }
    return true;
  }
  var overlay=document.getElementById('promo-popup-overlay');
  if(overlay && shouldShow()){
    overlay.style.display='flex';
    document.getElementById('promo-popup-close').addEventListener('click',function(){
      overlay.style.display='none';
    });
    overlay.addEventListener('click',function(e){ if(e.target===overlay) { overlay.style.display='none'; } });
  }
})();
</script>
<?php endif; ?>
</body>
</html>
