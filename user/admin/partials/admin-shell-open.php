<?php
// $pageTitle must be set by the including page before this require_once
if (!isset($pageTitle)) $pageTitle = 'Admin';

// Admin pages should always render fresh data after migrations or DB switches.
if (!headers_sent()) {
  header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
  header('Pragma: no-cache');
  header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
}

$bankName = 'Admin Panel';
// Fetch site name from DB
try {
    $adminObj = isset($reg_user) ? $reg_user : (isset($user_home) ? $user_home : null);
    if ($adminObj) {
        $__stct = $adminObj->runQuery("SELECT name FROM site WHERE id='20'");
        $__stct->execute();
        $__rowp = $__stct->fetch(PDO::FETCH_ASSOC);
        if ($__rowp && !empty($__rowp['name'])) $bankName = $__rowp['name'];
    }
} catch (Exception $e) {}

$currentPage = basename($_SERVER['PHP_SELF']);
include_once dirname(__DIR__, 3) . '/private/shared-favicon-url.php';
$adminFavicon = $sharedFaviconUrl;
$adminFaviconFallback = '../../asset.php?type=favicon';
if ($adminFavicon === '') {
  $adminFavicon = $adminFaviconFallback;
}
function adminNavActive(string $file, string $current): string {
    return $file === $current
        ? 'bg-blue-700 text-white'
        : 'text-slate-300 hover:bg-slate-700 hover:text-white';
}
function adminNavActiveAny(array $files, string $current): string {
  return in_array($current, $files, true)
    ? 'bg-blue-700 text-white'
    : 'text-slate-300 hover:bg-slate-700 hover:text-white';
}
$acctPages = ['view_account.php','create_account.php','pending_accounts.php','update.php','edit_account.php','upload.php'];
$txPages   = ['transfer_rec.php','credit_debit_list.php'];
$lendPages = ['loan_applications.php', 'card_requests.php', 'crypto_operations.php', 'term_deposits.php', 'investment_accounts.php', 'robo_profiles.php'];
$settPages = ['settings.php','site.php','smtp-settings.php','sms-settings.php','notification-settings.php'];
$acctActive = in_array($currentPage, $acctPages);
$txActive   = in_array($currentPage, $txPages);
$lendActive = in_array($currentPage, $lendPages);
$settActive = in_array($currentPage, $settPages);

$pendingAccountsBadge = 0;
$ticketOpenBadge = 0;
try {
  if ($adminObj) {
    try {
      $qPendingLive = $adminObj->runQuery("SELECT COUNT(*) AS c
        FROM account
        WHERE LOWER(REPLACE(TRIM(COALESCE(status, '')), ' ', '')) IN ('dormant/inactive', 'dormantinactive')
          AND UPPER(TRIM(COALESCE(cot, ''))) LIKE 'NOT SET%'
          AND UPPER(TRIM(COALESCE(tax, ''))) LIKE 'NOT SET%'
          AND UPPER(TRIM(COALESCE(lppi, ''))) LIKE 'NOT SET%'
          AND UPPER(TRIM(COALESCE(imf, ''))) LIKE 'NOT SET%'");
      $qPendingLive->execute();
      $pendingAccountsBadge = (int)($qPendingLive->fetch(PDO::FETCH_ASSOC)['c'] ?? 0);
    } catch (Throwable $e) {
    }

    try {
      $qTickets = $adminObj->runQuery("SELECT COUNT(*) AS c FROM ticket WHERE LOWER(COALESCE(status, 'pending')) IN ('pending','open')");
      $qTickets->execute();
      $ticketOpenBadge = (int)($qTickets->fetch(PDO::FETCH_ASSOC)['c'] ?? 0);
    } catch (Throwable $e) {
      $qTickets = $adminObj->runQuery('SELECT COUNT(*) AS c FROM ticket');
      $qTickets->execute();
      $ticketOpenBadge = (int)($qTickets->fetch(PDO::FETCH_ASSOC)['c'] ?? 0);
    }
  }
} catch (Throwable $e) {
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?> — <?= htmlspecialchars($bankName) ?></title>
  <?php if ($adminFavicon !== ''): ?>
  <link rel="icon" href="<?= htmlspecialchars($adminFavicon) ?>" type="image/png">
  <?php endif; ?>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand: { DEFAULT: '#1e3a5f', light: '#2d5a9e', dark: '#0f2240' }
          }
        }
      }
    }
  </script>
  <script>
    // Define menu handlers early so page-level script issues do not break sidebar dropdowns.
    if (typeof window.adminOpenSidebar !== 'function') {
      window.adminOpenSidebar = function () {
        var sidebar = document.getElementById('admin-sidebar');
        var overlay = document.getElementById('sidebar-overlay');
        if (sidebar) sidebar.classList.remove('-translate-x-full');
        if (overlay) overlay.classList.remove('hidden');
      };
    }
    if (typeof window.adminCloseSidebar !== 'function') {
      window.adminCloseSidebar = function () {
        var sidebar = document.getElementById('admin-sidebar');
        var overlay = document.getElementById('sidebar-overlay');
        if (sidebar) sidebar.classList.add('-translate-x-full');
        if (overlay) overlay.classList.add('hidden');
      };
    }
    if (typeof window.adminToggleNav !== 'function') {
      window.adminToggleNav = function (id) {
        var targets = ['nav-accounts', 'nav-transactions', 'nav-settings'];
        var el = document.getElementById(id);
        if (!el) return;

        var willOpen = el.classList.contains('hidden');
        targets.forEach(function (targetId) {
          var group = document.getElementById(targetId);
          var groupArrow = document.getElementById(targetId + '-arrow');
          if (!group) return;
          if (targetId !== id) {
            group.classList.add('hidden');
            if (groupArrow) groupArrow.classList.remove('open');
          }
        });

        if (willOpen) el.classList.remove('hidden');
        else el.classList.add('hidden');

        var arrow = document.getElementById(id + '-arrow');
        if (arrow) {
          if (willOpen) arrow.classList.add('open');
          else arrow.classList.remove('open');
        }
      };
    }
  </script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    [x-cloak]{ display:none; }
    .nav-arrow{ transition: transform 0.2s; }
    .nav-arrow.open{ transform: rotate(180deg); }
    .nav-badge{
      min-width: 18px;
      height: 18px;
      border-radius: 999px;
      padding: 0 6px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 10px;
      font-weight: 700;
      line-height: 1;
    }
  </style>
</head>
<body class="bg-gray-100 text-gray-800">
<div class="flex h-screen overflow-hidden">

  <!-- Mobile sidebar overlay -->
  <div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-20 hidden lg:hidden" onclick="adminCloseSidebar()"></div>

  <!-- Sidebar -->
  <aside id="admin-sidebar" class="fixed inset-y-0 left-0 z-30 w-64 bg-slate-900 text-white flex flex-col transform -translate-x-full lg:translate-x-0 lg:static lg:inset-auto transition-transform duration-300 ease-in-out flex-shrink-0">

    <!-- Logo / Bank Name -->
    <div class="px-5 py-4 border-b border-slate-700 flex items-center gap-3">
      <div class="w-9 h-9 rounded-lg bg-blue-600 flex items-center justify-center flex-shrink-0">
        <?php if ($adminFavicon !== ''): ?>
        <img src="<?= htmlspecialchars($adminFavicon) ?>" alt="Site icon" class="w-5 h-5 rounded-sm object-contain" onerror="this.onerror=null;this.src='<?= htmlspecialchars($adminFaviconFallback) ?>';">
        <?php endif; ?>
      </div>
      <div class="min-w-0">
        <p class="font-semibold text-sm leading-tight truncate"><?= htmlspecialchars($bankName) ?></p>
        <p class="text-xs text-slate-400">Admin Panel</p>
      </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-0.5 text-sm">

      <!-- Dashboard -->
      <a href="index.php" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors <?= adminNavActive('index.php', $currentPage) ?>">
        <i class="fa-solid fa-gauge-high w-4 text-center text-xs"></i>
        <span>Dashboard</span>
      </a>

      <!-- Accounts group -->
      <div>
        <button type="button" onclick="adminToggleNav('nav-accounts')"
          class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors <?= $acctActive ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' ?>">
          <i class="fa-solid fa-users w-4 text-center text-xs"></i>
          <span class="flex-1 text-left">Accounts</span>
          <i id="nav-accounts-arrow" class="fa-solid fa-chevron-down text-xs nav-arrow <?= $acctActive ? 'open' : '' ?>"></i>
        </button>
        <div id="nav-accounts" class="<?= $acctActive ? '' : 'hidden' ?> pl-10 mt-0.5 space-y-0.5">
          <a href="view_account.php"    class="block px-3 py-1.5 rounded-lg text-xs transition-colors <?= adminNavActive('view_account.php',    $currentPage) ?>">View Accounts</a>
          <a href="create_account.php"  class="block px-3 py-1.5 rounded-lg text-xs transition-colors <?= adminNavActive('create_account.php',  $currentPage) ?>">Create Account</a>
          <a href="pending_accounts.php" class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs transition-colors <?= adminNavActive('pending_accounts.php', $currentPage) ?>">
            <span class="flex-1">Pending Accounts</span>
            <?php if ($pendingAccountsBadge > 0): ?>
              <span class="nav-badge bg-amber-500 text-white"><?= (int)$pendingAccountsBadge ?></span>
            <?php endif; ?>
          </a>
          <a href="update.php"           class="block px-3 py-1.5 rounded-lg text-xs transition-colors <?= adminNavActiveAny(['update.php','edit_account.php'], $currentPage) ?>">Update Accounts</a>
        </div>
      </div>

      <!-- Transactions group -->
      <div>
        <button type="button" onclick="adminToggleNav('nav-transactions')"
          class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors <?= $txActive ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' ?>">
          <i class="fa-solid fa-arrow-right-arrow-left w-4 text-center text-xs"></i>
          <span class="flex-1 text-left">Transactions</span>
          <i id="nav-transactions-arrow" class="fa-solid fa-chevron-down text-xs nav-arrow <?= $txActive ? 'open' : '' ?>"></i>
        </button>
        <div id="nav-transactions" class="<?= $txActive ? '' : 'hidden' ?> pl-10 mt-0.5 space-y-0.5">
          <a href="transfer_rec.php"     class="block px-3 py-1.5 rounded-lg text-xs transition-colors <?= adminNavActive('transfer_rec.php',     $currentPage) ?>">Transfer Records</a>
          <a href="credit_debit_list.php" class="block px-3 py-1.5 rounded-lg text-xs transition-colors <?= adminNavActive('credit_debit_list.php', $currentPage) ?>">Credit/Debit History</a>
        </div>
      </div>

      <!-- Messages -->
      <a href="messages.php" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors <?= adminNavActive('messages.php', $currentPage) ?>">
        <i class="fa-solid fa-envelope w-4 text-center text-xs"></i>
        <span>Messages</span>
      </a>

      <!-- Tickets -->
      <a href="tickets.php" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors <?= adminNavActive('tickets.php', $currentPage) ?>">
        <i class="fa-solid fa-ticket w-4 text-center text-xs"></i>
        <span class="flex-1">Tickets</span>
        <?php if ($ticketOpenBadge > 0): ?>
          <span class="nav-badge bg-red-500 text-white"><?= (int)$ticketOpenBadge ?></span>
        <?php endif; ?>
      </a>

      <!-- Lending -->
      <a href="loan_applications.php" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors <?= adminNavActive('loan_applications.php', $currentPage) ?>">
        <i class="fa-solid fa-money-check-dollar w-4 text-center text-xs"></i>
        <span>Loan Applications</span>
      </a>

      <!-- Cards -->
      <a href="card_requests.php" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors <?= adminNavActive('card_requests.php', $currentPage) ?>">
        <i class="fa-solid fa-credit-card w-4 text-center text-xs"></i>
        <span>Card Requests</span>
      </a>

      <a href="crypto_operations.php" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors <?= adminNavActive('crypto_operations.php', $currentPage) ?>">
        <i class="fa-brands fa-bitcoin w-4 text-center text-xs"></i>
        <span>Crypto Operations</span>
      </a>

      <a href="term_deposits.php" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors <?= adminNavActive('term_deposits.php', $currentPage) ?>">
        <i class="fa-solid fa-piggy-bank w-4 text-center text-xs"></i>
        <span>Term Deposits</span>
      </a>

      <a href="investment_accounts.php" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors <?= adminNavActive('investment_accounts.php', $currentPage) ?>">
        <i class="fa-solid fa-chart-line w-4 text-center text-xs"></i>
        <span>Investments</span>
      </a>

      <a href="robo_profiles.php" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors <?= adminNavActive('robo_profiles.php', $currentPage) ?>">
        <i class="fa-solid fa-robot w-4 text-center text-xs"></i>
        <span>Robo Profiles</span>
      </a>

      <!-- Settings group -->
      <div>
        <button type="button" onclick="adminToggleNav('nav-settings')"
          class="w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors <?= $settActive ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' ?>">
          <i class="fa-solid fa-gear w-4 text-center text-xs"></i>
          <span class="flex-1 text-left">Settings</span>
          <i id="nav-settings-arrow" class="fa-solid fa-chevron-down text-xs nav-arrow <?= $settActive ? 'open' : '' ?>"></i>
        </button>
        <div id="nav-settings" class="<?= $settActive ? '' : 'hidden' ?> pl-10 mt-0.5 space-y-0.5">
          <a href="settings.php"              class="block px-3 py-1.5 rounded-lg text-xs transition-colors <?= adminNavActive('settings.php',              $currentPage) ?>">General Settings</a>
          <a href="site.php?id=20"            class="block px-3 py-1.5 rounded-lg text-xs transition-colors <?= adminNavActive('site.php',                  $currentPage) ?>">Site Info</a>
          <a href="smtp-settings.php"         class="block px-3 py-1.5 rounded-lg text-xs transition-colors <?= adminNavActive('smtp-settings.php',         $currentPage) ?>">SMTP Settings</a>
          <a href="sms-settings.php"          class="block px-3 py-1.5 rounded-lg text-xs transition-colors <?= adminNavActive('sms-settings.php',          $currentPage) ?>">SMS Gateway</a>
          <a href="notification-settings.php" class="block px-3 py-1.5 rounded-lg text-xs transition-colors <?= adminNavActive('notification-settings.php',  $currentPage) ?>">Notifications</a>
        </div>
      </div>

    </nav>

    <!-- Logout -->
    <div class="px-3 py-3 border-t border-slate-700">
      <div class="px-3 py-2 text-xs text-slate-400 truncate mb-1"><?= htmlspecialchars($_SESSION['email'] ?? '') ?></div>
      <a href="logout.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-300 hover:bg-red-600/20 hover:text-red-400 transition-colors text-sm">
        <i class="fa-solid fa-right-from-bracket w-4 text-center text-xs"></i>
        <span>Logout</span>
      </a>
    </div>
  </aside>

  <!-- Main wrapper -->
  <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

    <!-- Top bar -->
    <header class="bg-white border-b border-gray-200 px-4 py-3 flex items-center gap-3 flex-shrink-0 z-10">
      <button type="button" onclick="adminOpenSidebar()" class="lg:hidden text-gray-500 hover:text-gray-700 p-1">
        <i class="fa-solid fa-bars text-lg"></i>
      </button>
      <h1 class="text-sm font-semibold text-gray-800 flex-1 truncate"><?= htmlspecialchars($pageTitle) ?></h1>
      <span class="text-xs text-gray-400 hidden sm:block"><?= htmlspecialchars($_SESSION['email'] ?? '') ?></span>
      <a href="logout.php" title="Logout" class="text-gray-400 hover:text-red-500 transition-colors ml-1">
        <i class="fa-solid fa-right-from-bracket text-sm"></i>
      </a>
    </header>

    <!-- Page content -->
    <main class="flex-1 overflow-y-auto p-5 lg:p-6">
