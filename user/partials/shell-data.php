<?php
// ── Shell Data Partial ─────────────────────────────────────────────────────────
// Computes display variables for shell-open.php + shell-close.php.
// Include AFTER page has established $reg_user (or $user_home), $row, $accNo.
if (defined('SHELL_DATA_LOADED')) { return; }
define('SHELL_DATA_LOADED', 1);

// Resolve USER instance + row regardless of page variable name
$_shellDb  = isset($reg_user)   && ($reg_user  instanceof USER) ? $reg_user
           : (isset($user_home) && ($user_home instanceof USER) ? $user_home : null);
$_shellRow = isset($row) && is_array($row) ? $row : [];
$shellAccNo = isset($accNo) ? (string)$accNo : (string)($_SESSION['acc_no'] ?? '');

// -- Bank name + contact URL -----------------------------------------------
$shellBankName   = 'Banking Portal';
$shellContactUrl = '#';
$shellLogoUrl    = 'img/logo.png';
$shellFaviconUrl = '';
include_once dirname(__DIR__, 2) . '/private/shared-favicon-url.php';
if (!empty($sharedFaviconUrl)) {
    $shellFaviconUrl = $sharedFaviconUrl;
}
if ($_shellDb) {
    try {
        $s = $_shellDb->runQuery('SELECT name, url, image FROM site ORDER BY id ASC LIMIT 1');
        $s->execute();
        $sr = $s->fetch(PDO::FETCH_ASSOC) ?: [];
        $shellBankName   = trim((string)($sr['name'] ?? '')) ?: 'Banking Portal';
        $shellContactUrl = trim((string)($sr['url']  ?? '')) ?: '#';
        $logoFile = basename((string)($sr['image'] ?? ''));
        if ($logoFile !== '' && is_file(__DIR__ . '/../admin/site/' . $logoFile)) {
            $shellLogoUrl = 'admin/site/' . rawurlencode($logoFile);
        }
    } catch (Throwable $e) {}
}

// Load get_auth_palette() helper — scheme is queried via PDO below (not get_auth_color_scheme),
// because $conn (mysqli) is not in global scope on converted pages: config.php was first
// require_once'd inside Database::__construct() (function scope), so include_once skips it globally.
if (!function_exists('get_auth_palette') && is_file(__DIR__ . '/../auth-theme.php')) {
    require_once __DIR__ . '/../auth-theme.php';
}

// -- Color palette ---------------------------------------------------------
$shellPalette = [
    'navy'=>'#0d1f3c','navy2'=>'#162847','gold'=>'#c9a84c','gold2'=>'#e8c96e',
    'light'=>'#f5f6fa','muted'=>'#8895a7','border'=>'#dce3ec',
    'danger'=>'#c0392b','success'=>'#16a34a',
];
$_shellAuthScheme = 'default';
if ($_shellDb) {
    // Query via PDO (USER class connection — always available on converted pages)
    try {
        $sq = $_shellDb->runQuery("SELECT setting_value FROM site_settings WHERE setting_key='auth_color_scheme' LIMIT 1");
        $sq->execute();
        $sv = $sq->fetch(PDO::FETCH_ASSOC);
        if ($sv && !empty($sv['setting_value'])) { $_shellAuthScheme = (string)$sv['setting_value']; }
    } catch (Throwable $e) {
        try {
            $sq = $_shellDb->runQuery("SELECT `value` FROM site_settings WHERE `key`='auth_color_scheme' LIMIT 1");
            $sq->execute();
            $sv = $sq->fetch(PDO::FETCH_ASSOC);
            if ($sv && !empty($sv['value'])) { $_shellAuthScheme = (string)$sv['value']; }
        } catch (Throwable $e2) {}
    }
} elseif (function_exists('get_auth_color_scheme') && isset($conn) && $conn instanceof mysqli) {
    $_shellAuthScheme = get_auth_color_scheme($conn);
}
if (function_exists('get_auth_palette')) {
    $shellPalette = get_auth_palette($_shellAuthScheme);
}

// -- User identity ---------------------------------------------------------
$shellFullName = trim(
    ($_shellRow['fname'] ?? '') . ' ' .
    ($_shellRow['lname'] ?? '')
);
if ($shellFullName === '') { $shellFullName = 'Customer'; }

$shellBaseCurrency = strtoupper(trim((string)($_shellRow['currency'] ?? 'USD')));
if (!preg_match('/^[A-Z0-9]{2,10}$/', $shellBaseCurrency)) { $shellBaseCurrency = 'USD'; }

// -- Account status --------------------------------------------------------
$shellRawStatus    = trim((string)($_shellRow['status'] ?? 'Active'));
$shellStatusLower  = strtolower($shellRawStatus);
$shellDisplayStatus = $shellRawStatus;
$shellStatusColor   = '#64748b';
// IMPORTANT: check dormant/inactive BEFORE the generic 'active' substring check.
// "Dormant/Inactive" contains the substring "active" (via "in-active"), so order matters.
if (strpos($shellStatusLower, 'dormant') !== false || strpos($shellStatusLower, 'inactive') !== false) {
    $shellDisplayStatus = 'Dormant/Inactive';
    $shellStatusColor   = '#f59e0b';
} elseif ($shellStatusLower === 'active' || $shellStatusLower === 'pincode' || $shellStatusLower === 'otp'
          || strpos($shellStatusLower, 'active') !== false) {
    $shellDisplayStatus = 'Active';
    $shellStatusColor   = '#16a34a';
} elseif ($shellStatusLower === 'closed') {
    $shellDisplayStatus = 'Closed';
    $shellStatusColor   = '#6b7280';
} elseif ($shellStatusLower === 'disabled') {
    $shellDisplayStatus = 'Disabled';
    $shellStatusColor   = '#dc2626';
}

// -- Sidebar avatar + identity line ----------------------------------------
$shellAvatarSrc = '';
$shellAvatarCandidate = trim((string)($_shellRow['image'] ?? ''));
if ($shellAvatarCandidate !== '') {
    $avatarFile = __DIR__ . '/../admin/foto/' . basename($shellAvatarCandidate);
    if (is_file($avatarFile)) {
        $shellAvatarSrc = 'admin/foto/' . rawurlencode(basename($shellAvatarCandidate));
    }
}
if ($shellAvatarSrc === '') {
    $nameParts = preg_split('/\s+/', trim((string)$shellFullName)) ?: [];
    $initials = '';
    foreach ($nameParts as $part) {
        if ($part === '') { continue; }
        $initials .= strtoupper(substr($part, 0, 1));
        if (strlen($initials) >= 2) { break; }
    }
    if ($initials === '') {
        $initials = 'CU';
    }
    $label = htmlspecialchars($initials, ENT_QUOTES, 'UTF-8');
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="88" height="88" viewBox="0 0 88 88">'
         . '<defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#0d1f3c"/><stop offset="100%" stop-color="#1f3a63"/></linearGradient></defs>'
         . '<circle cx="44" cy="44" r="43" fill="url(#g)" stroke="#dce3ec" stroke-width="2"/>'
         . '<text x="44" y="51" text-anchor="middle" fill="#ffffff" font-size="28" font-family="Arial,sans-serif" font-weight="700">' . $label . '</text>'
         . '</svg>';
    $shellAvatarSrc = 'data:image/svg+xml;base64,' . base64_encode($svg);
}
$shellIdentityLine = 'Account ID: ' . $shellAccNo;

// -- Message + ticket counts -----------------------------------------------
$shellMessageCount = 0;
$shellTicketCount  = 0;
if ($_shellDb) {
    try {
        $mc = $_shellDb->runQuery('SELECT COUNT(*) AS c FROM message WHERE reci_name=:u AND COALESCE(is_read,0)=0');
        $mc->execute([':u' => ($_shellRow['uname'] ?? $shellAccNo)]);
        $shellMessageCount = (int)($mc->fetch(PDO::FETCH_ASSOC)['c'] ?? 0);
    } catch (Throwable $e) {}
    try {
        // Ensure the unread marker exists for ticket thread replies.
        try {
            $_shellDb->runQuery('ALTER TABLE ticket_replies ADD COLUMN is_read_user TINYINT(1) NOT NULL DEFAULT 0')->execute();
        } catch (Throwable $e) {
        }

        $ticketOwnerName = trim((string)($_shellRow['fname'] ?? '') . ' ' . (string)($_shellRow['lname'] ?? ''));
        $ticketOwnerMail = trim((string)($_shellRow['email'] ?? ''));

        $tc = $_shellDb->runQuery(
            "SELECT COUNT(*) AS c
             FROM ticket_replies tr
             INNER JOIN ticket t ON t.id = tr.ticket_id
             WHERE LOWER(COALESCE(tr.sender_role, 'admin')) = 'admin'
               AND COALESCE(tr.is_read_user, 0) = 0
               AND (
                   t.mail = :mail
                   OR (COALESCE(t.mail, '') = '' AND t.sender_name = :sender_name)
               )"
        );
        $tc->execute([
            ':mail' => $ticketOwnerMail,
            ':sender_name' => $ticketOwnerName,
        ]);
        $shellTicketCount = (int)($tc->fetch(PDO::FETCH_ASSOC)['c'] ?? 0);
    } catch (Throwable $e) {}
}

// -- Current page for active nav -------------------------------------------
$shellCurrentPage = basename((string)($_SERVER['SCRIPT_NAME'] ?? ''));
