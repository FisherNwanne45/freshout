<?php
session_start();

require 'connectdb.php';
require_once 'class.user.php';
require_once '../config.php';
require_once __DIR__ . '/partials/auto-migrate.php';
require_once __DIR__ . '/auth-theme.php';

if (!isset($_SESSION['acc_no'])) {
    header('Location: login.php');
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
$site = $siteStmt->fetch(PDO::FETCH_ASSOC) ?: [];

$palette = get_auth_palette(get_auth_color_scheme($conn));
$bankName = $site['name'] ?? 'Secure Banking';
$tawk = $site['tawk'] ?? '';

$msg = '';
$hasError = false;
$isSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['resend_otp'])) {
        $otp = $reg_user->createOtp($accNo, (string)($row['email'] ?? ''), 'login', 10);
        if ($otp !== '') {
            $reg_user->send_mail((string)($row['email'] ?? ''), '', 'Your Login OTP', 'otp_code', [
                'fname' => (string)($row['fname'] ?? ''),
                'otp' => $otp,
                'expiry_min' => 10,
            ]);
            $msg = 'A fresh OTP has been sent to your registered email.';
            $isSuccess = true;
        } else {
            $msg = 'Unable to generate OTP at the moment. Please try again.';
            $hasError = true;
        }
    } else {
        $otp = trim((string)($_POST['otp'] ?? ''));
        if (!preg_match('/^\d{6}$/', $otp)) {
            $msg = 'Enter a valid 6-digit OTP.';
            $hasError = true;
        } else {
            $valid = $reg_user->verifyOtp($accNo, (string)($row['email'] ?? ''), 'login', $otp);
            if (!$valid) {
                $msg = 'Invalid or expired OTP. Please request a new code.';
                $hasError = true;
            } else {
                $_SESSION['pin_verified'] = 'otp-verified';
                $_SESSION['pin'] = 'otp-verified'; // backward compatibility during phased migration
                header('Location: index.php');
                exit();
            }
        }
    }
}

$fullName = trim((string)($row['fname'] ?? '') . ' ' . (string)($row['lname'] ?? ''));
if ($fullName === '') {
    $fullName = (string)($row['uname'] ?? 'Customer');
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login OTP | <?= htmlspecialchars($bankName) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            navy: '<?= htmlspecialchars($palette['navy']) ?>',
                            navy2: '<?= htmlspecialchars($palette['navy2']) ?>',
                            gold: '<?= htmlspecialchars($palette['gold']) ?>',
                            gold2: '<?= htmlspecialchars($palette['gold2']) ?>',
                            light: '<?= htmlspecialchars($palette['light']) ?>',
                            muted: '<?= htmlspecialchars($palette['muted']) ?>',
                            border: '<?= htmlspecialchars($palette['border']) ?>',
                            danger: '<?= htmlspecialchars($palette['danger']) ?>'
                        }
                    }
                }
            }
        };
    </script>
    <style>
        #otp-wrapper {
            border: 1px solid <?= htmlspecialchars($hasError ? $palette['danger'] : $palette['border']) ?>;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }

        #otp-wrapper #otp {
            display: block;
            width: 100%;
            max-width: 18rem;
            margin: 0 auto;
            height: 3.25rem;
            border-radius: 0.75rem;
            border: 2px solid <?= htmlspecialchars($hasError ? $palette['danger'] : $palette['border']) ?>;
            background: #f8fafc;
            text-align: left;
            font-size: 1.2rem;
            font-weight: 700;
            letter-spacing: 0.45rem;
            color: <?= htmlspecialchars($palette['navy']) ?>;
            padding-left: 0;
            text-indent: calc((100% - 9rem) / 2 - 0.15rem);
            caret-color: <?= htmlspecialchars($palette['navy']) ?>;
            transition: border-color 0.15s, box-shadow 0.15s;
        }

        #otp-wrapper #otp::placeholder {
            color: <?= htmlspecialchars($palette['muted']) ?>;
            font-size: 1.7rem;
            letter-spacing: 0.62rem;
            font-weight: 500;
        }

        #otp-wrapper #otp:focus {
            border-color: <?= htmlspecialchars($palette['navy']) ?>;
            box-shadow: 0 0 0 4px <?= htmlspecialchars($palette['navy']) ?>1f;
            outline: none;
            background: #ffffff;
        }

        .auth-translator-light .gts-select {
            color: <?= htmlspecialchars($palette['navy']) ?>;
            background-color: #ffffff;
            border-color: <?= htmlspecialchars($palette['border']) ?>;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none'%3E%3Cpath stroke='rgba(23,63,109,0.85)' stroke-linecap='round' stroke-linejoin='round' stroke-width='2.5' d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
        }

        .auth-translator-light .gts-select option {
            background: #ffffff;
            color: #0f172a;
        }
    </style>
</head>

<body class="min-h-screen bg-brand-light">
    <div class="relative mx-auto flex min-h-screen max-w-6xl items-center p-4 md:p-8">
        <div class="grid w-full overflow-hidden rounded-3xl bg-white shadow-2xl lg:grid-cols-2">
            <section class="relative hidden lg:block">
                <div class="absolute inset-0 bg-gradient-to-br from-brand-navy via-brand-navy2 to-slate-900"></div>
                <div class="absolute -top-24 -right-20 h-64 w-64 rounded-full bg-brand-gold/15"></div>
                <div class="absolute -bottom-20 -left-20 h-56 w-56 rounded-full bg-brand-gold/10"></div>
                <div class="relative flex h-full flex-col justify-between p-10 text-white">
                    <div>
                        <p class="text-xs uppercase tracking-[0.22em] text-brand-gold2">Secure Access</p>
                        <h1 class="mt-3 text-3xl font-semibold leading-tight">OTP Verification Required</h1>
                        <p class="mt-4 max-w-md text-sm text-slate-200">Enter the 6-digit one-time code sent to your registered email address to continue.</p>
                        <div class="mt-4">
                            <?php include_once dirname(__DIR__) . '/private/shared-translator.php'; ?>
                        </div>
                    </div>
                    <div class="rounded-2xl border border-white/20 bg-white/10 p-4 backdrop-blur">
                        <p class="text-xs uppercase tracking-wide text-brand-gold2">Account</p>
                        <p class="mt-1 text-lg font-semibold"><?= htmlspecialchars($fullName) ?></p>
                        <p class="text-sm text-slate-200">#<?= htmlspecialchars($accNo) ?></p>
                    </div>
                </div>
            </section>

            <section class="p-6 sm:p-10">
                <div class="mx-auto max-w-md">
                    <p class="text-xs uppercase tracking-[0.22em] text-brand-muted">Welcome Back</p>
                    <h2 class="mt-2 text-2xl font-semibold text-brand-navy">Verify Login OTP</h2>
                    <p class="mt-2 text-sm text-brand-muted">Continue as <?= htmlspecialchars($fullName) ?>.</p>
                    <div class="mt-4 lg:hidden auth-translator-light">
                        <?php include_once dirname(__DIR__) . '/private/shared-translator.php'; ?>
                    </div>

                    <?php if ($msg !== ''): ?>
                        <div class="mt-4 rounded-xl border p-3 text-sm <?= $isSuccess ? 'border-green-200 bg-green-50 text-green-700' : 'border-brand-danger/30 bg-red-50 text-brand-danger' ?>" role="alert" aria-live="assertive">
                            <?= htmlspecialchars($msg) ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" class="mt-5" autocomplete="off">
                        <label for="otp" class="mb-2 block text-sm font-medium text-brand-navy">6-Digit OTP</label>
                        <div id="otp-wrapper" class="rounded-xl border bg-white p-4 shadow-sm">
                            <input
                                id="otp"
                                name="otp"
                                type="password"
                                inputmode="numeric"
                                maxlength="6"
                                pattern="\d{6}"
                                placeholder="• • • • • •"
                                autocomplete="off"
                                required>
                        </div>

                        <div class="mt-4 flex gap-3">
                            <button type="submit" class="flex-1 rounded-xl border border-brand-border bg-white px-4 py-3 text-sm font-semibold text-brand-navy transition hover:bg-brand-light">
                                Verify & Continue
                            </button>
                            <button type="submit" name="resend_otp" value="1" class="rounded-xl border border-brand-border px-4 py-3 text-sm font-medium text-brand-muted transition hover:bg-brand-light">
                                Resend OTP
                            </button>
                        </div>

                        <a href="logout.php" class="mt-3 inline-block text-xs text-brand-muted hover:text-brand-navy">Logout</a>
                    </form>
                </div>
            </section>
        </div>
    </div>

    <?= $tawk ?>

    <script>
        const otpInput = document.getElementById('otp');
        otpInput.addEventListener('input', () => {
            otpInput.value = (otpInput.value || '').replace(/\D/g, '').slice(0, 6);
        });
        otpInput.focus();
    </script>
</body>

</html>