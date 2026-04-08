<?php
session_start();
include_once 'session.php';
require_once 'class.user.php';
require_once '../config.php';
require_once __DIR__ . '/auth-theme.php';

if (!isset($_SESSION['acc_no'])) { header('Location: login.php'); exit(); }
if (!isset($_SESSION['mname'])) { header('Location: passcode.php'); exit(); }

$reg_user = new USER();
$accNo    = (string)$_SESSION['acc_no'];

$stmt = $reg_user->runQuery('SELECT * FROM account WHERE acc_no=:acc_no LIMIT 1');
$stmt->execute([':acc_no' => $accNo]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) { header('Location: logout.php'); exit(); }

$email = $row['email'];
$temp  = $reg_user->runQuery("SELECT * FROM transfer WHERE email='$email' ORDER BY id DESC LIMIT 1");
$temp->execute();
$rows  = $temp->fetch(PDO::FETCH_ASSOC) ?: [];

$currency = strtoupper(trim((string)($row['currency'] ?? 'USD')));
if (!preg_match('/^[A-Z0-9]{2,10}$/', $currency)) { $currency = 'USD'; }
if (isset($rows['currency_code']) && preg_match('/^[A-Z0-9]{2,10}$/', (string)$rows['currency_code'])) {
    $currency = (string)$rows['currency_code'];
}

$getSiteSetting = function (string $key, string $default = '') use ($reg_user): string {
    try {
        $stmt = $reg_user->runQuery("SELECT `value` FROM site_settings WHERE `key` = :k LIMIT 1");
        $stmt->execute([':k' => $key]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && trim((string)($row['value'] ?? '')) !== '') {
            return (string)$row['value'];
        }
    } catch (Throwable $e) {
    }

    try {
        $stmt = $reg_user->runQuery("SELECT setting_value FROM site_settings WHERE setting_key = :k LIMIT 1");
        $stmt->execute([':k' => $key]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && trim((string)($row['setting_value'] ?? '')) !== '') {
            return (string)$row['setting_value'];
        }
    } catch (Throwable $e) {
    }

    return $default;
};

$successTitle = $getSiteSetting('transfer_success_title', $getSiteSetting('success_transfer_title', 'Transfer Initiated'));
$successNote = $getSiteSetting('transfer_success_note', $getSiteSetting('success_transfer_note', 'International transfers are processed within 2-3 business days.'));
$failureTitle = $getSiteSetting('transfer_failure_title', 'Transfer Failed');
$failureNote = $getSiteSetting('transfer_failure_note', 'This transfer could not be completed. Please contact support or try again.');

$statusRaw = strtolower(trim((string)($rows['status'] ?? '')));
$failureStates = ['failed', 'cancelled', 'reversed'];
$isFailure = in_array($statusRaw, $failureStates, true);
$statusLabel = $statusRaw !== '' ? ucfirst($statusRaw) : 'Processing';
$displayTitle = $isFailure ? $failureTitle : $successTitle;
$displayNote = $isFailure ? $failureNote : $successNote;

$beneficiaryDisplay = trim((string)($rows['acc_name'] ?? ''));
if ($beneficiaryDisplay === '' || strcasecmp($beneficiaryDisplay, 'Beneficiary') === 0) {
    $lookupAcc = trim((string)($rows['acc_no'] ?? ''));

    if ($lookupAcc !== '') {
        try {
            $beneStmt = $reg_user->runQuery(
                'SELECT nick_name FROM beneficiaries WHERE acc_no = :owner_acc_no AND account_number = :account_number ORDER BY id DESC LIMIT 1'
            );
            $beneStmt->execute([
                ':owner_acc_no' => $accNo,
                ':account_number' => $lookupAcc,
            ]);
            $beneRow = $beneStmt->fetch(PDO::FETCH_ASSOC);
            if ($beneRow && trim((string)($beneRow['nick_name'] ?? '')) !== '') {
                $beneficiaryDisplay = trim((string)$beneRow['nick_name']);
            }
        } catch (Throwable $e) {
        }

        if ($beneficiaryDisplay === '' || strcasecmp($beneficiaryDisplay, 'Beneficiary') === 0) {
            try {
                $sameBankStmt = $reg_user->runQuery(
                    'SELECT a.fname, a.lname
                     FROM customer_accounts ca
                     INNER JOIN account a ON a.acc_no = ca.owner_acc_no
                     WHERE (ca.account_no = :lookup OR ca.iban = :lookup)
                     LIMIT 1'
                );
                $sameBankStmt->execute([':lookup' => $lookupAcc]);
                $sameBankRow = $sameBankStmt->fetch(PDO::FETCH_ASSOC);
                if ($sameBankRow) {
                    $resolved = trim((string)($sameBankRow['fname'] ?? '') . ' ' . (string)($sameBankRow['lname'] ?? ''));
                    if ($resolved !== '') {
                        $beneficiaryDisplay = $resolved;
                    }
                }
            } catch (Throwable $e) {
            }
        }
    }
}

if ($beneficiaryDisplay === '' || strcasecmp($beneficiaryDisplay, 'Beneficiary') === 0) {
    $beneficiaryDisplay = trim((string)($rows['reci_name'] ?? ''));
}
if ($beneficiaryDisplay === '' || strcasecmp($beneficiaryDisplay, 'Beneficiary') === 0) {
    $beneficiaryDisplay = trim((string)($rows['bank_name'] ?? ''));
}
if ($beneficiaryDisplay === '') {
    $beneficiaryDisplay = 'Beneficiary';
}

require_once __DIR__ . '/partials/shell-data.php';
$shellPageTitle = 'Transfer Submitted';
$txRef = 'TXN' . strtoupper(substr(md5((string)($rows['id'] ?? '') . $accNo), 0, 10));
$receiptLogoUrl = trim((string)($shellLogoUrl ?? 'img/logo.png'));
require_once __DIR__ . '/partials/shell-open.php';
?>

<style>
@media print {
    body > *:not(#receipt-printable) { display: none !important; }
    #receipt-printable { display: block !important; }
    .no-print { display: none !important; }
}
#receipt-printable {
    display: none;
    font-family: Arial, sans-serif;
    max-width: 480px;
    margin: 0 auto;
    padding: 32px;
    border: 1px solid #ddd;
    border-radius: 12px;
    color: #1a2340;
}
#receipt-printable .rp-header { text-align: center; border-bottom: 2px solid #1a2340; padding-bottom: 14px; margin-bottom: 18px; }
#receipt-printable .rp-header h1 { margin: 0; font-size: 18px; color: #1a2340; }
#receipt-printable .rp-header p { margin: 4px 0 0; font-size: 12px; color: #6b7280; }
#receipt-printable .rp-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f0f0f0; font-size: 13px; }
#receipt-printable .rp-row .label { color: #6b7280; }
#receipt-printable .rp-row .value { font-weight: 600; text-align: right; max-width: 60%; word-break: break-all; }
#receipt-printable .rp-footer { text-align: center; margin-top: 18px; font-size: 11px; color: #9ca3af; }
</style>

<header class="mb-6 rounded-2xl bg-gradient-to-r from-brand-navy to-brand-navy2 p-6 text-white shadow-xl">
    <p class="text-xs uppercase tracking-[0.2em] text-brand-gold2">Transaction</p>
    <h1 class="mt-1 text-2xl font-semibold">Transfer Submitted</h1>
</header>

<div class="flex justify-center">
    <div class="w-full max-w-lg rounded-2xl border border-brand-border bg-white p-8 shadow-sm text-center">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full <?= $isFailure ? 'bg-red-100' : 'bg-green-100' ?>">
            <?php if ($isFailure): ?>
            <svg class="h-8 w-8 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 6L6 18M6 6l12 12"/></svg>
            <?php else: ?>
            <svg class="h-8 w-8 text-brand-success" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            <?php endif; ?>
        </div>
        <h2 class="mt-4 text-xl font-bold text-brand-navy"><?= htmlspecialchars($displayTitle) ?></h2>
        <p class="mt-1 text-sm text-brand-muted"><?= htmlspecialchars($displayNote) ?></p>

        <div class="mt-6 divide-y divide-brand-border rounded-xl border border-brand-border text-left">
            <div class="flex justify-between px-4 py-3 text-sm">
                <span class="text-brand-muted">Reference</span>
                <span class="font-semibold text-brand-navy font-mono tracking-wide"><?= htmlspecialchars($txRef) ?></span>
            </div>
            <div class="flex justify-between px-4 py-3 text-sm">
                <span class="text-brand-muted">Amount</span>
                <span class="font-semibold text-brand-navy"><?= htmlspecialchars($currency) ?> <?= htmlspecialchars((string)($rows['amount'] ?? '—')) ?></span>
            </div>
            <div class="flex justify-between px-4 py-3 text-sm">
                <span class="text-brand-muted">Beneficiary Name</span>
                <span class="font-semibold text-brand-navy"><?= htmlspecialchars($beneficiaryDisplay) ?></span>
            </div>
            <div class="flex justify-between px-4 py-3 text-sm">
                <span class="text-brand-muted">Account / IBAN</span>
                <span class="font-semibold text-brand-navy"><?= htmlspecialchars((string)($rows['acc_no'] ?? '—')) ?></span>
            </div>
            <div class="flex justify-between px-4 py-3 text-sm">
                <span class="text-brand-muted">Bank</span>
                <span class="font-semibold text-brand-navy"><?= htmlspecialchars((string)($rows['bank_name'] ?? '—')) ?></span>
            </div>
            <?php if (!empty($rows['swift'])): ?>
            <div class="flex justify-between px-4 py-3 text-sm">
                <span class="text-brand-muted">SWIFT / BIC</span>
                <span class="font-semibold text-brand-navy"><?= htmlspecialchars((string)$rows['swift']) ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($rows['remarks'])): ?>
            <div class="flex justify-between px-4 py-3 text-sm">
                <span class="text-brand-muted">Remarks</span>
                <span class="font-semibold text-brand-navy"><?= htmlspecialchars((string)$rows['remarks']) ?></span>
            </div>
            <?php endif; ?>
            <div class="flex justify-between px-4 py-3 text-sm">
                <span class="text-brand-muted">Transfer Status</span>
                <span class="font-semibold <?= $isFailure ? 'text-red-600' : 'text-green-600' ?>"><?= htmlspecialchars($statusLabel) ?></span>
            </div>
            <div class="flex justify-between px-4 py-3 text-sm">
                <span class="text-brand-muted">Remaining Balance</span>
                <span class="font-semibold text-brand-navy"><?= htmlspecialchars($currency) ?> <?= number_format((float)($row['a_bal'] ?? 0), 2) ?></span>
            </div>
        </div>

        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-center">
            <button type="button" onclick="shareReceipt()" class="no-print inline-flex items-center justify-center gap-2 rounded-lg bg-brand-navy px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-navy2">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 12v7a1 1 0 001 1h14a1 1 0 001-1v-7"/><polyline points="16 6 12 2 8 6"/><line x1="12" y1="2" x2="12" y2="15"/></svg>
                Share Receipt
            </button>
            <button type="button" onclick="printReceipt()" class="no-print inline-flex items-center justify-center gap-2 rounded-lg border border-brand-border px-5 py-2.5 text-sm font-semibold text-brand-navy hover:bg-brand-light">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2V9a2 2 0 012-2h16a2 2 0 012 2v7a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Print
            </button>
            <a href="send.php" class="no-print inline-flex items-center justify-center gap-2 rounded-lg border border-brand-border px-5 py-2.5 text-sm font-semibold text-brand-navy hover:bg-brand-light">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h12"/><path d="M12 3l4 4-4 4"/><path d="M20 17H8"/><path d="M12 13l-4 4 4 4"/></svg>
                New Transfer
            </a>
        </div>
    </div>
</div>

<!-- Print-only receipt -->
<div id="receipt-printable" aria-hidden="true">
    <div class="rp-header">
        <h1><?= htmlspecialchars($shellBankName) ?></h1>
        <p>Transfer Receipt &mdash; <?= date('d M Y, H:i') ?></p>
    </div>
    <div class="rp-row"><span class="label">Reference</span><span class="value"><?= htmlspecialchars($txRef) ?></span></div>
    <div class="rp-row"><span class="label">Status</span><span class="value"><?= htmlspecialchars($statusLabel) ?></span></div>
    <div class="rp-row"><span class="label">Amount</span><span class="value"><?= htmlspecialchars($currency) ?> <?= htmlspecialchars((string)($rows['amount'] ?? '—')) ?></span></div>
    <div class="rp-row"><span class="label">Beneficiary</span><span class="value"><?= htmlspecialchars($beneficiaryDisplay) ?></span></div>
    <div class="rp-row"><span class="label">Account / IBAN</span><span class="value"><?= htmlspecialchars((string)($rows['acc_no'] ?? '—')) ?></span></div>
    <div class="rp-row"><span class="label">Bank</span><span class="value"><?= htmlspecialchars((string)($rows['bank_name'] ?? '—')) ?></span></div>
    <?php if (!empty($rows['swift'])): ?>
    <div class="rp-row"><span class="label">SWIFT / BIC</span><span class="value"><?= htmlspecialchars((string)$rows['swift']) ?></span></div>
    <?php endif; ?>
    <?php if (!empty($rows['remarks'])): ?>
    <div class="rp-row"><span class="label">Remarks</span><span class="value"><?= htmlspecialchars((string)$rows['remarks']) ?></span></div>
    <?php endif; ?>
    <div class="rp-row"><span class="label">Remaining Balance</span><span class="value"><?= htmlspecialchars($currency) ?> <?= number_format((float)($row['a_bal'] ?? 0), 2) ?></span></div>
    <div class="rp-footer"><?= htmlspecialchars($shellBankName) ?> &bull; This is a system-generated receipt.</div>
</div>

<!-- Toast for clipboard copy -->
<div id="shareToast" class="no-print fixed bottom-6 left-1/2 -translate-x-1/2 z-50 hidden rounded-xl bg-brand-navy px-5 py-3 text-sm font-semibold text-white shadow-xl transition-all">
    Receipt PDF downloaded
</div>

<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script>
(function () {
    const bankName   = <?= json_encode($shellBankName, JSON_HEX_TAG) ?>;
    const logoUrl    = <?= json_encode($receiptLogoUrl, JSON_HEX_TAG) ?>;
    const txRef      = <?= json_encode($txRef, JSON_HEX_TAG) ?>;
    const amount     = <?= json_encode($currency . ' ' . ($rows['amount'] ?? '—'), JSON_HEX_TAG) ?>;
    const bene       = <?= json_encode($beneficiaryDisplay, JSON_HEX_TAG) ?>;
    const accNo      = <?= json_encode((string)($rows['acc_no'] ?? '—'), JSON_HEX_TAG) ?>;
    const bankDest   = <?= json_encode((string)($rows['bank_name'] ?? '—'), JSON_HEX_TAG) ?>;
    const status     = <?= json_encode($statusLabel, JSON_HEX_TAG) ?>;
    const dateStr    = <?= json_encode(date('d M Y, H:i'), JSON_HEX_TAG) ?>;
    const swift      = <?= json_encode((string)($rows['swift'] ?? ''), JSON_HEX_TAG) ?>;
    const remarks    = <?= json_encode((string)($rows['remarks'] ?? ''), JSON_HEX_TAG) ?>;
    const balance    = <?= json_encode($currency . ' ' . number_format((float)($row['a_bal'] ?? 0), 2), JSON_HEX_TAG) ?>;

    function showToast(text) {
        var toast = document.getElementById('shareToast');
        if (!toast) return;
        toast.textContent = text;
        toast.classList.remove('hidden');
        setTimeout(function () { toast.classList.add('hidden'); }, 3200);
    }

    function escapeHtml(value) {
        return String(value || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function receiptRowsHtml() {
        var rows = '';
        function addRow(label, value) {
            rows += '<div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #eef2f7;font-size:13px;gap:10px;">'
              + '<span style="color:#64748b;">' + escapeHtml(label) + '</span>'
              + '<span style="color:#0f172a;font-weight:600;text-align:right;word-break:break-word;max-width:62%;">' + escapeHtml(value) + '</span>'
              + '</div>';
        }
        addRow('Reference', txRef);
        addRow('Status', status);
        addRow('Amount', amount);
        addRow('Beneficiary', bene);
        addRow('Account / IBAN', accNo);
        addRow('Bank', bankDest);
        if (swift) addRow('SWIFT / BIC', swift);
        if (remarks) addRow('Remarks', remarks);
        addRow('Remaining Balance', balance);
        return rows;
    }

    function openPrintWindow() {
        var w = window.open('', '_blank', 'width=880,height=920');
        if (!w) {
            showToast('Please allow pop-ups to print receipt');
            return;
        }
        var html = ''
          + '<!doctype html><html><head><meta charset="utf-8"><title>Transfer Receipt</title>'
          + '<style>body{font-family:Arial,sans-serif;margin:0;padding:28px;background:#f8fafc;color:#0f172a;}'
          + '.card{max-width:680px;margin:0 auto;background:#fff;border:1px solid #dbe3ee;border-radius:14px;overflow:hidden;}'
          + '.head{padding:18px 22px;border-bottom:1px solid #e5eaf2;text-align:center;background:#f8fbff;}'
          + '.head img{max-height:54px;max-width:220px;display:block;margin:0 auto 10px auto;object-fit:contain;}'
          + '.head h1{margin:0;font-size:18px;color:#0d1f3c;}.head p{margin:6px 0 0;font-size:12px;color:#64748b;}'
          + '.body{padding:18px 22px;}.foot{padding:14px 22px;background:#f8fbff;border-top:1px solid #e5eaf2;font-size:11px;color:#6b7280;text-align:center;}'
          + '</style></head><body>'
          + '<div class="card"><div class="head">'
          + (logoUrl ? '<img src="' + escapeHtml(logoUrl) + '" alt="Logo" onerror="this.style.display=\'none\'">' : '')
          + '<h1>' + escapeHtml(bankName) + ' - Transfer Receipt</h1><p>' + escapeHtml(dateStr) + '</p></div>'
          + '<div class="body">' + receiptRowsHtml() + '</div>'
          + '<div class="foot">This is a system-generated receipt.</div></div>'
          + '<script>window.onload=function(){setTimeout(function(){window.print();},200);};<\/script>'
          + '</body></html>';
        w.document.open();
        w.document.write(html);
        w.document.close();
    }

    function loadImageDataUrl(url) {
        return fetch(url, { cache: 'no-store' })
            .then(function (r) { if (!r.ok) throw new Error('logo'); return r.blob(); })
            .then(function (blob) {
                return new Promise(function (resolve, reject) {
                    var fr = new FileReader();
                    fr.onloadend = function () { resolve(fr.result); };
                    fr.onerror = reject;
                    fr.readAsDataURL(blob);
                });
            });
    }

    function buildPdfBlob() {
        if (!window.jspdf || !window.jspdf.jsPDF) {
            return Promise.resolve(null);
        }
        var jsPDF = window.jspdf.jsPDF;
        var doc = new jsPDF({ unit: 'pt', format: 'a4' });
        var y = 44;

        function drawContent() {
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(16);
            doc.text(bankName + ' - Transfer Receipt', 40, y);
            y += 20;
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(10);
            doc.setTextColor(100, 116, 139);
            doc.text('Generated: ' + dateStr, 40, y);
            y += 18;
            doc.setDrawColor(220, 227, 236);
            doc.line(40, y, 555, y);
            y += 20;

            var lines = [
                ['Reference', txRef],
                ['Status', status],
                ['Amount', amount],
                ['Beneficiary', bene],
                ['Account / IBAN', accNo],
                ['Bank', bankDest]
            ];
            if (swift) lines.push(['SWIFT / BIC', swift]);
            if (remarks) lines.push(['Remarks', remarks]);
            lines.push(['Remaining Balance', balance]);

            doc.setTextColor(15, 23, 42);
            doc.setFontSize(11);
            lines.forEach(function (entry) {
                doc.setFont('helvetica', 'normal');
                doc.setTextColor(100, 116, 139);
                doc.text(String(entry[0]), 40, y);
                doc.setFont('helvetica', 'bold');
                doc.setTextColor(15, 23, 42);
                doc.text(String(entry[1]), 555, y, { align: 'right', maxWidth: 330 });
                y += 20;
                doc.setDrawColor(238, 242, 247);
                doc.line(40, y - 8, 555, y - 8);
            });

            return doc.output('blob');
        }

        if (!logoUrl) {
            return Promise.resolve(drawContent());
        }

        return loadImageDataUrl(logoUrl)
            .then(function (dataUrl) {
                try {
                    doc.addImage(dataUrl, 'PNG', 40, y - 4, 96, 34);
                    y += 42;
                } catch (e) {
                }
                return drawContent();
            })
            .catch(function () {
                return drawContent();
            });
    }

    const receiptText =
        bankName + ' — Transfer Receipt\n' +
        '─────────────────────\n' +
        'Date:        ' + dateStr + '\n' +
        'Reference:   ' + txRef + '\n' +
        'Status:      ' + status + '\n' +
        'Amount:      ' + amount + '\n' +
        'Beneficiary: ' + bene + '\n' +
        'Account:     ' + accNo + '\n' +
        'Bank:        ' + bankDest + '\n' +
        '─────────────────────\n' +
        bankName;

    window.shareReceipt = function () {
        buildPdfBlob().then(function (blob) {
            if (!blob) {
                openPrintWindow();
                return;
            }

            var fileName = 'transfer-receipt-' + txRef + '.pdf';
            var pdfFile = new File([blob], fileName, { type: 'application/pdf' });

            if (navigator.canShare && navigator.share && navigator.canShare({ files: [pdfFile] })) {
                navigator.share({
                    title: bankName + ' Transfer Receipt',
                    text: 'Transfer receipt attached.',
                    files: [pdfFile],
                }).catch(function () {});
                return;
            }

            var dlUrl = URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = dlUrl;
            a.download = fileName;
            document.body.appendChild(a);
            a.click();
            a.remove();
            setTimeout(function () { URL.revokeObjectURL(dlUrl); }, 2500);
            showToast('Receipt PDF downloaded');
        }).catch(function () {
            prompt('Copy the receipt below:', receiptText);
        });
    };

    window.printReceipt = function () {
        openPrintWindow();
    };
}());
</script>

<?php require_once __DIR__ . '/partials/shell-close.php'; exit(); ?>
