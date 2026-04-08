<?php /* GTranslate widget — include anywhere in <body> */ ?>
<div class="gtranslate-wrap" id="gtranslate-widget">
    <style>
        .gtranslate-wrap {
            position: fixed;
            bottom: 18px;
            right: 18px;
            z-index: 9999;
        }
        .gtranslate-wrap select {
            font-size: 12px;
            padding: 5px 8px;
            border-radius: 4px;
            border: 1px solid #c9a84c;
            background: #0d1f3c;
            color: #fff;
            cursor: pointer;
            outline: none;
        }
        .gtranslate-wrap select:hover { background: #162847; }
        #google_translate_element2 { display: none !important; }
        .goog-te-banner-frame,
        .goog-te-banner-frame.skiptranslate,
        .skiptranslate,
        .skiptranslate iframe,
        iframe.skiptranslate,
        iframe.goog-te-banner-frame,
        iframe[src*="translate.google.com/translate"],
        iframe[src*="translate.googleapis.com"],
        .goog-logo-link,
        #goog-gt-tt,
        .goog-te-balloon-frame,
        .VIpgJd-ZVi9od-ORHb,
        .VIpgJd-ZVi9od-aZ2wEe-wOHMyf {
            display: none !important;
            visibility: hidden !important;
            height: 0 !important;
        }
        html,
        body {
            top: 0 !important;
            margin-top: 0 !important;
        }
    </style>
    <select onchange="doGTranslate(this);">
        <option value="">&#127760; Language</option>
        <option value="en|af">Afrikaans</option>
        <option value="en|sq">Albanian</option>
        <option value="en|ar">Arabic</option>
        <option value="en|hy">Armenian</option>
        <option value="en|az">Azerbaijani</option>
        <option value="en|eu">Basque</option>
        <option value="en|be">Belarusian</option>
        <option value="en|bg">Bulgarian</option>
        <option value="en|ca">Catalan</option>
        <option value="en|zh-CN">Chinese (Simplified)</option>
        <option value="en|zh-TW">Chinese (Traditional)</option>
        <option value="en|hr">Croatian</option>
        <option value="en|cs">Czech</option>
        <option value="en|da">Danish</option>
        <option value="en|nl">Dutch</option>
        <option value="en|en">English</option>
        <option value="en|et">Estonian</option>
        <option value="en|tl">Filipino</option>
        <option value="en|fi">Finnish</option>
        <option value="en|fr">French</option>
        <option value="en|gl">Galician</option>
        <option value="en|ka">Georgian</option>
        <option value="en|de">German</option>
        <option value="en|el">Greek</option>
        <option value="en|ht">Haitian Creole</option>
        <option value="en|iw">Hebrew</option>
        <option value="en|hi">Hindi</option>
        <option value="en|hu">Hungarian</option>
        <option value="en|is">Icelandic</option>
        <option value="en|id">Indonesian</option>
        <option value="en|ga">Irish</option>
        <option value="en|it">Italian</option>
        <option value="en|ja">Japanese</option>
        <option value="en|ko">Korean</option>
        <option value="en|lv">Latvian</option>
        <option value="en|lt">Lithuanian</option>
        <option value="en|mk">Macedonian</option>
        <option value="en|ms">Malay</option>
        <option value="en|mt">Maltese</option>
        <option value="en|no">Norwegian</option>
        <option value="en|fa">Persian</option>
        <option value="en|pl">Polish</option>
        <option value="en|pt">Portuguese</option>
        <option value="en|ro">Romanian</option>
        <option value="en|ru">Russian</option>
        <option value="en|sr">Serbian</option>
        <option value="en|sk">Slovak</option>
        <option value="en|sl">Slovenian</option>
        <option value="en|es">Spanish</option>
        <option value="en|sw">Swahili</option>
        <option value="en|sv">Swedish</option>
        <option value="en|th">Thai</option>
        <option value="en|tr">Turkish</option>
        <option value="en|uk">Ukrainian</option>
        <option value="en|ur">Urdu</option>
        <option value="en|vi">Vietnamese</option>
        <option value="en|cy">Welsh</option>
        <option value="en|yi">Yiddish</option>
    </select>
    <div id="google_translate_element2"></div>
</div>

<script type="text/javascript">
function googleTranslateElementInit2() {
    new google.translate.TranslateElement({pageLanguage: 'en', autoDisplay: false}, 'google_translate_element2');
}
</script>
<script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit2"></script>
<script type="text/javascript">
/* GTranslate helper */
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
            '.VIpgJd-ZVi9od-aZ2wEe-wOHMyf'
        ];
        for (var i = 0; i < selectors.length; i++) {
            var nodes = document.querySelectorAll(selectors[i]);
            for (var j = 0; j < nodes.length; j++) {
                nodes[j].style.setProperty('display', 'none', 'important');
                nodes[j].style.setProperty('visibility', 'hidden', 'important');
                nodes[j].style.setProperty('height', '0', 'important');
            }
        }
        document.documentElement.style.setProperty('top', '0', 'important');
        if (document.body) {
            document.body.style.setProperty('top', '0', 'important');
            document.body.style.setProperty('margin-top', '0', 'important');
        }
    }

    function GTranslateFireEvent(a, b) {
        try {
            if (document.createEvent) {
                var c = document.createEvent('HTMLEvents');
                c.initEvent(b, true, true);
                a.dispatchEvent(c);
            } else {
                var c = document.createEventObject();
                a.fireEvent('on' + b, c);
            }
        } catch(e) {}
    }
    window.doGTranslate = function(sel) {
        if (sel.value) sel = sel.value;
        if (sel === '') return;
        var pair = sel.split('|')[1];
        var el = null;
        var els = document.getElementsByTagName('select');
        for (var i = 0; i < els.length; i++) {
            if (els[i].className === 'goog-te-combo') { el = els[i]; break; }
        }
        if (!document.getElementById('google_translate_element2') ||
            !document.getElementById('google_translate_element2').innerHTML.length ||
            !el || !el.innerHTML.length) {
            setTimeout(function() { window.doGTranslate(sel); }, 500);
        } else {
            el.value = pair;
            GTranslateFireEvent(el, 'change');
            GTranslateFireEvent(el, 'change');
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        hideGoogleTranslateBanner();
        var observer = new MutationObserver(function() {
            hideGoogleTranslateBanner();
        });
        observer.observe(document.documentElement, { childList: true, subtree: true, attributes: true });
        setInterval(hideGoogleTranslateBanner, 600);
    });
})();
</script>
