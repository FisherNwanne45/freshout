<?php
error_reporting(0);

if (!function_exists('user_translate_banner_fix_markup')) {
    function user_translate_banner_fix_markup(): string
    {
        return <<<'HTML'
<style id="gt-banner-global-fix">
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
<script>
(function () {
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

    document.addEventListener('DOMContentLoaded', function () {
        hideGoogleTranslateBanner();
        var observer = new MutationObserver(function () {
            hideGoogleTranslateBanner();
        });
        observer.observe(document.documentElement, { childList: true, subtree: true, attributes: true });
        setInterval(hideGoogleTranslateBanner, 500);
    });
})();
</script>
HTML;
    }
}

if (!function_exists('user_translate_banner_fix_buffer')) {
    function user_translate_banner_fix_buffer(string $buffer): string
    {
        if (stripos($buffer, '<html') === false) {
            return $buffer;
        }

        if (stripos($buffer, 'gt-banner-global-fix') !== false) {
            return $buffer;
        }

        $inject = user_translate_banner_fix_markup();

        if (stripos($buffer, '</head>') !== false) {
            return preg_replace('/<\/head>/i', $inject . "\n</head>", $buffer, 1) ?? $buffer;
        }

        if (stripos($buffer, '<body') !== false) {
            return preg_replace('/<body\b/i', $inject . "\n<body", $buffer, 1) ?? $buffer;
        }

        return $inject . "\n" . $buffer;
    }
}

if (!defined('USER_TRANSLATE_FIX_OB')) {
    define('USER_TRANSLATE_FIX_OB', true);
    ob_start('user_translate_banner_fix_buffer');
}

if (!function_exists('user_auth_is_verified')) {
    function user_auth_is_verified(): bool
    {
        return isset($_SESSION['pin_verified']) || isset($_SESSION['pin']);
    }
}

// Phased compatibility layer for pin -> pin session key migration.
if (isset($_SESSION['pin']) && !isset($_SESSION['pin_verified'])) {
    $_SESSION['pin_verified'] = $_SESSION['pin'];
}
if (isset($_SESSION['pin_verified']) && !isset($_SESSION['pin'])) {
    $_SESSION['pin'] = $_SESSION['pin_verified'];
}

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1200)) {
    // last request was more than 30 minutes ago
    session_unset();
    session_destroy();
    header('Location: login.php');
    echo "Timeout";
}
$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
