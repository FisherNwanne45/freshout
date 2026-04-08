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

$statLower = strtolower(trim((string)($row['status'] ?? '')));
if (strpos($statLower, 'dormant') !== false || strpos($statLower, 'inactive') !== false) {
    header('Location: index.php?dormant');
    exit();
}

$email = (string)($row['email'] ?? '');

$tempStmt = $reg_user->runQuery('SELECT * FROM temp_transfer WHERE email = :email ORDER BY id DESC LIMIT 1');
$tempStmt->execute([':email' => $email]);
$tempRow = $tempStmt->fetch(PDO::FETCH_ASSOC);
if (!$tempRow) {
    header('Location: send.php');
    exit();
}

$amount = (float)($tempRow['amount'] ?? 0);
$currencyCode = strtoupper((string)($tempRow['currency_code'] ?? ($row['currency'] ?? 'USD')));
$beneficiary = (string)($tempRow['acc_name'] ?? '');
$bankName = (string)($tempRow['bank_name'] ?? '');
$transferType = (string)($tempRow['transfer_type'] ?? ($tempRow['type'] ?? 'standard'));

$transferTypeLabelMap = [
    'samebank' => 'Same Bank',
    'internal' => 'Same Bank',
    'interbank' => 'Same Bank',
    'wire' => 'Wire / International',
    'domestic' => 'Domestic',
    'crypto' => 'Crypto',
];
$transferTypeLabel = $transferTypeLabelMap[strtolower(trim($transferType))] ?? ucfirst($transferType);

$flashError = '';

function completeTransferFromTemp(USER $reg_user, array $row, array $tempRow, mysqli $conn): void
{
    $email = (string)($row['email'] ?? '');
    $amount = (float)($tempRow['amount'] ?? 0);
    $accNoR = (string)($tempRow['acc_no'] ?? '');
    $accName = (string)($tempRow['acc_name'] ?? '');
    $bankName = (string)($tempRow['bank_name'] ?? '');
    $swift = (string)($tempRow['swift'] ?? '');
    $routing = (string)($tempRow['routing'] ?? '');
    $type = (string)($tempRow['type'] ?? ($tempRow['transfer_type'] ?? 'standard'));
    $remarks = (string)($tempRow['remarks'] ?? '');
    $xferType = (string)($tempRow['transfer_type'] ?? $type);
    $curCode = strtoupper((string)($tempRow['currency_code'] ?? ($row['currency'] ?? 'USD')));
    $sourceAccountNo = (string)($tempRow['source_account_no'] ?? '');
    $destinationAccountNo = (string)($tempRow['destination_account_no'] ?? '');

    $normalizedXferType = strtolower(trim($xferType));
    if ($normalizedXferType === 'interbank' || $normalizedXferType === 'internal') {
        $normalizedXferType = 'samebank';
    }

    if ($normalizedXferType === 'samebank') {
        if ($destinationAccountNo === '') {
            header('Location: send.php?samebank_invalid=1');
            exit();
        }

        try {
            $destCheck = $reg_user->runQuery(
                'SELECT account_no FROM customer_accounts
                 WHERE (account_no = :lookup OR iban = :lookup)
                   AND currency_code = :currency_code
                   AND status = :status
                 LIMIT 1'
            );
            $destCheck->execute([
                ':lookup' => $destinationAccountNo,
                ':currency_code' => $curCode,
                ':status' => 'active',
            ]);
            $destCheckRow = $destCheck->fetch(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            $destCheckRow = false;
        }
        if (!$destCheckRow) {
            header('Location: send.php?samebank_invalid=1');
            exit();
        }

        $destinationAccountNo = (string)($destCheckRow['account_no'] ?? $destinationAccountNo);
        $accNoR = $destinationAccountNo;
        $bankName = 'Same Bank Transfer';
        $xferType = 'samebank';
    }

    if ($sourceAccountNo === '') {
        $sourceAccountNo = (string)($row['acc_no'] ?? '') . '-' . $curCode;
    }

    $sourceBal = null;
    try {
        $src = $reg_user->runQuery(
            'SELECT balance FROM customer_accounts
             WHERE owner_acc_no = :owner_acc_no
               AND account_no = :account_no
               AND currency_code = :currency_code
               AND status = :status
             LIMIT 1'
        );
        $src->execute([
            ':owner_acc_no' => (string)($row['acc_no'] ?? ''),
            ':account_no' => $sourceAccountNo,
            ':currency_code' => $curCode,
            ':status' => 'active',
        ]);
        $srcRow = $src->fetch(PDO::FETCH_ASSOC);
        if ($srcRow) {
            $sourceBal = (float)($srcRow['balance'] ?? 0);
        }
    } catch (Throwable $e) {
    }

    if ($sourceBal === null) {
        $sourceBal = (float)($row['a_bal'] ?? $row['t_bal'] ?? 0);
    }

    if ($sourceBal < $amount || $amount <= 0) {
        header('Location: send.php?insufficient');
        exit();
    }

    if ($reg_user->transfer($email, $amount, $accNoR, $accName, $bankName, $swift, $routing, $type, $remarks)) {
        try {
            $lastId = $reg_user->lasdID();
            $reg_user->runQuery(
                'UPDATE transfer
                 SET currency_code = :currency_code,
                     transfer_type = :transfer_type,
                     source_account_no = :source_account_no,
                     destination_account_no = :destination_account_no
                 WHERE id = :id'
            )->execute([
                ':currency_code' => $curCode,
                ':transfer_type' => $xferType,
                ':source_account_no' => $sourceAccountNo !== '' ? $sourceAccountNo : null,
                ':destination_account_no' => $destinationAccountNo !== '' ? $destinationAccountNo : null,
                ':id' => $lastId,
            ]);
        } catch (Throwable $e) {
        }

        try {
            $reg_user->runQuery(
                'UPDATE customer_accounts
                 SET balance = balance - :amount
                 WHERE owner_acc_no = :owner_acc_no
                   AND account_no = :account_no
                   AND currency_code = :currency_code'
            )->execute([
                ':amount' => $amount,
                ':owner_acc_no' => (string)($row['acc_no'] ?? ''),
                ':account_no' => $sourceAccountNo,
                ':currency_code' => $curCode,
            ]);
        } catch (Throwable $e) {
        }

        try {
            $reg_user->runQuery(
                'UPDATE account_balances SET balance = balance - :amount WHERE acc_no = :acc_no AND currency_code = :currency_code'
            )->execute([
                ':amount' => $amount,
                ':acc_no' => (string)($row['acc_no'] ?? ''),
                ':currency_code' => $curCode,
            ]);
        } catch (Throwable $e) {
        }

        if ($destinationAccountNo !== '') {
            try {
                $dest = $reg_user->runQuery(
                    'SELECT ca.owner_acc_no, ca.currency_code, a.email, a.fname, a.lname, a.uname
                                         FROM customer_accounts ca
                                         LEFT JOIN account a ON a.acc_no = ca.owner_acc_no
                                         WHERE (ca.account_no = :account_no OR ca.iban = :iban)
                                             AND ca.status = :status
                                         LIMIT 1'
                );
                $dest->execute([
                    ':account_no' => $destinationAccountNo,
                    ':iban' => $destinationAccountNo,
                    ':status' => 'active',
                ]);
                $destRow = $dest->fetch(PDO::FETCH_ASSOC);
                if ($destRow) {
                    $destOwner = (string)($destRow['owner_acc_no'] ?? '');
                    $destCurrency = (string)($destRow['currency_code'] ?? $curCode);
                    $destEmail = trim((string)($destRow['email'] ?? ''));
                    $destFname = trim((string)($destRow['fname'] ?? ''));
                    $destLname = trim((string)($destRow['lname'] ?? ''));
                    $destUname = trim((string)($destRow['uname'] ?? ''));

                    $reg_user->runQuery(
                        'UPDATE customer_accounts
                         SET balance = balance + :amount
                         WHERE owner_acc_no = :owner_acc_no AND account_no = :account_no'
                    )->execute([
                        ':amount' => $amount,
                        ':owner_acc_no' => $destOwner,
                        ':account_no' => $destinationAccountNo,
                    ]);

                    $reg_user->runQuery(
                        'INSERT INTO account_balances (acc_no, currency_code, balance)
                         VALUES (:acc_no, :currency_code, 0)
                         ON DUPLICATE KEY UPDATE acc_no = VALUES(acc_no)'
                    )->execute([
                        ':acc_no' => $destOwner,
                        ':currency_code' => $destCurrency,
                    ]);

                    $reg_user->runQuery(
                        'UPDATE account_balances SET balance = balance + :amount WHERE acc_no = :acc_no AND currency_code = :currency_code'
                    )->execute([
                        ':amount' => $amount,
                        ':acc_no' => $destOwner,
                        ':currency_code' => $destCurrency,
                    ]);

                    if ($destEmail !== '') {
                        try {
                            $destBalStmt = $reg_user->runQuery(
                                'SELECT balance FROM account_balances WHERE acc_no = :acc_no AND currency_code = :currency_code LIMIT 1'
                            );
                            $destBalStmt->execute([':acc_no' => $destOwner, ':currency_code' => $destCurrency]);
                            $destBalRow = $destBalStmt->fetch(PDO::FETCH_ASSOC);
                            $destNewBalance = (float)($destBalRow['balance'] ?? 0);

                            $creditData = [
                                'fname' => $destFname,
                                'lname' => $destLname,
                                'name' => trim($destFname . ' ' . $destLname),
                                'amount' => $amount,
                                'currency' => $destCurrency,
                                'transaction_type' => 'Credit',
                                'description' => 'Incoming transfer from ' . trim((string)($row['fname'] ?? '') . ' ' . (string)($row['lname'] ?? '')),
                                'status' => 'Completed',
                                'date' => date('Y-m-d H:i:s'),
                                'balance' => $destNewBalance,
                            ];
                            $reg_user->send_mail($destEmail, '', 'Credit Alert: Incoming Transfer', 'transaction_alert', $creditData);

                            if ($destUname !== '') {
                                $panelSubject = 'Incoming Transfer Credit';
                                $panelMsg = 'Your account has been credited with ' . strtoupper((string)$destCurrency) . ' ' . number_format((float)$amount, 2) . '.';
                                $reg_user->message('System', $destUname, $panelSubject, $panelMsg);
                            }
                        } catch (Throwable $e) {
                        }
                    }
                }
            } catch (Throwable $e) {
            }
        }

        $total = max(0, (float)($row['t_bal'] ?? 0) - $amount);
        $avail = max(0, (float)($row['a_bal'] ?? 0) - $amount);
        try {
            $reg_user->runQuery("UPDATE account SET t_bal = '$total', a_bal = '$avail' WHERE email = '$email'")->execute();
        } catch (Throwable $e) {
        }

        try {
            $beneficiaryName = trim((string)$accName);
            if ($beneficiaryName === '') {
                $beneficiaryName = trim((string)$accNoR);
            }
            $reg_user->send_mail($email, '', 'Debit Alert: Transfer Initiated', 'debit_alert', [
                'fname' => $row['fname'] ?? '',
                'lname' => $row['lname'] ?? '',
                'phone' => $row['phone'] ?? '',
                'amount' => $amount,
                'currency' => $curCode,
                'acc_name' => $beneficiaryName,
                'bank' => $bankName,
                'description' => 'Transfer initiated',
                'date' => date('Y-m-d H:i:s'),
                'balance' => $total,
            ]);
        } catch (Throwable $e) {
        }

        try {
            $reg_user->runQuery('DELETE FROM temp_transfer WHERE email = :email')
                ->execute([':email' => $email]);
        } catch (Throwable $e) {
        }

        unset($_SESSION['auth_step'], $_SESSION['auth_transfer_id']);

        header('Location: success.php');
        exit();
    }

    header('Location: send.php?error');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = trim((string)($_POST['otp'] ?? ''));
    if ($otp === '') {
        $flashError = 'Enter the OTP code sent to your email.';
    } else {
        $otpValid = $reg_user->verifyOtp((string)($row['acc_no'] ?? ''), $email, 'transfer', $otp);
        if (!$otpValid) {
            $flashError = 'Invalid or expired OTP. Please try again.';
        } else {
            completeTransferFromTemp($reg_user, $row, $tempRow, $conn);
        }
    }
}

if (!$reg_user->hasActiveOtp((string)($row['acc_no'] ?? ''), $email, 'transfer')) {
    $otp = $reg_user->createOtp((string)($row['acc_no'] ?? ''), $email, 'transfer', 10);
    try {
        $reg_user->send_mail($email, '', 'Your Transfer OTP', 'otp_code', [
            'fname' => $row['fname'] ?? '',
            'otp' => $otp,
            'amount' => $amount,
            'currency' => $currencyCode,
            'expiry_min' => 10,
        ]);
    } catch (Throwable $e) {
    }
}

require_once __DIR__ . '/partials/shell-data.php';
$shellPageTitle = 'OTP Verification';
require_once __DIR__ . '/partials/shell-open.php';
?>

<div class="max-w-lg mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-brand-border p-5 mb-6">
        <h2 class="text-xs font-semibold text-brand-muted uppercase tracking-wider mb-3">Transfer Summary</h2>
        <div class="flex justify-between items-center mb-2">
            <span class="text-sm text-brand-muted">Amount</span>
            <span class="text-lg font-bold text-brand-navy"><?= htmlspecialchars($currencyCode) ?> <?= number_format((float)$amount, 2) ?></span>
        </div>
        <?php if ($beneficiary !== ''): ?>
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm text-brand-muted">Beneficiary</span>
                <span class="text-sm font-semibold text-brand-navy"><?= htmlspecialchars($beneficiary) ?></span>
            </div>
        <?php endif; ?>
        <?php if ($bankName !== ''): ?>
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm text-brand-muted">Bank</span>
                <span class="text-sm text-brand-navy"><?= htmlspecialchars($bankName) ?></span>
            </div>
        <?php endif; ?>
        <div class="flex justify-between items-center">
            <span class="text-sm text-brand-muted">Type</span>
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700"><?= htmlspecialchars($transferTypeLabel) ?></span>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-brand-border p-6">
        <h1 class="text-xl font-bold text-brand-navy mb-1">One-Time Password</h1>
        <p class="text-sm text-brand-muted mb-5">Enter the OTP sent to your registered email to authorise this transfer.</p>

        <?php if ($flashError !== ''): ?>
            <div class="mb-4 rounded-xl p-3 bg-red-50 border border-red-200 text-sm text-red-700">
                <?= htmlspecialchars($flashError) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4" id="otpForm" novalidate>
            <div>
                <label for="otp" class="block text-sm font-semibold text-brand-navy mb-2">OTP Code</label>
                <input id="otp" name="otp" type="password" inputmode="numeric" autocomplete="one-time-code" maxlength="12"
                    class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-center text-2xl tracking-[0.3em] font-bold focus:outline-none focus:border-blue-500 transition-colors"
                    placeholder="• • • • • •">
            </div>

            <button id="otpSubmitBtn" type="submit"
                class="w-full bg-brand-navy hover:bg-brand-navy2 text-white font-semibold py-3 rounded-xl transition-colors text-sm shadow-sm">
                Verify OTP
            </button>
        </form>
    </div>

    <div class="mt-4 text-center">
        <a href="transfer-auth.php?cancel=1" class="text-sm text-brand-muted hover:text-brand-navy transition-colors">← Cancel transfer</a>
    </div>
</div>

<script>
    (function() {
        var otpInput = document.getElementById('otp');
        var form = document.getElementById('otpForm');
        var submitBtn = document.getElementById('otpSubmitBtn');

        if (otpInput) {
            setTimeout(function() {
                otpInput.focus();
            }, 60);
        }

        if (form && submitBtn) {
            form.addEventListener('submit', function() {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Verifying...';
            });
        }
    }());
</script>

<?php require_once __DIR__ . '/partials/shell-close.php'; ?>