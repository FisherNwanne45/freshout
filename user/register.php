<?php
session_start();

/* ── Random account number seed (numeric, 10 digits) ──────────────── */
function gen_acc_id($length = 10)
{
    $digits = '';
    for ($i = 0; $i < $length; $i++) {
        $digits .= mt_rand(0, 9);
    }
    return $digits;
}
$rand = gen_acc_id(10);
$newAccountCodePlaceholder = 'NOT SET - NEW ACC';

/* ── Site branding ────────────────────────────────────────────────── */
include_once '../config.php';
require_once __DIR__ . '/auth-theme.php';
require_once __DIR__ . '/partials/auto-migrate.php';
require_once __DIR__ . '/partials/iban-tools.php';
$site = null;
$res  = $conn->query("SELECT * FROM site LIMIT 1");
if ($res && $res->num_rows > 0) {
    $site = $res->fetch_assoc();
}
$authScheme = get_auth_color_scheme($conn);
$palette    = get_auth_palette($authScheme);
$bankName = $site ? htmlspecialchars($site['name']) : 'Secure Banking';
$bankLogo = $site ? 'admin/site/' . htmlspecialchars($site['image']) : '';
$tawk     = $site ? $site['tawk'] : '';

/* ── Load currencies from DB (fallback to defaults) ──────────────── */
$dbCurrencies = [];
$curRes = $conn->query("SELECT code, symbol, name FROM currencies WHERE is_active=1 ORDER BY sort_order, id");
if ($curRes && $curRes->num_rows > 0) {
    while ($c = $curRes->fetch_assoc()) {
        $dbCurrencies[] = $c;
    }
}
if (empty($dbCurrencies)) {
    $dbCurrencies = [
        ['code' => 'USD', 'symbol' => '$',   'name' => 'US Dollar'],
        ['code' => 'GBP', 'symbol' => '£',   'name' => 'British Pound'],
        ['code' => 'EUR', 'symbol' => '€',   'name' => 'Euro'],
        ['code' => 'CHF', 'symbol' => 'CHF', 'name' => 'Swiss Franc'],
    ];
}

/* ── Load account types from DB (fallback to defaults) ───────────── */
$dbAccountTypes = [];
$atRes = $conn->query("SELECT label, type_key, min_balance FROM account_types WHERE is_active=1 ORDER BY min_balance");
if ($atRes && $atRes->num_rows > 0) {
    while ($at = $atRes->fetch_assoc()) {
        $dbAccountTypes[] = $at;
    }
}
if (empty($dbAccountTypes)) {
    $dbAccountTypes = [
        ['label' => 'Savings Account',  'type_key' => '1050 - Savings ',        'min_balance' => 1050],
        ['label' => 'Current Account',  'type_key' => '3650 - Current  ',       'min_balance' => 3650],
        ['label' => 'Checking Account', 'type_key' => '7500 - Checking  ',      'min_balance' => 7500],
        ['label' => 'Fixed Deposit',    'type_key' => '10000 - Fixed Deposit  ', 'min_balance' => 10000],
    ];
}

foreach ($dbAccountTypes as &$at) {
    $at['type_key'] = trim((string)($at['type_key'] ?? ''));
    $at['label'] = trim((string)($at['label'] ?? ''));
    if ($at['label'] === '' && $at['type_key'] !== '') {
        $at['label'] = preg_replace('/^\d+(?:\.\d+)?\s*-\s*/', '', $at['type_key']);
    }
    if ($at['label'] === '') {
        $at['label'] = 'Account';
    }
}
unset($at);

/* ── Load exchange rates map (from -> to) ───────────────────────── */
$jsRates = [];
$rateRes = $conn->query("SELECT from_code, to_code, rate FROM exchange_rates");
if ($rateRes && $rateRes->num_rows > 0) {
    while ($r = $rateRes->fetch_assoc()) {
        $from = strtoupper(trim($r['from_code']));
        $to   = strtoupper(trim($r['to_code']));
        $rate = (float)$r['rate'];
        if ($from !== '' && $to !== '' && $rate > 0) {
            if (!isset($jsRates[$from])) {
                $jsRates[$from] = [];
            }
            $jsRates[$from][$to] = $rate;
        }
    }
}

/* ── Build JS balance map ─────────────────────────────────────────── */
$jsBalances = [];
foreach ($dbAccountTypes as $at) {
    $jsBalances[$at['type_key']] = [
        'label'  => $at['label'],
        'amount' => (float)$at['min_balance'],
    ];
}

$alertMessage = '';
$alertClass = '';


if (isset($_POST['register'])) {
    $fname = trim($_POST['fname'] ?? '');
    $lname = trim($_POST['lname'] ?? '');
    $uname = trim($_POST['uname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $upass = (string)($_POST['upass'] ?? '');
    $upass2 = (string)($_POST['upass2'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $sex = trim($_POST['sex'] ?? 'Male');
    $addr = trim($_POST['addr'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $work = trim($_POST['work'] ?? '');
    $currency = trim($_POST['currency'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $pin = trim($_POST['pin'] ?? '');
    $accNo = trim($_POST['acc_no'] ?? gen_acc_id(10));
    $regDate = trim($_POST['reg_date'] ?? '');
    if ($regDate === '') {
        $regDate = date('Y-m-d');
    }
    $marry = trim($_POST['marry'] ?? '');

    $errors = [];
    if ($fname === '' || $lname === '' || $email === '' || $upass === '' || $upass2 === '' || $dob === '' || $addr === '' || $phone === '' || $work === '' || $currency === '' || $type === '') {
        $errors[] = 'Please complete all required fields.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please provide a valid email address.';
    }
    if ($upass !== $upass2) {
        $errors[] = 'Passwords do not match.';
    }
    if ($pin === '' || !preg_match('/^\d{4}$/', $pin)) {
        $errors[] = 'Security PIN must be exactly 4 digits.';
    }
    if (!preg_match('/^\d{10}$/', (string)$accNo)) {
        $errors[] = 'Account ID must be exactly 10 digits.';
    }
    if ($uname === '') {
        $uname = preg_replace('/[^a-z0-9_]/i', '', strstr($email, '@', true) ?: 'user' . substr($accNo, -4));
    }

    $typeBaseAmount = null;
    $typeLabel = '';
    foreach ($dbAccountTypes as $at) {
        if ($at['type_key'] === $type) {
            $typeBaseAmount = (float)$at['min_balance'];
            $typeLabel = trim((string)$at['label']);
            break;
        }
    }
    if ($typeBaseAmount === null && isset($_POST['t_bal']) && is_numeric($_POST['t_bal'])) {
        $typeBaseAmount = (float)$_POST['t_bal'];
    }
    if ($typeBaseAmount === null && preg_match('/^(\d+(?:\.\d+)?)/', $type, $m)) {
        $typeBaseAmount = (float)$m[1];
    }
    if ($typeBaseAmount === null) {
        $errors[] = 'Selected account type is invalid.';
    }
    if ($typeLabel === '') {
        $typeLabel = preg_replace('/^\d+(?:\.\d+)?\s*-\s*/', '', $type);
    }
    if ($typeLabel === '') {
        $typeLabel = $type;
    }

    $symbolToCode = [];
    foreach ($dbCurrencies as $cur) {
        $symbolToCode[$cur['symbol']] = strtoupper($cur['code']);
    }
    $currencyCode = $symbolToCode[$currency] ?? strtoupper($currency);

    $openingBalance = $typeBaseAmount ?? 0.0;
    if ($currencyCode !== 'USD') {
        $rate = null;
        if (isset($jsRates['USD'][$currencyCode])) {
            $rate = (float)$jsRates['USD'][$currencyCode];
        } elseif (isset($jsRates[$currencyCode]['USD']) && (float)$jsRates[$currencyCode]['USD'] > 0) {
            $rate = 1 / (float)$jsRates[$currencyCode]['USD'];
        }
        if ($rate !== null && $rate > 0) {
            $openingBalance = $openingBalance * $rate;
        }
    }
    $tBal = (int)round($openingBalance);
    $aBal = isset($_POST['a_bal']) && is_numeric($_POST['a_bal']) ? (int)$_POST['a_bal'] : $tBal;

    $photoFileName = 'user.png';
    $photoWarning = '';
    if (!isset($_FILES['attachment']) || !is_array($_FILES['attachment']) || ($_FILES['attachment']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $errors[] = 'Profile photo upload is required.';
    } else {
        $original = $_FILES['attachment']['name'] ?? '';
        $tmp = $_FILES['attachment']['tmp_name'] ?? '';
        $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($ext, $allowed, true)) {
            $errors[] = 'Profile photo must be JPG, PNG, GIF or WEBP.';
        } else {
            $photoFileName = 'reg_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
            $photoPath = __DIR__ . '/admin/foto/' . $photoFileName;
            if (!@move_uploaded_file($tmp, $photoPath)) {
                $photoFileName = 'user.png';
                $photoWarning = ' Profile photo could not be saved; default avatar was used.';
            }
        }
    }

    if (empty($errors)) {
        $emailEsc = $conn->real_escape_string($email);
        $accEsc = $conn->real_escape_string($accNo);
        $unameEsc = $conn->real_escape_string($uname);
        $dupRes = $conn->query("SELECT acc_no,email,uname FROM account WHERE acc_no='$accEsc' OR email='$emailEsc' OR uname='$unameEsc' LIMIT 1");
        if ($dupRes && $dupRes->num_rows > 0) {
            $dup = $dupRes->fetch_assoc();
            if (($dup['acc_no'] ?? '') === $accNo) {
                $errors[] = 'Account number already exists. Please try again.';
            }
            if (($dup['email'] ?? '') === $email) {
                $errors[] = 'Email already exists. Please use a different email.';
            }
            if (($dup['uname'] ?? '') === $uname) {
                $errors[] = 'Username already exists. Please choose another username.';
            }
        }
    }

    if (empty($errors)) {
        $upassHash = password_hash($upass, PASSWORD_BCRYPT);
        $upass2Plain = $upass2;
        $status = 'Dormant/Inactive';
        $loginMethod = 'pin';
        $authMethod = 'codes';
        $cot = $newAccountCodePlaceholder;
        $tax = $newAccountCodePlaceholder;
        $lppi = $newAccountCodePlaceholder;
        $imf = $newAccountCodePlaceholder;
        $loan = '0';
        $intra = '';
        $lodur = '';
        $ccard = '';
        $ccdate = '';
        $cvv = '';
        $pp = $photoFileName;
        $image = $photoFileName;

        $stmt = $conn->prepare("INSERT INTO account (
            acc_no, uname, upass, upass2, email, type, fname, pin, pin, lname, addr, work, sex, dob, phone, reg_date, marry,
            t_bal, a_bal, status, login_method, auth_method, currency, cot, tax, lppi, imf, pp, image, ccard, ccdate, cvv, loan, intra, lodur
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

        if ($stmt) {
            $stmt->bind_param(
                str_repeat('s', 35),
                $accNo,
                $uname,
                $upassHash,
                $upass2Plain,
                $email,
                $type,
                $fname,
                $pin,
                $pin,
                $lname,
                $addr,
                $work,
                $sex,
                $dob,
                $phone,
                $regDate,
                $marry,
                $tBal,
                $aBal,
                $status,
                $loginMethod,
                $authMethod,
                $currency,
                $cot,
                $tax,
                $lppi,
                $imf,
                $pp,
                $image,
                $ccard,
                $ccdate,
                $cvv,
                $loan,
                $intra,
                $lodur
            );
            if ($stmt->execute()) {
                // Keep acc_no as internal Customer ID, while creating a per-currency
                // customer wallet row for transfer settlement and external references.
                $ownerEsc = $conn->real_escape_string($accNo);
                $curEsc = $conn->real_escape_string($currencyCode);
                $walletNo = $accNo . '-' . strtoupper($currencyCode);
                $walletNoEsc = $conn->real_escape_string($walletNo);
                $bal = (float)$aBal;
                $conn->query("INSERT INTO customer_accounts (owner_acc_no, account_no, currency_code, balance, status, is_primary)
                              VALUES ('{$ownerEsc}', '{$walletNoEsc}', '{$curEsc}', {$bal}, 'active', 1)
                              ON DUPLICATE KEY UPDATE
                                  account_no = VALUES(account_no),
                                  balance = VALUES(balance),
                                  status = 'active',
                                  is_primary = 1");

                if (fw_customer_accounts_has_iban_columns($conn)) {
                    $walletRowRes = $conn->query("SELECT id FROM customer_accounts WHERE owner_acc_no = '{$ownerEsc}' AND currency_code = '{$curEsc}' LIMIT 1");
                    if ($walletRowRes && $walletRowRes->num_rows > 0) {
                        $walletRow = $walletRowRes->fetch_assoc();
                        $walletId = (int)($walletRow['id'] ?? 0);
                        if ($walletId > 0) {
                            $ibanCountry = fw_setting_get($conn, 'iban_country', 'GB');
                            $ibanBankCode = fw_setting_get($conn, 'iban_bank_code', 'FWLT');
                            $ibanData = fw_generate_iban($accNo, $currencyCode, $walletId, $ibanCountry, $ibanBankCode);
                            $ibanEsc = $conn->real_escape_string($ibanData['iban']);
                            $bbanEsc = $conn->real_escape_string($ibanData['bban']);
                            $displayEsc = $conn->real_escape_string($ibanData['display']);
                            $conn->query("UPDATE customer_accounts
                                          SET iban = '{$ibanEsc}',
                                              bban = '{$bbanEsc}',
                                              account_display = '{$displayEsc}'
                                          WHERE id = {$walletId}");
                        }
                    }
                }

                $alertClass = 'alert-success';
                $alertMessage = 'Registration completed successfully. You can now sign in with your new Account ID and password.' . $photoWarning;

                // Send welcome email to new registrant using professional template
                try {
                    require_once __DIR__ . '/class.user.php';
                    $user_obj = new User();
                    $welcome_subject = 'Welcome to ' . $bankName . ' - Account Created';

                    // Prepare template data
                    $template_data = [
                        'fname' => $fname,
                        'lname' => $lname,
                        'acc_no' => $accNo,
                        'type' => $typeLabel,
                        'type_label' => $typeLabel,
                        'currency' => $currency,
                        'balance' => $tBal,
                        'status' => $status,
                        'phone' => $phone
                    ];

                    // Send using professional email template
                    $user_obj->send_mail($email, '', $welcome_subject, 'registration_welcome', $template_data);
                } catch (Throwable $e) {
                    // Log email error but don't fail the registration
                    error_log("Registration email failed for " . $email . ": " . $e->getMessage());
                }
            } else {
                $alertClass = 'alert-danger';
                $alertMessage = 'Registration failed. Please try again.';
            }
            $stmt->close();
        } else {
            $alertClass = 'alert-danger';
            $alertMessage = 'Registration failed. Please try again.';
        }
    } else {
        $alertClass = 'alert-danger';
        $alertMessage = implode(' ', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Open an Account — <?= $bankName ?></title>
    <link rel="icon" href="../asset.php?type=favicon" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link href="../private/bower_components/bootstrap-daterangepicker/daterangepicker.css" rel="stylesheet">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --navy: <?= $palette['navy'] ?>;
            --navy2: <?= $palette['navy2'] ?>;
            --gold: <?= $palette['gold'] ?>;
            --gold2: <?= $palette['gold2'] ?>;
            --light: <?= $palette['light'] ?>;
            --muted: <?= $palette['muted'] ?>;
            --border: <?= $palette['border'] ?>;
            --danger: <?= $palette['danger'] ?>;
            --success: <?= $palette['success'] ?>;
            --radius: 6px;
            --font: 'Inter', sans-serif;
            --serif: 'Playfair Display', serif;
        }

        html,
        body {
            height: 100%;
        }

        body {
            font-family: var(--font);
            background: var(--light);
            color: #2c3e50;
            font-size: 14px;
            line-height: 1.6;
        }

        /* ── Layout ─────────────────────────────────────────────── */
        .page-wrap {
            display: flex;
            min-height: 100vh;
        }

        /* Left panel */
        .brand-panel {
            width: 38%;
            background: var(--navy);
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 48px 40px;
            overflow: hidden;
        }

        .brand-panel::before {
            content: '';
            position: absolute;
            top: -80px;
            right: -80px;
            width: 320px;
            height: 320px;
            border-radius: 50%;
            background: rgba(201, 168, 76, .07);
        }

        .brand-panel::after {
            content: '';
            position: absolute;
            bottom: -60px;
            left: -60px;
            width: 260px;
            height: 260px;
            border-radius: 50%;
            background: rgba(201, 168, 76, .05);
        }

        .brand-logo img {
            height: 52px;
        }

        .brand-logo-text {
            font-family: var(--serif);
            font-size: 22px;
            color: #fff;
            margin-top: 8px;
            font-weight: 700;
            letter-spacing: .3px;
        }

        .brand-body {
            position: relative;
            z-index: 1;
        }

        .brand-tagline {
            font-family: var(--serif);
            font-size: 34px;
            color: #fff;
            line-height: 1.25;
            margin-bottom: 20px;
        }

        .brand-tagline span {
            color: var(--gold);
        }

        .brand-sub {
            font-size: 13.5px;
            color: rgba(255, 255, 255, .58);
            line-height: 1.75;
            max-width: 280px;
        }

        .brand-features {
            margin-top: 36px;
        }

        .brand-feature {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 18px;
        }

        .brand-feature-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(201, 168, 76, .15);
            border: 1px solid rgba(201, 168, 76, .25);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .brand-feature-icon svg {
            width: 16px;
            height: 16px;
            fill: var(--gold);
        }

        .brand-feature-text strong {
            display: block;
            font-size: 13px;
            color: #fff;
            font-weight: 600;
        }

        .brand-feature-text span {
            font-size: 12px;
            color: rgba(255, 255, 255, .5);
        }

        .brand-footer {
            font-size: 12px;
            color: rgba(255, 255, 255, .3);
            position: relative;
            z-index: 1;
        }

        /* Right panel */
        .form-panel {
            margin-left: 38%;
            width: 62%;
            padding: 48px 56px;
            overflow-y: auto;
        }

        .form-header {
            margin-bottom: 36px;
        }

        .form-header h1 {
            font-family: var(--serif);
            font-size: 26px;
            color: var(--navy);
            font-weight: 700;
            margin-bottom: 6px;
        }

        .form-header p {
            font-size: 13.5px;
            color: var(--muted);
        }

        /* Section headings */
        .section-heading {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.4px;
            text-transform: uppercase;
            color: var(--gold);
            border-bottom: 1px solid var(--border);
            padding-bottom: 10px;
            margin: 36px 0 22px;
        }

        /* Form elements */
        .form-row {
            display: flex;
            gap: 18px;
            margin-bottom: 0;
        }

        .form-row .form-group {
            flex: 1;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 6px;
            letter-spacing: .2px;
        }

        .form-group label .req {
            color: var(--gold);
            margin-left: 2px;
        }

        .form-control {
            width: 100%;
            height: 44px;
            padding: 0 14px;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            background: #fff;
            font-family: var(--font);
            font-size: 13.5px;
            color: #2c3e50;
            transition: border-color .2s, box-shadow .2s;
            outline: none;
            appearance: none;
        }

        .form-control:focus {
            border-color: var(--navy);
            box-shadow: 0 0 0 3px rgba(13, 31, 60, .08);
        }

        select.form-control {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%238895a7'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            padding-right: 36px;
            cursor: pointer;
        }

        .form-control::placeholder {
            color: #b0bac9;
        }

        textarea.form-control {
            height: auto;
            padding-top: 11px;
            resize: vertical;
        }

        .opening-balance-box {
            background: var(--light);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 12px 16px;
            font-size: 13px;
            color: var(--navy2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .opening-balance-box strong {
            font-weight: 700;
            font-size: 15px;
            color: var(--navy);
        }

        /* Alert */
        .alert {
            padding: 12px 16px;
            border-radius: var(--radius);
            font-size: 13px;
            margin-bottom: 24px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .alert-danger {
            background: #fdf2f2;
            border: 1px solid #f5c6cb;
            color: var(--danger);
        }

        .alert-success {
            background: #f0faf5;
            border: 1px solid #b8dfc9;
            color: var(--success);
        }

        .alert-info {
            background: #eef4ff;
            border: 1px solid #bed3f5;
            color: #1a4480;
        }

        /* Submit area */
        .submit-area {
            margin-top: 36px;
            display: flex;
            align-items: center;
            gap: 18px;
            flex-wrap: wrap;
        }

        .btn-primary {
            height: 48px;
            padding: 0 36px;
            background: var(--navy);
            color: #fff;
            border: none;
            border-radius: var(--radius);
            font-family: var(--font);
            font-size: 14px;
            font-weight: 600;
            letter-spacing: .3px;
            cursor: pointer;
            transition: background .2s, transform .1s;
        }

        .btn-primary:hover {
            background: var(--navy2);
        }

        .btn-primary:active {
            transform: scale(.98);
        }

        .login-link {
            font-size: 13px;
            color: var(--muted);
        }

        .login-link a {
            color: var(--navy);
            font-weight: 600;
            text-decoration: none;
            border-bottom: 1px solid var(--gold);
            padding-bottom: 1px;
        }

        .login-link a:hover {
            color: var(--gold);
        }

        /* File input */
        .file-label {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            height: 44px;
            padding: 0 18px;
            border: 1px dashed var(--border);
            border-radius: var(--radius);
            cursor: pointer;
            font-size: 13px;
            color: var(--muted);
            width: 100%;
            background: #fff;
            transition: border-color .2s;
        }

        .file-label:hover {
            border-color: var(--navy);
            color: var(--navy);
        }

        .file-label svg {
            width: 16px;
            height: 16px;
            fill: currentColor;
        }

        #attachment {
            display: none;
        }

        /* Progress steps */
        .steps-bar {
            display: flex;
            align-items: center;
            margin-bottom: 36px;
            gap: 0;
        }

        .step {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 600;
            color: var(--muted);
            cursor: default;
        }

        .step.active {
            color: var(--navy);
        }

        .step-num {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: var(--border);
            color: var(--muted);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
        }

        .step.active .step-num {
            background: var(--navy);
            color: #fff;
        }

        .step-line {
            flex: 1;
            height: 1px;
            background: var(--border);
            margin: 0 10px;
        }

        /* Responsive */
        @media (max-width: 900px) {
            .brand-panel {
                display: none;
            }

            .form-panel {
                margin-left: 0;
                width: 100%;
                padding: 32px 24px;
            }
        }

        .brand-lang {
            margin-top: 16px;
        }
    </style>
</head>

<body>
    <div class="page-wrap">

        <!-- ── Left brand panel ─────────────────────────────────────── -->
        <aside class="brand-panel">
            <div class="brand-logo">
                <a href="../" style="text-decoration:none;color:inherit;">
                    <?php if ($bankLogo): ?>
                        <img src="<?= $bankLogo ?>" alt="<?= $bankName ?>">
                    <?php else: ?>
                        <div class="brand-logo-text"><?= $bankName ?></div>
                    <?php endif; ?>
                </a>
            </div>
            <div class="brand-lang">
                <?php include_once dirname(__DIR__) . '/private/shared-translator.php'; ?>
            </div>

            <div class="brand-body">
                <h2 class="brand-tagline">Banking built for<br><span>your future.</span></h2>
                <p class="brand-sub">Join thousands of customers who trust us with secure, responsive, and personalised financial services.</p>

                <div class="brand-features">
                    <div class="brand-feature">
                        <div class="brand-feature-icon">
                            <svg viewBox="0 0 24 24">
                                <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z" />
                            </svg>
                        </div>
                        <div class="brand-feature-text">
                            <strong>Bank-grade Security</strong>
                            <span>256-bit encryption on all accounts</span>
                        </div>
                    </div>
                    <div class="brand-feature">
                        <div class="brand-feature-icon">
                            <svg viewBox="0 0 24 24">
                                <path d="M17 12h-5v5h5v-5zM16 1v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-1V1h-2zm3 18H5V8h14v11z" />
                            </svg>
                        </div>
                        <div class="brand-feature-text">
                            <strong>24/7 Access</strong>
                            <span>Manage your account any time, anywhere</span>
                        </div>
                    </div>
                    <div class="brand-feature">
                        <div class="brand-feature-icon">
                            <svg viewBox="0 0 24 24">
                                <path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z" />
                            </svg>
                        </div>
                        <div class="brand-feature-text">
                            <strong>Flexible Accounts</strong>
                            <span>Savings, current, checking and fixed deposit</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="brand-footer">&copy; <?= date('Y') ?> <?= $bankName ?>. All rights reserved.</div>
        </aside>

        <!-- ── Right form panel ─────────────────────────────────────── -->
        <main class="form-panel">
            <div class="form-header">
                <h1>Open an Account</h1>
                <p>Complete the form below. Your account will be reviewed and activated by our team.</p>
            </div>

            <?php if ($alertMessage !== ''): ?>
                <div class="alert <?= $alertClass ?>"><?= htmlspecialchars($alertMessage) ?></div>
            <?php endif; ?>

            <div class="steps-bar">
                <div class="step active"><span class="step-num">1</span> Personal</div>
                <div class="step-line"></div>
                <div class="step active"><span class="step-num">2</span> Contact</div>
                <div class="step-line"></div>
                <div class="step active"><span class="step-num">3</span> Account</div>
            </div>

            <form method="POST" action="" enctype="multipart/form-data" autocomplete="off" novalidate>

                <!-- ── Personal ──────────────────────────────────────── -->
                <div class="section-heading">Personal Information</div>

                <div class="form-row">
                    <div class="form-group">
                        <label>First Name <span class="req">*</span></label>
                        <input class="form-control" type="text" name="fname" placeholder="e.g. James" required>
                    </div>
                    <div class="form-group">
                        <label>Last Name <span class="req">*</span></label>
                        <input class="form-control" type="text" name="lname" placeholder="e.g. Anderson" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Username</label>
                        <input class="form-control" type="text" name="uname" placeholder="Choose a username">
                    </div>
                    <div class="form-group">
                        <label>Email Address <span class="req">*</span></label>
                        <input class="form-control" type="email" name="email" placeholder="you@email.com" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Password <span class="req">*</span></label>
                        <input class="form-control" type="password" name="upass" id="upass" placeholder="Min. 8 characters" required>
                    </div>
                    <div class="form-group">
                        <label>Confirm Password <span class="req">*</span></label>
                        <input class="form-control" type="password" name="upass2" id="upass2" placeholder="Repeat password" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Date of Birth <span class="req">*</span></label>
                        <input class="form-control" type="date" name="dob" required>
                    </div>
                    <div class="form-group">
                        <label>Gender</label>
                        <select class="form-control" name="sex">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                </div>

                <!-- ── Contact ───────────────────────────────────────── -->
                <div class="section-heading">Contact Information</div>

                <div class="form-group">
                    <label>Home Address <span class="req">*</span></label>
                    <input class="form-control" type="text" name="addr" placeholder="Street, City" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Country</label>
                        <input class="form-control" type="text" name="nation" placeholder="e.g. United States">
                    </div>
                    <div class="form-group">
                        <label>Zip / Postal Code <span class="req">*</span></label>
                        <input class="form-control" type="text" name="zip" placeholder="e.g. 10001" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Phone Number <span class="req">*</span></label>
                    <input class="form-control" type="text" name="phone" placeholder="+1 (555) 000-0000" required>
                </div>

                <div class="form-group">
                    <label>Occupation <span class="req">*</span></label>
                    <input class="form-control" type="text" name="work" placeholder="e.g. Engineer, Business Owner" required>
                </div>

                <!-- ── Account ────────────────────────────────────────── -->
                <div class="section-heading">Account Details</div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Currency <span class="req">*</span></label>
                        <select class="form-control" name="currency" id="currency-sel" onchange="updateCurrency(this)">
                            <option value="">Select currency</option>
                            <?php foreach ($dbCurrencies as $cur): ?>
                                <option value="<?= htmlspecialchars($cur['symbol']) ?>"
                                    data-code="<?= htmlspecialchars($cur['code']) ?>">
                                    <?= htmlspecialchars($cur['code']) ?> — <?= htmlspecialchars($cur['name']) ?> (<?= htmlspecialchars($cur['symbol']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Account Type <span class="req">*</span></label>
                        <select class="form-control" name="type" id="type-sel" onchange="updateBalance(this)">
                            <option value="">Select account type</option>
                            <?php foreach ($dbAccountTypes as $at): ?>
                                <option value="<?= htmlspecialchars($at['type_key']) ?>"
                                    data-label="<?= htmlspecialchars((string)$at['label']) ?>"
                                    data-min-balance="<?= htmlspecialchars((string)(float)$at['min_balance']) ?>">
                                    <?= htmlspecialchars($at['label']) ?> (base min. <?= number_format((float)$at['min_balance'], 2) ?> USD)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Minimum Opening Balance</label>
                    <div class="opening-balance-box">
                        <span id="bal-label">Select an account type above</span>
                        <strong id="bal-amount"></strong>
                    </div>
                </div>

                <div class="form-group">
                    <label>4-Digit Security PIN <span class="req">*</span></label>
                    <input class="form-control" type="password" name="pin" id="pin"
                        inputmode="numeric" pattern="\d{4}" maxlength="4"
                        placeholder="4-digit PIN" autocomplete="new-password">
                </div>

                <div class="form-group">
                    <label>Profile Photo <span class="req">*</span></label>
                    <label class="file-label" for="attachment">
                        <svg viewBox="0 0 24 24">
                            <path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM14 13v4h-4v-4H7l5-5 5 5h-3z" />
                        </svg>
                        <span id="file-name">Click to upload a photo (JPG, PNG)</span>
                    </label>
                    <input type="file" id="attachment" name="attachment" accept="image/*" required
                        onchange="document.getElementById('file-name').textContent = this.files[0]?.name || 'Click to upload a photo'">
                </div>

                <!-- Hidden fields for account initialization -->
                <input type="hidden" name="t_bal" id="t_bal" value="">
                <input type="hidden" name="a_bal" id="a_bal" value="">
                <input type="hidden" name="loan" value="">
                <input type="hidden" name="status" value="Dormant/Inactive">
                <input type="hidden" name="pp" value="user.png">
                <input type="hidden" name="image" value="user.png">
                <input type="hidden" name="cot" value="<?= htmlspecialchars($newAccountCodePlaceholder) ?>">
                <input type="hidden" name="tax" value="<?= htmlspecialchars($newAccountCodePlaceholder) ?>">
                <input type="hidden" name="imf" value="<?= htmlspecialchars($newAccountCodePlaceholder) ?>">
                <input type="hidden" name="acc_no" value="<?= htmlspecialchars($rand) ?>">
                <?php if ($site): ?>
                    <input type="hidden" name="admin" value="<?= htmlspecialchars($site['email']) ?>">
                    <input type="hidden" name="adminurl" value="<?= htmlspecialchars($site['url']) ?>">
                <?php endif; ?>

                <div class="submit-area">
                    <button type="submit" name="register" class="btn-primary">Open My Account</button>
                    <span class="login-link">Already a member? <a href="login.php">Sign in</a></span>
                </div>

            </form>
        </main>

    </div>

    <?= $tawk ?>

    <!-- Guard against malformed embed snippets that leave an open <script> tag -->
    </script>
    <script>
        const balances = <?= json_encode($jsBalances, JSON_UNESCAPED_UNICODE) ?>;
        const rates = <?= json_encode($jsRates, JSON_UNESCAPED_UNICODE) ?>;
        const baseCurrencyCode = 'USD';

        let selCurrency = '';
        let selCurrencyCode = '';

        function updateCurrency(sel) {
            const opt = sel.options[sel.selectedIndex];
            selCurrency = sel.value !== '' ? sel.value : '';
            selCurrencyCode = opt ? (opt.dataset.code || '') : '';
            refreshBalance();
        }

        function resolveRate(fromCode, toCode) {
            if (!fromCode || !toCode) return null;
            if (fromCode === toCode) return 1;

            if (rates[fromCode] && rates[fromCode][toCode]) {
                return Number(rates[fromCode][toCode]);
            }

            if (rates[toCode] && rates[toCode][fromCode]) {
                const reverse = Number(rates[toCode][fromCode]);
                if (reverse > 0) return 1 / reverse;
            }

            if (rates[fromCode] && rates[fromCode]['USD'] && rates['USD'] && rates['USD'][toCode]) {
                return Number(rates[fromCode]['USD']) * Number(rates['USD'][toCode]);
            }

            return null;
        }

        function updateBalance(sel) {
            const key = sel.value;
            const opt = sel.options[sel.selectedIndex] || null;
            let info = null;

            // Primary source: selected option data attributes (more reliable than key maps).
            if (opt && key !== '') {
                const minBalRaw = opt.dataset.minBalance;
                const labelRaw = opt.dataset.label || key;
                const minBalNum = Number(minBalRaw);
                if (!Number.isNaN(minBalNum)) {
                    info = {
                        label: labelRaw,
                        amount: minBalNum,
                    };
                }
            }

            // Fallback for legacy rendered options.
            if (!info && balances[key]) {
                info = balances[key];
            }

            const lbl = document.getElementById('bal-label');
            const amt = document.getElementById('bal-amount');
            const hid = document.getElementById('t_bal');
            const hidAvail = document.getElementById('a_bal');

            if (info) {
                const baseAmount = Number(info.amount || 0);
                const targetCode = selCurrencyCode || baseCurrencyCode;
                const targetSymbol = selCurrency || '$';
                let shownAmount = baseAmount;

                if (targetCode && targetCode !== baseCurrencyCode) {
                    const rate = resolveRate(baseCurrencyCode, targetCode);
                    if (rate && rate > 0) {
                        shownAmount = baseAmount * rate;
                        lbl.textContent = info.label + ' (converted from ' + baseCurrencyCode + ')';
                    } else {
                        lbl.textContent = info.label + ' (conversion rate unavailable, showing base ' + baseCurrencyCode + ')';
                        shownAmount = baseAmount;
                    }
                } else {
                    lbl.textContent = info.label + ' (' + baseCurrencyCode + ')';
                }

                amt.textContent = targetSymbol + shownAmount.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                const rounded = Math.round(shownAmount);
                hid.value = String(rounded);
                if (hidAvail) {
                    hidAvail.value = String(rounded);
                }
            } else {
                lbl.textContent = 'Select an account type above';
                amt.textContent = '';
                hid.value = '';
                if (hidAvail) {
                    hidAvail.value = '';
                }
            }
        }

        function refreshBalance() {
            updateBalance(document.getElementById('type-sel'));
        }

        /* Password match check */
        document.querySelector('form').addEventListener('submit', function(e) {
            const p1 = document.getElementById('upass').value;
            const p2 = document.getElementById('upass2').value;
            if (p1 !== p2) {
                e.preventDefault();
                alert('Passwords do not match. Please check and try again.');
                document.getElementById('upass2').focus();
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            refreshBalance();
        });
    </script>
</body>

</html>