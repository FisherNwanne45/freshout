<?php
session_start();
include_once 'session.php';
require_once 'class.user.php';
require_once '../config.php';
require_once __DIR__ . '/auth-theme.php';
require_once __DIR__ . '/partials/auto-migrate.php';

if (!isset($_SESSION['acc_no'])) {
    header('Location: login.php');
    exit();
}
if (!isset($_SESSION['pin'])) {
    header('Location: passcode.php');
    exit();
}

$reg_user = new USER();
$accNo = (string)$_SESSION['acc_no'];

$stmt = $reg_user->runQuery('SELECT * FROM account WHERE acc_no = :acc_no LIMIT 1');
$stmt->execute([':acc_no' => $accNo]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) {
    header('Location: logout.php');
    exit();
}

$siteStmt = $reg_user->runQuery('SELECT * FROM site ORDER BY id ASC LIMIT 1');
$siteStmt->execute();
$siteRow = $siteStmt->fetch(PDO::FETCH_ASSOC) ?: [];

$bankName = $siteRow['name'] ?? 'Banking Portal';
$themeScheme = get_auth_color_scheme($conn);
$palette = get_auth_palette($themeScheme);
require_once __DIR__ . '/partials/index-dashboard-bootstrap.php';

// ── Promo Banner ──────────────────────────────────────────────────────
$promoSettings = [];
try {
    $pdoPromo = $reg_user->runQuery("SELECT `key`, `value` FROM site_settings WHERE `key` LIKE 'promo_%'");
    $pdoPromo->execute();
    while ($pr = $pdoPromo->fetch(PDO::FETCH_ASSOC)) {
        $promoSettings[(string)$pr['key']] = (string)$pr['value'];
    }
} catch (Throwable $e) {
    // Fallback: legacy setting_key / setting_value schema
    try {
        $pdoPromo = $reg_user->runQuery("SELECT setting_key AS `key`, setting_value AS `value` FROM site_settings WHERE setting_key LIKE 'promo_%'");
        $pdoPromo->execute();
        while ($pr = $pdoPromo->fetch(PDO::FETCH_ASSOC)) {
            $promoSettings[(string)$pr['key']] = (string)$pr['value'];
        }
    } catch (Throwable $e2) {
    }
}
$promoActive      = ($promoSettings['promo_enabled'] ?? '0') === '1';
$promoCardActive  = $promoActive && ($promoSettings['promo_card_enabled'] ?? '0') === '1';
$promoImageUrl    = $promoSettings['promo_image_url'] ?? '';
$promoHeadline    = $promoSettings['promo_headline'] ?? '';
$promoBodyText    = $promoSettings['promo_body'] ?? '';
$promoBtnLabel    = $promoSettings['promo_btn_label'] ?? '';
$promoBtnUrl      = $promoSettings['promo_btn_url'] ?? '';
?>
<header class="mb-6 rounded-2xl bg-gradient-to-r from-brand-navy to-brand-navy2 p-6 text-white shadow-xl">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-xs uppercase tracking-[0.2em] text-brand-gold2">User Account Panel</p>
            <h1 class="mt-1 text-2xl font-semibold"><?= htmlspecialchars($bankName) ?> Dashboard</h1>
            <p class="mt-1 text-sm text-slate-200">Welcome back, <?= htmlspecialchars($fullName) ?></p>
        </div>
        <div class="text-right">
            <p class="text-xs uppercase tracking-wide text-brand-gold2">Available Balance</p>
            <p class="text-3xl font-bold"><?= htmlspecialchars($activeAccountCode) ?> <?= number_format($activeAccountBalance, 2) ?></p>
        </div>
    </div>
</header>

<section class="mb-6 overflow-hidden rounded-2xl border border-brand-border bg-white shadow-sm">
    <form method="get" class="flex flex-col gap-4 bg-gradient-to-r from-brand-light/60 via-white to-blue-50/40 p-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-brand-navy text-white shadow-sm">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M3 12h18M3 17h18" />
                </svg>
            </span>
            <div>
                <p class="text-xs uppercase tracking-wide text-brand-muted">Account Switcher</p>
                <p class="text-sm font-medium text-brand-navy">View balances, cards, and activity by currency account.</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <label for="account_currency" class="text-xs font-semibold uppercase tracking-wide text-brand-muted">Account</label>
            <div class="relative min-w-[220px]">
                <select id="account_currency" name="account_currency" class="w-full appearance-none rounded-xl border-2 border-brand-border bg-white pl-3 pr-10 py-2.5 text-sm font-semibold text-brand-navy shadow-sm outline-none transition-colors hover:border-brand-navy focus:border-brand-navy" onchange="this.form.submit()">
                    <?php foreach ($wallets as $wallet): ?>
                        <?php $code = strtoupper((string)$wallet['currency_code']); ?>
                        <option value="<?= htmlspecialchars($code) ?>" <?= $activeAccountCode === $code ? 'selected' : '' ?>><?= htmlspecialchars($code) ?> Account</option>
                    <?php endforeach; ?>
                </select>
                <svg class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-brand-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </div>
    </form>
</section>

<?php if ($flashError !== ''): ?>
    <div class="mb-4 rounded-xl border border-brand-danger/30 bg-red-50 p-3 text-sm text-brand-danger"><?= htmlspecialchars($flashError) ?></div>
<?php endif; ?>
<?php if ($flashSuccess !== ''): ?>
    <div class="mb-4 rounded-xl border border-brand-success/30 bg-green-50 p-3 text-sm text-brand-success"><?= htmlspecialchars($flashSuccess) ?></div>
<?php endif; ?>

<section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
    <div class="rounded-2xl border border-brand-border bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between">
            <p class="text-xs uppercase tracking-wide text-brand-muted">Primary Account Balance</p>
            <svg class="h-5 w-5 text-brand-navy" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 2v20m4-16.5a4.5 4.5 0 0 0-4-1.5c-2.4 0-4 1.3-4 3.2 0 2 1.7 2.8 4 3.3 2.3.5 4 1.2 4 3.2 0 1.9-1.6 3.3-4 3.3a4.9 4.9 0 0 1-4.5-2" />
            </svg>
        </div>
        <p class="mt-1.5 text-2xl font-bold text-brand-navy"><?= htmlspecialchars($activeAccountCode) ?> <?= number_format($activeAccountBalance, 2) ?></p>
    </div>
    <div class="rounded-2xl border border-brand-border bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between">
            <p class="text-xs uppercase tracking-wide text-brand-muted">Ledger Balance</p>
            <svg class="h-5 w-5 text-brand-navy" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 19h16M7 15l3-3 3 2 4-5" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 10h2v2" />
            </svg>
        </div>
        <p class="mt-1.5 text-2xl font-bold text-brand-navy"><?= htmlspecialchars($activeAccountCode) ?> <?= number_format($ledgerBalance, $activeAccountIsCrypto === 1 ? 8 : 2) ?></p>
    </div>
    <div class="rounded-2xl border border-brand-border bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between">
            <p class="text-xs uppercase tracking-wide text-brand-muted">Accounts</p>
            <svg class="h-5 w-5 text-brand-navy" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <rect x="3" y="4" width="18" height="15" rx="2" />
                <path stroke-linecap="round" d="M3 9h18" />
            </svg>
        </div>
        <p class="mt-1.5 text-2xl font-bold text-brand-navy"><?= number_format($walletCount) ?></p>
        <p class="mt-1 text-xs text-brand-muted">Total Assets: <?= htmlspecialchars($activeAccountCode) ?> <?= number_format($walletTotalInActive, $activeAccountIsCrypto === 1 ? 8 : 2) ?></p>
    </div>
    <div class="rounded-2xl border border-brand-border bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between">
            <p class="text-xs uppercase tracking-wide text-brand-muted">Monthly Net Flow</p>
            <svg class="h-5 w-5 text-brand-navy" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 18h16" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 14l4-4 3 3 5-6" />
            </svg>
        </div>
        <p class="mt-1.5 text-2xl font-bold <?= $netFlow < 0 ? 'text-brand-danger' : 'text-brand-success' ?>"><?= $netFlow < 0 ? '-' : '+' ?><?= htmlspecialchars($activeAccountCode) ?> <?= number_format(abs($netFlow), $activeAccountIsCrypto === 1 ? 8 : 2) ?></p>
    </div>
</section>

<!-- ═══ Mastercard + My Currency Accounts ═══ -->
<section class="mt-6 items-stretch grid gap-6 lg:grid-cols-3">
    <div class="rounded-2xl border border-brand-border bg-gradient-to-br from-slate-900 via-slate-800 to-brand-navy2 p-5 text-white shadow-xl">
        <p class="text-xs uppercase tracking-[0.25em] text-slate-300">Platinum Mastercard</p>
        <div class="mt-4 flex items-center justify-between">
            <div class="h-10 w-14 rounded-md bg-gradient-to-br from-yellow-200 to-yellow-500/80"></div>
            <div class="relative h-10 w-16">
                <span class="absolute left-0 top-0 inline-block h-10 w-10 rounded-full bg-red-500/90"></span>
                <span class="absolute right-0 top-0 inline-block h-10 w-10 rounded-full bg-orange-400/90 mix-blend-screen"></span>
            </div>
        </div>
        <p class="mt-5 text-lg font-semibold tracking-[0.14em] whitespace-nowrap"><?= htmlspecialchars($cardMasked) ?></p>
        <div class="mt-4 flex items-end justify-between">
            <div>
                <p class="text-[10px] uppercase tracking-[0.2em] text-slate-300">Card Holder</p>
                <p class="text-sm font-semibold"><?= htmlspecialchars(strtoupper($fullName)) ?></p>
            </div>
            <div class="text-right">
                <p class="text-[10px] uppercase tracking-[0.2em] text-slate-300">Expires</p>
                <p class="text-sm font-semibold"><?= htmlspecialchars($cardExpiry) ?></p>
            </div>
        </div>
    </div>

    <div class="lg:col-span-2 rounded-2xl border border-brand-border bg-white p-5 shadow-sm">
        <div class="mb-4 flex items-center justify-between gap-2">
            <h2 class="text-lg font-semibold text-brand-navy">My Currency Accounts</h2>
            <div class="flex items-center gap-2">
                <button type="button" onclick="document.getElementById('allAccountsModal').classList.remove('hidden')"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-brand-border px-3 py-1.5 text-xs font-semibold text-brand-navy hover:bg-brand-light transition-colors">
                    All Accounts
                </button>
                <button type="button" onclick="document.getElementById('addWalletForm').classList.toggle('hidden')"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-brand-navy px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-navy2 transition-colors">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Currency
                </button>
            </div>
        </div>

        <div id="addWalletForm" class="hidden mb-4 rounded-xl border border-brand-border bg-brand-light/50 p-4">
            <p class="text-xs font-semibold text-brand-muted mb-3">Open a New Currency Account</p>
            <?php if ($flashError): ?>
                <p class="mb-2 text-xs text-red-600"><?= htmlspecialchars($flashError) ?></p>
            <?php endif; ?>
            <?php if ($flashSuccess): ?>
                <p class="mb-2 text-xs text-green-700"><?= htmlspecialchars($flashSuccess) ?></p>
            <?php endif; ?>
            <form method="post" class="flex gap-2 flex-wrap items-end">
                <input type="hidden" name="add_wallet" value="1">
                <div class="flex-1 min-w-[180px]">
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-brand-muted">Select Currency</label>
                    <div class="relative" id="addWalletWrap">
                        <button type="button" id="addWalletBtn"
                            onclick="document.getElementById('addWalletList').classList.toggle('hidden')"
                            class="w-full flex items-center gap-3 rounded-xl border-2 border-brand-border bg-white px-3 py-2.5 text-left hover:border-brand-navy transition-colors">
                            <img id="addWalletImg" src="" alt="" class="h-5 w-7 rounded flex-shrink-0 object-cover hidden" onerror="this.style.display='none'">
                            <span class="flex-1 min-w-0 text-sm font-semibold text-brand-muted" id="addWalletLabel">Choose…</span>
                            <svg class="h-4 w-4 text-brand-muted flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div id="addWalletList" class="hidden absolute z-30 w-full mt-1.5 rounded-xl border border-brand-border bg-white shadow-xl overflow-hidden">
                            <div class="max-h-52 overflow-y-auto">
                                <div class="sticky top-0 bg-white border-b border-brand-border/40 px-3 py-2">
                                    <input type="text" placeholder="Search…" class="w-full rounded-lg border border-brand-border px-2 py-1 text-xs outline-none" oninput="filterAddWallet(this.value)">
                                </div>
                                <div id="addWalletListItems">
                                    <?php
                                    $existingCodes = array_column($wallets, 'currency_code');
                                    foreach ($availableCurrencies as $ac):
                                        if (in_array($ac['code'], $existingCodes, true)) continue;
                                        $acCode = htmlspecialchars($ac['code']);
                                        $acName = htmlspecialchars($ac['name']);
                                    ?>
                                        <button type="button"
                                            class="add-wallet-item w-full flex items-center gap-3 px-4 py-2.5 hover:bg-brand-light text-left transition-colors border-b border-brand-border/40 last:border-0"
                                            data-code="<?= $acCode ?>" data-name="<?= $acName ?>"
                                            onclick="pickAddWallet('<?= $acCode ?>','<?= $acName ?>')">
                                            <img src="flag-preview.php?code=<?= urlencode($ac['code']) ?>" alt="" class="h-5 w-7 rounded flex-shrink-0 object-cover" onerror="this.style.display='none'">
                                            <span class="flex-1 min-w-0">
                                                <span class="block text-sm font-semibold text-brand-navy"><?= $acCode ?></span>
                                                <span class="block text-[11px] text-brand-muted truncate"><?= $acName ?></span>
                                            </span>
                                            <?php if ((int)$ac['is_crypto']): ?>
                                                <span class="text-[10px] bg-amber-100 text-amber-700 rounded-full px-1.5 py-0.5">crypto</span>
                                            <?php endif; ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="new_currency" id="addWalletInput" required>
                    </div>
                </div>
                <button type="submit" class="rounded-xl bg-brand-navy px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-navy2 transition-colors">Open Account</button>
            </form>
        </div>

        <?php if ($flashSuccess && !isset($_POST['add_wallet'])): ?>
            <p class="mb-3 text-xs text-green-700"><?= htmlspecialchars($flashSuccess) ?></p>
        <?php endif; ?>
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
            <?php foreach ($spotlightWalletCards as $walletCard): ?>
                <?php $wCode = strtoupper((string)$walletCard['code']); ?>
                <?php $symbol = (string)$walletCard['symbol']; ?>
                <?php $ibanDisplay = trim(chunk_split(str_replace(' ', '', (string)$walletCard['iban']), 4, ' ')); ?>
                <div class="rounded-xl border border-brand-border bg-white p-4 flex flex-col gap-2 transition-shadow <?= $walletCard['exists'] ? 'hover:shadow-md' : 'opacity-65 border-dashed' ?>">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 min-w-0">
                            <img src="flag-preview.php?code=<?= urlencode($wCode) ?>" alt="<?= htmlspecialchars($wCode) ?>" class="h-6 w-8 rounded object-cover shadow-sm flex-shrink-0" onerror="this.style.display='none'">
                            <div class="min-w-0">
                                <p class="text-xs font-semibold text-brand-navy leading-tight flex items-center gap-1.5">
                                    <?= htmlspecialchars($wCode) ?>
                                    <?php if ($walletCard['is_active']): ?>
                                        <span class="rounded-full bg-brand-navy/10 px-1.5 py-0.5 text-[9px] uppercase tracking-wide text-brand-navy">Main</span>
                                    <?php endif; ?>
                                </p>
                                <p class="text-[10px] text-brand-muted leading-tight truncate max-w-[120px]"><?= htmlspecialchars((string)$walletCard['name']) ?></p>
                            </div>
                        </div>
                        <span class="text-[10px] font-semibold rounded-full px-2 py-0.5 <?= (int)$walletCard['is_crypto'] === 1 ? 'bg-amber-100 text-amber-700' : 'bg-blue-50 text-blue-600' ?>">
                            <?= (int)$walletCard['is_crypto'] === 1 ? 'Crypto' : 'Fiat' ?>
                        </span>
                    </div>
                    <?php if ($walletCard['exists']): ?>
                        <p class="text-xl font-bold text-brand-navy"><?= htmlspecialchars((string)$symbol) ?> <?= number_format((float)$walletCard['balance'], (int)$walletCard['is_crypto'] === 1 ? 8 : 2) ?></p>
                        <?php if ($ibanDisplay !== ''): ?>
                            <p class="text-[10px] font-mono text-brand-muted break-all" title="<?= htmlspecialchars((string)$walletCard['iban']) ?>"><?= htmlspecialchars($ibanDisplay) ?></p>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-lg font-semibold text-brand-muted"><?= htmlspecialchars((string)$symbol) ?> 0.00</p>
                        <p class="text-[11px] text-brand-muted">No account yet. Use Add Currency to open <?= htmlspecialchars($wCode) ?>.</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="allAccountsModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4" onclick="if(event.target===this)this.classList.add('hidden')">
            <div class="relative w-full max-w-3xl rounded-2xl bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-brand-border px-5 py-4">
                    <h3 class="text-base font-semibold text-brand-navy">All Currency Accounts</h3>
                    <button type="button" onclick="document.getElementById('allAccountsModal').classList.add('hidden')"
                        class="rounded-lg p-1 text-brand-muted hover:bg-brand-light transition-colors">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="max-h-[65vh] overflow-y-auto p-5">
                    <?php if (empty($wallets)): ?>
                        <p class="text-sm text-brand-muted">No currency accounts yet.</p>
                    <?php else: ?>
                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            <?php foreach ($wallets as $wallet): ?>
                                <?php $wCode = strtoupper((string)$wallet['currency_code']); ?>
                                <?php $symbol = $wallet['symbol'] ?: $wCode; ?>
                                <?php $ibanDisplay = trim(chunk_split(str_replace(' ', '', (string)($wallet['iban'] ?? '')), 4, ' ')); ?>
                                <div class="rounded-xl border border-brand-border bg-white p-4 flex flex-col gap-2">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <img src="flag-preview.php?code=<?= urlencode($wCode) ?>" alt="<?= htmlspecialchars($wCode) ?>" class="h-6 w-8 rounded object-cover shadow-sm flex-shrink-0" onerror="this.style.display='none'">
                                            <div class="min-w-0">
                                                <p class="text-xs font-semibold text-brand-navy leading-tight"><?= htmlspecialchars($wCode) ?></p>
                                                <p class="text-[10px] text-brand-muted leading-tight truncate max-w-[120px]"><?= htmlspecialchars((string)($wallet['name'] ?: $wCode)) ?></p>
                                            </div>
                                        </div>
                                        <span class="text-[10px] font-semibold rounded-full px-2 py-0.5 <?= (int)$wallet['is_crypto'] === 1 ? 'bg-amber-100 text-amber-700' : 'bg-blue-50 text-blue-600' ?>">
                                            <?= (int)$wallet['is_crypto'] === 1 ? 'Crypto' : 'Fiat' ?>
                                        </span>
                                    </div>
                                    <p class="text-xl font-bold text-brand-navy"><?= htmlspecialchars((string)$symbol) ?> <?= number_format((float)$wallet['balance'], (int)$wallet['is_crypto'] === 1 ? 8 : 2) ?></p>
                                    <?php if ($ibanDisplay !== ''): ?>
                                        <p class="text-[10px] font-mono text-brand-muted break-all" title="<?= htmlspecialchars((string)($wallet['iban'] ?? '')) ?>"><?= htmlspecialchars($ibanDisplay) ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</section>

<script>
    (function() {
        var exState = {
            from: null,
            fropin: '',
            fromBal: '',
            to: null,
            toName: ''
        };
        var exRates = <?= json_encode(array_values(array_map(function ($r) {
                            return ['f' => $r['from_code'], 't' => $r['to_code'], 'r' => (float)$r['rate']];
                        }, $rates))) ?>;
        window.exchToggle = function(showId, hideId) {
            var s = document.getElementById(showId),
                h = document.getElementById(hideId);
            if (h) h.classList.add('hidden');
            if (s) s.classList.toggle('hidden');
            if (showId === 'toList') {
                var si = document.getElementById('toCurrencySearch');
                if (si) {
                    si.value = '';
                    exchFilterTo('');
                    si.focus();
                }
            }
        };
        document.addEventListener('click', function(e) {
            var pairs = {
                fromWrap: 'fromList',
                toWrap: 'toList',
                addWalletWrap: 'addWalletList'
            };
            Object.keys(pairs).forEach(function(wid) {
                var w = document.getElementById(wid);
                if (!w) return;
                if (!w.contains(e.target)) {
                    var l = document.getElementById(pairs[wid]);
                    if (l) l.classList.add('hidden');
                }
            });
        });
        window.exchPickFrom = function(code, name, bal) {
            exState.from = code;
            exState.fropin = name;
            exState.fromBal = bal;
            document.getElementById('fromInput').value = code;
            document.getElementById('fromCodeLabel').textContent = code;
            document.getElementById('fropinLabel').textContent = name;
            document.getElementById('fromBalLabel').textContent = bal;
            document.getElementById('fromSymbolPrefix').textContent = code;
            var img = document.getElementById('fromImg');
            img.src = 'flag-preview.php?code=' + encodeURIComponent(code);
            img.style.display = '';
            document.getElementById('fromList').classList.add('hidden');
            exchUpdateRate();
        };
        window.exchPickTo = function(code, name) {
            exState.to = code;
            exState.toName = name;
            document.getElementById('toInput').value = code;
            document.getElementById('toCodeLabel').textContent = code;
            document.getElementById('toNameLabel').textContent = name;
            var img = document.getElementById('toImg');
            img.src = 'flag-preview.php?code=' + encodeURIComponent(code);
            img.style.display = '';
            document.getElementById('toList').classList.add('hidden');
            exchUpdateRate();
        };
        window.exchSwap = function() {
            var fc = exState.from,
                fn = exState.fropin,
                tc = exState.to,
                tn = exState.toName;
            if (!fc || !tc) return;
            exchPickFrom(tc, tn, '');
            exchPickTo(fc, fn);
        };
        window.exchFilterTo = function(val) {
            val = (val || '').toLowerCase();
            document.querySelectorAll('.exch-to-item').forEach(function(b) {
                var c = (b.getAttribute('data-code') || '').toLowerCase(),
                    n = (b.getAttribute('data-name') || '').toLowerCase();
                b.style.display = (c.includes(val) || n.includes(val)) ? '' : 'none';
            });
        };
        window.exchUpdateRate = function() {
            var from = exState.from,
                to = exState.to,
                box = document.getElementById('ratePreviewBox');
            if (!from || !to) {
                box.classList.add('hidden');
                return;
            }
            var rate = null;
            for (var i = 0; i < exRates.length; i++) {
                if (exRates[i].f === from && exRates[i].t === to) {
                    rate = exRates[i].r;
                    break;
                }
            }
            if (!rate) {
                for (var i = 0; i < exRates.length; i++) {
                    if (exRates[i].f === to && exRates[i].t === from && exRates[i].r > 0) {
                        rate = 1 / exRates[i].r;
                        break;
                    }
                }
            }
            if (rate) {
                box.classList.remove('hidden');
                document.getElementById('ratePreviewText').textContent = '1 ' + from + ' = ' + rate.toFixed(6) + ' ' + to;
                var amt = parseFloat(document.getElementById('exchangeAmt').value) || 0;
                document.getElementById('rateConvertedText').textContent = amt > 0 ? amt + ' ' + from + ' \u2248 ' + (amt * rate).toFixed(4) + ' ' + to : '';
            } else {
                box.classList.add('hidden');
            }
        };
        window.filterAddWallet = function(val) {
            val = (val || '').toLowerCase();
            document.querySelectorAll('.add-wallet-item').forEach(function(b) {
                var c = (b.getAttribute('data-code') || '').toLowerCase(),
                    n = (b.getAttribute('data-name') || '').toLowerCase();
                b.style.display = (c.includes(val) || n.includes(val)) ? '' : 'none';
            });
        };
        window.pickAddWallet = function(code, name) {
            document.getElementById('addWalletInput').value = code;
            var lbl = document.getElementById('addWalletLabel');
            lbl.textContent = code + ' \u2013 ' + name;
            lbl.className = 'text-sm font-semibold text-brand-navy';
            var img = document.getElementById('addWalletImg');
            img.src = 'flag-preview.php?code=' + encodeURIComponent(code);
            img.classList.remove('hidden');
            document.getElementById('addWalletList').classList.add('hidden');
        };
    })();
</script>

<?php $ratesPreview = array_slice($rates, 0, 5);
$ratesExtra = array_slice($rates, 5); ?>
<section class="mt-6 grid gap-6 lg:grid-cols-3">
    <div class="min-w-0 lg:col-span-2 rounded-2xl border border-brand-border bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-lg font-semibold text-brand-navy"><?= htmlspecialchars($bankName) ?> Exchange Rate</h2>
            <span class="text-xs text-brand-muted">Live configured table</span>
        </div>
        <div class="mt-4 overflow-hidden rounded-xl border border-brand-border bg-slate-900 text-slate-100">
            <?php if (!empty($rates)): ?>
                <div class="ticker-track flex gap-4 px-3 py-3 text-xs sm:text-sm">
                    <?php foreach ($rates as $rate): ?>
                        <span class="inline-flex items-center gap-2 whitespace-nowrap rounded-md bg-white/5 px-3 py-1">
                            <strong><?= htmlspecialchars((string)$rate['from_code']) ?>/<?= htmlspecialchars((string)$rate['to_code']) ?></strong>
                            <span class="text-emerald-300"><?= number_format((float)$rate['rate'], 6) ?></span>
                        </span>
                    <?php endforeach; ?>
                    <?php foreach ($rates as $rate): ?>
                        <span class="inline-flex items-center gap-2 whitespace-nowrap rounded-md bg-white/5 px-3 py-1">
                            <strong><?= htmlspecialchars((string)$rate['from_code']) ?>/<?= htmlspecialchars((string)$rate['to_code']) ?></strong>
                            <span class="text-emerald-300"><?= number_format((float)$rate['rate'], 6) ?></span>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="px-4 py-3 text-sm text-slate-300">No rates configured by admin.</p>
            <?php endif; ?>
        </div>
        <div class="mt-4 w-full overflow-x-auto rounded-xl border border-brand-border">
            <table class="w-full min-w-[280px] table-fixed text-sm">
                <thead class="bg-brand-light/50">
                    <tr class="text-left text-[11px] uppercase tracking-wide text-brand-muted">
                        <th class="px-3 py-2">From</th>
                        <th class="px-3 py-2">To</th>
                        <th class="px-3 py-2">Rate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ratesPreview as $rate): ?>
                        <tr class="border-t border-brand-border/70">
                            <td class="px-3 py-2 font-semibold text-brand-navy"><?= htmlspecialchars((string)$rate['from_code']) ?></td>
                            <td class="px-3 py-2 text-brand-navy"><?= htmlspecialchars((string)$rate['to_code']) ?></td>
                            <td class="px-3 py-2 font-medium text-emerald-700"><?= number_format((float)$rate['rate'], 8) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($rates)): ?>
                        <tr>
                            <td class="px-3 py-3 text-brand-muted" colspan="3">No rates configured by admin.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if (!empty($ratesExtra)): ?>
            <div class="mt-3 text-right">
                <button type="button" onclick="document.getElementById('ratesModal').classList.remove('hidden')"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-brand-border px-3 py-1.5 text-xs font-semibold text-brand-navy hover:bg-brand-light transition-colors">
                    See More (<?= count($ratesExtra) ?> more)
                </button>
            </div>
        <?php endif; ?>
    </div>

    <div class="min-w-0 rounded-2xl border border-brand-border bg-white p-5 shadow-sm flex flex-col">
        <div>
            <h2 class="text-lg font-semibold text-brand-navy">Exchange</h2>
            <p class="mt-0.5 text-xs text-brand-muted">Convert between your currency accounts.</p>
        </div>

        <form method="post" id="exchangeForm" class="mt-5 flex flex-col gap-4 flex-1">
            <input type="hidden" name="wallet_exchange" value="1">

            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-brand-muted">From</label>
                <div class="relative" id="fromWrap">
                    <button type="button" id="fromBtn"
                        onclick="exchToggle('fromList','toList')"
                        class="w-full flex items-center gap-3 rounded-xl border-2 border-brand-border bg-white px-3 py-2.5 text-left hover:border-brand-navy outline-none transition-colors">
                        <img id="fromImg" src="" alt="" class="h-6 w-8 rounded flex-shrink-0 object-cover" style="display:none" onerror="this.style.display='none'">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-brand-navy leading-tight" id="fromCodeLabel">Select account</p>
                            <p class="text-[11px] text-brand-muted truncate" id="fropinLabel">Choose a source wallet</p>
                        </div>
                        <p class="text-[11px] font-semibold text-emerald-600 whitespace-nowrap ml-1" id="fromBalLabel"></p>
                        <svg class="h-4 w-4 text-brand-muted flex-shrink-0 ml-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div id="fromList" class="hidden absolute z-30 w-full mt-1.5 rounded-xl border border-brand-border bg-white shadow-xl overflow-hidden">
                        <div class="max-h-52 overflow-y-auto">
                            <?php foreach ($wallets as $w):
                                $wc = htmlspecialchars((string)$w['currency_code']);
                                $wn = htmlspecialchars((string)($w['name'] ?: $w['currency_code']));
                                $wb = number_format((float)$w['balance'], (int)$w['is_crypto'] === 1 ? 8 : 2);
                            ?>
                                <button type="button"
                                    class="w-full flex items-center gap-3 px-4 py-2.5 hover:bg-brand-light text-left transition-colors border-b border-brand-border/40 last:border-0"
                                    onclick="exchPickFrom('<?= $wc ?>','<?= $wn ?>','<?= htmlspecialchars($wb) ?>')">
                                    <img src="flag-preview.php?code=<?= urlencode((string)$w['currency_code']) ?>" alt="" class="h-5 w-7 rounded flex-shrink-0 object-cover" onerror="this.style.display='none'">
                                    <span class="flex-1 min-w-0">
                                        <span class="block text-sm font-semibold text-brand-navy"><?= $wc ?></span>
                                        <span class="block text-[11px] text-brand-muted truncate"><?= $wn ?></span>
                                    </span>
                                    <span class="text-[11px] font-semibold text-emerald-600 whitespace-nowrap"><?= htmlspecialchars($wb) ?></span>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <input type="hidden" name="from_currency" id="fromInput" required>
                </div>
            </div>

            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-brand-muted">Amount</label>
                <div class="relative">
                    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm font-semibold text-brand-muted select-none" id="fromSymbolPrefix">—</span>
                    <input type="number" name="exchange_amount" id="exchangeAmt" min="0.01" step="0.00000001"
                        class="w-full rounded-xl border-2 border-brand-border pl-10 pr-3 py-2.5 text-sm font-semibold text-brand-navy focus:border-brand-navy outline-none transition-colors"
                        placeholder="0.00" required oninput="exchUpdateRate()">
                </div>
            </div>

            <div class="flex items-center gap-3 -my-1">
                <div class="flex-1 h-px bg-brand-border"></div>
                <button type="button" onclick="exchSwap()" title="Swap currencies"
                    class="flex-shrink-0 h-9 w-9 rounded-full border-2 border-brand-border bg-white flex items-center justify-center hover:border-brand-navy hover:bg-brand-light transition-colors shadow-sm">
                    <svg class="h-4 w-4 text-brand-navy" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0L3 8m4-4l4 4M17 8v12m0 0l4-4m-4 4l-4-4" />
                    </svg>
                </button>
                <div class="flex-1 h-px bg-brand-border"></div>
            </div>

            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-brand-muted">To</label>
                <div class="relative" id="toWrap">
                    <button type="button" id="toBtn"
                        onclick="exchToggle('toList','fromList')"
                        class="w-full flex items-center gap-3 rounded-xl border-2 border-brand-border bg-white px-3 py-2.5 text-left hover:border-brand-navy outline-none transition-colors">
                        <img id="toImg" src="" alt="" class="h-6 w-8 rounded flex-shrink-0 object-cover" style="display:none" onerror="this.style.display='none'">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-brand-navy leading-tight" id="toCodeLabel">Select currency</p>
                            <p class="text-[11px] text-brand-muted truncate" id="toNameLabel">Choose a destination</p>
                        </div>
                        <svg class="h-4 w-4 text-brand-muted flex-shrink-0 ml-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div id="toList" class="hidden absolute z-30 w-full mt-1.5 rounded-xl border border-brand-border bg-white shadow-xl overflow-hidden">
                        <div class="sticky top-0 bg-white border-b border-brand-border/40 px-3 py-2 z-10">
                            <input type="text" id="toCurrencySearch" placeholder="Search currency…"
                                class="w-full rounded-lg border border-brand-border px-2 py-1 text-xs outline-none"
                                oninput="exchFilterTo(this.value)">
                        </div>
                        <div id="toListItems" class="max-h-48 overflow-y-auto">
                            <?php foreach ($availableCurrencies as $cur):
                                $cc = htmlspecialchars((string)$cur['code']);
                                $cn = htmlspecialchars((string)$cur['name']);
                            ?>
                                <button type="button"
                                    class="exch-to-item w-full flex items-center gap-3 px-4 py-2.5 hover:bg-brand-light text-left transition-colors border-b border-brand-border/40 last:border-0"
                                    data-code="<?= $cc ?>" data-name="<?= $cn ?>"
                                    onclick="exchPickTo('<?= $cc ?>','<?= $cn ?>')">
                                    <img src="flag-preview.php?code=<?= urlencode((string)$cur['code']) ?>" alt="" class="h-5 w-7 rounded flex-shrink-0 object-cover" onerror="this.style.display='none'">
                                    <span class="flex-1 min-w-0">
                                        <span class="block text-sm font-semibold text-brand-navy"><?= $cc ?></span>
                                        <span class="block text-[11px] text-brand-muted truncate"><?= $cn ?></span>
                                    </span>
                                    <?php if ((int)$cur['is_crypto']): ?>
                                        <span class="text-[10px] bg-amber-100 text-amber-700 rounded-full px-1.5 py-0.5 flex-shrink-0">crypto</span>
                                    <?php endif; ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <input type="hidden" name="to_currency" id="toInput" required>
                </div>
            </div>

            <div id="ratePreviewBox" class="hidden rounded-xl bg-gradient-to-r from-brand-light to-blue-50 border border-brand-border px-4 py-3">
                <p class="text-[10px] font-bold uppercase tracking-widest text-brand-muted mb-1">Exchange Rate</p>
                <p id="ratePreviewText" class="text-sm font-bold text-brand-navy"></p>
                <p id="rateConvertedText" class="text-xs text-brand-muted mt-0.5"></p>
            </div>

            <button type="submit"
                class="mt-auto w-full rounded-xl bg-brand-navy px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-navy2 active:scale-95 transition-all flex items-center justify-center gap-2">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4M4 17h12m0 0l-4-4m4 4l-4 4" />
                </svg>
                Convert &amp; Move
            </button>
        </form>
    </div>
</section>

<!-- ═══ Card Profile / Products Summary / Promo ═══ -->
<section class="mt-6 grid gap-6 <?= $promoCardActive ? 'lg:grid-cols-3' : 'lg:grid-cols-2' ?>">
    <div class="rounded-2xl border border-brand-border bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-brand-navy">Card Profile</h2>
            <a href="cards.php" class="text-xs font-semibold text-brand-navy hover:underline">Open Card Center</a>
        </div>
        <p class="mt-1 text-xs text-brand-muted">Dedicated card center foundation for issuance tracking.</p>
        <div class="mt-4 space-y-3 text-sm">
            <div class="flex items-center justify-between border border-brand-border rounded-lg p-3">
                <span class="text-brand-muted">Card Number</span>
                <span class="font-semibold text-brand-navy"><?= htmlspecialchars($cardMasked) ?></span>
            </div>
            <div class="flex items-center justify-between border border-brand-border rounded-lg p-3">
                <span class="text-brand-muted">Card Status</span>
                <span class="rounded-full px-2 py-0.5 text-xs font-semibold <?= trim((string)($row['ccard'] ?? '')) !== '' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' ?>"><?= trim((string)($row['ccard'] ?? '')) !== '' ? 'Issued' : 'Pending Issuance' ?></span>
            </div>
            <div class="flex items-center justify-between border border-brand-border rounded-lg p-3">
                <span class="text-brand-muted">Expiry</span>
                <span class="font-semibold text-brand-navy"><?= htmlspecialchars($cardExpiry) ?></span>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-brand-border">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-semibold text-brand-muted uppercase">Loan Summary</p>
                <a href="loan.php" class="text-xs font-semibold text-brand-navy hover:underline">Details →</a>
            </div>
            <div class="grid grid-cols-2 gap-2 text-xs">
                <div class="rounded-lg bg-brand-light/40 p-2">
                    <p class="text-brand-muted uppercase text-[10px]">Submitted</p>
                    <p class="mt-0.5 font-bold text-brand-navy"><?= (int)$loanSummary['submitted'] ?></p>
                </div>
                <div class="rounded-lg bg-amber-50 p-2">
                    <p class="text-amber-700 uppercase text-[10px]">Under Review</p>
                    <p class="mt-0.5 font-bold text-amber-700"><?= (int)$loanSummary['under_review'] ?></p>
                </div>
                <div class="rounded-lg bg-green-50 p-2">
                    <p class="text-green-700 uppercase text-[10px]">Approved</p>
                    <p class="mt-0.5 font-bold text-green-700"><?= (int)$loanSummary['approved'] ?></p>
                </div>
                <div class="rounded-lg bg-red-50 p-2">
                    <p class="text-red-700 uppercase text-[10px]">Rejected</p>
                    <p class="mt-0.5 font-bold text-red-700"><?= (int)$loanSummary['rejected'] ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-brand-border bg-white p-5 shadow-sm overflow-x-auto">
        <h2 class="text-lg font-semibold text-brand-navy mb-4">Products Summary</h2>
        <table class="w-full text-sm">
            <tbody class="divide-y divide-brand-border">
                <!-- Term Deposits -->
                <tr>
                    <td class="py-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-semibold text-brand-navy">Term Deposits</p>
                                <p class="text-xs text-brand-muted">Active Placements</p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-brand-navy"><?= (int)$tdSummary['active_count'] ?></p>
                                <p class="text-xs text-brand-muted"><?= htmlspecialchars($displayCurrency) ?> <?= number_format((float)$tdSummary['active_value'], 2) ?></p>
                            </div>
                        </div>
                        <div class="mt-1 text-xs text-amber-600">Matured: <?= (int)$tdSummary['matured_count'] ?></div>
                        <a href="term-deposits.php" class="text-xs text-brand-navy font-semibold hover:underline mt-1 inline-block">View →</a>
                    </td>
                </tr>
                <!-- Investments -->
                <tr>
                    <td class="py-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-semibold text-brand-navy">Investments</p>
                                <p class="text-xs text-brand-muted">Positions</p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-brand-navy"><?= (int)$investSummary['position_count'] ?></p>
                                <p class="text-xs text-brand-muted"><?= htmlspecialchars($displayCurrency) ?> <?= number_format((float)$investSummary['market_value'], 2) ?></p>
                            </div>
                        </div>
                        <div class="mt-1 text-xs text-brand-muted">Account: <?= $investSummary['has_account'] ? htmlspecialchars($investSummary['account_ref']) : 'Not opened' ?></div>
                        <a href="investments.php" class="text-xs text-brand-navy font-semibold hover:underline mt-1 inline-block">Manage →</a>
                    </td>
                </tr>
                <!-- Robo Advisory -->
                <tr>
                    <td class="py-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-semibold text-brand-navy">Robo Advisory</p>
                                <p class="text-xs text-brand-muted">Risk Band</p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold <?= $roboSummary['enabled'] ? 'text-green-700' : 'text-amber-700' ?>"><?= $roboSummary['enabled'] ? 'Enabled' : 'Pending' ?></p>
                                <p class="text-xs text-brand-muted"><?= htmlspecialchars(strtoupper((string)$roboSummary['risk_band'])) ?></p>
                            </div>
                        </div>
                        <div class="mt-1 text-xs text-brand-muted">Model: <?= htmlspecialchars((string)$roboSummary['model_name']) ?></div>
                        <a href="robo.php" class="text-xs text-brand-navy font-semibold hover:underline mt-1 inline-block">Configure →</a>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="mt-4 border-t border-brand-border pt-3">
            <p class="text-xs text-brand-muted">
                Need help choosing the right investment strategy?
                <a href="ticket.php" class="font-semibold text-brand-navy hover:underline">Ask for investment advice</a>.
            </p>
        </div>
    </div>

    <?php if ($promoCardActive): ?>
        <div class="rounded-2xl border border-brand-border bg-white p-5 shadow-sm overflow-hidden flex flex-col">
            <?php if (!empty($promoImageUrl)): ?>
                <div class="-mx-5 -mt-5 mb-4 overflow-hidden bg-brand-light" style="aspect-ratio:1/1">
                    <img src="<?= htmlspecialchars($promoImageUrl) ?>" alt="Promo" class="h-full w-full object-cover">
                </div>
            <?php endif; ?>
            <?php if (!empty($promoHeadline)): ?>
                <h2 class="text-base font-bold text-brand-navy leading-snug"><?= htmlspecialchars($promoHeadline) ?></h2>
            <?php endif; ?>
            <?php if (!empty($promoBodyText)): ?>
                <p class="mt-2 text-xs text-brand-muted leading-relaxed flex-1"><?= nl2br(htmlspecialchars($promoBodyText)) ?></p>
            <?php endif; ?>
            <?php if (!empty($promoBtnLabel) && !empty($promoBtnUrl)): ?>
                <a href="<?= htmlspecialchars($promoBtnUrl) ?>" target="_blank" rel="noopener noreferrer"
                    class="mt-4 inline-flex items-center justify-center rounded-xl bg-brand-navy px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-navy2 transition-colors">
                    <?= htmlspecialchars($promoBtnLabel) ?>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>

<section class="mt-6 rounded-2xl border border-brand-border bg-white shadow-sm overflow-hidden">
    <div class="px-5 pt-5 pb-3 flex items-center justify-between">
        <h2 class="text-lg font-semibold text-brand-navy">Recent Transactions</h2>
        <a href="statement.php" class="text-xs font-medium text-brand-muted hover:text-brand-navy transition-colors">View all →</a>
    </div>
    <?php if (empty($unifiedActivity)): ?>
        <div class="px-5 pb-8 text-center text-sm text-brand-muted">No recent activity found.</div>
    <?php else: ?>
        <div class="divide-y divide-brand-border">
            <?php foreach ($unifiedActivity as $act): ?>
                <?php $isDebit = $act['direction'] === 'debit'; ?>
                <div class="flex items-center gap-3 px-5 py-3.5 hover:bg-brand-light/40 transition-colors">
                    <div class="flex-shrink-0 w-9 h-9 rounded-full flex items-center justify-center <?= $isDebit ? 'bg-red-50' : 'bg-emerald-50' ?>">
                        <?php if ($isDebit): ?>
                            <svg class="w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                            </svg>
                        <?php else: ?>
                            <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                            </svg>
                        <?php endif; ?>
                    </div>
                    <span class="flex-shrink-0 inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600"><?= htmlspecialchars($act['currency']) ?></span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-brand-navy truncate"><?= htmlspecialchars($act['description']) ?></p>
                        <p class="text-xs text-brand-muted mt-0.5"><?= htmlspecialchars($act['date_str']) ?></p>
                    </div>
                    <div class="flex-shrink-0 text-right">
                        <p class="text-sm font-bold <?= $isDebit ? 'text-red-600' : 'text-emerald-600' ?> whitespace-nowrap">
                            <?= $isDebit ? '−' : '+' ?><?= htmlspecialchars($act['currency']) ?>&nbsp;<?= number_format($act['amount'], 2) ?>
                        </p>
                        <span class="inline-block mt-0.5 rounded-full px-2 py-0.5 text-xs font-medium <?= $isDebit ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-700' ?>">
                            <?= htmlspecialchars($act['type_label']) ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php if (!empty($ratesExtra)): ?>
    <div id="ratesModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4" onclick="if(event.target===this)this.classList.add('hidden')">
        <div class="relative w-full max-w-lg rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-brand-border px-5 py-4">
                <h3 class="text-base font-semibold text-brand-navy"><?= htmlspecialchars($bankName) ?> — All Exchange Rates</h3>
                <button type="button" onclick="document.getElementById('ratesModal').classList.add('hidden')"
                    class="rounded-lg p-1 text-brand-muted hover:bg-brand-light transition-colors">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="max-h-[60vh] overflow-y-auto px-5 pb-5">
                <table class="min-w-full text-sm">
                    <thead class="sticky top-0 bg-white">
                        <tr class="border-b border-brand-border text-left text-xs uppercase tracking-wide text-brand-muted">
                            <th class="py-2">From</th>
                            <th class="py-2">To</th>
                            <th class="py-2">Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rates as $rate): ?>
                            <tr class="border-b border-brand-border/70">
                                <td class="py-2"><?= htmlspecialchars((string)$rate['from_code']) ?></td>
                                <td class="py-2"><?= htmlspecialchars((string)$rate['to_code']) ?></td>
                                <td class="py-2"><?= number_format((float)$rate['rate'], 8) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

</div>
</main>

<?php require_once __DIR__ . '/partials/shell-close.php';
exit(); ?>