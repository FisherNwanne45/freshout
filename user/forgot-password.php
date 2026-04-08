<?php
session_start();

require 'connectdb.php';
require_once 'class.user.php';
include_once '../config.php';
require_once __DIR__ . '/partials/auto-migrate.php';
require_once __DIR__ . '/auth-theme.php';

$reg_user = new USER();

$site = null;
$res = $conn->query("SELECT * FROM site LIMIT 1");
if ($res && $res->num_rows > 0) {
    $site = $res->fetch_assoc();
}

$authScheme = get_auth_color_scheme($conn);
$palette = get_auth_palette($authScheme);

$bankName = $site ? htmlspecialchars((string)$site['name']) : 'Secure Banking';
$bankLogo = $site ? 'admin/site/' . htmlspecialchars((string)$site['image']) : '';
$tawk = $site ? (string)$site['tawk'] : '';

$msg = '';
$msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim((string)($_POST['action'] ?? ''));

    if ($action === 'request_otp') {
        $accNo = trim((string)($_POST['acc_no'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));

        if ($accNo === '' || $email === '') {
            $msg = 'Enter your Account ID and registered email.';
            $msgType = 'error';
        } else {
            $stmt = $reg_user->runQuery('SELECT acc_no, email, fname, status FROM account WHERE acc_no = :acc_no LIMIT 1');
            $stmt->execute([':acc_no' => $accNo]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $matched = $row && strcasecmp(trim((string)$row['email']), $email) === 0;

            if ($matched) {
                $status = strtolower(trim((string)($row['status'] ?? 'active')));
                if ($status === 'closed') {
                    $msg = 'This account is closed. Please contact support.';
                    $msgType = 'error';
                } else {
                    if (!$reg_user->hasActiveOtp($accNo, $email, 'password_reset')) {
                        $otp = $reg_user->createOtp($accNo, $email, 'password_reset', 10);
                        if ($otp !== '') {
                            $reg_user->send_mail($email, '', 'Your Password Reset OTP', 'otp_code', [
                                'fname' => (string)($row['fname'] ?? ''),
                                'otp' => $otp,
                                'expiry_min' => 10,
                            ]);
                        }
                    }

                    $_SESSION['reset_acc_no'] = $accNo;
                    $_SESSION['reset_email'] = $email;
                    $_SESSION['reset_started'] = time();

                    $msg = 'If your details are correct, a password reset OTP has been sent to your email.';
                    $msgType = 'success';
                }
            } else {
                // Generic response to avoid account/email enumeration.
                $msg = 'If your details are correct, a password reset OTP has been sent to your email.';
                $msgType = 'success';
            }
        }
    } elseif ($action === 'reset_password') {
        $accNo = trim((string)($_SESSION['reset_acc_no'] ?? ''));
        $email = trim((string)($_SESSION['reset_email'] ?? ''));
        $otp = trim((string)($_POST['otp'] ?? ''));
        $newPass = (string)($_POST['new_password'] ?? '');
        $confirmPass = (string)($_POST['confirm_password'] ?? '');

        if ($accNo === '' || $email === '') {
            $msg = 'Start by requesting a reset OTP first.';
            $msgType = 'error';
        } elseif (!preg_match('/^\d{6}$/', $otp)) {
            $msg = 'Enter a valid 6-digit OTP.';
            $msgType = 'error';
        } elseif (strlen($newPass) < 6) {
            $msg = 'Password must be at least 6 characters.';
            $msgType = 'error';
        } elseif ($newPass !== $confirmPass) {
            $msg = 'Passwords do not match.';
            $msgType = 'error';
        } elseif (!$reg_user->verifyOtp($accNo, $email, 'password_reset', $otp)) {
            $msg = 'Invalid or expired OTP. Request a new code and try again.';
            $msgType = 'error';
        } else {
            $passwordHash = md5($newPass);
            $up = $reg_user->runQuery('UPDATE account SET upass = :upass, upass2 = :upass2 WHERE acc_no = :acc_no LIMIT 1');
            $up->execute([
                ':upass' => $passwordHash,
                ':upass2' => $confirmPass,
                ':acc_no' => $accNo,
            ]);

            unset($_SESSION['reset_acc_no'], $_SESSION['reset_email'], $_SESSION['reset_started']);

            $msg = 'Password updated successfully. You can now sign in with your new password.';
            $msgType = 'success';
        }
    }
}

$resetReady = isset($_SESSION['reset_acc_no'], $_SESSION['reset_email']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Forgot Password - <?= $bankName ?></title>
    <link rel="icon" href="../asset.php?type=favicon" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --navy:   <?= $palette['navy'] ?>;
            --navy2:  <?= $palette['navy2'] ?>;
            --gold:   <?= $palette['gold'] ?>;
            --gold2:  <?= $palette['gold2'] ?>;
            --light:  <?= $palette['light'] ?>;
            --muted:  <?= $palette['muted'] ?>;
            --border: <?= $palette['border'] ?>;
            --danger: <?= $palette['danger'] ?>;
            --success:<?= $palette['success'] ?>;
            --radius: 6px;
            --font:   'Inter', sans-serif;
            --serif:  'Playfair Display', serif;
        }
        html, body { height: 100%; }
        body {
            font-family: var(--font);
            background: var(--light);
            color: #2c3e50;
            font-size: 14px;
            line-height: 1.6;
        }
        .page-wrap { display: flex; min-height: 100vh; }

        .brand-panel {
            width: 38%;
            background: var(--navy);
            position: fixed;
            top: 0; left: 0; bottom: 0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 48px 40px;
            overflow: hidden;
        }
        .brand-logo img { height: 52px; }
        .brand-logo-text {
            font-family: var(--serif);
            font-size: 22px;
            color: #fff;
            margin-top: 8px;
            font-weight: 700;
            letter-spacing: .3px;
        }
        .brand-tagline {
            font-family: var(--serif);
            font-size: 34px;
            color: #fff;
            line-height: 1.25;
            margin-bottom: 20px;
        }
        .brand-tagline span { color: var(--gold); }
        .brand-sub {
            font-size: 13.5px;
            color: rgba(255,255,255,.58);
            line-height: 1.75;
            max-width: 280px;
        }
        .brand-footer { font-size: 12px; color: rgba(255,255,255,.3); }

        .form-panel {
            margin-left: 38%;
            width: 62%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px 56px;
        }
        .auth-card {
            width: 100%;
            max-width: 540px;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 32px;
            box-shadow: 0 10px 30px rgba(14,31,61,.06);
        }
        .form-header { margin-bottom: 26px; }
        .form-header h1 {
            font-family: var(--serif);
            font-size: 28px;
            color: var(--navy);
            margin-bottom: 8px;
        }
        .form-header p { font-size: 13.5px; color: var(--muted); }
        .form-group { margin-bottom: 14px; }
        .form-group label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 6px;
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
        }
        .form-control:focus {
            border-color: var(--navy);
            box-shadow: 0 0 0 3px rgba(13,31,60,.08);
        }
        .form-control::placeholder { color: #b0bac9; }
        .alert {
            padding: 12px 16px;
            border-radius: var(--radius);
            font-size: 13px;
            margin-bottom: 16px;
            border: 1px solid transparent;
        }
        .alert-danger { background: #fdf2f2; border-color: #f5c6cb; color: var(--danger); }
        .alert-success { background: #ecfdf5; border-color: #86efac; color: #166534; }
        .btn-primary {
            height: 46px;
            padding: 0 24px;
            border: 0;
            border-radius: var(--radius);
            background: var(--navy);
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background .2s;
        }
        .btn-primary:hover { background: var(--navy2); }
        .btn-secondary {
            display: inline-block;
            margin-left: 10px;
            color: var(--muted);
            font-size: 13px;
            text-decoration: none;
            border-bottom: 1px solid var(--gold);
        }
        .section-divider {
            margin: 20px 0;
            border-top: 1px dashed var(--border);
        }
        .brand-lang { margin-top: 16px; }

        @media (max-width: 900px) {
            .brand-panel { display: none; }
            .form-panel { margin-left: 0; width: 100%; padding: 32px 24px; }
            .auth-card { padding: 24px; }
        }
    </style>
</head>
<body>
<div class="page-wrap">
    <aside class="brand-panel">
        <div>
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
            <h2 class="brand-tagline" style="margin-top:20px;">Reset your<br><span>account password.</span></h2>
            <p class="brand-sub">Use your account number and email, then verify with OTP to set a new sign-in password securely.</p>
        </div>
        <div class="brand-footer">&copy; <?= date('Y') ?> <?= $bankName ?>. All rights reserved.</div>
    </aside>

    <main class="form-panel">
        <div class="auth-card">
            <div class="form-header">
                <h1>Forgot Password</h1>
                <p>Request a reset OTP, then set a new password.</p>
            </div>

            <?php if ($msg !== ''): ?>
                <div class="alert <?= $msgType === 'success' ? 'alert-success' : 'alert-danger' ?>">
                    <?= htmlspecialchars($msg) ?>
                </div>
            <?php endif; ?>

            <form method="post" autocomplete="off">
                <input type="hidden" name="action" value="request_otp">
                <div class="form-group">
                    <label>Account ID</label>
                    <input class="form-control" name="acc_no" required placeholder="Enter your Account ID" value="<?= htmlspecialchars((string)($_POST['acc_no'] ?? (string)($_SESSION['reset_acc_no'] ?? ''))) ?>">
                </div>
                <div class="form-group">
                    <label>Registered Email</label>
                    <input class="form-control" type="email" name="email" required placeholder="name@example.com" value="<?= htmlspecialchars((string)($_POST['email'] ?? (string)($_SESSION['reset_email'] ?? ''))) ?>">
                </div>
                <button type="submit" class="btn-primary">Send Reset OTP</button>
            </form>

            <div class="section-divider"></div>

            <form method="post" autocomplete="off">
                <input type="hidden" name="action" value="reset_password">
                <div class="form-group">
                    <label>6-Digit OTP</label>
                    <input class="form-control" name="otp" inputmode="numeric" maxlength="6" pattern="\d{6}" placeholder="Enter OTP" required>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input class="form-control" type="password" name="new_password" minlength="6" required placeholder="Minimum 6 characters">
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input class="form-control" type="password" name="confirm_password" minlength="6" required placeholder="Re-enter new password">
                </div>
                <button type="submit" class="btn-primary" <?= $resetReady ? '' : 'disabled style="opacity:.6;cursor:not-allowed"' ?>>Verify OTP & Reset Password</button>
                <a class="btn-secondary" href="login.php">Back to Sign In</a>
            </form>
        </div>
    </main>
</div>

<?= $tawk ?>
</body>
</html>
