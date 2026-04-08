<?php
// ── Shell Open Partial ─────────────────────────────────────────────────────────
// Outputs DOCTYPE → <head> → <body> → sidebar → opens <main> content wrapper.
// Expects: all $shellXxx vars from shell-data.php.
// Each page sets $shellPageTitle before including this file.

if (!function_exists('shellNavClass')) {
    function shellNavClass(string ...$pages): string {
        global $shellCurrentPage;
        $base = 'flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition-colors';
        return in_array($shellCurrentPage, $pages, true)
            ? $base . ' bg-brand-navy text-white font-medium'
            : $base . ' text-brand-navy hover:bg-brand-light';
    }
}

$shellCryptoTab = strtolower(trim((string)($_GET['tab'] ?? '')));
$shellCryptoMenuOpen = $shellCurrentPage === 'crypto-vault.php';

$_title = isset($shellPageTitle) ? $shellPageTitle . ' | ' . $shellBankName : $shellBankName;
?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($_title) ?></title>
    <link rel="icon" href="<?= htmlspecialchars($shellFaviconUrl ?? 'img/favicon.png') ?>" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            navy:    '<?= htmlspecialchars($shellPalette['navy'])    ?>',
                            navy2:   '<?= htmlspecialchars($shellPalette['navy2'])   ?>',
                            gold:    '<?= htmlspecialchars($shellPalette['gold'])    ?>',
                            gold2:   '<?= htmlspecialchars($shellPalette['gold2'])   ?>',
                            muted:   '<?= htmlspecialchars($shellPalette['muted'])   ?>',
                            success: '<?= htmlspecialchars($shellPalette['success']) ?>',
                            danger:  '<?= htmlspecialchars($shellPalette['danger'])  ?>',
                            border:  '<?= htmlspecialchars($shellPalette['border'])  ?>',
                            light:   '<?= htmlspecialchars($shellPalette['light'])   ?>'
                        }
                    }
                }
            }
        };
    </script>
    <link rel="stylesheet" href="css/new-ui-bridge.css">
    <style>
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
        }());
    </script>
    <?php if (isset($shellExtraHead)) echo $shellExtraHead; ?>
</head>
<body class="min-h-screen bg-brand-light pb-24 text-slate-800 lg:pb-0 has-new-shell"
      style="--shell-navy:<?= htmlspecialchars($shellPalette['navy']) ?>;--shell-navy2:<?= htmlspecialchars($shellPalette['navy2']) ?>;--shell-accent:<?= htmlspecialchars($shellPalette['gold']) ?>;--shell-danger:<?= htmlspecialchars($shellPalette['danger']) ?>;">

    <!-- Sidebar -->
    <aside id="app-sidebar" class="fixed inset-y-0 left-0 z-40 w-72 -translate-x-full overflow-y-auto border-r border-brand-border bg-white/95 p-4 shadow-xl transition-transform duration-300 lg:translate-x-0 lg:shadow-none">
        <div class="mb-5 flex items-center justify-between">
            <a href="index.php" class="inline-flex items-center">
                <img src="<?= htmlspecialchars($shellLogoUrl ?? 'img/logo.png') ?>" alt="<?= htmlspecialchars($shellBankName) ?>" class="h-10 w-auto" onerror="this.onerror=null;this.src='assets/img/logo.png';">
            </a>
            <button id="sidebar-close" class="rounded-md border border-brand-border px-2 py-1 text-xs font-medium text-brand-muted lg:hidden">Close</button>
        </div>

        <div class="rounded-xl border border-brand-border bg-brand-light/50 p-3">
            <p class="flex items-center gap-2 text-xs uppercase tracking-wide text-brand-muted">
                <span class="inline-block h-2.5 w-2.5 rounded-full" style="background:<?= htmlspecialchars($shellStatusColor) ?>;"></span>
                <?= htmlspecialchars($shellDisplayStatus) ?>
            </p>
            <p class="mt-1 text-sm font-semibold text-brand-navy"><?= htmlspecialchars($shellFullName) ?></p>
            <p class="mt-2 flex items-center gap-2 text-xs text-brand-muted">
                <span class="inline-flex h-8 w-8 items-center justify-center overflow-hidden rounded-full border border-brand-border bg-white">
                    <img src="<?= htmlspecialchars($shellAvatarSrc) ?>" alt="<?= htmlspecialchars($shellFullName) ?>" class="h-full w-full object-cover" onerror="this.onerror=null;this.src='img/avatar.jpg';">
                </span>
                <span><?= htmlspecialchars($shellIdentityLine) ?></span>
            </p>
        </div>

        <p class="mt-6 text-xs uppercase tracking-[0.15em] text-brand-muted">Navigation</p>
        <nav class="mt-2 space-y-0.5">
            <a href="index.php" class="<?= shellNavClass('index.php') ?>">
                <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/></svg>
                <span class="sidebar-label">Dashboard</span>
            </a>
            <a href="send.php" class="<?= shellNavClass('send.php','transfer-auth.php','otp_auth.php','pincode.php') ?>">
                <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h12"/><path d="M12 3l4 4-4 4"/><path d="M20 17H8"/><path d="M12 13l-4 4 4 4"/></svg>
                <span class="sidebar-label">Send / Transfer</span>
            </a>
            <a href="statement.php" class="<?= shellNavClass('statement.php') ?>">
                <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="5" y="3" width="14" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h6"/></svg>
                <span class="sidebar-label">Statements</span>
            </a>
            <a href="loan.php" class="<?= shellNavClass('loan.php') ?>">
                <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="7" width="18" height="10" rx="2"/><path d="M7 12h4"/></svg>
                <span class="sidebar-label">Loans</span>
            </a>
            <a href="cards.php" class="<?= shellNavClass('cards.php') ?>">
                <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="6" width="18" height="12" rx="2"/><path d="M3 10h18"/></svg>
                <span class="sidebar-label">Cards</span>
            </a>
            <div class="space-y-1">
                <button type="button"
                    onclick="(function(){var m=document.getElementById('crypto-submenu');var c=document.getElementById('crypto-caret');if(!m||!c)return;m.classList.toggle('hidden');c.classList.toggle('rotate-180');})();"
                    class="w-full flex items-center justify-between rounded-lg px-3 py-2 text-sm transition-colors <?= $shellCurrentPage === 'crypto-vault.php' ? 'bg-brand-navy text-white font-medium' : 'text-brand-navy hover:bg-brand-light' ?>">
                    <span class="flex items-center gap-3">
                        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="8"/><path d="M12 7v10M9 9.5c0-1 1.2-1.8 3-1.8s3 .8 3 1.8-1.2 1.8-3 1.8-3 .8-3 1.8 1.2 1.8 3 1.8 3-.8 3-1.8"/></svg>
                        <span class="sidebar-label">Crypto Vault</span>
                    </span>
                    <svg id="crypto-caret" class="h-4 w-4 transition-transform <?= $shellCryptoMenuOpen ? 'rotate-180' : '' ?>" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
                </button>
                <div id="crypto-submenu" class="ml-7 space-y-1 <?= $shellCryptoMenuOpen ? '' : 'hidden' ?>">
                    <a href="crypto-vault.php?tab=deposit" class="flex items-center rounded-lg px-3 py-1.5 text-xs transition-colors <?= $shellCurrentPage === 'crypto-vault.php' && $shellCryptoTab !== 'withdraw' ? 'bg-brand-light text-brand-navy font-semibold' : 'text-brand-muted hover:bg-brand-light/60' ?>">
                        <span class="sidebar-label">Crypto Deposit</span>
                    </a>
                    <a href="crypto-vault.php?tab=withdraw" class="flex items-center rounded-lg px-3 py-1.5 text-xs transition-colors <?= $shellCurrentPage === 'crypto-vault.php' && $shellCryptoTab === 'withdraw' ? 'bg-brand-light text-brand-navy font-semibold' : 'text-brand-muted hover:bg-brand-light/60' ?>">
                        <span class="sidebar-label">Crypto Withdraw</span>
                    </a>
                </div>
            </div>
            <a href="term-deposits.php" class="<?= shellNavClass('term-deposits.php') ?>">
                <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 7h18"/><path d="M6 7v10a3 3 0 0 0 3 3h6a3 3 0 0 0 3-3V7"/><path d="M9 11h6"/></svg>
                <span class="sidebar-label">Term Deposits</span>
            </a>
            <a href="investments.php" class="<?= shellNavClass('investments.php') ?>">
                <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19h16"/><path d="M7 15l3-3 3 2 4-5"/></svg>
                <span class="sidebar-label">Investments</span>
            </a>
            <a href="robo.php" class="<?= shellNavClass('robo.php') ?>">
                <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="7" y="7" width="10" height="10" rx="2"/><path d="M9 3v2M15 3v2M9 19v2M15 19v2M3 9h2M3 15h2M19 9h2M19 15h2"/></svg>
                <span class="sidebar-label">Robo Advisory</span>
            </a>
            <a href="profile.php" class="<?= shellNavClass('profile.php') ?>">
                <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M4 20c1.8-3.7 5-5.5 8-5.5s6.2 1.8 8 5.5"/></svg>
                <span class="sidebar-label">Profile</span>
            </a>
            <a href="ticket.php" class="<?= shellNavClass('ticket.php') ?> justify-between">
                <span class="flex items-center gap-3">
                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5h16v14H4z"/><path d="M8 9h8M8 13h5"/></svg>
                    <span class="sidebar-label">Tickets</span>
                </span>
                <?php if ($shellTicketCount > 0): ?>
                    <span class="rounded-full bg-brand-light px-2 py-0.5 text-xs text-brand-navy"><?= $shellTicketCount ?></span>
                <?php endif; ?>
            </a>
            <a href="logout.php" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-brand-danger hover:bg-red-50 transition-colors">
                <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
                <span class="sidebar-label">Logout</span>
            </a>
        </nav>
    </aside>

    <!-- Main -->
    <main id="app-main" class="transition-all duration-300 lg:pl-72">
        <div class="mx-auto max-w-7xl p-4 md:p-8">
