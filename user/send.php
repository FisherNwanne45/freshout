<?php
session_start();
include_once 'session.php';
require_once 'class.user.php';
require_once '../config.php';
require_once __DIR__ . '/partials/auto-migrate.php';

if (!isset($_SESSION['acc_no'])) {
  header('Location: login.php');
  exit();
}
if (!isset($_SESSION['pin_verified']) && !isset($_SESSION['pin'])) {
  header('Location: passcode.php');
  exit();
}

$reg_user = new USER();
$accNo    = (string)$_SESSION['acc_no'];

$stmt = $reg_user->runQuery('SELECT * FROM account WHERE acc_no = :acc_no LIMIT 1');
$stmt->execute([':acc_no' => $accNo]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) {
  header('Location: logout.php');
  exit();
}

$statLower = strtolower(trim($row['status'] ?? ''));
if ($statLower === 'dormant/inactive') {
  header('Location: index.php?dormant');
  exit();
}

// Detect auth method
$authMethod = $row['auth_method'] ?? null;
if (!$authMethod) {
  if ($statLower === 'pincode') $authMethod = 'pin';
  elseif ($statLower === 'otp') $authMethod = 'otp';
  else $authMethod = 'codes';
}

// Load wallets: account_balances is the authoritative balance source.
// customer_accounts provides only metadata (account_no, iban, status).
$walletsRes = $reg_user->runQuery(
  'SELECT ab.currency_code, ab.balance,
          COALESCE(ca.account_no, CONCAT(:acc_no_pfx, \'-\', ab.currency_code)) AS account_no,
          COALESCE(ca.iban, \'\') AS iban,
          c.symbol, c.name AS cur_name, c.is_crypto, c.flag_code,
          COALESCE(ca.is_primary, 0) AS is_primary
   FROM account_balances ab
   LEFT JOIN customer_accounts ca
          ON ca.owner_acc_no = ab.acc_no
         AND ca.currency_code = ab.currency_code
         AND ca.status = \'active\'
   LEFT JOIN currencies c ON c.code = ab.currency_code
   WHERE ab.acc_no = :acc_no
   ORDER BY COALESCE(ca.is_primary, 0) DESC, c.is_crypto, c.sort_order, ab.currency_code'
);
$walletsRes->execute([':acc_no' => $accNo, ':acc_no_pfx' => $accNo]);
$wallets = $walletsRes->fetchAll(PDO::FETCH_ASSOC);

if (empty($wallets)) {
  $wallets = [[
    'account_no'    => $accNo . '-' . strtoupper((string)($row['currency'] ?? 'USD')),
    'currency_code' => $row['currency'] ?? 'USD',
    'balance'       => $row['t_bal']   ?? 0,
    'symbol'        => $row['currency'] ?? '$',
    'cur_name'      => 'Primary Account',
    'is_crypto'     => 0,
    'flag_code'     => null,
    'iban'          => '',
    'is_primary'    => 1,
  ]];
}

// ── Ensure beneficiaries table exists ─────────────────────────────────────────
try {
  $reg_user->runQuery("CREATE TABLE IF NOT EXISTS `beneficiaries` (
        `id`             INT AUTO_INCREMENT PRIMARY KEY,
        `acc_no`         VARCHAR(50)   NOT NULL,
        `nick_name`      VARCHAR(100)  NOT NULL,
        `bank_name`      VARCHAR(150)  NOT NULL,
        `account_number` VARCHAR(60)   NOT NULL,
        `swift`          VARCHAR(30)   DEFAULT NULL,
        `routing`        VARCHAR(30)   DEFAULT NULL,
        `created_at`     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        KEY `idx_bene_acc` (`acc_no`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4")->execute();
} catch (Throwable $e) {
}

// ── AJAX: beneficiary CRUD ─────────────────────────────────────────────────────
if (isset($_POST['bene_action'])) {
  header('Content-Type: application/json; charset=utf-8');
  $san = fn(string $v): string => trim(htmlspecialchars(strip_tags($v), ENT_QUOTES, 'UTF-8'));
  if ($_POST['bene_action'] === 'add') {
    $nick = $san((string)($_POST['nick_name']      ?? ''));
    $bank = $san((string)($_POST['bank_name']       ?? ''));
    $acct = $san((string)($_POST['account_number']  ?? ''));
    $swft = $san((string)($_POST['swift']            ?? ''));
    $rout = $san((string)($_POST['routing']          ?? ''));
    if ($nick === '' || $bank === '' || $acct === '') {
      echo json_encode(['ok' => false, 'msg' => 'Name, bank and account number are required.']);
      exit();
    }
    try {
      $ins = $reg_user->runQuery(
        'INSERT INTO beneficiaries (acc_no,nick_name,bank_name,account_number,swift,routing)
                 VALUES (:a,:n,:b,:ac,:s,:r)'
      );
      $ins->execute([
        ':a' => $accNo,
        ':n' => $nick,
        ':b' => $bank,
        ':ac' => $acct,
        ':s' => ($swft ?: null),
        ':r' => ($rout ?: null)
      ]);
      $newId = (int)$reg_user->lasdID();
      echo json_encode([
        'ok' => true,
        'id' => $newId,
        'nick_name' => $nick,
        'bank_name' => $bank,
        'account_number' => $acct,
        'swift' => $swft,
        'routing' => $rout
      ]);
      exit();
    } catch (Throwable $e) {
      echo json_encode(['ok' => false, 'msg' => 'Database error.']);
    }
    // ── PDO migration: ensure customer_accounts.iban column exists ───────────────
    // auto-migrate.php uses mysqli; for PDO paths we handle it here safely.
    try {
      $reg_user->runQuery(
        'ALTER TABLE customer_accounts ADD COLUMN iban VARCHAR(34) NULL DEFAULT NULL'
      )->execute();
    } catch (Throwable $e) {
      // Either the column already exists (expected) or table missing – both are fine.
    }

    // ── AJAX: beneficiary CRUD ─────────────────────────────────────────────────────
  }
  if ($_POST['bene_action'] === 'delete') {
    $delId = (int)($_POST['id'] ?? 0);
    if ($delId > 0) {
      try {
        $del = $reg_user->runQuery('DELETE FROM beneficiaries WHERE id=:id AND acc_no=:a');
        $del->execute([':id' => $delId, ':a' => $accNo]);
      } catch (Throwable $e) {
      }
    }
    echo json_encode(['ok' => true]);
    exit();
  }
  echo json_encode(['ok' => false, 'msg' => 'Unknown action']);
  exit();
}

// ── AJAX: same-bank account validation ────────────────────────────────────────
if (isset($_POST['samebank_check_acct'])) {
  header('Content-Type: application/json; charset=utf-8');
  $lookupRaw = strtoupper(preg_replace('/[^A-Z0-9\-]/', '', (string)($_POST['account_number'] ?? '')));
  if ($lookupRaw === '') {
    echo json_encode(['valid' => false]);
    exit();
  }
  try {
    $chk = $reg_user->runQuery(
      'SELECT ca.owner_acc_no
             FROM customer_accounts ca
             WHERE (ca.account_no = :lookup OR ca.iban = :lookup OR ca.owner_acc_no = :lookup)
               AND ca.status = :status
             LIMIT 1'
    );
    $chk->execute([':lookup' => $lookupRaw, ':status' => 'active']);
    $chkRow = $chk->fetch(PDO::FETCH_ASSOC);
    echo json_encode(['valid' => (bool)$chkRow]);
  } catch (Throwable $e) {
    echo json_encode(['valid' => true]); // fail-open; server-side will catch it
  }
  exit();
}

// ── Load saved beneficiaries ───────────────────────────────────────────────────
$beneficiaries = [];
try {
  $bq = $reg_user->runQuery('SELECT * FROM beneficiaries WHERE acc_no = :a ORDER BY nick_name ASC');
  $bq->execute([':a' => $accNo]);
  $beneficiaries = $bq->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
}

// ── World banks (used by datalist + beneficiary modal) ─────────────────────────
$worldBanks = [
  'ABN AMRO',
  'Access Bank',
  'Agricultural Bank of China',
  'AIB Group',
  'Al Rajhi Bank',
  'Ally Bank',
  'Alpha Bank',
  'American Express Bank',
  'ANZ',
  'Arab National Bank',
  'ASB Bank',
  'Attijariwafa Bank',
  'Axis Bank',
  'Banco BHD Leon',
  'Banco BPM',
  'Banco Bradesco',
  'Banco de Bogota',
  'Banco de la Nacion Argentina',
  'Banco de Reservas',
  'Banco do Brasil',
  'Banco Galicia',
  'Banco Guayaquil',
  'Banco Pichincha',
  'Banco Popular Dominicano',
  'Bancolombia',
  'Bangkok Bank',
  'Bank Hapoalim',
  'Bank Leumi',
  'Bank of America',
  'Bank of China',
  'Bank of Communications',
  'Bank of Ireland',
  'Bank of New Zealand',
  'Bank of New York Mellon',
  'Bank of the Philippine Islands',
  'Bank Pekao',
  'Bankinter',
  'Banque Populaire du Maroc',
  'Banque Saudi Fransi',
  'Banorte',
  'Barclays',
  'Bayerische Landesbank',
  'BBVA',
  'BBVA Argentina',
  'BBVA Mexico',
  'BMO Bank of Montreal',
  'BMCE Bank',
  'BNP Paribas',
  'BPER Banca',
  'BPCE Group',
  'BTG Pactual',
  'Caixa Economica Federal',
  'CaixaBank',
  'Capital One',
  'Capitec Bank',
  'Cathay United Bank',
  'Charles Schwab Bank',
  'CIBC',
  'CIMB Bank',
  'CITIC Bank',
  'Citibank',
  'Co-operative Bank of Kenya',
  'Commerzbank',
  'Commonwealth Bank',
  'Commercial Bank of Qatar',
  'Credit Agricole',
  'Credit Mutuel',
  'CTBC Bank',
  'Davivienda',
  'DBS Bank',
  'Deutsche Bank',
  'Discover Bank',
  'DNB Bank',
  'DZ Bank',
  'Ecobank',
  'Emirates NBD',
  'Equity Bank',
  'Erste Group Bank',
  'Eurobank',
  'First Abu Dhabi Bank',
  'First Bank of Nigeria',
  'FirstRand Bank',
  'Goldman Sachs',
  'Guaranty Trust Bank',
  'Gulf Bank',
  'Grupo Bancolombia',
  'Hana Financial Group',
  'HDFC Bank',
  'HSBC',
  'HSBC Mexico',
  'Huntington National Bank',
  'ICBC',
  'ICICI Bank',
  'ING Group',
  'Intesa Sanpaolo',
  'Israel Discount Bank',
  'Itau Unibanco',
  'Japan Post Bank',
  'JPMorgan Chase',
  'Julius Baer',
  'Kasikorn Bank',
  'KCB Group',
  'KB Financial Group',
  'KeyBank',
  'Kiwibank',
  'KfW',
  'Kotak Mahindra Bank',
  'Krungthai Bank',
  'Kuwait Finance House',
  'Lloyds Bank',
  'Macquarie Bank',
  'Mashreq Bank',
  'Maybank',
  'mBank',
  'Mega International Commercial Bank',
  'Metro Bank',
  'Metrobank',
  'Mizuho Financial Group',
  'Monte dei Paschi di Siena',
  'Morgan Stanley',
  'National Australia Bank',
  'National Bank of Canada',
  'National Bank of Greece',
  'National Bank of Kuwait',
  'National Commercial Bank',
  'NatWest',
  'NCBA Bank',
  'Nedbank',
  'Nordea Bank',
  'OCBC Bank',
  'OP Financial Group',
  'Permanent TSB',
  'Ping An Bank',
  'PKO Bank Polski',
  'PNC Bank',
  'Public Bank Berhad',
  'Qatar Islamic Bank',
  'Qatar National Bank',
  'Rabobank',
  'Raiffeisen Bank International',
  'Raiffeisen Switzerland',
  'Regions Bank',
  'Resona Bank',
  'RHB Bank',
  'Riyad Bank',
  'Royal Bank of Canada',
  'Sabadell',
  'Santander',
  'Santander Argentina',
  'Santander Bank Polska',
  'Santander Mexico',
  'Santander UK',
  'Saudi National Bank',
  'Scotiabank',
  'SEB',
  'Siam Commercial Bank',
  'Shinhan Financial Group',
  'Societe Generale',
  'Standard Bank',
  'Standard Chartered',
  'State Bank of India',
  'Stanbic Bank',
  'Sumitomo Mitsui Banking Corporation',
  'Svenska Handelsbanken',
  'Swedbank',
  'Synchrony Bank',
  'TD Bank',
  'TD Canada Trust',
  'TSB Bank',
  'Truist Bank',
  'UniCredit',
  'United Bank for Africa',
  'United Overseas Bank',
  'UnionBank of the Philippines',
  'UBS',
  'US Bank',
  'Virgin Money',
  'Volksbank',
  'Westpac',
  'Wells Fargo',
  'Woori Bank',
  'Zenith Bank',
  'Zenith Bank Ghana',
  'Zurcher Kantonalbank',
];

$flashError = '';

// Handle redirect back from auth pages when same-bank account re-validation fails
if (!empty($_GET['samebank_invalid'])) {
  $flashError = 'The recipient account is not registered with this bank. Please verify the account number or switch to Domestic / International transfer.';
}

// ── Handle form submission ───────────────────────────────────────────────────
if (isset($_POST['transfer'])) {
  $email        = $row['email'];
  $transferType = strtolower(trim((string)($_POST['transfer_type'] ?? 'domestic')));
  if ($transferType === 'interbank') {
    // Backward compatibility for older UI values
    $transferType = 'samebank';
  }
  $currencyCode = trim($_POST['currency_code'] ?? ($row['currency'] ?? 'USD'));
  $sourceAccountNo = trim((string)($_POST['source_account_no'] ?? ''));

  $sanitize = function (string $v): string {
    return htmlspecialchars(strip_tags(trim($v)), ENT_QUOTES, 'UTF-8');
  };

  $amount   = $sanitize($_POST['amount']    ?? '');
  $accNoRRaw = trim((string)($_POST['acc_no'] ?? ''));
  $accNoR   = $sanitize($accNoRRaw);
  $accName  = $sanitize($_POST['acc_name']  ?? ($_POST['crypto_label'] ?? ''));
  $bankName = $sanitize($_POST['bank_name'] ?? '');
  $swift    = $sanitize($_POST['swift']     ?? '');
  $routing  = $sanitize($_POST['routing']   ?? '');
  $remarks  = $sanitize($_POST['remarks']   ?? '');
  $destinationAccountNo = null;

  if ($sourceAccountNo === '') {
    $sourceAccountNo = (string)($wallets[0]['account_no'] ?? '');
  }

  $sourceAcctStmt = $reg_user->runQuery(
    'SELECT account_no, currency_code, balance
       FROM customer_accounts
       WHERE owner_acc_no = :owner_acc_no AND account_no = :account_no AND status = :status
       LIMIT 1'
  );
  $sourceAcctStmt->execute([
    ':owner_acc_no' => $accNo,
    ':account_no' => $sourceAccountNo,
    ':status' => 'active',
  ]);
  $sourceAcct = $sourceAcctStmt->fetch(PDO::FETCH_ASSOC);

  if (!$sourceAcct) {
    $flashError = 'Invalid source account selected.';
  } else {
    $currencyCode = strtoupper((string)($sourceAcct['currency_code'] ?? $currencyCode));
  }

  // For crypto: reuse swift/bank_name fields for wallet_address/network
  if ($transferType === 'crypto') {
    $accName  = $sanitize($_POST['crypto_label'] ?? $accName);
    $accName  = $accName  ?: 'Crypto Transfer';
    $bankName = $sanitize($_POST['network'] ?? '');
    $swift    = $sanitize($_POST['wallet_address'] ?? '');
    $routing  = '';
  }

  if ($flashError === '' && ($transferType === 'samebank' || $transferType === 'domestic')) {
    $requiresInternalRecipient = ($transferType === 'samebank');

    if ($requiresInternalRecipient && $accNoR === '') {
      $flashError = 'Recipient Account ID / Account Number is required for Same Bank transfer.';
    }

    $destLookupRaw = strtoupper(preg_replace('/\s+/', '', $accNoRRaw));
    $destLookupRaw = preg_replace('/[^A-Z0-9\-]/', '', $destLookupRaw);
    if ($requiresInternalRecipient && $flashError === '' && $destLookupRaw === '') {
      $flashError = 'Enter a valid destination account identifier.';
    }

    // Domestic transfers may target external banks; only enforce this lookup for same-bank.
    if ($flashError === '' && $destLookupRaw !== '') {
      try {
        $destAcctStmt = $reg_user->runQuery(
          'SELECT ca.owner_acc_no, ca.account_no, ca.currency_code, ca.iban, a.fname, a.lname
                 FROM customer_accounts ca
                 LEFT JOIN account a ON a.acc_no = ca.owner_acc_no
                 WHERE (ca.account_no = :lookup OR ca.iban = :lookup OR ca.owner_acc_no = :lookup)
                   AND ca.status = :status
                 LIMIT 1'
        );
        $destAcctStmt->execute([
          ':lookup' => $destLookupRaw,
          ':status' => 'active',
        ]);
        $destAcct = $destAcctStmt->fetch(PDO::FETCH_ASSOC);
      } catch (Throwable $e) {
        $destAcct = false;
      }

      if (!$destAcct) {
        if ($requiresInternalRecipient) {
          $flashError = 'No active in-bank recipient account found for the provided identifier.';
        }
      } else {
        $destAccountNo = (string)($destAcct['account_no'] ?? '');
        $destCurrency  = strtoupper((string)($destAcct['currency_code'] ?? ''));
        $destOwner     = (string)($destAcct['owner_acc_no'] ?? '');

        if ($destAccountNo === '' || $destCurrency === '') {
          if ($requiresInternalRecipient) {
            $flashError = 'Destination account record is incomplete.';
          }
        } elseif (strcasecmp($destAccountNo, $sourceAccountNo) === 0 && strcasecmp($destOwner, $accNo) === 0) {
          $flashError = 'Source and destination accounts must be different.';
        } elseif ($requiresInternalRecipient && $destCurrency !== strtoupper((string)$currencyCode)) {
          $flashError = 'Same Bank transfer requires a destination account in ' . strtoupper((string)$currencyCode) . '.';
        } else {
          // Enable internal top-up/credit flow when domestic identifier maps to an in-bank account.
          $destinationAccountNo = $destAccountNo;
          $accNoR = $destAccountNo;
          if ($requiresInternalRecipient) {
            $bankName = 'Same Bank Transfer';
          }
          if ($accName === '') {
            $accName = trim((string)($destAcct['fname'] ?? '') . ' ' . (string)($destAcct['lname'] ?? ''));
            if ($accName === '') {
              $accName = $requiresInternalRecipient ? 'Same Bank Beneficiary' : 'Domestic Beneficiary';
            }
          }
        }
      }
    }
  }

  if ($flashError === '' && (float)$amount <= 0) {
    $flashError = 'Please enter a valid amount.';
  } elseif ($flashError === '' && $sourceAcct && (float)$sourceAcct['balance'] < (float)$amount) {
    $flashError = 'Insufficient balance in selected source account.';
  } else {
    if ($flashError === '' && $reg_user->temp($email, $amount, $accNoR, $accName, $bankName, $swift, $routing, $transferType, $remarks)) {
      // Update the two new columns on the just-inserted row
      try {
        $lastId = $reg_user->lasdID();
        $upd = $reg_user->runQuery(
          "UPDATE temp_transfer
               SET currency_code = :cc,
                 transfer_type = :tt,
                 source_account_no = :source_account_no,
                 destination_account_no = :destination_account_no
               WHERE id = :id"
        );
        $upd->execute([
          ':cc' => $currencyCode,
          ':tt' => $transferType,
          ':source_account_no' => $sourceAccountNo,
          ':destination_account_no' => $destinationAccountNo,
          ':id' => $lastId,
        ]);
        $_SESSION['auth_step'] = 1;
        $_SESSION['auth_transfer_id'] = (int)$lastId;
      } catch (Throwable $e) {
      }

      header('Location: transfer-auth.php');
      exit();
    } elseif ($flashError === '') {
      $flashError = 'Could not save transfer. Please try again.';
    }
  }
}

// Organise wallets into fiat / crypto groups
$fiatWallets   = array_filter($wallets, fn($w) => !($w['is_crypto'] ?? 0));
$cryptoWallets = array_filter($wallets, fn($w)  => (bool)($w['is_crypto'] ?? 0));

// Icon helper: returns a data-URI src for crypto (coloured badge) or fiat (local SVG flag).
function sendFlagSrc(array $w): string
{
  $code    = strtoupper(trim((string)($w['currency_code'] ?? '')));
  $isCrypto = (bool)($w['is_crypto'] ?? 0);

  // ── Crypto: coloured inline SVG badge ──────────────────────────────────
  if ($isCrypto) {
    $cryptoBadges = [
      'BTC'  => ['bg' => '#f7931a', 'fg' => '#ffffff'],
      'ETH'  => ['bg' => '#627eea', 'fg' => '#ffffff'],
      'USDT' => ['bg' => '#26a17b', 'fg' => '#ffffff'],
      'USDC' => ['bg' => '#2775ca', 'fg' => '#ffffff'],
      'XRP'  => ['bg' => '#23292f', 'fg' => '#ffffff'],
      'LTC'  => ['bg' => '#345d9d', 'fg' => '#ffffff'],
      'BNB'  => ['bg' => '#f3ba2f', 'fg' => '#111827'],
      'SOL'  => ['bg' => '#111827', 'fg' => '#6dffa7'],
      'ADA'  => ['bg' => '#0033ad', 'fg' => '#ffffff'],
      'DOT'  => ['bg' => '#e6007a', 'fg' => '#ffffff'],
      'DOGE' => ['bg' => '#c3a634', 'fg' => '#ffffff'],
      'TRX'  => ['bg' => '#ef0027', 'fg' => '#ffffff'],
      'MATIC' => ['bg' => '#8247e5', 'fg' => '#ffffff'],
      'XLM'  => ['bg' => '#3e1bdb', 'fg' => '#ffffff'],
    ];
    $c   = $cryptoBadges[$code] ?? ['bg' => '#64748b', 'fg' => '#ffffff'];
    $lbl = htmlspecialchars(strlen($code) > 4 ? substr($code, 0, 4) : $code, ENT_QUOTES, 'UTF-8');
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="28" viewBox="0 0 40 28">'
      . '<rect x="1" y="1" width="38" height="26" rx="5" fill="' . $c['bg'] . '" stroke="#cbd5e1" stroke-width="0.5"/>'
      . '<text x="20" y="18" text-anchor="middle" fill="' . $c['fg'] . '" font-size="8.5" font-family="Arial,sans-serif" font-weight="700">' . $lbl . '</text>'
      . '</svg>';
    return 'data:image/svg+xml;base64,' . base64_encode($svg);
  }

  // ── Fiat: try local flag SVG file ──────────────────────────────────────
  $fc   = strtolower(trim((string)($w['flag_code'] ?? $code)));
  $path = __DIR__ . '/assets/flags/' . $fc . '.svg';
  if ($fc !== '' && file_exists($path)) {
    return 'data:image/svg+xml;base64,' . base64_encode(file_get_contents($path));
  }
  return '';
}

require_once __DIR__ . '/partials/shell-data.php';
$shellPageTitle = 'Send / Transfer';
require_once __DIR__ . '/partials/shell-open.php';
?>

<?php if ($flashError): ?>
  <div class="mb-5 rounded-xl p-4 bg-red-50 border border-red-200 text-red-800 text-sm flex items-center gap-3">
    <svg class="w-4 h-4 flex-shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
    </svg>
    <?= htmlspecialchars($flashError) ?>
  </div>
<?php endif; ?>

<style>
  @keyframes wizardCardIn {
    from {
      opacity: 0;
      transform: translateY(18px) scale(0.985);
    }

    to {
      opacity: 1;
      transform: translateY(0) scale(1);
    }
  }

  @keyframes stepIn {
    from {
      opacity: 0;
      transform: translateY(12px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  @keyframes itemIn {
    from {
      opacity: 0;
      transform: translateY(8px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  #sendForm {
    animation: wizardCardIn 380ms cubic-bezier(0.2, 0.8, 0.2, 1);
  }

  .wizard-step-enter {
    animation: stepIn 280ms ease-out;
  }

  .wallet-card,
  .bene-card,
  #typeTabs .type-tab {
    animation: itemIn 320ms ease-out both;
  }

  #walletCards .wallet-card:nth-child(1),
  #beneGrid .bene-card:nth-child(1) {
    animation-delay: 20ms;
  }

  #walletCards .wallet-card:nth-child(2),
  #beneGrid .bene-card:nth-child(2) {
    animation-delay: 40ms;
  }

  #walletCards .wallet-card:nth-child(3),
  #beneGrid .bene-card:nth-child(3) {
    animation-delay: 60ms;
  }

  #walletCards .wallet-card:nth-child(4),
  #beneGrid .bene-card:nth-child(4) {
    animation-delay: 80ms;
  }

  #walletCards .wallet-card:nth-child(5),
  #beneGrid .bene-card:nth-child(5) {
    animation-delay: 100ms;
  }

  #walletCards .wallet-card:nth-child(6),
  #beneGrid .bene-card:nth-child(6) {
    animation-delay: 120ms;
  }

  .wizard-dot {
    transition: all 220ms ease;
  }

  #wizardBar {
    transition: width 320ms cubic-bezier(0.2, 0.8, 0.2, 1);
  }
</style>

<section class="relative overflow-hidden rounded-[2rem] border border-brand-border/60 bg-gradient-to-br from-slate-50 via-white to-brand-light/40 p-6 md:p-10">
  <div class="pointer-events-none absolute -top-20 -left-16 h-56 w-56 rounded-full bg-brand-navy/10 blur-3xl"></div>
  <div class="pointer-events-none absolute -bottom-20 -right-16 h-56 w-56 rounded-full bg-cyan-200/40 blur-3xl"></div>

  <div class="relative mx-auto max-w-5xl">
    <div class="mb-8 text-center">
      <p class="text-xs uppercase tracking-[0.25em] text-brand-muted">Smart Transfer</p>
      <h1 class="mt-2 text-3xl md:text-4xl font-black text-brand-navy">Send Money In 4 Seamless Steps</h1>
      <p class="mt-2 text-sm md:text-base text-brand-muted">A guided flow from wallet selection to transfer review before authentication.</p>
    </div>

    <form method="POST" action="" id="sendForm" class="mx-auto max-w-4xl rounded-3xl border border-brand-border bg-white/95 shadow-[0_30px_90px_-40px_rgba(15,23,42,0.5)] backdrop-blur">
      <input type="hidden" name="transfer" value="1">
      <input type="hidden" name="transfer_type" id="hiddenTransferType" value="domestic">
      <input type="hidden" name="currency_code" id="hiddenCurrencyCode" value="<?= htmlspecialchars($wallets[0]['currency_code'] ?? 'USD') ?>">
      <input type="hidden" name="source_account_no" id="hiddenSourceAccountNo" value="<?= htmlspecialchars($wallets[0]['account_no'] ?? '') ?>">
      <input type="hidden" id="recipientMode" value="existing">

      <div class="border-b border-brand-border/70 px-5 py-4 md:px-8">
        <div class="mb-3 h-1.5 w-full overflow-hidden rounded-full bg-slate-200/80">
          <div id="wizardBar" class="h-full rounded-full bg-gradient-to-r from-brand-navy via-cyan-500 to-brand-navy" style="width:25%"></div>
        </div>
        <div class="grid grid-cols-2 gap-2 md:grid-cols-4 md:gap-3" id="wizardProgress">
          <button type="button" data-step-dot="1" class="wizard-dot rounded-2xl border border-brand-navy bg-brand-navy/10 px-3 py-2 text-left">
            <p class="text-[10px] uppercase tracking-wider text-brand-muted">Step 1</p>
            <p class="text-xs font-semibold text-brand-navy">Source Wallet</p>
          </button>
          <button type="button" data-step-dot="2" class="wizard-dot rounded-2xl border border-brand-border px-3 py-2 text-left">
            <p class="text-[10px] uppercase tracking-wider text-brand-muted">Step 2</p>
            <p class="text-xs font-semibold text-brand-muted">Transfer Type</p>
          </button>
          <button type="button" data-step-dot="3" class="wizard-dot rounded-2xl border border-brand-border px-3 py-2 text-left">
            <p class="text-[10px] uppercase tracking-wider text-brand-muted">Step 3</p>
            <p class="text-xs font-semibold text-brand-muted">Recipient</p>
          </button>
          <button type="button" data-step-dot="4" class="wizard-dot rounded-2xl border border-brand-border px-3 py-2 text-left">
            <p class="text-[10px] uppercase tracking-wider text-brand-muted">Step 4</p>
            <p class="text-xs font-semibold text-brand-muted">Details</p>
          </button>
        </div>
      </div>

      <div class="px-5 py-6 md:px-8 md:py-7">
        <div class="wizard-step" data-step="1">
          <div class="mb-4">
            <h2 class="text-lg font-bold text-brand-navy">Choose Source Wallet</h2>
            <p class="text-sm text-brand-muted">Pick the wallet you want to debit for this transfer.</p>
          </div>
          <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2.5" id="walletCards">
            <?php foreach ($wallets as $i => $w):
              $flagSrc  = sendFlagSrc($w);
              $isCrypto = (bool)($w['is_crypto'] ?? 0);
              $code     = htmlspecialchars($w['currency_code'] ?? '');
              $bal      = number_format((float)($w['balance'] ?? 0), $isCrypto ? 6 : 2);
              $sym      = htmlspecialchars($w['symbol'] ?? $code);
              $name     = htmlspecialchars($w['cur_name'] ?? $code);
            ?>
              <button type="button"
                class="wallet-card relative flex flex-col items-start gap-1.5 p-3.5 rounded-xl border-2 text-left transition-all
                     <?= $i === 0 ? 'border-brand-navy bg-brand-navy/5' : 'border-brand-border bg-white hover:border-brand-navy/30' ?>"
                data-code="<?= $code ?>" data-crypto="<?= $isCrypto ? '1' : '0' ?>" data-account="<?= htmlspecialchars((string)($w['account_no'] ?? '')) ?>"
                onclick="selectWallet(this)">
                <?php if ($flagSrc): ?>
                  <img src="<?= $flagSrc ?>" class="w-8 h-5 rounded object-cover shadow-sm" alt="<?= $code ?>">
                <?php else: ?>
                  <span class="w-8 h-5 rounded bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-500"><?= substr($code, 0, 3) ?></span>
                <?php endif; ?>
                <div>
                  <p class="text-xs font-bold text-brand-navy leading-none"><?= $code ?></p>
                  <p class="text-xs text-brand-muted mt-0.5 leading-none"><?= $name ?></p>
                </div>
                <p class="text-sm font-bold text-gray-900 mt-auto"><?= $sym . $bal ?></p>
                <?php if (!empty($w['account_no'])): ?>
                  <p class="text-[10px] font-mono text-brand-muted mt-1" title="Currency account number"><?= htmlspecialchars((string)$w['account_no']) ?></p>
                <?php endif; ?>
                <span class="absolute top-2 right-2 w-3.5 h-3.5 rounded-full border-2 border-brand-border bg-white wallet-dot <?= $i === 0 ? '!border-brand-navy !bg-brand-navy' : '' ?>"></span>
              </button>
            <?php endforeach; ?>
          </div>
          <div class="mt-6 flex justify-end">
            <button type="button" onclick="nextStep()" class="rounded-xl bg-brand-navy px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-navy2 transition-colors">Continue</button>
          </div>
        </div>

        <div class="wizard-step hidden" data-step="2">
          <div class="mb-4">
            <h2 class="text-lg font-bold text-brand-navy">Select Transfer Type</h2>
            <p class="text-sm text-brand-muted">This controls the recipient form and validation fields.</p>
          </div>
          <div class="flex flex-wrap gap-2" id="typeTabs">
            <button type="button" class="type-tab active-tab px-4 py-2 rounded-full text-sm font-semibold border-2 border-brand-navy bg-brand-navy text-white transition-all"
              data-type="domestic" onclick="selectType(this)">Domestic</button>
            <button type="button" class="type-tab px-4 py-2 rounded-full text-sm font-medium border-2 border-brand-border bg-white text-brand-muted hover:border-brand-navy/40 transition-all"
              data-type="samebank" onclick="selectType(this)">Same Bank</button>
            <button type="button" class="type-tab px-4 py-2 rounded-full text-sm font-medium border-2 border-brand-border bg-white text-brand-muted hover:border-brand-navy/40 transition-all"
              data-type="wire" onclick="selectType(this)">Wire / International</button>
            <button type="button" class="type-tab px-4 py-2 rounded-full text-sm font-medium border-2 border-brand-border bg-white text-brand-muted hover:border-brand-navy/40 transition-all hidden"
              data-type="crypto" id="cryptoTab" onclick="selectType(this)">Crypto</button>
          </div>
          <div class="mt-6 flex items-center justify-between">
            <button type="button" onclick="prevStep()" class="rounded-xl border border-brand-border px-5 py-2.5 text-sm font-semibold text-brand-navy hover:bg-brand-light transition-colors">Back</button>
            <button type="button" onclick="nextStep()" class="rounded-xl bg-brand-navy px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-navy2 transition-colors">Continue</button>
          </div>
        </div>

        <div class="wizard-step hidden" data-step="3">
          <div class="mb-4">
            <h2 class="text-lg font-bold text-brand-navy">Choose Recipient Flow</h2>
            <p class="text-sm text-brand-muted">Use a saved beneficiary or start with a new recipient.</p>
          </div>

          <div class="grid gap-3 md:grid-cols-2 mb-4">
            <button type="button" id="recipientModeExistingBtn" onclick="setRecipientMode('existing')"
              class="rounded-2xl border-2 border-brand-navy bg-brand-navy/5 p-4 text-left transition-colors">
              <p class="text-xs uppercase tracking-wider text-brand-muted">Option A</p>
              <p class="mt-1 text-sm font-bold text-brand-navy">Select Existing Beneficiary</p>
            </button>
            <button type="button" id="recipientModeNewBtn" onclick="setRecipientMode('new')"
              class="rounded-2xl border-2 border-brand-border bg-white p-4 text-left transition-colors hover:border-brand-navy/40">
              <p class="text-xs uppercase tracking-wider text-brand-muted">Option B</p>
              <p class="mt-1 text-sm font-bold text-brand-navy">New Recipient</p>
            </button>
          </div>

          <div id="existingRecipientPanel" class="space-y-3">
            <div class="flex items-center justify-between">
              <p class="text-xs font-bold text-brand-muted uppercase tracking-widest">Saved Beneficiaries</p>
              <button type="button" onclick="openBeneModal()"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-navy border border-brand-border rounded-lg px-3 py-1.5 hover:bg-brand-light transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Add New
              </button>
            </div>

            <?php if (empty($beneficiaries)): ?>
              <div id="beneEmpty" class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-brand-border py-8 px-4 text-center">
                <p class="text-sm font-medium text-gray-700">No saved beneficiaries yet</p>
                <p class="text-xs text-brand-muted mt-1">Add one now, then select it to continue.</p>
              </div>
              <div id="beneGrid" class="hidden grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3"></div>
            <?php else: ?>
              <div id="beneEmpty" class="hidden flex-col items-center justify-center rounded-2xl border-2 border-dashed border-brand-border py-8 px-4 text-center">
                <p class="text-sm font-medium text-gray-700">No saved beneficiaries</p>
              </div>
              <div id="beneGrid" class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3">
                <?php
                $beneColors = [
                  ['bg-blue-100', 'text-blue-700'],
                  ['bg-emerald-100', 'text-emerald-700'],
                  ['bg-amber-100', 'text-amber-700'],
                  ['bg-purple-100', 'text-purple-700'],
                  ['bg-rose-100', 'text-rose-700'],
                ];
                foreach ($beneficiaries as $ben):
                  $initials  = strtoupper(substr(trim($ben['nick_name']), 0, 2));
                  $ci        = abs(crc32($ben['nick_name'])) % count($beneColors);
                  $avatarBg  = $beneColors[$ci][0];
                  $avatarFg  = $beneColors[$ci][1];
                  $acctMask  = '&bull;&bull;&bull;' . htmlspecialchars(substr($ben['account_number'], -4));
                ?>
                  <div class="bene-card relative flex flex-col gap-2 p-4 rounded-2xl border-2 border-brand-border bg-white hover:border-brand-navy/40 cursor-pointer transition-all select-none"
                    data-id="<?= (int)$ben['id'] ?>"
                    data-nick="<?= htmlspecialchars($ben['nick_name']) ?>"
                    data-bank="<?= htmlspecialchars($ben['bank_name']) ?>"
                    data-acct="<?= htmlspecialchars($ben['account_number']) ?>"
                    data-swift="<?= htmlspecialchars($ben['swift'] ?? '') ?>"
                    data-routing="<?= htmlspecialchars($ben['routing'] ?? '') ?>"
                    onclick="selectBeneficiary(this)">
                    <div class="flex items-start justify-between">
                      <div class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold <?= $avatarBg . ' ' . $avatarFg ?>">
                        <?= htmlspecialchars($initials) ?>
                      </div>
                      <button type="button"
                        onclick="deleteBeneficiary(event, <?= (int)$ben['id'] ?>)"
                        class="text-gray-300 hover:text-red-500 transition-colors p-0.5 rounded">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                      </button>
                    </div>
                    <div class="min-w-0">
                      <p class="text-sm font-semibold text-brand-navy truncate"><?= htmlspecialchars($ben['nick_name']) ?></p>
                      <p class="text-xs text-brand-muted truncate"><?= htmlspecialchars($ben['bank_name']) ?></p>
                      <p class="text-xs text-brand-muted font-mono mt-0.5"><?= $acctMask ?></p>
                    </div>
                    <span class="bene-check hidden absolute bottom-2 right-2">
                      <svg class="w-4 h-4 text-brand-navy" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                      </svg>
                    </span>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>

          <div id="newRecipientPanel" class="hidden rounded-2xl border border-brand-border bg-brand-light/40 p-4">
            <p class="text-sm font-semibold text-brand-navy">You selected New Recipient</p>
            <p class="mt-1 text-xs text-brand-muted">Proceed to the next step and fill the recipient details manually.</p>
          </div>

          <div id="beneSelectedBadge" class="hidden mt-3 items-center gap-1.5 px-2.5 py-1 rounded-full bg-brand-navy/10 text-brand-navy text-xs font-semibold w-fit">
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span id="beneSelectedName"></span>
            <button type="button" onclick="clearBeneficiary()" class="ml-0.5 text-brand-muted hover:text-brand-navy leading-none">&times;</button>
          </div>

          <div id="samebankBeneError" class="hidden mt-3 rounded-xl p-3 bg-red-50 border border-red-200 text-red-700 text-sm flex items-start gap-2">
            <svg class="w-4 h-4 flex-shrink-0 mt-0.5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <span id="samebankBeneErrorMsg"></span>
          </div>

          <div class="mt-6 flex items-center justify-between">
            <button type="button" onclick="prevStep()" class="rounded-xl border border-brand-border px-5 py-2.5 text-sm font-semibold text-brand-navy hover:bg-brand-light transition-colors">Back</button>
            <button type="button" onclick="nextStep()" class="rounded-xl bg-brand-navy px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-navy2 transition-colors">Continue</button>
          </div>
        </div>

        <div class="wizard-step hidden" data-step="4">
          <div class="mb-4">
            <h2 class="text-lg font-bold text-brand-navy">Transfer Details</h2>
            <p class="text-sm text-brand-muted">Complete recipient fields and amount, then continue to verification.</p>
          </div>

          <div id="fiatFields" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Recipient Name</label>
                <input type="text" name="acc_name" id="fieldAccName"
                  class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-navy transition-colors"
                  placeholder="Full name of recipient">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Account Number or IBAN</label>
                <input type="text" name="acc_no" id="fieldAccNo"
                  class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-brand-navy transition-colors"
                  placeholder="Recipient account number or IBAN">
                <p class="mt-1 text-xs text-brand-muted">IBAN spaces are optional; they will be normalized automatically.</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Bank Name</label>
                <input type="text" name="bank_name" id="fieldBankName" list="banksList" autocomplete="off"
                  class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-navy transition-colors"
                  placeholder="Type or select bank">
              </div>
              <div id="routingField" class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Routing Number</label>
                <input type="text" name="routing" id="fieldRouting"
                  class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-brand-navy transition-colors"
                  placeholder="ABA / routing number">
              </div>
              <div id="swiftField" class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">SWIFT / BIC Code</label>
                <input type="text" name="swift" id="fieldSwift"
                  class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-brand-navy transition-colors"
                  placeholder="e.g. CHASUS33">
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Remarks <span class="text-gray-400 font-normal">(optional)</span></label>
              <input type="text" name="remarks"
                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-navy transition-colors"
                placeholder="Payment reference or note">
            </div>
            <div class="flex items-center justify-end pt-1">
              <button type="button" onclick="prefillAndOpenBene()"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-navy border border-brand-border rounded-lg px-3 py-1.5 hover:bg-brand-light transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                </svg>
                Save as Beneficiary
              </button>
            </div>
          </div>

          <div id="cryptoFields" class="hidden space-y-4">
            <div class="rounded-xl bg-amber-50 border border-amber-200 p-4 text-sm text-amber-800">
              <strong>Note:</strong> You must hold the selected cryptocurrency before sending. Convert fiat first if needed.
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Recipient Wallet Address</label>
              <input type="text" name="wallet_address"
                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-brand-navy transition-colors"
                placeholder="0x&hellip; or bc1&hellip; or T&hellip;">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Network</label>
                <select name="network"
                  class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-brand-navy transition-colors">
                  <option value="ethereum">Ethereum (ERC-20)</option>
                  <option value="bitcoin">Bitcoin (BTC)</option>
                  <option value="tron">Tron (TRC-20)</option>
                  <option value="binance">BNB Smart Chain (BEP-20)</option>
                  <option value="solana">Solana (SOL)</option>
                  <option value="polygon">Polygon (MATIC)</option>
                  <option value="other">Other</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Recipient Label <span class="text-gray-400 font-normal">(optional)</span></label>
                <input type="text" name="crypto_label"
                  class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-navy transition-colors"
                  placeholder="e.g. My hardware wallet">
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Remarks <span class="text-gray-400 font-normal">(optional)</span></label>
              <input type="text" name="remarks"
                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-navy transition-colors"
                placeholder="Optional note">
            </div>
          </div>

          <div class="mt-5 rounded-2xl border border-brand-border bg-slate-50/80 p-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-brand-muted">Amount</p>
            <div class="relative mt-2 max-w-sm">
              <span class="absolute left-4 top-1/2 -translate-y-1/2 text-brand-muted text-sm font-bold select-none pointer-events-none" id="currencyLabel">
                <?= htmlspecialchars($wallets[0]['symbol'] ?? ($wallets[0]['currency_code'] ?? '$')) ?>
              </span>
              <input type="number" name="amount" min="0.01" step="0.000001" required
                class="w-full border-2 border-brand-border rounded-xl pl-14 pr-4 py-3.5 text-2xl font-bold text-brand-navy focus:outline-none focus:border-brand-navy transition-colors"
                placeholder="0.00">
            </div>
            <p class="mt-2 text-xs text-brand-muted">
              Available: <span id="walletBalDisplay" class="font-semibold text-brand-navy">
                <?= htmlspecialchars(($wallets[0]['symbol'] ?? '') . number_format((float)($wallets[0]['balance'] ?? 0), (int)($wallets[0]['is_crypto'] ?? 0) ? 6 : 2)) ?>
              </span>
            </p>
          </div>

          <div class="mt-6 flex items-center justify-between">
            <button type="button" onclick="prevStep()" class="rounded-xl border border-brand-border px-5 py-2.5 text-sm font-semibold text-brand-navy hover:bg-brand-light transition-colors">Back</button>
            <button type="submit"
              class="inline-flex items-center gap-2 rounded-xl bg-brand-navy px-5 py-2.5 text-sm font-bold text-white hover:bg-brand-navy2 transition-colors">
              Continue to Verification
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </form>
  </div>
</section>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- ADD BENEFICIARY MODAL                                       -->
<!-- ═══════════════════════════════════════════════════════════ -->
<div id="beneModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">
  <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
      <h3 class="text-base font-bold text-gray-900">Add Beneficiary</h3>
      <button onclick="closeBeneModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>
    <div class="px-6 py-5 space-y-4">
      <div id="beneModalErr" class="hidden rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-2.5"></div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Nickname / Full Name <span class="text-red-500">*</span></label>
        <input id="beneNick" type="text" maxlength="100" placeholder="e.g. John Doe"
          class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-navy">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Bank Name <span class="text-red-500">*</span></label>
        <input id="beneBank" type="text" maxlength="150" list="banksList" autocomplete="off"
          placeholder="Type to search banks&hellip;"
          class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-navy">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Account Number or IBAN <span class="text-red-500">*</span></label>
        <input id="beneAcct" type="text" maxlength="60" placeholder="Recipient account number or IBAN"
          class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-brand-navy">
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">SWIFT / BIC <span class="text-gray-400 font-normal text-xs">(optional)</span></label>
          <input id="beneSwift" type="text" maxlength="30" placeholder="e.g. CHASUS33"
            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-brand-navy">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Routing No. <span class="text-gray-400 font-normal text-xs">(optional)</span></label>
          <input id="beneRouting" type="text" maxlength="30" placeholder="ABA routing"
            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-brand-navy">
        </div>
      </div>
    </div>
    <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50">
      <button type="button" onclick="closeBeneModal()"
        class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-xl hover:bg-white transition-colors">
        Cancel
      </button>
      <button type="button" id="beneSaveBtn" onclick="saveBeneficiary()"
        class="px-5 py-2 text-sm font-semibold bg-brand-navy text-white rounded-xl hover:bg-brand-navy2 transition-colors">
        Save Beneficiary
      </button>
    </div>
  </div>
</div>

<datalist id="banksList">
  <?php foreach ($worldBanks as $bk): ?>
    <option value="<?= htmlspecialchars($bk) ?>">
    <?php endforeach; ?>
</datalist>

<script>
  const wallets = <?= json_encode(array_values($wallets), JSON_HEX_TAG) ?>;
  const bankDisplayName = <?= json_encode($shellBankName ?? 'this bank', JSON_HEX_TAG) ?>;
  let selectedCode = wallets[0]?.currency_code ?? 'USD';
  let selectedCrypto = !!(wallets[0]?.is_crypto);
  let selectedType = 'domestic';
  let activeBeneId = null;
  let currentStep = 1;
  let recipientMode = 'existing';

  function updateProgressDots() {
    const bar = document.getElementById('wizardBar');
    if (bar) {
      const pct = Math.max(25, Math.min(100, (currentStep / 4) * 100));
      bar.style.width = pct + '%';
    }

    document.querySelectorAll('[data-step-dot]').forEach(dot => {
      const step = parseInt(dot.dataset.stepDot, 10);
      const p1 = dot.querySelector('p:nth-child(1)');
      const p2 = dot.querySelector('p:nth-child(2)');
      if (step <= currentStep) {
        dot.classList.add('border-brand-navy', 'bg-brand-navy/10');
        dot.classList.remove('border-brand-border');
        p2?.classList.remove('text-brand-muted');
        p2?.classList.add('text-brand-navy');
      } else {
        dot.classList.remove('border-brand-navy', 'bg-brand-navy/10');
        dot.classList.add('border-brand-border');
        p2?.classList.remove('text-brand-navy');
        p2?.classList.add('text-brand-muted');
      }
    });
  }

  function showStep(step) {
    currentStep = Math.max(1, Math.min(4, step));
    document.querySelectorAll('.wizard-step').forEach(panel => {
      const isCurrent = parseInt(panel.dataset.step, 10) === currentStep;
      panel.classList.toggle('hidden', !isCurrent);
      panel.classList.remove('wizard-step-enter');
      if (isCurrent) {
        // restart animation on every step navigation
        void panel.offsetWidth;
        panel.classList.add('wizard-step-enter');
      }
    });
    updateProgressDots();
  }

  function nextStep() {
    if (currentStep === 3 && recipientMode === 'existing' && selectedType !== 'crypto' && !activeBeneId) {
      alert('Please select an existing beneficiary or switch to New Recipient.');
      return;
    }

    // For same-bank transfers with a selected beneficiary, validate the account is registered in this bank.
    if (currentStep === 3 && selectedType === 'samebank' && recipientMode === 'existing' && activeBeneId) {
      const card = document.querySelector('#beneGrid .bene-card.border-brand-navy');
      const acctNum = card ? (card.dataset.acct || '').replace(/\s/g, '').toUpperCase() : '';
      const errWrap = document.getElementById('samebankBeneError');
      const errMsg = document.getElementById('samebankBeneErrorMsg');
      const btn = document.querySelector('[data-step="3"] button[onclick="nextStep()"]');

      if (!acctNum) {
        showStep(4);
        return;
      }

      if (btn) {
        btn.disabled = true;
        btn.textContent = 'Checking\u2026';
      }
      if (errWrap) errWrap.classList.add('hidden');

      const fd = new FormData();
      fd.append('samebank_check_acct', '1');
      fd.append('account_number', acctNum);
      fetch('send.php', {
          method: 'POST',
          body: fd
        })
        .then(r => r.json())
        .then(data => {
          if (btn) {
            btn.disabled = false;
            btn.textContent = 'Continue';
          }
          if (data.valid) {
            if (errWrap) errWrap.classList.add('hidden');
            showStep(4);
          } else {
            if (errWrap && errMsg) {
              errMsg.textContent = 'This beneficiary does not have an account registered with ' + bankDisplayName + '. To send funds to this person, switch to Domestic or International transfer.';
              errWrap.classList.remove('hidden');
            } else {
              alert('This beneficiary does not have an account registered with ' + bankDisplayName + '. Switch to Domestic or International transfer.');
            }
          }
        })
        .catch(() => {
          if (btn) {
            btn.disabled = false;
            btn.textContent = 'Continue';
          }
          showStep(4); // fail-open; server-side validates again
        });
      return;
    }

    showStep(currentStep + 1);
  }

  function prevStep() {
    showStep(currentStep - 1);
  }

  function setRecipientMode(mode) {
    recipientMode = mode === 'new' ? 'new' : 'existing';
    document.getElementById('recipientMode').value = recipientMode;

    const existingBtn = document.getElementById('recipientModeExistingBtn');
    const newBtn = document.getElementById('recipientModeNewBtn');
    const existingPanel = document.getElementById('existingRecipientPanel');
    const newPanel = document.getElementById('newRecipientPanel');

    if (recipientMode === 'existing') {
      existingBtn.classList.add('border-brand-navy', 'bg-brand-navy/5');
      existingBtn.classList.remove('border-brand-border', 'bg-white');
      newBtn.classList.remove('border-brand-navy', 'bg-brand-navy/5');
      newBtn.classList.add('border-brand-border', 'bg-white');
      existingPanel.classList.remove('hidden');
      newPanel.classList.add('hidden');
    } else {
      newBtn.classList.add('border-brand-navy', 'bg-brand-navy/5');
      newBtn.classList.remove('border-brand-border', 'bg-white');
      existingBtn.classList.remove('border-brand-navy', 'bg-brand-navy/5');
      existingBtn.classList.add('border-brand-border', 'bg-white');
      newPanel.classList.remove('hidden');
      existingPanel.classList.add('hidden');
      clearBeneficiary();
    }
  }

  // ── Wallet selection ──────────────────────────────────────────────────────────
  function selectWallet(btn) {
    document.querySelectorAll('.wallet-card').forEach(c => {
      c.classList.remove('border-brand-navy', 'bg-brand-navy/5');
      c.classList.add('border-brand-border', 'bg-white');
      c.querySelector('.wallet-dot').classList.remove('!border-brand-navy', '!bg-brand-navy');
    });
    btn.classList.add('border-brand-navy', 'bg-brand-navy/5');
    btn.classList.remove('border-brand-border', 'bg-white');
    btn.querySelector('.wallet-dot').classList.add('!border-brand-navy', '!bg-brand-navy');

    selectedCode = btn.dataset.code;
    selectedCrypto = btn.dataset.crypto === '1';
    document.getElementById('hiddenSourceAccountNo').value = btn.dataset.account || '';
    document.getElementById('hiddenCurrencyCode').value = selectedCode;

    const w = wallets.find(x => x.currency_code === selectedCode);
    if (w) {
      const sym = w.symbol || selectedCode;
      const isCr = !!(w.is_crypto);
      document.getElementById('currencyLabel').textContent = sym;
      document.getElementById('walletBalDisplay').textContent = sym + parseFloat(w.balance || 0).toFixed(isCr ? 6 : 2);
    }

    document.getElementById('cryptoTab').classList.toggle('hidden', !selectedCrypto);
    if (selectedCrypto) {
      const ct = document.querySelector('[data-type="crypto"]');
      if (ct) selectType(ct);
    } else if (selectedType === 'crypto') {
      const dt = document.querySelector('[data-type="domestic"]');
      if (dt) selectType(dt);
    }
  }

  // ── Transfer type ─────────────────────────────────────────────────────────────
  function selectType(btn) {
    document.querySelectorAll('.type-tab').forEach(t => {
      t.classList.remove('border-brand-navy', 'bg-brand-navy', 'text-white');
      t.classList.add('border-brand-border', 'bg-white', 'text-brand-muted');
    });
    btn.classList.add('border-brand-navy', 'bg-brand-navy', 'text-white');
    btn.classList.remove('border-brand-border', 'bg-white', 'text-brand-muted');

    selectedType = btn.dataset.type;
    document.getElementById('hiddenTransferType').value = selectedType;

    const isCrypto = selectedType === 'crypto';
    const isWire = selectedType === 'wire';
    const isSameBank = selectedType === 'samebank';
    const isDomestic = selectedType === 'domestic';

    document.getElementById('fiatFields').classList.toggle('hidden', isCrypto);
    document.getElementById('cryptoFields').classList.toggle('hidden', !isCrypto);
    document.getElementById('routingField').classList.toggle('hidden', !isDomestic || isSameBank);
    document.getElementById('swiftField').classList.toggle('hidden', !isWire);

    // Same Bank mode: emphasize in-system recipient identifier and lock bank name.
    const accInput = document.getElementById('fieldAccNo');
    const bankInput = document.getElementById('fieldBankName');
    if (accInput) {
      accInput.placeholder = isSameBank ?
        'Recipient Account ID / Account Number / IBAN in this bank' :
        'Recipient account number or IBAN';
    }
    if (bankInput) {
      if (isSameBank) {
        bankInput.value = 'Same Bank Transfer';
        bankInput.setAttribute('readonly', 'readonly');
        bankInput.classList.add('bg-slate-100');
      } else {
        if (bankInput.value === 'Same Bank Transfer') bankInput.value = '';
        bankInput.removeAttribute('readonly');
        bankInput.classList.remove('bg-slate-100');
      }
    }

    // Clear same-bank validation error whenever type switches
    const _sbTypeErr = document.getElementById('samebankBeneError');
    if (_sbTypeErr) _sbTypeErr.classList.add('hidden');

    if (isCrypto) {
      setRecipientMode('new');
      document.getElementById('recipientModeExistingBtn')?.setAttribute('disabled', 'disabled');
      document.getElementById('recipientModeExistingBtn')?.classList.add('opacity-50', 'cursor-not-allowed');
    } else {
      document.getElementById('recipientModeExistingBtn')?.removeAttribute('disabled');
      document.getElementById('recipientModeExistingBtn')?.classList.remove('opacity-50', 'cursor-not-allowed');
    }
  }

  // ── Beneficiary modal ─────────────────────────────────────────────────────────
  function openBeneModal() {
    ['beneNick', 'beneBank', 'beneAcct', 'beneSwift', 'beneRouting'].forEach(id => {
      document.getElementById(id).value = '';
    });
    document.getElementById('beneModalErr').classList.add('hidden');
    const m = document.getElementById('beneModal');
    m.classList.remove('hidden');
    m.classList.add('flex');
    document.getElementById('beneNick').focus();
  }

  function prefillAndOpenBene() {
    document.getElementById('beneNick').value = document.getElementById('fieldAccName')?.value || '';
    document.getElementById('beneBank').value = document.getElementById('fieldBankName')?.value || '';
    document.getElementById('beneAcct').value = document.getElementById('fieldAccNo')?.value || '';
    document.getElementById('beneSwift').value = document.getElementById('fieldSwift')?.value || '';
    document.getElementById('beneRouting').value = document.getElementById('fieldRouting')?.value || '';
    document.getElementById('beneModalErr').classList.add('hidden');
    const m = document.getElementById('beneModal');
    m.classList.remove('hidden');
    m.classList.add('flex');
    document.getElementById('beneNick').focus();
  }

  function closeBeneModal() {
    const m = document.getElementById('beneModal');
    m.classList.add('hidden');
    m.classList.remove('flex');
  }

  function saveBeneficiary() {
    const nick = document.getElementById('beneNick').value.trim();
    const bank = document.getElementById('beneBank').value.trim();
    const acct = document.getElementById('beneAcct').value.trim();
    const swift = document.getElementById('beneSwift').value.trim();
    const routing = document.getElementById('beneRouting').value.trim();
    const errEl = document.getElementById('beneModalErr');

    if (!nick || !bank || !acct) {
      errEl.textContent = 'Name, bank and account number are required.';
      errEl.classList.remove('hidden');
      return;
    }
    errEl.classList.add('hidden');

    const btn = document.getElementById('beneSaveBtn');
    btn.disabled = true;
    btn.textContent = 'Saving\u2026';

    const fd = new FormData();
    fd.append('bene_action', 'add');
    fd.append('nick_name', nick);
    fd.append('bank_name', bank);
    fd.append('account_number', acct);
    fd.append('swift', swift);
    fd.append('routing', routing);

    fetch(window.location.pathname, {
        method: 'POST',
        body: fd
      })
      .then(r => r.json())
      .then(data => {
        btn.disabled = false;
        btn.textContent = 'Save Beneficiary';
        if (!data.ok) {
          errEl.textContent = data.msg || 'Error saving.';
          errEl.classList.remove('hidden');
          return;
        }
        appendBeneCard(data);
        closeBeneModal();
      })
      .catch(() => {
        btn.disabled = false;
        btn.textContent = 'Save Beneficiary';
        errEl.textContent = 'Network error. Please try again.';
        errEl.classList.remove('hidden');
      });
  }

  function appendBeneCard(b) {
    const grid = document.getElementById('beneGrid');
    const empty = document.getElementById('beneEmpty');
    const init = (b.nick_name || '?').slice(0, 2).toUpperCase();
    const clrs = [
      ['bg-blue-100', 'text-blue-700'],
      ['bg-emerald-100', 'text-emerald-700'],
      ['bg-amber-100', 'text-amber-700'],
      ['bg-purple-100', 'text-purple-700'],
      ['bg-rose-100', 'text-rose-700']
    ];
    const ci = Math.abs(b.nick_name.split('').reduce((a, c) => a + c.charCodeAt(0), 0)) % clrs.length;
    const mask = '\u2022\u2022\u2022' + b.account_number.slice(-4);

    const div = document.createElement('div');
    div.className = 'bene-card relative flex flex-col gap-2 p-4 rounded-2xl border-2 border-brand-border bg-white hover:border-brand-navy/40 cursor-pointer transition-all select-none';
    div.dataset.id = b.id;
    div.dataset.nick = b.nick_name;
    div.dataset.bank = b.bank_name;
    div.dataset.acct = b.account_number;
    div.dataset.swift = b.swift || '';
    div.dataset.routing = b.routing || '';
    div.setAttribute('onclick', 'selectBeneficiary(this)');
    div.innerHTML =
      '<div class="flex items-start justify-between">' +
      '<div class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold ' + clrs[ci][0] + ' ' + clrs[ci][1] + '">' + escHtml(init) + '</div>' +
      '<button type="button" onclick="deleteBeneficiary(event,' + parseInt(b.id, 10) + ')" class="text-gray-300 hover:text-red-500 transition-colors p-0.5 rounded">' +
      '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>' +
      '</button>' +
      '</div>' +
      '<div class="min-w-0">' +
      '<p class="text-sm font-semibold text-brand-navy truncate">' + escHtml(b.nick_name) + '</p>' +
      '<p class="text-xs text-brand-muted truncate">' + escHtml(b.bank_name) + '</p>' +
      '<p class="text-xs text-brand-muted font-mono mt-0.5">' + escHtml(mask) + '</p>' +
      '</div>' +
      '<span class="bene-check hidden absolute bottom-2 right-2">' +
      '<svg class="w-4 h-4 text-brand-navy" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>' +
      '</span>';

    grid.appendChild(div);
    grid.classList.remove('hidden');
    empty.classList.add('hidden');
    empty.classList.remove('flex');
  }

  function deleteBeneficiary(e, id) {
    e.stopPropagation();
    const card = document.querySelector('.bene-card[data-id="' + id + '"]');
    if (!card) return;
    card.style.opacity = '0.4';
    card.style.pointerEvents = 'none';

    const fd = new FormData();
    fd.append('bene_action', 'delete');
    fd.append('id', id);

    fetch(window.location.pathname, {
        method: 'POST',
        body: fd
      })
      .then(r => r.json())
      .then(() => {
        if (activeBeneId === id) clearBeneficiary();
        card.remove();
        const grid = document.getElementById('beneGrid');
        if (!grid.querySelector('.bene-card')) {
          grid.classList.add('hidden');
          const empty = document.getElementById('beneEmpty');
          empty.classList.remove('hidden');
          empty.classList.add('flex');
        }
      })
      .catch(() => {
        card.style.opacity = '1';
        card.style.pointerEvents = '';
      });
  }

  function selectBeneficiary(card) {
    if (recipientMode !== 'existing') return;

    // Clear any previous same-bank validation error
    const _sbErr = document.getElementById('samebankBeneError');
    if (_sbErr) _sbErr.classList.add('hidden');

    document.querySelectorAll('.bene-card').forEach(c => {
      c.classList.remove('border-brand-navy', 'bg-brand-navy/5');
      c.classList.add('border-brand-border', 'bg-white');
      c.querySelector('.bene-check')?.classList.add('hidden');
    });
    card.classList.add('border-brand-navy', 'bg-brand-navy/5');
    card.classList.remove('border-brand-border', 'bg-white');
    card.querySelector('.bene-check').classList.remove('hidden');

    activeBeneId = parseInt(card.dataset.id, 10);

    const set = (id, val) => {
      const el = document.getElementById(id);
      if (el) el.value = val;
    };
    set('fieldAccName', card.dataset.nick || '');
    set('fieldAccNo', card.dataset.acct || '');
    // In same-bank mode the bank name is locked to "Same Bank Transfer" by selectType(); don't overwrite it.
    if (selectedType !== 'samebank') {
      set('fieldBankName', card.dataset.bank || '');
    }
    set('fieldSwift', card.dataset.swift || '');
    set('fieldRouting', card.dataset.routing || '');

    const badge = document.getElementById('beneSelectedBadge');
    document.getElementById('beneSelectedName').textContent = card.dataset.nick;
    badge.classList.remove('hidden');
    badge.classList.add('flex');
  }

  function clearBeneficiary() {
    const _sbErr = document.getElementById('samebankBeneError');
    if (_sbErr) _sbErr.classList.add('hidden');

    document.querySelectorAll('.bene-card').forEach(c => {
      c.classList.remove('border-brand-navy', 'bg-brand-navy/5');
      c.classList.add('border-brand-border', 'bg-white');
      c.querySelector('.bene-check')?.classList.add('hidden');
    });
    activeBeneId = null;
    const badge = document.getElementById('beneSelectedBadge');
    badge.classList.add('hidden');
    badge.classList.remove('flex');
  }

  function escHtml(s) {
    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  function formatAccountOrIbanInput(el) {
    if (!el) return;
    const raw = (el.value || '').toUpperCase().replace(/\s+/g, '');
    // Keep alphanumeric + dash only to support both account numbers and IBAN.
    const compact = raw.replace(/[^A-Z0-9\-]/g, '');
    // For IBAN-like values (2 letters + 2 digits prefix), group in blocks of 4.
    if (/^[A-Z]{2}\d{2}[A-Z0-9]+$/.test(compact)) {
      el.value = compact.match(/.{1,4}/g)?.join(' ') || compact;
    } else {
      el.value = compact;
    }
  }

  document.getElementById('beneModal').addEventListener('click', function(e) {
    if (e.target === this) closeBeneModal();
  });

  ['fieldAccNo', 'beneAcct'].forEach(id => {
    const el = document.getElementById(id);
    if (el) {
      el.addEventListener('input', () => formatAccountOrIbanInput(el));
      el.addEventListener('blur', () => formatAccountOrIbanInput(el));
    }
  });

  // Init
  selectWallet(document.querySelector('.wallet-card'));
  selectType(document.querySelector('[data-type="domestic"]'));
  setRecipientMode('existing');
  showStep(<?= $flashError ? 4 : 1 ?>);
</script>

<?php require_once __DIR__ . '/partials/shell-close.php';
exit(); ?>