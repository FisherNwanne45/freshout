<?php
session_start();

require 'connectdb.php';
require_once 'class.user.php';
include_once '../config.php';
require_once __DIR__ . '/partials/auto-migrate.php';
require_once __DIR__ . '/auth-theme.php';

$reg_user = new USER();
$msg = null;

if (isset($_POST['acc_no']) && isset($_POST['upass'])) {
    $loginInput = trim($_POST['acc_no']);
    $rawPassword = trim((string)($_POST['upass'] ?? ''));
    $legacyMd5 = md5($rawPassword);

    $stmt = $reg_user->runQuery('SELECT * FROM account WHERE (acc_no = :login_acc OR email = :login_email) LIMIT 1');
    $stmt->execute([
        ':login_acc' => $loginInput,
        ':login_email' => $loginInput,
    ]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $storedHash = (string)($row['upass'] ?? '');
    $passwordOk = false;
    if ($row) {
        if (password_verify($rawPassword, $storedHash)) {
            $passwordOk = true;
        } elseif (hash_equals($legacyMd5, $storedHash)) {
            $passwordOk = true;
            // Opportunistic upgrade of legacy MD5 passwords on successful login.
            try {
                $rehash = password_hash($rawPassword, PASSWORD_BCRYPT);
                if ($rehash !== '') {
                    $upgrade = $reg_user->runQuery('UPDATE account SET upass = :upass WHERE id = :id LIMIT 1');
                    $upgrade->execute([':upass' => $rehash, ':id' => (int)($row['id'] ?? 0)]);
                    $row['upass'] = $rehash;
                }
            } catch (Throwable $e) {}
        }
    }

    $status = trim((string)($row['status'] ?? ''));
    $statusLower = strtolower($status);

    $loginMethod = trim(strtolower((string)($row['login_method'] ?? '')));
    if ($loginMethod === '') {
        // Backward compatibility for legacy status-based auth routing.
        if ($statusLower === 'otp') {
            $loginMethod = 'otp';
            $status = 'Active';
            $statusLower = 'active';
        } elseif ($statusLower === 'pin' || $statusLower === 'pincode') {
            $loginMethod = 'pin';
            $status = 'Active';
            $statusLower = 'active';
        } else {
            $loginMethod = 'pin';
        }
    }

    if (!$row || !$passwordOk) {
        $msg = 'Invalid Account ID or password. Ensure your details are correct or contact support.';
    } elseif ($statusLower === 'disabled') {
        $msg = 'Your account has been disabled for violation of our terms.';
    } elseif ($statusLower === 'closed') {
        $msg = 'This account no longer exists.';
    } else {
        $acc_no = (string)($row['acc_no'] ?? '');
        $log = $reg_user->runQuery('UPDATE account SET logins = logins + 1 WHERE acc_no = :acc_no');
        $log->execute([':acc_no' => $acc_no]);

        $_SESSION['acc_no'] = $acc_no;
        unset($_SESSION['pin_verified'], $_SESSION['mname']);

        if ($loginMethod === 'otp') {
            $loginEmail = (string)($row['email'] ?? '');
            if ($reg_user->hasActiveOtp($acc_no, $loginEmail, 'login')) {
                header('Location: login-otp.php');
                exit();
            }

            $otp = $reg_user->createOtp($acc_no, $loginEmail, 'login', 10);
            if ($otp !== '') {
                $reg_user->send_mail($loginEmail, '', 'Your Login OTP', 'otp_code', [
                    'fname' => (string)($row['fname'] ?? ''),
                    'otp' => $otp,
                    'expiry_min' => 10,
                ]);
                header('Location: login-otp.php');
                exit();
            }
            $msg = 'We could not generate your login OTP at this time. Please try again.';
        } else {
            header('Location: passcode.php');
        }
        exit();
    }
}

$site = null;
$res = $conn->query("SELECT * FROM site LIMIT 1");
if ($res && $res->num_rows > 0) {
    $site = $res->fetch_assoc();
}

$authScheme = get_auth_color_scheme($conn);
$palette = get_auth_palette($authScheme);

$bankName = $site ? htmlspecialchars($site['name']) : 'Secure Banking';
$bankLogo = $site ? 'admin/site/' . htmlspecialchars($site['image']) : '';
$tawk = $site ? $site['tawk'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Sign In - <?= $bankName ?></title>
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
        .brand-panel::before {
            content: '';
            position: absolute;
            top: -80px; right: -80px;
            width: 320px; height: 320px;
            border-radius: 50%;
            background: rgba(201,168,76,.07);
        }
        .brand-panel::after {
            content: '';
            position: absolute;
            bottom: -60px; left: -60px;
            width: 260px; height: 260px;
            border-radius: 50%;
            background: rgba(201,168,76,.05);
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
        .brand-body { position: relative; z-index: 1; }
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
        .brand-features { margin-top: 36px; }
        .brand-feature {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 18px;
        }
        .brand-feature-icon {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: rgba(201,168,76,.15);
            border: 1px solid rgba(201,168,76,.25);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .brand-feature-icon svg { width: 16px; height: 16px; fill: var(--gold); }
        .brand-feature-text strong { display: block; font-size: 13px; color: #fff; font-weight: 600; }
        .brand-feature-text span { font-size: 12px; color: rgba(255,255,255,.5); }
        .brand-footer {
            font-size: 12px;
            color: rgba(255,255,255,.3);
            position: relative; z-index: 1;
        }

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
            max-width: 480px;
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
        .form-group { margin-bottom: 18px; }
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
        }
        .alert-danger { background: #fdf2f2; border: 1px solid #f5c6cb; color: var(--danger); }
        .alert-info { background: #eef4ff; border: 1px solid #bed3f5; color: #1a4480; }
        .submit-area {
            margin-top: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            flex-wrap: wrap;
        }
        .btn-primary {
            height: 46px;
            padding: 0 30px;
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
        .register-link {
            font-size: 13px;
            color: var(--muted);
        }
        .register-link a {
            color: var(--navy);
            font-weight: 600;
            text-decoration: none;
            border-bottom: 1px solid var(--gold);
            padding-bottom: 1px;
        }
        .register-link a:hover { color: var(--gold); }

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
            <p class="brand-sub">Secure online access, reliable transfers, and personalized support for your financial goals.</p>
            <div class="brand-features">
                <div class="brand-feature">
                    <div class="brand-feature-icon">
                        <svg viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/></svg>
                    </div>
                    <div class="brand-feature-text">
                        <strong>Bank-grade Security</strong>
                        <span>256-bit encrypted sessions</span>
                    </div>
                </div>
                <div class="brand-feature">
                    <div class="brand-feature-icon">
                        <svg viewBox="0 0 24 24"><path d="M17 12h-5v5h5v-5zM16 1v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-1V1h-2zm3 18H5V8h14v11z"/></svg>
                    </div>
                    <div class="brand-feature-text">
                        <strong>24/7 Account Access</strong>
                        <span>Bank from anywhere</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="brand-footer">&copy; <?= date('Y') ?> <?= $bankName ?>. All rights reserved.</div>
    </aside>

    <main class="form-panel">
        <div class="auth-card">
            <div class="form-header">
                <h1>Welcome Back</h1>
                <p>Sign in to continue to your online banking dashboard.</p>
            </div>

            <?php if (isset($_GET['inactive'])): ?>
                <div class="alert alert-info">This account is not activated yet. Please check your inbox and activate it first.</div>
            <?php endif; ?>

            <?php if ($msg): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($msg) ?></div>
            <?php endif; ?>

            <form method="post" autocomplete="off">
                <div class="form-group">
                    <label>Account ID or Email</label>
                    <input class="form-control" name="acc_no" id="acc_no" required placeholder="Enter your Account ID or email" value="<?= isset($_POST['acc_no']) ? htmlspecialchars($_POST['acc_no']) : '' ?>">
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input class="form-control" name="upass" id="upass" type="password" required placeholder="Enter your password">
                    <div style="margin-top:8px; text-align:right;">
                        <a href="forgot-password.php" style="font-size:12px; color: var(--navy); text-decoration:none; border-bottom:1px solid var(--gold);">Forgot password?</a>
                    </div>
                </div>

                <div class="submit-area">
                    <button type="submit" name="submit" class="btn-primary">Sign In</button>
                    <span class="register-link">New customer? <a href="register.php">Create an account</a></span>
                </div>
            </form>
        </div>
    </main>
</div>

<?= $tawk ?>

</body>
</html>
