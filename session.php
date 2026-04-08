<?php
include_once __DIR__ . '/config.php';

// Reuse shared mysqli connection from config.php.
$connection = $conn;

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

// Global Google Translate banner suppression via output buffer (for theme1 pages)
if (!defined('TRANSLATE_BANNER_FIX_ACTIVE')) {
	define('TRANSLATE_BANNER_FIX_ACTIVE', 1);
	ob_start(function(string $buffer): string {
		if (stripos($buffer, '<html') === false) return $buffer;
		if (stripos($buffer, 'gt-banner-suppress') !== false) return $buffer;
		
		$css = <<<'CSS'
<style id="gt-banner-suppress">
.goog-te-banner-frame, .goog-te-banner-frame.skiptranslate, iframe.skiptranslate, 
iframe.goog-te-banner-frame, iframe[src*="translate.google.com/translate"],
iframe[src*="translate.googleapis.com"], .VIpgJd-ZVi9od-ORHb, 
.VIpgJd-ZVi9od-aZ2wEe-wOHMyf, body > .skiptranslate {
  display: none !important; visibility: hidden !important; height: 0 !important; min-height: 0 !important;
}
html, body { top: 0 !important; margin-top: 0 !important; }
</style>
<script>
(function() {
  function hideGoogleTranslateBanner() {
    var selectors = ['.goog-te-banner-frame', '.goog-te-banner-frame.skiptranslate', 'iframe.skiptranslate',
      'iframe.goog-te-banner-frame', 'iframe[src*="translate.google.com/translate"]',
      'iframe[src*="translate.googleapis.com"]', '.VIpgJd-ZVi9od-ORHb', '.VIpgJd-ZVi9od-aZ2wEe-wOHMyf', 'body > .skiptranslate'];
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
  if (document.readyState !== 'loading') hideGoogleTranslateBanner();
  var observer = new MutationObserver(hideGoogleTranslateBanner);
  observer.observe(document.documentElement, { childList: true, subtree: true });
  setInterval(hideGoogleTranslateBanner, 500);
}());
</script>
CSS;
		$headPos = stripos($buffer, '</head>');
		if ($headPos !== false) {
			return substr_replace($buffer, $css . '</head>', $headPos, 7);
		}
		$bodyPos = stripos($buffer, '<body');
		if ($bodyPos !== false) {
			$bodyEnd = stripos($buffer, '>', $bodyPos);
			if ($bodyEnd !== false) {
				return substr_replace($buffer, substr($buffer, $bodyEnd + 1, 0) . $css, $bodyEnd + 1, 0);
			}
		}
		return $css . $buffer;
	});
}

$login_session = null;
$user_check = isset($_SESSION['login_user']) ? $_SESSION['login_user'] : '';

if ($user_check !== '') {
	$safe_user = mysqli_real_escape_string($connection, $user_check);
	$ses_sql = mysqli_query($connection, "SELECT username FROM login WHERE username='$safe_user'");
	if ($ses_sql) {
		$row = mysqli_fetch_assoc($ses_sql);
		if ($row && isset($row['username'])) {
			$login_session = $row['username'];
		}
	}
}
 
?>