<?php
/* ── Shared Google Translate widget ─────────────────────────────────────────
 * Reads the active language list from DB on every request.
 * ─────────────────────────────────────────────────────────────────────────── */
$_gtsDefault = 'en,es,fr,de,it,pt,ru,zh-CN';
$_gtsLangs   = '';

// Helper: read translator_languages from a mysqli connection
function _gts_read_langs(mysqli $c): string {
    try {
        $r = $c->query("SELECT `value` FROM site_settings WHERE `key` = 'translator_languages' LIMIT 1");
        if ($r && $r->num_rows > 0) {
            $v = (string)($r->fetch_row()[0] ?? '');
            if (trim($v) !== '') return $v;
        }
    } catch (Throwable $e) {}
    return '';
}

// 1) Theme context: $settingMap already has it (fastest, no extra query)
if (!empty($settingMap['translator_languages'])) {
    $_gtsLangs = (string)$settingMap['translator_languages'];
// 2) Theme context: $conn is mysqli — do a fresh read
} elseif (isset($conn) && $conn instanceof mysqli) {
    $_gtsLangs = _gts_read_langs($conn);
// 3) User-panel context: $connection (procedural mysqli)
} elseif (isset($connection) && $connection instanceof mysqli) {
    $_gtsLangs = _gts_read_langs($connection);
}

// If still empty, try bootstrapping our own DB connection as last resort
if ($_gtsLangs === '') {
    try {
        $_gtsRootCfg = dirname(__DIR__) . '/config.php';
        if (is_file($_gtsRootCfg) && !isset($conn) && !isset($connection)) {
            include_once $_gtsRootCfg;
        }
        if (isset($conn) && $conn instanceof mysqli) {
            $_gtsLangs = _gts_read_langs($conn);
        }
    } catch (Throwable $e) {}
}

if ($_gtsLangs === '') {
    $_gtsLangs = $_gtsDefault;
}

$_gtsNameMap = [
    'af'=>'Afrikaans','ar'=>'العربية','az'=>'Azərbaycan','be'=>'Беларуская',
    'bg'=>'Български','ca'=>'Català','cs'=>'Čeština','cy'=>'Cymraeg',
    'da'=>'Dansk','de'=>'Deutsch','el'=>'Ελληνικά','en'=>'English',
    'es'=>'Español','et'=>'Eesti','eu'=>'Euskara','fa'=>'فارسی',
    'fi'=>'Suomi','fr'=>'Français','ga'=>'Gaeilge','gl'=>'Galego',
    'ht'=>'Kreyòl','hi'=>'हिन्दी','hr'=>'Hrvatski','hu'=>'Magyar',
    'hy'=>'Հայերեն','id'=>'Indonesia','is'=>'Íslenska','it'=>'Italiano',
    'iw'=>'עברית','ja'=>'日本語','jv'=>'Jawa','ka'=>'ქართული',
    'ko'=>'한국어','lt'=>'Lietuvių','lv'=>'Latviešu','mk'=>'Македонски',
    'ms'=>'Melayu','mt'=>'Malti','nl'=>'Nederlands','no'=>'Norsk',
    'pa'=>'ਪੰਜਾਬੀ','pl'=>'Polski','pt'=>'Português','ro'=>'Română',
    'ru'=>'Русский','sk'=>'Slovenčina','sl'=>'Slovenščina','sq'=>'Shqip',
    'sr'=>'Srpski','sv'=>'Svenska','sw'=>'Kiswahili','th'=>'ภาษาไทย',
    'tl'=>'Filipino','tr'=>'Türkçe','uk'=>'Українська','ur'=>'اردو',
    'vi'=>'Tiếng Việt','yi'=>'ייִדיש','zh-CN'=>'中文(简体)','zh-TW'=>'中文(繁體)',
];
$_gtsCodes = array_filter(array_map('trim', explode(',', $_gtsLangs)));
?>
<style>
/* Pre-hide Google injected widget UI to prevent caret/arrow flash on slow loads */
#google_translate_element,
#google_translate_element * {
  display: none !important;
  visibility: hidden !important;
}
.goog-te-gadget,
.goog-te-gadget-simple,
.goog-te-menu-value,
.goog-te-combo,
.VIpgJd-ZVi9od-ORHb,
.VIpgJd-ZVi9od-aZ2wEe-wOHMyf,
body > .skiptranslate,
iframe.goog-te-banner-frame,
iframe.skiptranslate {
  display: none !important;
  visibility: hidden !important;
  height: 0 !important;
  min-height: 0 !important;
}
</style>
<div class="gts-wrap">
  <select id="gts-select" class="gts-select" onchange="gtsSwitch(this.value)" aria-label="Select language" style="appearance:none!important;-webkit-appearance:none!important;-moz-appearance:none!important;overflow:hidden!important;background-image:url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2212%22 height=%2212%22 viewBox=%220 0 24 24%22 fill=%22none%22%3E%3Cpath stroke=%22rgba(255,255,255,0.75)%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222.5%22 d=%22M6 9l6 6 6-6%22/%3E%3C/svg%3E')!important;background-repeat:no-repeat!important;background-position:right 9px center!important;padding-right:28px!important;">
    <option value="">&#127760; Language</option>
    <?php foreach ($_gtsCodes as $_gtsCode): ?>
      <option value="en|<?= htmlspecialchars($_gtsCode) ?>"><?= htmlspecialchars($_gtsNameMap[$_gtsCode] ?? strtoupper($_gtsCode)) ?></option>
    <?php endforeach; ?>
  </select>
</div>

<!-- Hidden Google Translate init target — must not render any visible widget -->
<div id="google_translate_element" style="display:none!important;visibility:hidden;position:absolute;width:0;height:0;overflow:hidden;pointer-events:none;"></div>

<script>
function googleTranslateElementInit(){
  new google.translate.TranslateElement({pageLanguage:'en',autoDisplay:false},'google_translate_element');
  gtsSuppressInjectedUi();
  var saved = gtsReadPreferredLang();
  if (saved && saved !== 'en') {
    gtsApplyLanguage(saved);
  }
}
function gtsSwitch(val){
  if(!val) return;
  var lang = (val.indexOf('|')>-1) ? val.split('|')[1] : val;
  if(!lang) return;
  if (lang === 'en') {
    gtsClearLanguage();
    window.location.reload();
    return;
  }

  gtsSetLanguage(lang);

  gtsApplyLanguage(lang, function(){
    window.location.reload();
  });
}

function gtsCookiePaths(){
  var paths = ['/'];
  var segs = window.location.pathname.split('/').filter(Boolean);
  var current = '';
  for (var i = 0; i < segs.length; i++) {
    current += '/' + segs[i];
    paths.push(current);
  }
  return paths;
}

function gtsSetCookie(path, cp){
  document.cookie = 'googtrans=' + path + ';path=' + cp + ';SameSite=Lax';
  try { document.cookie = 'googtrans=' + path + ';path=' + cp + ';domain=' + window.location.hostname + ';SameSite=Lax'; } catch(e){}
  try { document.cookie = 'googtrans=' + path + ';path=' + cp + ';domain=.' + window.location.hostname + ';SameSite=Lax'; } catch(e){}
}

function gtsSetLanguage(lang){
  var path = '/en/' + lang;
  var cookiePaths = gtsCookiePaths();
  for (var i = 0; i < cookiePaths.length; i++) {
    gtsSetCookie(path, cookiePaths[i]);
  }
  try { localStorage.setItem('preferred_lang', lang); } catch(e){}
  try { sessionStorage.setItem('preferred_lang', lang); } catch(e){}
}

function gtsClearLanguage(){
  var cookiePaths = gtsCookiePaths();
  for (var i = 0; i < cookiePaths.length; i++) {
    var cp = cookiePaths[i];
    document.cookie = 'googtrans=;path=' + cp + ';expires=Thu, 01 Jan 1970 00:00:00 GMT;SameSite=Lax';
    try { document.cookie = 'googtrans=;path=' + cp + ';domain=' + window.location.hostname + ';expires=Thu, 01 Jan 1970 00:00:00 GMT;SameSite=Lax'; } catch(e){}
    try { document.cookie = 'googtrans=;path=' + cp + ';domain=.' + window.location.hostname + ';expires=Thu, 01 Jan 1970 00:00:00 GMT;SameSite=Lax'; } catch(e){}
  }
  try { localStorage.removeItem('preferred_lang'); } catch(e){}
  try { sessionStorage.removeItem('preferred_lang'); } catch(e){}
}

function gtsReadPreferredLang(){
  var m = document.cookie.match(/(?:^|;)\s*googtrans=\/[^\/]+\/([^;]+)/);
  if (m && m[1]) return decodeURIComponent(m[1]);
  try {
    var ls = localStorage.getItem('preferred_lang') || '';
    if (ls) return ls;
  } catch(e){}
  try {
    var ss = sessionStorage.getItem('preferred_lang') || '';
    if (ss) return ss;
  } catch(e){}
  return '';
}

function gtsApplyLanguage(lang, onFail){
  var attempts = 0;
  var maxAttempts = 18;

  function applyNow(){
    var combo = document.querySelector('select.goog-te-combo');
    if (combo && combo.options && combo.options.length > 0) {
      combo.value = lang;
      try {
        var ev = document.createEvent('HTMLEvents');
        ev.initEvent('change', true, true);
        combo.dispatchEvent(ev);
        combo.dispatchEvent(ev);
      } catch(e){}
      return;
    }

    attempts += 1;
    if (attempts < maxAttempts) {
      setTimeout(applyNow, 120);
    } else if (typeof onFail === 'function') {
      onFail();
    }
  }

  applyNow();
}

(function(){
  function gtsSuppressInjectedUi(){
    var selectors = [
      '#google_translate_element',
      '#google_translate_element *',
      '.goog-te-gadget',
      '.goog-te-gadget *',
      '.goog-te-gadget-simple',
      '.goog-te-gadget-simple *',
      '.goog-te-menu-value',
      '.goog-te-combo',
      '.VIpgJd-ZVi9od-ORHb',
      '.VIpgJd-ZVi9od-aZ2wEe-wOHMyf',
      'body > .skiptranslate',
      'iframe.goog-te-banner-frame',
      'iframe.skiptranslate'
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
  }

  // Sync selector to the active translation cookie on load
  function gtsSync(){
    var lang = gtsReadPreferredLang();
    if(!lang) return;
    var sel = document.getElementById('gts-select');
    if(sel) sel.value = 'en|' + lang;
  }
  // Hide the Google banner frame that appears when a translation is active
  function gtsSuppressBanner(){
    var targets = [
      '.goog-te-banner-frame','iframe.skiptranslate',
      'body > .skiptranslate','.VIpgJd-ZVi9od-ORHb',
      '.VIpgJd-ZVi9od-aZ2wEe-wOHMyf'
    ];
    for(var i=0;i<targets.length;i++){
      var nodes = document.querySelectorAll(targets[i]);
      for(var j=0;j<nodes.length;j++){
        nodes[j].style.setProperty('display','none','important');
        nodes[j].style.setProperty('height','0','important');
        nodes[j].style.setProperty('min-height','0','important');
      }
    }
    document.documentElement.style.setProperty('top','0','important');
    if(document.body){
      document.body.style.setProperty('top','0','important');
      document.body.style.setProperty('margin-top','0','important');
    }
    gtsSuppressInjectedUi();
  }
  if(document.readyState==='loading'){
    document.addEventListener('DOMContentLoaded',function(){ gtsSync(); gtsSuppressBanner(); gtsSuppressInjectedUi(); });
  } else {
    gtsSync(); gtsSuppressBanner(); gtsSuppressInjectedUi();
  }
  new MutationObserver(function(){ gtsSuppressBanner(); gtsSuppressInjectedUi(); }).observe(document.documentElement,{childList:true,subtree:true,attributes:true});
  setInterval(function(){ gtsSuppressBanner(); gtsSuppressInjectedUi(); },250);
}());
</script>
<script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

<style>
/* ── Custom language selector ─────────────────────────── */
.gts-wrap { display:inline-flex; align-items:center; vertical-align:middle; }
.gts-select {
  appearance: none !important;
  -webkit-appearance: none !important;
  -moz-appearance: none !important;
  background-color: rgba(255,255,255,0.10);
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none'%3E%3Cpath stroke='rgba(255,255,255,0.75)' stroke-linecap='round' stroke-linejoin='round' stroke-width='2.5' d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 9px center;
  background-size: 12px;
  border: 1px solid rgba(255,255,255,0.28);
  color: #ffffff;
  border-radius: 999px;
  padding: 5px 28px 5px 12px;
  font-size: 11.5px;
  font-weight: 600;
  line-height: 1.4;
  cursor: pointer;
  outline: none !important;
  min-width: 112px;
  letter-spacing: 0.03em;
  transition: background-color 150ms, border-color 150ms;
  overflow: hidden !important;
  text-indent: 0 !important;
  text-overflow: clip !important;
}
/* Hide all native select arrow variants comprehensively */
.gts-select::-ms-expand { display: none !important; width: 0 !important; }
.gts-select::-webkit-outer-spin-button,
.gts-select::-webkit-inner-spin-button,
.gts-select::-webkit-search-decoration,
.gts-select::-webkit-scrollbar { display: none !important; -webkit-appearance: none !important; margin: 0 !important; padding: 0 !important; }
.gts-select::before,
.gts-select::after { display: none !important; visibility: hidden !important; width: 0 !important; height: 0 !important; content: '' !important; }
.gts-select option { background: #1e293b; color: #ffffff; }
/* Suppress any injected Google select overlap and duplication */
.goog-te-combo,
.goog-te-combo::-ms-expand,
.goog-te-combo::-webkit-outer-spin-button,
select.goog-te-combo,
select.goog-te-combo * { display: none !important; visibility: hidden !important; width: 0 !important; height: 0 !important; margin: 0 !important; padding: 0 !important; pointer-events: none !important; }
.gts-select:hover {
  background-color: rgba(255,255,255,0.18);
  border-color: rgba(255,255,255,0.50);
}
.gts-select option {
  background: #1e293b;
  color: #f1f5f9;
}
/* ── Banner / body-shift suppression ─────────────────── */
.goog-te-banner-frame,
iframe.skiptranslate,
body > .skiptranslate,
.VIpgJd-ZVi9od-ORHb,
.VIpgJd-ZVi9od-aZ2wEe-wOHMyf {
  display: none !important;
  height: 0 !important;
  min-height: 0 !important;
}
html, body { top: 0 !important; margin-top: 0 !important; }
/* Hide the injected Google widget shell (we use our own select) */
#google_translate_element .goog-te-gadget,
#google_translate_element .goog-te-gadget-simple { display:none!important; }
</style>
