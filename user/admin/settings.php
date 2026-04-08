<?php
session_start();
require_once ('class.admin.php');
include_once ('session.php');

$reg_user = new USER();

if(!isset($_SESSION['email'])){
	
header("Location: login.php");

exit(); 
}

$stmt = $reg_user->runQuery("SELECT * FROM account");
$stmt->execute();

if(isset($_POST['set']))
{
	
	$uname = trim($_POST['uname']);
	$uname = strip_tags($uname);
	$uname = htmlspecialchars($uname);
	
	$status = trim($_POST['status']);
	$status = strip_tags($status);
	$status = htmlspecialchars($status);
	
			$set= $reg_user->runQuery("UPDATE account SET uname = '$uname', status = '$status' WHERE uname = '$uname'");
			$set->execute();
			
			$id = $reg_user->lasdID();		
			
			
			$msg= "<div class='alert alert-info'>
				<button class='close' data-dismiss='alert'>&times;</button>
					<strong>$fname $uname $lname Account Successfully Set to <b>$status</b>!</strong> 
			  </div>";	

}

// ── Theme management ─────────────────────────────────────────────────
// $conn (mysqli) is available via the root config loaded by class.admin.php.
require dirname(__DIR__, 2) . '/config.php';

function setting_get(mysqli $conn, string $key, string $default = ''): string
{
    $safe = $conn->real_escape_string($key);
    // Primary path: key / value schema (actual table)
    try {
        $res = $conn->query("SELECT `value` FROM site_settings WHERE `key`='$safe' LIMIT 1");
        if ($res && $res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $val = (string)($row['value'] ?? '');
            if (trim($val) !== '') {
                return $val;
            }
        }
    } catch (Throwable $e) {
    }
    // Legacy fallback: setting_key / setting_value schema
    try {
        $res = $conn->query("SELECT setting_value FROM site_settings WHERE setting_key='$safe' LIMIT 1");
        if ($res && $res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $val = (string)($row['setting_value'] ?? '');
            if (trim($val) !== '') {
                return $val;
            }
        }
    } catch (Throwable $e) {
    }
    return $default;
}

function setting_set(mysqli $conn, string $key, string $value): bool
{
    // Primary path: use the actual table schema (key / value columns)
    try {
        $stmt = $conn->prepare("INSERT INTO site_settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");
        if ($stmt) {
            $stmt->bind_param('ss', $key, $value);
            $ok = $stmt->execute();
            $stmt->close();
            if ($ok) {
                return true;
            }
        }
    } catch (Throwable $e) {
    }
    // Legacy fallback (setting_key / setting_value schema)
    $safeKey = $conn->real_escape_string($key);
    $safeVal = $conn->real_escape_string($value);
    try {
        $conn->query("INSERT INTO site_settings (setting_key, setting_value) VALUES ('$safeKey', '$safeVal') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        return !$conn->error;
    } catch (Throwable $e) {
        return false;
    }
}

function flag_asset_url(string $code): string
{
    $clean = strtoupper(trim($code));
    if (!preg_match('/^[A-Z0-9]{2,10}$/', $clean)) {
        return '';
    }
    return '../flag-preview.php?code=' . rawurlencode($clean);
}


/**
 * Updates the active theme name in the root .htaccess
 * between the [THEME_REWRITE_START] / [THEME_REWRITE_END] markers.
 */
function updateHtaccessTheme(string $newTheme): bool
{
    $htaccessPath = dirname(__DIR__, 2) . '/.htaccess';
    if (!is_writable($htaccessPath)) {
    return true;
    }
    $content = file_get_contents($htaccessPath);
    $updated = preg_replace_callback(
        '/# \[THEME_REWRITE_START\].*?# \[THEME_REWRITE_END\]/s',
        function ($m) use ($newTheme) {
            // Update the comment line.
            $block = preg_replace(
                '/^(# Active theme: )[a-zA-Z0-9_-]+$/m',
                '$1' . $newTheme,
                $m[0]
            );
            // Update every themes/OLD_THEME/ reference to themes/NEW_THEME/.
            $block = preg_replace(
                '/\bthemes\/[a-zA-Z0-9_-]+\//',
                'themes/' . $newTheme . '/',
                $block
            );
            return $block;
        },
        $content
    );
    if ($updated === null || $updated === $content) {
        // Nothing changed or regex failed.
        return $updated !== null;
    }
    // Atomic write via a temp file.
    $tmp = $htaccessPath . '.tmp.' . getmypid();
    if (file_put_contents($tmp, $updated) === false) {
        return false;
    }
    return rename($tmp, $htaccessPath);
}

// Discover all installed themes (each subdirectory of /themes/ is a theme).
$themesDir   = dirname(__DIR__, 2) . '/themes/';
$availThemes = [];
if (is_dir($themesDir)) {
    foreach (array_diff(scandir($themesDir), ['.', '..']) as $entry) {
        if (is_dir($themesDir . $entry) && preg_match('/^[a-zA-Z0-9_-]+$/', $entry)) {
            $availThemes[] = $entry;
        }
    }
}

// Current active theme from DB.
$activeThemeRow = $conn->query("SELECT `value` FROM site_settings WHERE `key` = 'theme' LIMIT 1");
$activeTheme    = 'theme1';
if ($activeThemeRow && $activeThemeRow->num_rows > 0) {
    $activeTheme = $activeThemeRow->fetch_assoc()['value'];
}

$frontendSchemes = [
  'classic' => 'Classic Blue',
  'ocean' => 'Ocean Teal',
  'forest' => 'Forest Green',
  'ember' => 'Ember Orange',
  'royal' => 'Royal Indigo',
  'graphite' => 'Graphite Gray',
  'sunset' => 'Sunset Rose',
  'teal-gold' => 'Teal & Gold',
  'midnight' => 'Midnight Blue',
  'mint' => 'Mint Fresh',
  'sandstone' => 'Sandstone',
  'plum' => 'Plum Luxe'
];

// Keep auth scheme list exactly aligned with frontend schemes.
$authSchemes = $frontendSchemes;

$authSchemeRow = $conn->query("SELECT `value` FROM site_settings WHERE `key` = 'auth_color_scheme' LIMIT 1");
$activeAuthScheme = 'classic';
if ($authSchemeRow && $authSchemeRow->num_rows > 0) {
    $candidate = $authSchemeRow->fetch_assoc()['value'];
  if ($candidate === 'default') {
    $candidate = 'classic';
  }
    if (isset($authSchemes[$candidate])) {
        $activeAuthScheme = $candidate;
    }
}

    $frontendSchemeRow = $conn->query("SELECT `value` FROM site_settings WHERE `key` = 'frontend_color_scheme' LIMIT 1");
    $activeFrontendScheme = 'classic';
    if ($frontendSchemeRow && $frontendSchemeRow->num_rows > 0) {
      $candidate = $frontendSchemeRow->fetch_assoc()['value'];
      if (isset($frontendSchemes[$candidate])) {
        $activeFrontendScheme = $candidate;
      }
    }

$translatorLanguages = setting_get($conn, 'translator_languages', 'en,es,fr,de,it,pt,ru,zh-CN');

// Handle theme switch form submission.
if (isset($_POST['set_theme'])) {
    $newTheme = trim($_POST['theme'] ?? '');
    if (preg_match('/^[a-zA-Z0-9_-]+$/', $newTheme) && in_array($newTheme, $availThemes, true)) {
        $safeTheme = $conn->real_escape_string($newTheme);
        $conn->query("UPDATE site_settings SET `value` = '$safeTheme' WHERE `key` = 'theme'");
    updateHtaccessTheme($newTheme);
    $activeTheme = $newTheme;
    $themeMsg = "<div class='alert alert-success'>
      <button class='close' data-dismiss='alert'>&times;</button>
      <strong>Theme switched to <b>" . htmlspecialchars($newTheme) . "</b> successfully.</strong>
    </div>";
    }
}

if (isset($_POST['set_auth_scheme'])) {
  $newScheme = trim($_POST['auth_scheme'] ?? 'classic');
    if (isset($authSchemes[$newScheme])) {
        $safeScheme = $conn->real_escape_string($newScheme);
        $conn->query("INSERT INTO site_settings (`key`, `value`) VALUES ('auth_color_scheme', '$safeScheme') ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");
        $activeAuthScheme = $newScheme;
        $authThemeMsg = "<div class='alert alert-success'>
            <button class='close' data-dismiss='alert'>&times;</button>
            <strong>Auth pages color scheme updated to <b>" . htmlspecialchars($authSchemes[$newScheme]) . "</b>.</strong>
        </div>";
    }
}

      if (isset($_POST['set_frontend_scheme'])) {
        $newScheme = trim($_POST['frontend_scheme'] ?? 'classic');
        if (isset($frontendSchemes[$newScheme])) {
          $safeScheme = $conn->real_escape_string($newScheme);
          $conn->query("INSERT INTO site_settings (`key`, `value`) VALUES ('frontend_color_scheme', '$safeScheme') ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");
          $activeFrontendScheme = $newScheme;
          $frontendThemeMsg = "<div class='alert alert-success'>
            <button class='close' data-dismiss='alert'>&times;</button>
            <strong>Frontend color scheme updated to <b>" . htmlspecialchars($frontendSchemes[$newScheme]) . "</b>.</strong>
          </div>";
        }
      }

if (isset($_POST['set_translator_languages'])) {
    $raw = (string)($_POST['translator_languages'] ?? '');
    $codes = [];
    foreach (explode(',', $raw) as $piece) {
        $code = trim($piece);
        if (!preg_match('/^[a-z]{2}(?:-[A-Z]{2})?$/', $code)) {
            continue;
        }
        if (!in_array($code, $codes, true)) {
            $codes[] = $code;
        }
    }

    if (!in_array('en', $codes, true)) {
        array_unshift($codes, 'en');
    }

    $translatorLanguages = implode(',', $codes);
    if (setting_set($conn, 'translator_languages', $translatorLanguages)) {
        $translatorMsg = "<div class='alert alert-success'>
            <button class='close' data-dismiss='alert'>&times;</button>
            <strong>Translator languages updated successfully.</strong>
        </div>";
    } else {
        $translatorMsg = "<div class='alert alert-danger'>
            <button class='close' data-dismiss='alert'>&times;</button>
            <strong>Unable to save translator languages.</strong>
        </div>";
    }
}

$dormantMessage = setting_get($conn, 'dormant_transfer_message', '');
if (trim($dormantMessage) === '') {
  $dormantMessage = setting_get($conn, 'dormant_message', '');
}
$transferSuccessTitle = setting_get($conn, 'transfer_success_title', setting_get($conn, 'success_transfer_title', 'Transfer Initiated'));
$transferSuccessNote = setting_get($conn, 'transfer_success_note', setting_get($conn, 'success_transfer_note', 'International transfers are processed within 2-3 business days.'));
$transferFailureTitle = setting_get($conn, 'transfer_failure_title', 'Transfer Failed');
$transferFailureNote = setting_get($conn, 'transfer_failure_note', 'This transfer could not be completed. Please contact support or try again.');

if (isset($_POST['set_dormant_message'])) {
    $newDormantMsg = trim((string)($_POST['dormant_message'] ?? ''));
  $savedPrimary = setting_set($conn, 'dormant_transfer_message', $newDormantMsg);
  // Backward compatibility for installations that previously used a shorter key.
  $savedLegacy = setting_set($conn, 'dormant_message', $newDormantMsg);
  if ($savedPrimary || $savedLegacy) {
        $dormantMsg_notice = "<div class='alert alert-success'>
            <button class='close' data-dismiss='alert'>&times;</button>
            <strong>Dormant account message updated successfully.</strong>
        </div>";
        $dormantMessage = $newDormantMsg;
    } else {
        $dormantMsg_notice = "<div class='alert alert-danger'>
            <button class='close' data-dismiss='alert'>&times;</button>
            <strong>Unable to save dormant message.</strong>
        </div>";
    }
}

if (isset($_POST['set_success_copy'])) {
  $newSuccessTitle = trim(strip_tags((string)($_POST['success_transfer_title'] ?? '')));
  $newSuccessNote = trim(strip_tags((string)($_POST['success_transfer_note'] ?? '')));
  if ($newSuccessTitle === '') {
    $newSuccessTitle = 'Transfer Initiated';
  }
  if ($newSuccessNote === '') {
    $newSuccessNote = 'International transfers are processed within 2-3 business days.';
  }

  $savedTitle = setting_set($conn, 'success_transfer_title', $newSuccessTitle) && setting_set($conn, 'transfer_success_title', $newSuccessTitle);
  $savedNote = setting_set($conn, 'success_transfer_note', $newSuccessNote) && setting_set($conn, 'transfer_success_note', $newSuccessNote);
  if ($savedTitle || $savedNote) {
    $successCopyMsg = "<div class='alert alert-success'>
      <button class='close' data-dismiss='alert'>&times;</button>
      <strong>Success page copy updated successfully.</strong>
    </div>";
    $transferSuccessTitle = $newSuccessTitle;
    $transferSuccessNote = $newSuccessNote;
  } else {
    $successCopyMsg = "<div class='alert alert-danger'>
      <button class='close' data-dismiss='alert'>&times;</button>
      <strong>Unable to save success page copy.</strong>
    </div>";
  }
}

if (isset($_POST['set_transfer_result_copy'])) {
  $newSuccessTitle = trim(strip_tags((string)($_POST['transfer_success_title'] ?? '')));
  $newSuccessNote = trim(strip_tags((string)($_POST['transfer_success_note'] ?? '')));
  $newFailureTitle = trim(strip_tags((string)($_POST['transfer_failure_title'] ?? '')));
  $newFailureNote = trim(strip_tags((string)($_POST['transfer_failure_note'] ?? '')));

  if ($newSuccessTitle === '') {
    $newSuccessTitle = 'Transfer Initiated';
  }
  if ($newSuccessNote === '') {
    $newSuccessNote = 'International transfers are processed within 2-3 business days.';
  }
  if ($newFailureTitle === '') {
    $newFailureTitle = 'Transfer Failed';
  }
  if ($newFailureNote === '') {
    $newFailureNote = 'This transfer could not be completed. Please contact support or try again.';
  }

  $saveOk = true;
  $saveOk = $saveOk && setting_set($conn, 'transfer_success_title', $newSuccessTitle);
  $saveOk = $saveOk && setting_set($conn, 'transfer_success_note', $newSuccessNote);
  $saveOk = $saveOk && setting_set($conn, 'transfer_failure_title', $newFailureTitle);
  $saveOk = $saveOk && setting_set($conn, 'transfer_failure_note', $newFailureNote);
  // Keep legacy keys in sync for older reads.
  $saveOk = $saveOk && setting_set($conn, 'success_transfer_title', $newSuccessTitle);
  $saveOk = $saveOk && setting_set($conn, 'success_transfer_note', $newSuccessNote);

  if ($saveOk) {
    $transferResultCopyMsg = "<div class='alert alert-success'>
      <button class='close' data-dismiss='alert'>&times;</button>
      <strong>Transfer result page messages updated successfully.</strong>
    </div>";
    $transferSuccessTitle = $newSuccessTitle;
    $transferSuccessNote = $newSuccessNote;
    $transferFailureTitle = $newFailureTitle;
    $transferFailureNote = $newFailureNote;
  } else {
    $transferResultCopyMsg = "<div class='alert alert-danger'>
      <button class='close' data-dismiss='alert'>&times;</button>
      <strong>Unable to save transfer result page messages.</strong>
    </div>";
  }
}

// ── Transfer Settings management ─────────────────────────────────────
$transferSettingsMsg = '';
if (isset($_POST['update_transfer_settings'])) {
    $autoUpdateEnabled = isset($_POST['auto_update_enabled']) ? '1' : '0';
    $autoUpdateDelay = (int)($_POST['auto_update_delay'] ?? 1440);
    $autoUpdateDelay = max(0, $autoUpdateDelay);
    $targetStatus = trim($_POST['auto_update_target_status'] ?? 'successful');
  $initialStatus = trim($_POST['initial_transfer_status'] ?? 'pending');
    
    $allowedStatuses = ['pending', 'processing', 'completed', 'successful', 'failed', 'cancelled', 'reversed'];
    if (!in_array($targetStatus, $allowedStatuses)) {
        $targetStatus = 'successful';
    }
    if (!in_array($initialStatus, $allowedStatuses)) {
      $initialStatus = 'pending';
    }
    
    if ($reg_user->updateTransferSetting('auto_update_enabled', $autoUpdateEnabled) &&
        $reg_user->updateTransferSetting('auto_update_delay_minutes', (string)$autoUpdateDelay) &&
      $reg_user->updateTransferSetting('auto_update_target_status', $targetStatus) &&
      $reg_user->updateTransferSetting('initial_transfer_status', $initialStatus)) {
        $transferSettingsMsg = "<div class='alert alert-success'>
            <button class='close' data-dismiss='alert'>&times;</button>
            <strong>Transfer settings updated successfully.</strong>
        </div>";
    } else {
        $transferSettingsMsg = "<div class='alert alert-danger'>
            <button class='close' data-dismiss='alert'>&times;</button>
            <strong>Unable to save transfer settings.</strong>
        </div>";
    }
}

// Load current transfer settings
$transferAutoUpdateEnabled = $reg_user->getTransferSetting('auto_update_enabled', '1');
$transferAutoUpdateDelay = $reg_user->getTransferSetting('auto_update_delay_minutes', '1440');
$transferTargetStatus = $reg_user->getTransferSetting('auto_update_target_status', 'successful');
$transferInitialStatus = $reg_user->getTransferSetting('initial_transfer_status', 'pending');
if (!in_array($transferInitialStatus, ['pending', 'processing', 'completed', 'successful', 'failed', 'cancelled', 'reversed'], true)) {
  $transferInitialStatus = 'pending';
}

// ── Currencies management ────────────────────────────────────────────
try {
    $conn->query("ALTER TABLE currencies ADD COLUMN flag_code VARCHAR(8) NOT NULL DEFAULT '' AFTER symbol");
} catch (Throwable $e) {
}

$currenciesRes = $conn->query("SELECT * FROM currencies ORDER BY sort_order, id");
$allCurrencies = [];
if ($currenciesRes) {
    while ($c = $currenciesRes->fetch_assoc()) {
        $allCurrencies[] = $c;
    }
}

if (isset($_POST['add_currency'])) {
    $cCode   = strtoupper(trim($conn->real_escape_string($_POST['cur_code'] ?? '')));
    $cSym    = trim($conn->real_escape_string($_POST['cur_symbol'] ?? ''));
    $cFlag   = strtoupper(trim($conn->real_escape_string($_POST['cur_flag'] ?? '')));
    $cName   = trim($conn->real_escape_string($_POST['cur_name'] ?? ''));
    $cCrypto = isset($_POST['cur_crypto']) ? 1 : 0;
    $cOrder  = (int)($_POST['cur_order'] ?? 99);
    if (!preg_match('/^[A-Z]{0,4}$/', $cFlag)) {
        $cFlag = '';
    }
    if ($cCode && $cSym && $cName) {
        $conn->query("INSERT IGNORE INTO currencies (code,symbol,flag_code,name,is_crypto,is_active,sort_order)
                      VALUES ('$cCode','$cSym','$cFlag','$cName',$cCrypto,1,$cOrder)");
        $conn->query("UPDATE currencies SET flag_code='$cFlag' WHERE code='$cCode'");
        $currencyMsg = "<div class='alert alert-success'><button class='close' data-dismiss='alert'>&times;</button>
            <strong>Currency <b>$cCode</b> added.</strong></div>";
    } else {
        $currencyMsg = "<div class='alert alert-danger'><button class='close' data-dismiss='alert'>&times;</button>
            <strong>Code, symbol, and name are required.</strong></div>";
    }
    // Refresh list
    $currenciesRes = $conn->query("SELECT * FROM currencies ORDER BY sort_order, id");
    $allCurrencies = [];
    while ($c = $currenciesRes->fetch_assoc()) { $allCurrencies[] = $c; }
}

if (isset($_POST['update_currency_flag'])) {
    $currencyId = (int)($_POST['currency_id'] ?? 0);
    $currencyFlag = strtoupper(trim((string)($_POST['currency_flag_edit'] ?? '')));
    if (!preg_match('/^[A-Z]{0,4}$/', $currencyFlag)) {
        $currencyFlag = '';
    }
    if ($currencyId > 0) {
        $safeFlag = $conn->real_escape_string($currencyFlag);
        $conn->query("UPDATE currencies SET flag_code='$safeFlag' WHERE id=$currencyId");
        $currencyMsg = "<div class='alert alert-success'><button class='close' data-dismiss='alert'>&times;</button>
            <strong>Currency flag updated.</strong></div>";
    }
    $currenciesRes = $conn->query("SELECT * FROM currencies ORDER BY sort_order, id");
    $allCurrencies = [];
    while ($c = $currenciesRes->fetch_assoc()) { $allCurrencies[] = $c; }
}

if (isset($_POST['toggle_currency'])) {
    $togId  = (int)$_POST['toggle_currency'];
    $conn->query("UPDATE currencies SET is_active = 1 - is_active WHERE id = $togId");
    header('Location: ' . $_SERVER['PHP_SELF'] . '#currency-mgmt');
    exit();
}

if (isset($_POST['delete_currency'])) {
    $delId = (int)$_POST['delete_currency'];
    $conn->query("DELETE FROM currencies WHERE id = $delId");
    header('Location: ' . $_SERVER['PHP_SELF'] . '#currency-mgmt');
    exit();
}

// ── Account types management ─────────────────────────────────────────
$accountTypesRes = $conn->query("SELECT * FROM account_types ORDER BY min_balance");
$allAccountTypes = [];
if ($accountTypesRes) {
    while ($at = $accountTypesRes->fetch_assoc()) {
        $allAccountTypes[] = $at;
    }
}

if (isset($_POST['add_account_type'])) {
    $atLabel   = trim($conn->real_escape_string($_POST['at_label'] ?? ''));
    $atKey     = trim($conn->real_escape_string(preg_replace('/^\d+\s*-\s*/', '', $_POST['at_key'] ?? '')));
    $atBalance = (float)str_replace(',', '', $_POST['at_balance'] ?? 0);
    if ($atLabel && $atKey && $atBalance >= 0) {
        $conn->query("INSERT IGNORE INTO account_types (label,type_key,min_balance,is_active)
                      VALUES ('$atLabel','$atKey',$atBalance,1)");
        $accountTypeMsg = "<div class='alert alert-success'><button class='close' data-dismiss='alert'>&times;</button>
            <strong>Account type <b>$atLabel</b> added.</strong></div>";
    } else {
        $accountTypeMsg = "<div class='alert alert-danger'><button class='close' data-dismiss='alert'>&times;</button>
            <strong>Label, key, and minimum balance are required.</strong></div>";
    }
    $accountTypesRes = $conn->query("SELECT * FROM account_types ORDER BY min_balance");
    $allAccountTypes = [];
    while ($at = $accountTypesRes->fetch_assoc()) { $allAccountTypes[] = $at; }
}

if (isset($_POST['update_account_type'])) {
    $atId      = (int)$_POST['at_id'];
    $atBalance = (float)str_replace(',', '', $_POST['at_balance_edit'] ?? 0);
    $atLabel   = trim($conn->real_escape_string($_POST['at_label_edit'] ?? ''));
    $conn->query("UPDATE account_types SET label='$atLabel', min_balance=$atBalance WHERE id=$atId");
    header('Location: ' . $_SERVER['PHP_SELF'] . '#account-types-mgmt');
    exit();
}

if (isset($_POST['toggle_account_type'])) {
    $togId = (int)$_POST['toggle_account_type'];
    $conn->query("UPDATE account_types SET is_active = 1 - is_active WHERE id = $togId");
    header('Location: ' . $_SERVER['PHP_SELF'] . '#account-types-mgmt');
    exit();
}

// ── Exchange rates management ────────────────────────────────────────
$ratesRes = $conn->query("SELECT r.*, fc.name AS from_name, tc.name AS to_name
                          FROM exchange_rates r
                          LEFT JOIN currencies fc ON fc.code=r.from_code
                          LEFT JOIN currencies tc ON tc.code=r.to_code
                          ORDER BY r.from_code, r.to_code");
$allRates = [];
if ($ratesRes) {
    while ($r = $ratesRes->fetch_assoc()) { $allRates[] = $r; }
}

if (isset($_POST['upsert_rate'])) {
    $rFrom = strtoupper(trim($conn->real_escape_string($_POST['rate_from'] ?? '')));
    $rTo   = strtoupper(trim($conn->real_escape_string($_POST['rate_to'] ?? '')));
    $rRate = (float)$_POST['rate_value'];
    if ($rFrom && $rTo && $rFrom !== $rTo && $rRate > 0) {
        $conn->query("INSERT INTO exchange_rates (from_code,to_code,rate,source)
                      VALUES ('$rFrom','$rTo',$rRate,'manual')
                      ON DUPLICATE KEY UPDATE rate=$rRate, source='manual', updated_at=NOW()");
        $rateMsg = "<div class='alert alert-success'><button class='close' data-dismiss='alert'>&times;</button>
            <strong>Rate <b>$rFrom &rarr; $rTo</b> set to <b>$rRate</b>.</strong></div>";
    } else {
        $rateMsg = "<div class='alert alert-danger'><button class='close' data-dismiss='alert'>&times;</button>
            <strong>Invalid rate input.</strong></div>";
    }
    $ratesRes = $conn->query("SELECT r.*, fc.name AS from_name, tc.name AS to_name
                              FROM exchange_rates r
                              LEFT JOIN currencies fc ON fc.code=r.from_code
                              LEFT JOIN currencies tc ON tc.code=r.to_code
                              ORDER BY r.from_code, r.to_code");
    $allRates = [];
    while ($r = $ratesRes->fetch_assoc()) { $allRates[] = $r; }
}

if (isset($_POST['delete_rate'])) {
    $delId = (int)$_POST['delete_rate'];
    $conn->query("DELETE FROM exchange_rates WHERE id = $delId");
    header('Location: ' . $_SERVER['PHP_SELF'] . '#exchange-rates-mgmt');
    exit();
}

// ── Transaction Codes management ──────────────────────────────────────
$txMaxCodes = (int)setting_get($conn, 'tx_max_codes', '3');
$txDefaultNames = [1=>'COT',2=>'TAX',3=>'IMF',4=>'LPPI',5=>'Code 5'];
$txCodeNames = [];
for ($i=1;$i<=5;$i++) $txCodeNames[$i] = setting_get($conn, "tx_code{$i}_name", $txDefaultNames[$i]);

if (isset($_POST['save_tx_codes'])) {
    $newMax = max(1, min(5, (int)($_POST['tx_max_codes'] ?? 3)));
    setting_set($conn, 'tx_max_codes', (string)$newMax);
    for ($i=1;$i<=5;$i++) {
        $nm = trim((string)($_POST["tx_code{$i}_name"] ?? $txDefaultNames[$i]));
        if ($nm === '') $nm = $txDefaultNames[$i];
        setting_set($conn, "tx_code{$i}_name", $nm);
        $txCodeNames[$i] = $nm;
    }
    $txMaxCodes = $newMax;
    $txCodesMsg = "<div class='alert alert-success'><button class='close' data-dismiss='alert'>&times;</button><strong>Transaction code settings saved.</strong></div>";
}

// ── Promo Banner settings ────────────────────────────────────────────
@mkdir(__DIR__ . '/img/promo', 0755, true);

$promoEnabled       = setting_get($conn, 'promo_enabled', '0');
$promoImageUrl      = setting_get($conn, 'promo_image_url', '');
$promoHeadline      = setting_get($conn, 'promo_headline', '');
$promoBody          = setting_get($conn, 'promo_body', '');
$promoBtnLabel      = setting_get($conn, 'promo_btn_label', '');
$promoBtnUrl        = setting_get($conn, 'promo_btn_url', '');
$promoCardEnabled   = setting_get($conn, 'promo_card_enabled', '0');
$promoPopupEnabled  = setting_get($conn, 'promo_popup_enabled', '0');
$promoPopupCondition = setting_get($conn, 'promo_popup_condition', 'once_session');
$promoMsg = '';

if (isset($_POST['save_promo'])) {
    $promoEnabled      = isset($_POST['promo_enabled']) ? '1' : '0';
    $promoHeadline     = trim(strip_tags((string)($_POST['promo_headline'] ?? '')));
    $promoBody         = trim(strip_tags((string)($_POST['promo_body'] ?? '')));
    $promoBtnLabel     = trim(strip_tags((string)($_POST['promo_btn_label'] ?? '')));
    $promoBtnUrl       = trim((string)($_POST['promo_btn_url'] ?? ''));
    // Validate button URL
    if ($promoBtnUrl !== '' && !preg_match('/^(https?:\/\/|\/)/i', $promoBtnUrl)) {
        $promoBtnUrl = '';
    }
    $promoCardEnabled  = isset($_POST['promo_card_enabled']) ? '1' : '0';
    $promoPopupEnabled = isset($_POST['promo_popup_enabled']) ? '1' : '0';
    $allowedPopupConds = ['always', 'once_session', 'once_day', 'guest_only'];
    $promoPopupCondition = in_array($_POST['promo_popup_condition'] ?? '', $allowedPopupConds, true)
        ? $_POST['promo_popup_condition'] : 'once_session';

    // Handle image upload
    if (!empty($_FILES['promo_image']['name']) && $_FILES['promo_image']['error'] === UPLOAD_ERR_OK) {
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo        = finfo_open(FILEINFO_MIME_TYPE);
        $mime         = finfo_file($finfo, $_FILES['promo_image']['tmp_name']);
        finfo_close($finfo);
        if (in_array($mime, $allowedMimes, true) && $_FILES['promo_image']['size'] <= 2 * 1024 * 1024) {
            $ext      = pathinfo((string)$_FILES['promo_image']['name'], PATHINFO_EXTENSION);
            $ext      = strtolower(preg_replace('/[^a-z0-9]/i', '', $ext));
            if ($ext === '') $ext = 'jpg';
            $fname    = 'promo_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $savePath = __DIR__ . '/img/promo/' . $fname;
            
            // Ensure directory exists before attempting upload
            $uploadDir = dirname($savePath);
            if (!is_dir($uploadDir)) {
                if (!@mkdir($uploadDir, 0755, true)) {
                    $promoMsg = "<div class='alert alert-danger'><button class='close' data-dismiss='alert'>&times;</button><strong>Cannot create upload directory.</strong></div>";
                }
            }
            
            if (move_uploaded_file($_FILES['promo_image']['tmp_name'], $savePath)) {
                // URL based on absolute file system path to document root
                $docRoot      = rtrim(str_replace('\\', '/', (string)$_SERVER['DOCUMENT_ROOT']), '/');
                $filePath     = str_replace('\\', '/', $savePath);
                $relativePath = str_replace($docRoot, '', $filePath);
                $promoImageUrl = $relativePath; // e.g., /fresh/user/admin/img/promo/promo_xxx.jpg
                setting_set($conn, 'promo_image_url', $promoImageUrl);
            } else {
                $promoMsg = "<div class='alert alert-danger'><button class='close' data-dismiss='alert'>&times;</button><strong>Failed to save image file. Check directory permissions.</strong></div>";
            }
        } else {
            $promoMsg = "<div class='alert alert-danger'><button class='close' data-dismiss='alert'>&times;</button><strong>Image must be JPEG/PNG/GIF/WebP and under 2 MB.</strong></div>";
        }
    }

    setting_set($conn, 'promo_enabled',        $promoEnabled);
    setting_set($conn, 'promo_headline',        $promoHeadline);
    setting_set($conn, 'promo_body',            $promoBody);
    setting_set($conn, 'promo_btn_label',       $promoBtnLabel);
    setting_set($conn, 'promo_btn_url',         $promoBtnUrl);
    setting_set($conn, 'promo_card_enabled',    $promoCardEnabled);
    setting_set($conn, 'promo_popup_enabled',   $promoPopupEnabled);
    setting_set($conn, 'promo_popup_condition', $promoPopupCondition);

    if ($promoMsg === '') {
        $promoMsg = "<div class='alert alert-success'><button class='close' data-dismiss='alert'>&times;</button><strong>Promo banner settings saved.</strong></div>";
    }
}

if (isset($_POST['clear_promo_image'])) {
    setting_set($conn, 'promo_image_url', '');
    $promoImageUrl = '';
    $promoMsg = "<div class='alert alert-success'><button class='close' data-dismiss='alert'>&times;</button><strong>Promo image cleared.</strong></div>";
}

$pageTitle = 'Settings';
require_once __DIR__ . '/partials/admin-shell-open.php';
?>

<?php if(isset($msg)) echo $msg; ?>
<?php if(isset($themeMsg)) echo $themeMsg; ?>
<?php if(isset($authThemeMsg)) echo $authThemeMsg; ?>
<?php if(isset($translatorMsg)) echo $translatorMsg; ?>
<?php if(isset($dormantMsg_notice)) echo $dormantMsg_notice; ?>
<?php if(isset($currencyMsg)) echo $currencyMsg; ?>
<?php if(isset($accountTypeMsg)) echo $accountTypeMsg; ?>
<?php if(isset($rateMsg)) echo $rateMsg; ?>

<!-- Tabs -->
<div class="mb-1" id="settings-tabs">
  <div class="flex flex-wrap gap-1 border-b border-gray-200 mb-6">
    <?php
    $tabs=["general"=>"General","currencies"=>"Currencies","account_types"=>"Account Types","exchange_rates"=>"Exchange Rates","transfer_settings"=>"Transfer Settings","promo"=>"Promo Banner"];
    $activeTab=$_GET['tab'] ?? 'general';
    foreach($tabs as $k=>$v):
      $active=$activeTab===$k;
    ?>
    <a href="?tab=<?= $k ?>" class="px-4 py-2 text-sm rounded-t-lg border-b-2 transition-colors
      <?= $active ? 'border-blue-600 text-blue-600 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700' ?>">
      <?= htmlspecialchars($v) ?>
    </a>
    <?php endforeach; ?>
  </div>
  <div class="mb-6">
    <a href="migrate.php" class="inline-flex items-center gap-2 bg-gray-900 hover:bg-black text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors cursor-pointer">
      <i class="fa-solid fa-screwdriver-wrench"></i> Open Build Migration
    </a>
  </div>
</div>

<!-- ── TAB: General ──────────────────────────────────────────────────────── -->
<?php if($activeTab==='general'): ?>
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">

  <!-- LEFT COLUMN -->
  <div class="space-y-6">
    <!-- Frontend Theme -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
      <h2 class="font-semibold text-gray-800 mb-4">Frontend Theme</h2>
      <form method="POST" class="space-y-4">
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Select Active Theme</label>
          <select name="theme" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <?php foreach($availThemes as $t): ?>
            <option value="<?= htmlspecialchars($t) ?>" <?= $t===$activeTheme?'selected':'' ?>><?= htmlspecialchars($t) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" name="set_theme" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors cursor-pointer"><i class="fa-solid fa-palette"></i> Apply Theme</button>
      </form>
    </div>
    <!-- Color Scheme -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
      <h2 class="font-semibold text-gray-800 mb-4">Color Scheme</h2>
      <?php if(isset($frontendThemeMsg)) echo $frontendThemeMsg; ?>
      <form method="POST" class="space-y-4">
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Frontend Theme Color Scheme</label>
          <select name="frontend_scheme" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <?php foreach($frontendSchemes as $k=>$v): ?>
            <option value="<?= htmlspecialchars($k) ?>" <?= $k===$activeFrontendScheme?'selected':'' ?>><?= htmlspecialchars($v) ?></option>
            <?php endforeach; ?>
          </select>
          <p class="text-xs text-gray-400 mt-1">Applies to frontend themes (theme2, theme3, theme4, theme5).</p>
        </div>
        <button type="submit" name="set_frontend_scheme" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors cursor-pointer"><i class="fa-solid fa-palette"></i> Save Frontend Scheme</button>
      </form>

      <div class="my-5 border-t border-gray-100"></div>

      <?php if(isset($authThemeMsg)) echo $authThemeMsg; ?>
      <form method="POST" class="space-y-4">
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Auth Pages Color Scheme</label>
          <select name="auth_scheme" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <?php foreach($authSchemes as $k=>$v): ?>
            <option value="<?= htmlspecialchars($k) ?>" <?= $k===$activeAuthScheme?'selected':'' ?>><?= htmlspecialchars($v) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" name="set_auth_scheme" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors cursor-pointer"><i class="fa-solid fa-swatchbook"></i> Save Scheme</button>
      </form>
    </div>
    <!-- Translator -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
      <h2 class="font-semibold text-gray-800 mb-4">Translator Languages</h2>
      <form method="POST" class="space-y-4">
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Comma-separated language codes (e.g. en,fr,es,de)</label>
          <input type="text" name="translator_languages" value="<?= htmlspecialchars($translatorLanguages) ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="en,fr,es,de">
        </div>
        <button type="submit" name="set_translator_languages" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors cursor-pointer"><i class="fa-solid fa-language"></i> Save Languages</button>
      </form>
    </div>
  </div>

  <!-- RIGHT COLUMN -->
  <div class="space-y-6">
    <!-- Dormant Message -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
      <h2 class="font-semibold text-gray-800 mb-4">Dormant Account Transfer Message</h2>
      <?php if(isset($dormantMsg_notice)) echo $dormantMsg_notice; ?>
      <form method="POST" class="space-y-4">
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Message shown when a dormant account tries to transfer</label>
          <textarea name="dormant_message" rows="4" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($dormantMessage) ?></textarea>
        </div>
        <button type="submit" name="set_dormant_message" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors cursor-pointer"><i class="fa-solid fa-floppy-disk"></i> Save Message</button>
      </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
      <h2 class="font-semibold text-gray-800 mb-4">Success Page Copy</h2>
      <?php if(isset($successCopyMsg)) echo $successCopyMsg; ?>
      <form method="POST" class="space-y-4">
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Success Heading</label>
          <input type="text" name="success_transfer_title" value="<?= htmlspecialchars($transferSuccessTitle) ?>" maxlength="120" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Transfer Initiated">
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Success Description</label>
          <textarea name="success_transfer_note" rows="3" maxlength="300" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="International transfers are processed within 2-3 business days."><?= htmlspecialchars($transferSuccessNote) ?></textarea>
        </div>
        <button type="submit" name="set_success_copy" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors cursor-pointer"><i class="fa-solid fa-floppy-disk"></i> Save Success Copy</button>
      </form>
    </div>

    <!-- Transaction Codes -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
      <h2 class="font-semibold text-gray-800 mb-4">Transaction Code Settings</h2>
      <?php if(isset($txCodesMsg)) echo $txCodesMsg; ?>
      <form method="POST" class="space-y-5">
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Maximum codes required per transaction</label>
          <select name="tx_max_codes" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <?php for($n=1;$n<=5;$n++): ?>
            <option value="<?= $n ?>" <?= $txMaxCodes===$n?'selected':'' ?>><?= $n ?> code<?= $n>1?'s':'' ?></option>
            <?php endfor; ?>
          </select>
          <p class="text-xs text-gray-400 mt-1">Only the first <em>N</em> codes will be required during a transfer.</p>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <?php for($i=1;$i<=5;$i++): $inactive=$i>$txMaxCodes; ?>
          <div <?= $inactive?'class="opacity-50"':'' ?>>
            <label class="block text-xs font-medium text-gray-700 mb-1">
              Code <?= $i ?> Name <?= $inactive?'<span class="text-gray-400 font-normal">(inactive)</span>':'' ?>
            </label>
            <input type="text" name="tx_code<?= $i ?>_name" value="<?= htmlspecialchars($txCodeNames[$i]) ?>"
              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="<?= ['','COT','TAX','IMF','LPPI','Code 5'][$i] ?>">
          </div>
          <?php endfor; ?>
        </div>
        <button type="submit" name="save_tx_codes" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors cursor-pointer"><i class="fa-solid fa-floppy-disk"></i> Save Code Settings</button>
      </form>
    </div>
  </div>

</div>

<!-- ── TAB: Currencies ────────────────────────────────────────────────── -->
<?php elseif($activeTab==='currencies'): ?>
<div class="space-y-6" id="currency-mgmt">
  <!-- Add currency -->
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 max-w-2xl">
    <h2 class="font-semibold text-gray-800 mb-4">Add Currency</h2>
    <form method="POST" class="grid grid-cols-2 sm:grid-cols-3 gap-4">
      <div><label class="block text-xs font-medium text-gray-700 mb-1">Code (e.g. USD)</label>
        <input type="text" name="cur_code" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="USD" maxlength="4" style="text-transform:uppercase"></div>
      <div><label class="block text-xs font-medium text-gray-700 mb-1">Symbol</label>
        <input type="text" id="cur_symbol" name="cur_symbol" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="$ or BTC" maxlength="120">
        <p class="mt-1 text-xs text-gray-500">Use the display symbol/ticker shown to users (for example $, EUR, BTC).</p>
        <div class="mt-2 rounded-lg border border-dashed border-gray-300 bg-gray-50 px-3 py-2 text-sm" id="cur-symbol-preview">
          <span class="text-gray-400 text-xs">No symbol yet.</span>
        </div></div>
      <div><label class="block text-xs font-medium text-gray-700 mb-1">Flag Code (ISO2)</label>
        <input type="text" name="cur_flag" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="US" maxlength="4" style="text-transform:uppercase"></div>
      <div><label class="block text-xs font-medium text-gray-700 mb-1">Name</label>
        <input type="text" name="cur_name" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="US Dollar"></div>
      <div><label class="block text-xs font-medium text-gray-700 mb-1">Sort Order</label>
        <input type="number" name="cur_order" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" value="99" min="0"></div>
      <div class="flex items-end">
        <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
          <input type="checkbox" id="cur_crypto" name="cur_crypto" class="rounded border-gray-300"> Crypto
        </label>
      </div>
      <div class="col-span-2 sm:col-span-3">
        <button type="submit" name="add_currency" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors cursor-pointer"><i class="fa-solid fa-plus"></i> Add Currency</button>
      </div>
    </form>
  </div>
  <!-- Currencies table -->
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h2 class="font-semibold text-gray-800 mb-4">Active Currencies</h2>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm border-collapse">
        <thead>
          <tr class="bg-gray-50 border-y border-gray-200">
            <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Code</th><th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Symbol</th><th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Flag</th>
            <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Name</th><th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Crypto</th><th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Active</th>
            <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php foreach($allCurrencies as $c): ?>
          <tr class="hover:bg-gray-50">
            <td class="px-3 py-3 text-sm text-gray-700 font-mono font-bold"><?= htmlspecialchars($c['code']) ?></td>
            <td class="px-3 py-3 text-sm text-gray-700"><?= htmlspecialchars((string)$c['symbol']) ?></td>
            <td class="px-3 py-3 text-sm text-gray-700">
              <?php
                $isCryptoRow = (int)($c['is_crypto'] ?? 0) === 1;
                $previewCode = $isCryptoRow
                  ? strtoupper(trim((string)($c['code'] ?? '')))
                  : strtoupper(trim((string)($c['flag_code'] ?? '')));
                if ($previewCode === '') {
                  $previewCode = strtoupper(trim((string)($c['code'] ?? '')));
                }
                $previewSrc = flag_asset_url($previewCode);
              ?>
              <?php if($previewSrc !== ''): ?>
              <img src="<?= htmlspecialchars($previewSrc) ?>" alt="<?= htmlspecialchars($previewCode) ?>" class="inline h-4 rounded-sm align-middle"> <?= htmlspecialchars($previewCode) ?>
              <?php else: ?><span class="text-gray-400 text-xs">—</span><?php endif; ?>
            </td>
            <td class="px-3 py-3 text-sm text-gray-700"><?= htmlspecialchars($c['name']) ?></td>
            <td class="px-3 py-3 text-sm text-gray-700"><?= $c['is_crypto'] ? '<span class="text-purple-600 text-xs font-medium">Yes</span>' : '<span class="text-gray-400 text-xs">No</span>' ?></td>
            <td class="px-3 py-3 text-sm text-gray-700">
              <form method="POST" class="inline">
                <input type="hidden" name="toggle_currency" value="<?= $c['id'] ?>">
                <button type="submit" class="text-xs <?= $c['is_active'] ? 'text-green-600' : 'text-gray-400' ?> hover:underline"><?= $c['is_active'] ? 'Active':'Disabled' ?></button>
              </form>
            </td>
            <td class="px-3 py-3 text-sm text-gray-700">
              <form method="POST" class="inline" onsubmit="return confirm('Delete currency?')">
                <input type="hidden" name="delete_currency" value="<?= $c['id'] ?>">
                <button type="submit" class="inline-flex items-center gap-2 bg-red-500 hover:bg-red-600 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition-colors cursor-pointer !py-0.5 !px-2"><i class="fa-solid fa-trash"></i></button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ── TAB: Account Types ─────────────────────────────────────────────── -->
<?php elseif($activeTab==='account_types'): ?>
<div class="space-y-6" id="account-types-mgmt">
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 max-w-2xl">
    <h2 class="font-semibold text-gray-800 mb-4">Add Account Type</h2>
    <form method="POST" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div><label class="block text-xs font-medium text-gray-700 mb-1">Label</label>
        <input type="text" name="at_label" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Savings Account"></div>
      <div><label class="block text-xs font-medium text-gray-700 mb-1">Key</label>
        <input type="text" name="at_key" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="savings"></div>
      <div><label class="block text-xs font-medium text-gray-700 mb-1">Min Balance</label>
        <input type="number" step="0.01" name="at_balance" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" value="0"></div>
      <div class="sm:col-span-3">
        <button type="submit" name="add_account_type" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors cursor-pointer"><i class="fa-solid fa-plus"></i> Add Type</button>
      </div>
    </form>
  </div>
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h2 class="font-semibold text-gray-800 mb-4">Account Types</h2>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm border-collapse">
        <thead>
          <tr class="bg-gray-50 border-y border-gray-200">
            <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Label</th><th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Key</th>
            <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Min Balance</th><th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Active</th>
            <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php foreach($allAccountTypes as $at): ?>
          <tr class="hover:bg-gray-50">
            <td class="px-3 py-3 text-sm text-gray-700 font-medium"><?= htmlspecialchars($at['label']) ?></td>
            <td class="px-3 py-3 text-sm text-gray-700 font-mono text-xs"><?= htmlspecialchars($at['type_key']) ?></td>
            <td class="px-3 py-3 text-sm text-gray-700 text-right"><?= number_format((float)$at['min_balance'],2) ?></td>
            <td class="px-3 py-3 text-sm text-gray-700">
              <form method="POST" class="inline">
                <input type="hidden" name="toggle_account_type" value="<?= $at['id'] ?>">
                <button type="submit" class="text-xs <?= $at['is_active'] ? 'text-green-600' : 'text-gray-400' ?> hover:underline"><?= $at['is_active'] ? 'Active' : 'Disabled' ?></button>
              </form>
            </td>
            <td class="px-3 py-3 text-sm text-gray-700">
              <button onclick="editType(<?= $at['id'] ?>,'<?= htmlspecialchars(addslashes($at['label'])) ?>',<?= $at['min_balance'] ?>)"
                class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors cursor-pointer !py-0.5 !px-2 !text-xs"><i class="fa-solid fa-pen"></i></button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <!-- Edit account type inline modal -->
  <div id="edit-type-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
    <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-sm">
      <h3 class="font-semibold mb-4">Edit Account Type</h3>
      <form method="POST" class="space-y-3">
        <input type="hidden" id="at-id" name="at_id">
        <div><label class="block text-xs font-medium text-gray-700 mb-1">Label</label><input type="text" id="at-label-e" name="at_label_edit" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
        <div><label class="block text-xs font-medium text-gray-700 mb-1">Min Balance</label><input type="number" step="0.01" id="at-bal-e" name="at_balance_edit" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
        <div class="flex gap-3 justify-end">
          <button type="button" onclick="document.getElementById('edit-type-modal').classList.add('hidden')" class="inline-flex items-center gap-2 bg-red-500 hover:bg-red-600 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition-colors cursor-pointer">Cancel</button>
          <button type="submit" name="update_account_type" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition-colors cursor-pointer">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ── TAB: Exchange Rates ────────────────────────────────────────────── -->
<?php elseif($activeTab==='exchange_rates'): ?>
<div class="space-y-6" id="exchange-rates-mgmt">
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 max-w-md">
    <h2 class="font-semibold text-gray-800 mb-4">Set Exchange Rate</h2>
    <form method="POST" class="space-y-4">
      <div class="grid grid-cols-2 gap-4">
        <div><label class="block text-xs font-medium text-gray-700 mb-1">From Currency</label>
          <select name="rate_from" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <?php foreach($allCurrencies as $c): ?><option value="<?= htmlspecialchars($c['code']) ?>"><?= htmlspecialchars($c['code']) ?></option><?php endforeach; ?>
          </select></div>
        <div><label class="block text-xs font-medium text-gray-700 mb-1">To Currency</label>
          <select name="rate_to" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <?php foreach($allCurrencies as $c): ?><option value="<?= htmlspecialchars($c['code']) ?>"><?= htmlspecialchars($c['code']) ?></option><?php endforeach; ?>
          </select></div>
      </div>
      <div><label class="block text-xs font-medium text-gray-700 mb-1">Rate</label>
        <input type="number" step="0.000001" name="rate_value" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="1.0500" required></div>
      <button type="submit" name="upsert_rate" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors cursor-pointer"><i class="fa-solid fa-arrows-rotate"></i> Set Rate</button>
    </form>
  </div>
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h2 class="font-semibold text-gray-800 mb-4">Current Rates</h2>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm border-collapse">
        <thead>
          <tr class="bg-gray-50 border-y border-gray-200">
            <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">From</th><th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">To</th>
            <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Rate</th><th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Source</th><th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Updated</th>
            <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Del</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php foreach($allRates as $r): ?>
          <tr class="hover:bg-gray-50">
            <td class="px-3 py-3 text-sm text-gray-700 font-mono font-bold"><?= htmlspecialchars($r['from_code']) ?></td>
            <td class="px-3 py-3 text-sm text-gray-700 font-mono font-bold"><?= htmlspecialchars($r['to_code']) ?></td>
            <td class="px-3 py-3 text-sm text-gray-700 text-right font-medium"><?= number_format((float)$r['rate'], 6) ?></td>
            <td class="px-3 py-3 text-sm text-gray-700 text-xs text-gray-500"><?= htmlspecialchars($r['source'] ?? 'manual') ?></td>
            <td class="px-3 py-3 text-sm text-gray-700 text-xs text-gray-500"><?= htmlspecialchars($r['updated_at'] ?? '') ?></td>
            <td class="px-3 py-3 text-sm text-gray-700">
              <form method="POST" class="inline" onsubmit="return confirm('Delete rate?')">
                <input type="hidden" name="delete_rate" value="<?= $r['id'] ?>">
                <button type="submit" class="inline-flex items-center gap-2 bg-red-500 hover:bg-red-600 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition-colors cursor-pointer !py-0.5 !px-2"><i class="fa-solid fa-trash"></i></button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php elseif($activeTab==='transfer_settings'): ?>
<div class="space-y-6" id="transfer-settings-mgmt">
  <?php if ($transferSettingsMsg) echo $transferSettingsMsg; ?>
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
  
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h2 class="font-semibold text-gray-800 mb-4">Transfer Auto-Update Configuration</h2>
    <p class="text-sm text-gray-600 mb-6">Configure automatic status updates for transfers and admin-controlled transfer statuses.</p>
    
    <form method="POST" class="space-y-6">
      <!-- Enable Auto-Update -->
      <div class="border-b border-gray-200 pb-6">
        <div class="flex items-center gap-3 mb-2">
          <input type="checkbox" name="auto_update_enabled" id="auto_update_enabled" value="1" <?php if ($transferAutoUpdateEnabled == '1') echo 'checked'; ?> class="w-4 h-4">
          <label for="auto_update_enabled" class="text-sm font-medium text-gray-900">Enable Automatic Status Updates</label>
        </div>
        <p class="text-xs text-gray-500 ml-7">When enabled, transfers marked as 'processing' will automatically update to the target status after the specified delay.</p>
      </div>

      <!-- Auto-Update Delay -->
      <div class="border-b border-gray-200 pb-6">
        <label class="block text-sm font-medium text-gray-700 mb-2">Auto-Update Delay (Minutes)</label>
        <input type="number" name="auto_update_delay" min="0" step="60" value="<?= htmlspecialchars($transferAutoUpdateDelay) ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
        <p class="text-xs text-gray-500 mt-1">Default: 1440 minutes (24 hours). Set delay before automatic status update occurs.</p>
      </div>

      <!-- Target Status -->
      <div class="border-b border-gray-200 pb-6">
        <label class="block text-sm font-medium text-gray-700 mb-2">Target Status After Auto-Update</label>
        <select name="auto_update_target_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
          <option value="pending" <?php if ($transferTargetStatus == 'pending') echo 'selected'; ?>>Pending</option>
          <option value="processing" <?php if ($transferTargetStatus == 'processing') echo 'selected'; ?>>Processing</option>
          <option value="completed" <?php if ($transferTargetStatus == 'completed') echo 'selected'; ?>>Completed</option>
          <option value="successful" <?php if ($transferTargetStatus == 'successful') echo 'selected'; ?>>Successful</option>
          <option value="failed" <?php if ($transferTargetStatus == 'failed') echo 'selected'; ?>>Failed</option>
          <option value="cancelled" <?php if ($transferTargetStatus == 'cancelled') echo 'selected'; ?>>Cancelled</option>
          <option value="reversed" <?php if ($transferTargetStatus == 'reversed') echo 'selected'; ?>>Reversed</option>
        </select>
        <p class="text-xs text-gray-500 mt-1">Status transfers will be automatically updated to after the delay expires.</p>
      </div>

      <!-- Initial Status -->
      <div class="border-b border-gray-200 pb-6">
        <label class="block text-sm font-medium text-gray-700 mb-2">Initial Status For New Transfers</label>
        <select name="initial_transfer_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
          <option value="pending" <?php if ($transferInitialStatus == 'pending') echo 'selected'; ?>>Pending</option>
          <option value="processing" <?php if ($transferInitialStatus == 'processing') echo 'selected'; ?>>Processing</option>
          <option value="completed" <?php if ($transferInitialStatus == 'completed') echo 'selected'; ?>>Completed</option>
          <option value="successful" <?php if ($transferInitialStatus == 'successful') echo 'selected'; ?>>Successful</option>
          <option value="failed" <?php if ($transferInitialStatus == 'failed') echo 'selected'; ?>>Failed</option>
          <option value="cancelled" <?php if ($transferInitialStatus == 'cancelled') echo 'selected'; ?>>Cancelled</option>
          <option value="reversed" <?php if ($transferInitialStatus == 'reversed') echo 'selected'; ?>>Reversed</option>
        </select>
        <p class="text-xs text-gray-500 mt-1">New transfers created from the user flow will start with this status.</p>
      </div>

      <!-- Cron Job Info -->
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <p class="text-xs font-semibold text-blue-900 mb-2">Clock icon Cron Job Setup</p>
        <p class="text-xs text-blue-800 mb-3">To enable automatic updates, add this line to your system crontab (runs every 15 minutes):</p>
        <div class="bg-white rounded border border-blue-300 p-3 font-mono text-xs text-gray-700 overflow-x-auto mb-3">
          */15 * * * * php <?= htmlspecialchars(dirname(__FILE__)) ?>/transfer_auto_update.php &gt;&gt; /tmp/transfer_auto_update.log 2&gt;&amp;1
        </div>
        <p class="text-xs text-blue-800">Logs will be saved to: <code class="bg-white px-1 py-0.5 rounded text-xs"><?= htmlspecialchars(dirname(__FILE__)) ?>/logs/</code></p>
      </div>

      <button type="submit" name="update_transfer_settings" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2 rounded-lg transition-colors">
        Save Transfer Settings
      </button>
    </form>
  </div>

  <div class="space-y-6">
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h3 class="font-semibold text-gray-800 mb-2">Transfer Result Page Messages</h3>
    <p class="text-sm text-gray-600 mb-5">Control what users see on the transfer result page for successful and failed transfers.</p>
    <?php if (isset($transferResultCopyMsg)) echo $transferResultCopyMsg; ?>

    <form method="POST" class="space-y-5">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Success Title</label>
          <input type="text" name="transfer_success_title" value="<?= htmlspecialchars($transferSuccessTitle) ?>" maxlength="120" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Transfer Initiated">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Failure Title</label>
          <input type="text" name="transfer_failure_title" value="<?= htmlspecialchars($transferFailureTitle) ?>" maxlength="120" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Transfer Failed">
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Success Description</label>
          <textarea name="transfer_success_note" rows="3" maxlength="400" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="International transfers are processed within 2-3 business days."><?= htmlspecialchars($transferSuccessNote) ?></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Failure Description</label>
          <textarea name="transfer_failure_note" rows="3" maxlength="400" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="This transfer could not be completed. Please contact support or try again."><?= htmlspecialchars($transferFailureNote) ?></textarea>
        </div>
      </div>

      <button type="submit" name="set_transfer_result_copy" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors cursor-pointer">
        <i class="fa-solid fa-floppy-disk"></i> Save Result Messages
      </button>
    </form>
  </div>

  <!-- Transfer Status History Info -->
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h3 class="font-semibold text-gray-800 mb-4">Admin Features</h3>
    <div class="space-y-4">
      <div class="flex gap-4">
        <div class="flex-shrink-0">
          <div class="flex items-center justify-center h-10 w-10 rounded-md bg-green-500 text-white">
            <i class="fa-solid fa-history"></i>
          </div>
        </div>
        <div>
          <h4 class="font-medium text-gray-900">View Status History</h4>
          <p class="text-sm text-gray-600">Admins can view complete audit trail of all status changes for every transfer.</p>
          <a href="transfer_status_history.php" class="text-sm text-blue-600 hover:text-blue-700 font-medium mt-2 inline-flex items-center gap-1">
            View History <i class="fa-solid fa-arrow-right"></i>
          </a>
        </div>
      </div>
      
      <div class="flex gap-4 pt-4 border-t border-gray-200">
        <div class="flex-shrink-0">
          <div class="flex items-center justify-center h-10 w-10 rounded-md bg-blue-500 text-white">
            <i class="fa-solid fa-pen"></i>
          </div>
        </div>
        <div>
          <h4 class="font-medium text-gray-900">Edit Transfer Status</h4>
          <p class="text-sm text-gray-600">Admins can manually update transfer status at any time with custom notes for audit trail.</p>
          <a href="transfer_rec.php" class="text-sm text-blue-600 hover:text-blue-700 font-medium mt-2 inline-flex items-center gap-1">
            View Transfers <i class="fa-solid fa-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>
  </div>
  </div>
  </div>
</div>
<?php elseif($activeTab==='promo'): ?>
<!-- ── TAB: Promo Banner ───────────────────────────────────────────────── -->
<div class="max-w-3xl space-y-6">
  <?php if ($promoMsg) echo $promoMsg; ?>
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h2 class="font-semibold text-gray-800 mb-1">Promo Banner Settings</h2>
    <p class="text-xs text-gray-500 mb-5">Configure a promotional card shown in the user dashboard and/or a popup on the public website.</p>
    <form method="POST" enctype="multipart/form-data" class="space-y-5">

      <!-- Master switch -->
      <div class="flex items-center gap-3 p-4 bg-gray-50 border border-gray-200 rounded-lg">
        <input type="checkbox" id="promo_enabled" name="promo_enabled" value="1" <?= $promoEnabled === '1' ? 'checked' : '' ?> class="w-4 h-4 accent-blue-600">
        <label for="promo_enabled" class="text-sm font-medium text-gray-800">Enable Promo Banner (master switch)</label>
      </div>

      <!-- Headline -->
      <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">Headline</label>
        <input type="text" name="promo_headline" value="<?= htmlspecialchars($promoHeadline) ?>" maxlength="120"
          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="e.g. Open a Premium Account Today">
      </div>

      <!-- Body text -->
      <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">Body Text</label>
        <textarea name="promo_body" rows="3" maxlength="400"
          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="Short description shown under the headline."><?= htmlspecialchars($promoBody) ?></textarea>
      </div>

      <!-- Action button -->
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Button Label <span class="text-gray-400 font-normal">(leave blank to hide)</span></label>
          <input type="text" name="promo_btn_label" value="<?= htmlspecialchars($promoBtnLabel) ?>" maxlength="60"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="e.g. Get Started">
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Button URL</label>
          <input type="text" name="promo_btn_url" value="<?= htmlspecialchars($promoBtnUrl) ?>" maxlength="500"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="https://... or /path/to/page">
        </div>
      </div>

      <!-- Image upload -->
      <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">Promo Image <span class="text-gray-400 font-normal">(JPEG/PNG/GIF/WebP, max 2 MB)</span></label>
        <?php if (!empty($promoImageUrl)): ?>
        <div class="mb-2 flex items-center gap-3">
          <img src="<?= htmlspecialchars($promoImageUrl) ?>" alt="Promo" class="h-20 rounded-lg border border-gray-200 object-cover">
          <button type="submit" name="clear_promo_image" class="text-xs text-red-600 hover:underline">Remove image</button>
        </div>
        <?php endif; ?>
        <input type="file" name="promo_image" accept="image/jpeg,image/png,image/gif,image/webp"
          class="block w-full text-sm text-gray-700 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-50 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-blue-700 hover:file:bg-blue-100">
      </div>

      <hr class="border-gray-200">

      <!-- Display options -->
      <div class="space-y-3">
        <p class="text-xs font-semibold text-gray-700 uppercase tracking-wide">Display Options</p>

        <div class="flex items-center gap-3">
          <input type="checkbox" id="promo_card_enabled" name="promo_card_enabled" value="1" <?= $promoCardEnabled === '1' ? 'checked' : '' ?> class="w-4 h-4 accent-blue-600">
          <label for="promo_card_enabled" class="text-sm text-gray-700">Show as 3rd card in the user dashboard <em>(Card Profile / Account Mix row)</em></label>
        </div>

        <div class="flex items-center gap-3">
          <input type="checkbox" id="promo_popup_enabled" name="promo_popup_enabled" value="1" <?= $promoPopupEnabled === '1' ? 'checked' : '' ?> class="w-4 h-4 accent-blue-600">
          <label for="promo_popup_enabled" class="text-sm text-gray-700">Show as popup on the public website (frontend themes)</label>
        </div>

        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Popup display condition</label>
          <select name="promo_popup_condition" class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="always"       <?= $promoPopupCondition === 'always'       ? 'selected' : '' ?>>Every page load</option>
            <option value="once_session" <?= $promoPopupCondition === 'once_session' ? 'selected' : '' ?>>Once per browser session</option>
            <option value="once_day"     <?= $promoPopupCondition === 'once_day'     ? 'selected' : '' ?>>Once per day</option>
            <option value="guest_only"   <?= $promoPopupCondition === 'guest_only'   ? 'selected' : '' ?>>Guests only (not logged-in users)</option>
          </select>
        </div>
      </div>

      <button type="submit" name="save_promo"
        class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2.5 rounded-lg transition-colors cursor-pointer">
        <i class="fa-solid fa-floppy-disk"></i> Save Promo Settings
      </button>
    </form>
  </div>
</div>
<?php endif; ?>
<script>
function editType(id, label, balance) {
  document.getElementById('at-id').value = id;
  document.getElementById('at-label-e').value = label;
  document.getElementById('at-bal-e').value = balance;
  document.getElementById('edit-type-modal').classList.remove('hidden');
}

(function () {
  var symbolInput = document.getElementById('cur_symbol');
  var cryptoCheckbox = document.getElementById('cur_crypto');
  var preview = document.getElementById('cur-symbol-preview');
  if (!symbolInput || !cryptoCheckbox || !preview) {
    return;
  }

  function escapeHtml(value) {
    return String(value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function sanitizeClassList(value) {
    return value
      .split(/\s+/)
      .filter(function (token) {
        return /^[A-Za-z0-9_-]+$/.test(token);
      })
      .join(' ')
      .trim();
  }

  function isFontAwesomeClassList(value) {
    if (!value) {
      return false;
    }
    var parts = value.split(/\s+/);
    for (var i = 0; i < parts.length; i++) {
      if (parts[i] === 'fa' || parts[i] === 'fas' || parts[i] === 'far' || parts[i] === 'fal' || parts[i] === 'fat' || parts[i] === 'fab' || parts[i].indexOf('fa-') === 0) {
        return true;
      }
    }
    return false;
  }

  function renderCurrencyPreview() {
    var raw = symbolInput.value.trim();
    if (!raw) {
      preview.innerHTML = '<span class="text-gray-400 text-xs">No symbol yet.</span>';
      return;
    }

    if (cryptoCheckbox.checked) {
      var classList = sanitizeClassList(raw);
      if (isFontAwesomeClassList(classList)) {
        preview.innerHTML = '<span class="inline-flex items-center gap-2 text-gray-700"><i class="' + escapeHtml(classList) + '"></i><span class="text-xs text-gray-400">' + escapeHtml(raw) + '</span></span>';
        return;
      }
    }

    preview.innerHTML = '<span class="text-gray-700">' + escapeHtml(raw) + '</span>';
  }

  symbolInput.addEventListener('input', renderCurrencyPreview);
  cryptoCheckbox.addEventListener('change', renderCurrencyPreview);
  renderCurrencyPreview();
})();
</script>

<?php require_once __DIR__ . '/partials/admin-shell-close.php'; ?>
