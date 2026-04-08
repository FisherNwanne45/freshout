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
$isWrongPin = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pin = trim((string)($_POST['pin'] ?? ($_POST['pin'] ?? '')));

    if (!preg_match('/^\d{4}$/', $pin)) {
        $msg = 'Invalid PIN. Enter your 4-digit account PIN.';
    } else {
        $pinStmt = $reg_user->runQuery('SELECT pin, pin FROM account WHERE acc_no = :acc_no LIMIT 1');
        $pinStmt->execute([':acc_no' => $accNo]);
        $pinRow = $pinStmt->fetch(PDO::FETCH_ASSOC);

        $storedPin = trim((string)($pinRow['pin'] ?? ''));
        if ($storedPin === '') {
            $storedPin = trim((string)($pinRow['pin'] ?? ''));
        }

        if (!$pinRow || $storedPin !== $pin) {
            $msg = 'Invalid PIN, please ensure you enter the correct information.';
            $hasError = true;
            $isWrongPin = true;
        } else {
            $_SESSION['pin_verified'] = $pin;
            $_SESSION['pin'] = $pin; // backward compatibility during phased migration
            header('Location: index.php');
            exit();
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
    <title>PIN Verification | <?= htmlspecialchars($bankName) ?></title>
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
        @keyframes pinShake {

            0%,
            100% {
                transform: translateX(0);
            }

            20% {
                transform: translateX(-6px);
            }

            40% {
                transform: translateX(6px);
            }

            60% {
                transform: translateX(-4px);
            }

            80% {
                transform: translateX(4px);
            }
        }

        .pin-shake {
            animation: pinShake 0.35s ease-in-out;
        }

        #pin-wrapper {
            border: 1px solid <?= htmlspecialchars($hasError ? $palette['danger'] : $palette['border']) ?>;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }

        #pin-wrapper #pin {
            display: block;
            width: 100%;
            max-width: 16rem;
            margin: 0 auto;
            height: 3.25rem;
            border-radius: 0.75rem;
            border: 2px solid <?= htmlspecialchars($hasError ? $palette['danger'] : $palette['border']) ?>;
            background: #f8fafc;
            text-align: left;
            font-size: 1.35rem;
            font-weight: 700;
            letter-spacing: 0.55rem;
            color: <?= htmlspecialchars($palette['navy']) ?>;
            padding-left: 0;
            text-indent: calc((100% - 6.8rem) / 2 - 0.2rem);
            caret-color: <?= htmlspecialchars($palette['navy']) ?>;
            transition: border-color 0.15s, box-shadow 0.15s;
        }

        #pin-wrapper #pin::placeholder {
            color: <?= htmlspecialchars($palette['muted']) ?>;
            font-size: 1.9rem;
            letter-spacing: 0.7rem;
            font-weight: 500;
        }

        #pin-wrapper #pin:focus {
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
                        <h1 class="mt-3 text-3xl font-semibold leading-tight">PIN Verification Required</h1>
                        <p class="mt-4 max-w-md text-sm text-slate-200">Enter your 4-digit transaction PIN to continue to your dashboard and authorize protected actions.</p>
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
                    <h2 class="mt-2 text-2xl font-semibold text-brand-navy">Verify Your PIN</h2>
                    <p class="mt-2 text-sm text-brand-muted">Continue as <?= htmlspecialchars($fullName) ?>.</p>
                    <div class="mt-4 lg:hidden auth-translator-light">
                        <?php include_once dirname(__DIR__) . '/private/shared-translator.php'; ?>
                    </div>

                    <div class="mt-6 flex items-center gap-3 rounded-xl border border-brand-border bg-brand-light/60 p-3">
                        <img src="admin/foto/<?= htmlspecialchars((string)($row['pp'] ?? 'user.png')) ?>" alt="Profile" class="h-12 w-12 rounded-full object-cover" onerror="this.src='admin/foto/user.png'">
                        <div>
                            <p class="text-sm font-medium text-brand-navy"><?= htmlspecialchars($fullName) ?></p>
                            <p class="text-xs text-brand-muted">Account #: <?= htmlspecialchars($accNo) ?></p>
                        </div>
                    </div>

                    <?php if ($msg !== ''): ?>
                        <div class="mt-4 rounded-xl border border-brand-danger/30 bg-red-50 p-3 text-sm text-brand-danger" role="alert" aria-live="assertive">
                            <?= htmlspecialchars($msg) ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" class="mt-5" autocomplete="off">
                        <label for="pin" class="mb-2 block text-sm font-medium text-brand-navy">4-Digit PIN</label>
                        <div id="pin-wrapper" class="rounded-xl border bg-white p-4 shadow-sm <?= $hasError ? 'pin-shake' : '' ?>">
                            <input id="pin" name="pin" type="password" inputmode="numeric" maxlength="4" pattern="\d{4}" placeholder="• • • •" autocomplete="off" required>
                        </div>
                        <?php if ($isWrongPin): ?>
                            <p class="mt-2 text-xs font-semibold text-brand-danger" role="alert" aria-live="polite">Incorrect PIN. Please try again.</p>
                        <?php endif; ?>
                        <p class="mt-2 text-xs <?= $hasError ? 'text-brand-danger' : 'text-brand-muted' ?>">Your PIN is encrypted in transit and never displayed to protect your account privacy.</p>

                        <div class="mt-4 flex gap-3">
                            <button type="submit" class="flex-1 rounded-xl border border-brand-border bg-white px-4 py-3 text-sm font-semibold text-brand-navy transition hover:bg-brand-light">
                                Continue
                            </button>
                            <a href="logout.php" class="rounded-xl border border-brand-border px-4 py-3 text-sm font-medium text-brand-muted transition hover:bg-brand-light">
                                Logout
                            </a>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>

    <?= $tawk ?>

    <script>
        const pinInput = document.getElementById('pin');
        const pinForm = pinInput.form;

        function sanitizePinValue() {
            const clean = (pinInput.value || '').replace(/\D/g, '').slice(0, 4);
            pinInput.value = clean;
            return clean;
        }

        pinInput.addEventListener('input', () => {
            const value = sanitizePinValue();
            if (/^\d{4}$/.test(value)) {
                pinForm.submit();
            }
        });

        pinForm.addEventListener('submit', (event) => {
            if (!/^\d{4}$/.test(sanitizePinValue())) {
                event.preventDefault();
                pinInput.focus();
            }
        });

        pinInput.focus();
    </script>
</body>

</html>