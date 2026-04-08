  </main>

  <section class="max-w-7xl mx-auto px-4 pb-10">
    <div class="rounded-3xl p-6 md:p-8 border border-white/10 bg-slate-950/65 backdrop-blur <?= htmlspecialchars($tweak['card']) ?>">
      <div class="grid md:grid-cols-3 gap-7 text-slate-200">
        <div>
          <h3 class="text-xs uppercase tracking-[0.2em] mb-2 text-slate-400">Headquarters</h3>
          <p class="font-semibold text-white"><?= htmlspecialchars($site['name'] ?? 'Bank') ?></p>
          <p class="text-sm mt-2 text-slate-300"><?= $site['addr'] ?? '' ?></p>
        </div>
        <div>
          <h3 class="text-xs uppercase tracking-[0.2em] mb-2 text-slate-400">Executive Services</h3>
          <ul class="space-y-1 text-sm">
            <li><a href="Business-Online-Banking.php">Treasury Platform</a></li>
            <li><a href="Payment-and-Receivables.php">Cash Movement</a></li>
            <li><a href="Security-Center.php">Security Center</a></li>
          </ul>
        </div>
        <div>
          <h3 class="text-xs uppercase tracking-[0.2em] mb-2 text-slate-400">Client Line</h3>
          <p class="text-sm text-slate-300"><?= htmlspecialchars($site['phone'] ?? '') ?></p>
          <p class="text-sm text-slate-300 mt-1"><?= htmlspecialchars($site['email'] ?? '') ?></p>
          <a href="Contact-Us.php" class="inline-flex mt-3 px-4 py-2 rounded-lg text-sm font-semibold text-slate-950" style="background:var(--accent)">Reach Advisor</a>
        </div>
      </div>
    </div>
  </section>

  <footer class="border-t border-white/10 bg-slate-950/70">
    <div class="max-w-7xl mx-auto px-4 py-6 text-xs md:text-sm flex flex-col md:flex-row gap-2 md:items-center md:justify-between text-slate-400">
      <?php
        $siteYearLine = trim((string)($site['year'] ?? ''));
        if ($siteYearLine === '') {
          $siteYearLine = 'Copyright ' . html_entity_decode('&copy;', ENT_QUOTES, 'UTF-8') . ' ' . date('Y');
        }
        $siteYearLine = (string)preg_replace('/\b(19|20)\d{2}\b/', date('Y'), $siteYearLine);
        $siteYearLine = html_entity_decode($siteYearLine, ENT_QUOTES | ENT_HTML5, 'UTF-8');
      ?>
      <p><?= htmlspecialchars($siteYearLine) ?></p>
      <div class="flex gap-4">
        <a href="Privacy.php">Privacy</a>
        <a href="Security-Center.php">Security</a>
        <a href="Site-Map.php">Site Map</a>
      </div>
    </div>
  </footer>

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
