<?php
session_start();
include_once('session.php');
require_once 'class.user.php';
if (!isset($_SESSION['acc_no'])) {

  header("Location: login.php");

  exit();
} elseif (!isset($_SESSION['pin'])) {

  header("Location: passcode.php");
  exit();
}

$reg_user = new USER();

$stmt = $reg_user->runQuery("SELECT * FROM account WHERE acc_no=:acc_no");
$stmt->execute(array(":acc_no" => $_SESSION['acc_no']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);

$site = $row['site'];

$stct = $reg_user->runQuery("SELECT * FROM site WHERE id = '20'");
$stct->execute();
$rowp = $stct->fetch(PDO::FETCH_ASSOC);

$email   = (string)($row['email'] ?? '');
$accNo   = (string)($row['acc_no'] ?? '');
$curCode = strtoupper(trim((string)($row['currency'] ?? 'USD')));

include_once('counter.php');

// -- All transfers for this account
$txStmt = $reg_user->runQuery('SELECT * FROM transfer WHERE email = :email ORDER BY id DESC');
$txStmt->execute([':email' => $email]);
$allTransfers = $txStmt->fetchAll(PDO::FETCH_ASSOC);

// -- All alerts for this account
$alStmt = $reg_user->runQuery('SELECT * FROM alerts WHERE uname = :uname ORDER BY id DESC');
$alStmt->execute([':uname' => $accNo]);
$allAlerts = $alStmt->fetchAll(PDO::FETCH_ASSOC);

// -- Build unified sorted activity
$allActivity = [];
foreach ($allTransfers as $tx) {
  $ts    = strtotime((string)($tx['date'] ?? ''));
  $txCur = strtoupper(trim((string)($tx['currency_code'] ?: $row['currency'] ?: 'USD')));
  $allActivity[] = [
    'source'        => 'transfer',
    'direction'     => 'debit',
    'type_label'    => 'Debit',
    'amount'        => (float)($tx['amount'] ?? 0),
    'currency'      => $txCur,
    'description'   => trim((string)($tx['acc_name'] ?: $tx['bank_name'] ?: '—')),
    'date_str'      => $ts ? date('d F, Y', $ts) : (string)($tx['date'] ?? '—'),
    'sort_key'      => $ts ?: 0,
    'bank_name'     => (string)($tx['bank_name'] ?? ''),
    'acc_no'        => (string)($tx['acc_no'] ?? ''),
    'reci_name'     => (string)($tx['reci_name'] ?? ''),
    'transfer_type' => (string)($tx['transfer_type'] ?? ''),
    'swift'         => (string)($tx['swift'] ?? ''),
    'routing'       => (string)($tx['routing'] ?? ''),
    'remarks'       => (string)($tx['remarks'] ?? ''),
    'status'        => (string)($tx['status'] ?? ''),
  ];
}
foreach ($allAlerts as $al) {
  $aDate = trim((string)($al['date'] ?? ''));
  $aTime = trim((string)($al['time'] ?? ''));
  $ts    = strtotime($aDate . ($aTime !== '' ? ' ' . $aTime : ' 00:00:00')) ?: 0;
  $allActivity[] = [
    'source'        => 'alert',
    'direction'     => 'credit',
    'type_label'    => 'Credit',
    'amount'        => (float)($al['amount'] ?? 0),
    'currency'      => $curCode,
    'description'   => trim((string)($al['sender_name'] ?? '—')),
    'date_str'      => $ts > 0 ? date('d F, Y', $ts) : $aDate,
    'sort_key'      => $ts,
    'bank_name'     => '',
    'acc_no'        => '',
    'reci_name'     => '',
    'transfer_type' => trim((string)($al['type'] ?? '')),
    'swift'         => '',
    'routing'       => '',
    'remarks'       => (string)($al['remarks'] ?? ''),
    'status'        => '',
  ];
}
usort($allActivity, static fn($a, $b) => $b['sort_key'] <=> $a['sort_key']);

require_once __DIR__ . '/partials/shell-data.php';
$_logoBase64 = '';
$_logoFile   = __DIR__ . '/img/logo.png';
if (is_file($_logoFile)) {
  $_logoRaw = file_get_contents($_logoFile);
  if ($_logoRaw !== false && strlen($_logoRaw) > 0) {
    $_logoBase64 = 'data:image/png;base64,' . base64_encode($_logoRaw);
  }
}
$shellPageTitle = 'Transaction History';
require_once __DIR__ . '/partials/shell-open.php';
?>

<div class="mb-6 flex items-start justify-between gap-4 flex-wrap">
  <div>
    <h1 class="text-2xl font-bold text-gray-900">Transaction History</h1>
    <p class="text-sm text-gray-500 mt-1">All transactions linked to your account.</p>
  </div>
  <?php if (!empty($allActivity)): ?>
    <button onclick="printFullStatement()" class="flex-shrink-0 inline-flex items-center gap-2 rounded-xl bg-brand-navy px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-navy2 transition-colors shadow-sm">
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
      </svg>
      Print Statement
    </button>
  <?php endif; ?>
</div>

<?php if (empty($allActivity)): ?>
  <div class="bg-white rounded-2xl shadow-sm border border-brand-border p-12 text-center text-sm text-brand-muted">
    No transactions found.
  </div>
<?php else: ?>

  <!-- Detail modal -->
  <div id="txModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4 py-8">
    <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">
      <div id="txModalContent" class="p-6 overflow-y-auto">
        <div class="flex items-center justify-between mb-5">
          <h3 class="text-lg font-bold text-gray-900">Transaction Details</h3>
          <button onclick="closeTxModal()" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <dl id="txModalBody" class="space-y-2 text-sm"></dl>
      </div>
      <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50 flex-shrink-0">
        <button onclick="printTxModal()" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-white">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
          </svg>
          Print
        </button>
        <button onclick="closeTxModal()" class="rounded-lg bg-brand-navy px-4 py-2 text-sm font-medium text-white hover:bg-brand-navy2">Close</button>
      </div>
    </div>
  </div>

  <div class="bg-white rounded-2xl shadow-sm border border-brand-border overflow-hidden">
    <div class="divide-y divide-brand-border">
      <?php foreach ($allActivity as $idx => $act): ?>
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
          <div class="flex-shrink-0">
            <button onclick="openTxModal(<?= (int)$idx ?>)" class="text-xs font-medium text-brand-navy border border-brand-border rounded-lg px-3 py-1.5 hover:bg-brand-light transition-colors whitespace-nowrap">
              Details
            </button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <script>
    var txData = <?= json_encode(array_values($allActivity), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    var _siteName = <?= json_encode($shellBankName,  JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    var _logoSrc = <?= json_encode($_logoBase64,    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    var _accHolder = <?= json_encode($shellFullName,  JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    var _accNumber = <?= json_encode($shellAccNo,     JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    var _accCur = <?= json_encode($curCode,        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    var _today = <?= json_encode(date('d F, Y'),  JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;

    function _logoHtml(h) {
      return _logoSrc ?
        '<img src="' + _logoSrc + '" style="height:' + h + 'px;width:auto;object-fit:contain;display:block;" alt="' + escHtml(_siteName) + '">' :
        '<span style="font-size:' + Math.round(h * 0.55) + 'px;font-weight:800;color:#0d1f3c;">' + escHtml(_siteName) + '</span>';
    }

    function openTxModal(idx) {
      var tx = txData[idx];
      if (!tx) return;
      var isDebit = tx.direction === 'debit';
      var fields = [
        ['Date', tx.date_str],
        ['Type', tx.type_label],
        ['Description', tx.description],
        ['Currency', tx.currency],
        ['Amount', (isDebit ? '\u2212' : '+') + tx.currency + '\u00a0' + parseFloat(tx.amount).toFixed(2)],
      ];
      if (tx.source === 'transfer') {
        if (tx.reci_name) fields.splice(2, 0, ['Recipient', tx.reci_name]);
        if (tx.bank_name) fields.splice(3, 0, ['Bank', tx.bank_name]);
        if (tx.acc_no) fields.splice(4, 0, ['Account No.', tx.acc_no]);
        if (tx.transfer_type) fields.push(['Transfer Type', tx.transfer_type]);
        if (tx.swift) fields.push(['SWIFT / BIC', tx.swift]);
        if (tx.routing) fields.push(['Routing No.', tx.routing]);
        if (tx.status) fields.push(['Status', tx.status]);
      } else {
        if (tx.transfer_type) fields.splice(2, 0, ['Alert Type', tx.transfer_type]);
      }
      if (tx.remarks) fields.push(['Remarks', tx.remarks]);

      document.getElementById('txModalBody').innerHTML = fields.map(function(f) {
        return '<div class="flex justify-between gap-4 items-start py-1.5 border-b border-gray-50">' +
          '<dt class="text-gray-500 shrink-0 w-36">' + escHtml(f[0]) + '</dt>' +
          '<dd class="font-medium text-gray-900 text-right break-all">' + escHtml(String(f[1])) + '</dd>' +
          '</div>';
      }).join('');

      document.getElementById('txModal').dataset.idx = idx;
      var modal = document.getElementById('txModal');
      modal.classList.remove('hidden');
      modal.classList.add('flex');
    }

    function closeTxModal() {
      var modal = document.getElementById('txModal');
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    }

    function printTxModal() {
      var idx = parseInt(document.getElementById('txModal').dataset.idx || '0', 10);
      var tx = txData[idx];
      if (!tx) return;
      var isDebit = tx.direction === 'debit';
      var body = document.getElementById('txModalBody').innerHTML;

      var win = window.open('', '_blank', 'width=700,height=860');
      win.document.write(
        '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Transaction Receipt &mdash; ' + escHtml(_siteName) + '</title>' +
        '<style>' +
        '*{box-sizing:border-box;margin:0;padding:0}' +
        'body{font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#111;background:#fff}' +
        '.page{max-width:620px;margin:0 auto;padding:36px 28px}' +
        '.header{display:flex;align-items:center;justify-content:space-between;padding-bottom:18px;border-bottom:2px solid #0d1f3c;margin-bottom:22px}' +
        '.header-right{text-align:right}' +
        '.header-right h1{font-size:17px;font-weight:700;color:#0d1f3c;letter-spacing:.3px}' +
        '.header-right p{font-size:11px;color:#6b7280;margin-top:4px}' +
        '.acct-box{background:#f8fafc;border:1px solid #dce3ec;border-radius:8px;padding:14px 18px;margin-bottom:22px;display:flex;gap:24px;flex-wrap:wrap}' +
        '.acct-box .f .lbl{font-size:10px;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px}' +
        '.acct-box .f .val{font-size:13px;font-weight:700;color:#0d1f3c}' +
        '.sec{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#6b7280;margin-bottom:10px}' +
        'dl>div{display:flex;justify-content:space-between;gap:1rem;padding:7px 0;border-bottom:1px solid #f1f5f9}' +
        'dl>div:last-child{border-bottom:none}' +
        'dt{color:#6b7280;flex-shrink:0;width:150px;font-size:12px}' +
        'dd{font-weight:600;text-align:right;word-break:break-all;font-size:12px;color:#0d1f3c;margin:0}' +
        '.amt dd{font-size:15px;font-weight:700;color:' + (isDebit ? '#dc2626' : '#16a34a') + '}' +
        '.footer{margin-top:30px;padding-top:14px;border-top:1px solid #e2e8f0;font-size:10px;color:#9ca3af;text-align:center;line-height:1.7}' +
        '@media print{body{-webkit-print-color-adjust:exact;print-color-adjust:exact}}' +
        '</style></head><body><div class="page">' +
        '<div class="header"><div>' + _logoHtml(46) + '</div>' +
        '<div class="header-right"><h1>Transaction Receipt</h1>' +
        '<p>Ref: ' + escHtml(_accNumber) + '</p><p>' + escHtml(_today) + '</p></div></div>' +
        '<div class="acct-box">' +
        '<div class="f"><div class="lbl">Account Holder</div><div class="val">' + escHtml(_accHolder) + '</div></div>' +
        '<div class="f"><div class="lbl">Account No.</div><div class="val">' + escHtml(_accNumber) + '</div></div>' +
        '<div class="f"><div class="lbl">Currency</div><div class="val">' + escHtml(_accCur) + '</div></div>' +
        '</div>' +
        '<div class="sec">Transaction Details</div>' +
        '<dl>' + body + '</dl>' +
        '<div class="footer">' + escHtml(_siteName) + ' &mdash; This receipt is computer-generated and does not require a signature.<br>' +
        'Printed on ' + escHtml(_today) + '</div>' +
        '</div></body></html>'
      );
      win.document.close();
      win.focus();
      win.print();
    }

    function printFullStatement() {
      var rows = '';
      var totalDebit = 0,
        totalCredit = 0;
      // Render oldest first in the statement
      for (var i = txData.length - 1; i >= 0; i--) {
        var tx = txData[i];
        var isD = tx.direction === 'debit';
        var amt = parseFloat(tx.amount).toFixed(2);
        if (isD) {
          totalDebit += parseFloat(tx.amount);
        } else {
          totalCredit += parseFloat(tx.amount);
        }
        var chipBg = isD ? '#fef2f2' : '#f0fdf4';
        var chipClr = isD ? '#dc2626' : '#16a34a';
        rows += '<tr>' +
          '<td>' + escHtml(tx.date_str) + '</td>' +
          '<td><span style="display:inline-block;padding:2px 8px;border-radius:99px;font-size:10px;font-weight:700;background:' + chipBg + ';color:' + chipClr + ';-webkit-print-color-adjust:exact;print-color-adjust:exact">' + escHtml(tx.type_label) + '</span></td>' +
          '<td>' + escHtml(tx.description) + '</td>' +
          '<td class="num red">' + (isD ? escHtml(tx.currency) + '\u00a0' + amt : '') + '</td>' +
          '<td class="num grn">' + (!isD ? escHtml(tx.currency) + '\u00a0' + amt : '') + '</td>' +
          '</tr>';
      }

      var firstDate = txData.length ? txData[txData.length - 1].date_str : '\u2014';
      var lastDate = txData.length ? txData[0].date_str : '\u2014';

      var win = window.open('', '_blank', 'width=940,height=1050');
      win.document.write(
        '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Account Statement &mdash; ' + escHtml(_siteName) + '</title>' +
        '<style>' +
        '*{box-sizing:border-box;margin:0;padding:0}' +
        'body{font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#111;background:#fff}' +
        '.page{max-width:820px;margin:0 auto;padding:40px 36px}' +
        '.header{display:flex;align-items:flex-start;justify-content:space-between;padding-bottom:20px;border-bottom:3px solid #0d1f3c;margin-bottom:28px}' +
        '.header-right{text-align:right}' +
        '.header-right h1{font-size:22px;font-weight:700;color:#0d1f3c;margin-bottom:5px}' +
        '.header-right p{font-size:11px;color:#6b7280;margin-top:3px}' +
        '.acct-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;background:#f8fafc;border:1px solid #dce3ec;border-radius:8px;padding:18px 22px;margin-bottom:28px}' +
        '.acct-grid .lbl{font-size:10px;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px}' +
        '.acct-grid .val{font-size:13px;font-weight:700;color:#0d1f3c}' +
        '.sec{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#6b7280;margin-bottom:10px}' +
        'table{width:100%;border-collapse:collapse}' +
        'thead tr{background:#0d1f3c;-webkit-print-color-adjust:exact;print-color-adjust:exact}' +
        'thead th{color:#fff;padding:10px 10px;font-size:11px;text-align:left;font-weight:600;letter-spacing:.3px}' +
        'thead th.num{text-align:right}' +
        'tbody td{padding:8px 10px;border-bottom:1px solid #f1f5f9;font-size:11px;color:#374151;vertical-align:middle}' +
        'tbody tr:nth-child(even){background:#f8fafc;-webkit-print-color-adjust:exact;print-color-adjust:exact}' +
        'td.num{text-align:right;font-weight:600}' +
        'td.red{color:#dc2626}' +
        'td.grn{color:#16a34a}' +
        '.tot td{padding:10px 10px;font-size:12px;font-weight:700;border-top:2px solid #0d1f3c;background:#f0f4ff;-webkit-print-color-adjust:exact;print-color-adjust:exact}' +
        '.footer{margin-top:36px;padding-top:16px;border-top:1px solid #e2e8f0;font-size:10px;color:#9ca3af;text-align:center;line-height:1.8}' +
        '@media print{body{-webkit-print-color-adjust:exact;print-color-adjust:exact}@page{margin:1.5cm}}' +
        '</style></head><body><div class="page">' +
        '<div class="header">' +
        '<div>' + _logoHtml(54) + '</div>' +
        '<div class="header-right"><h1>Account Statement</h1>' +
        '<p>Period: ' + escHtml(firstDate) + ' &ndash; ' + escHtml(lastDate) + '</p>' +
        '<p>Generated: ' + escHtml(_today) + '</p></div></div>' +
        '<div class="acct-grid">' +
        '<div><div class="lbl">Account Holder</div><div class="val">' + escHtml(_accHolder) + '</div></div>' +
        '<div><div class="lbl">Account Number</div><div class="val">' + escHtml(_accNumber) + '</div></div>' +
        '<div><div class="lbl">Base Currency</div><div class="val">' + escHtml(_accCur) + '</div></div>' +
        '</div>' +
        '<div class="sec">Transaction History (' + txData.length + ' record' + (txData.length !== 1 ? 's' : '') + ')</div>' +
        '<table><thead><tr>' +
        '<th>Date</th><th>Type</th><th>Description</th><th class="num">Debit (&minus;)</th><th class="num">Credit (+)</th>' +
        '</tr></thead><tbody>' + rows +
        '<tr class="tot">' +
        '<td colspan="3" style="text-align:right;color:#6b7280;font-size:11px">Totals</td>' +
        '<td class="num red">' + escHtml(_accCur) + '\u00a0' + totalDebit.toFixed(2) + '</td>' +
        '<td class="num grn">' + escHtml(_accCur) + '\u00a0' + totalCredit.toFixed(2) + '</td>' +
        '</tr>' +
        '</tbody></table>' +
        '<div class="footer">' + escHtml(_siteName) + ' &mdash; This statement is computer-generated and does not require a signature.<br>' +
        'For enquiries, please contact your relationship manager. &bull; Printed on ' + escHtml(_today) + '</div>' +
        '</div></body></html>'
      );
      win.document.close();
      win.focus();
      win.print();
    }

    function escHtml(s) {
      return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    document.getElementById('txModal').addEventListener('click', function(e) {
      if (e.target === this) closeTxModal();
    });
  </script>

<?php endif; ?>

<?php require_once __DIR__ . '/partials/shell-close.php';
exit(); ?>
<!DOCTYPE html>
<!-- saved from url=(0054)statement.php -->
<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <title>Account Statement</title>

  <meta content="ie=edge" http-equiv="x-ua-compatible">
  <meta content="template language" name="keywords">
  <meta content="Online Bank" name="author">
  <meta content="Secure Banking" name="description">
  <meta content="width=device-width, initial-scale=1" name="viewport">
  <link href="../asset.php?type=favicon" rel="shortcut icon">

  <link rel="stylesheet" type="text/css" href="dash/statement_files/jquery.dataTables.css">
  <script async="" src="dash/statement_files/analytics.js.download"></script>
  <script type="text/javascript" charset="utf8" src="dash/statement_files/jquery.dataTables.js.download"></script>
  <link href="dash/statement_files/css" rel="stylesheet" type="text/css">
  <link href="dash/statement_files/select2.min.css" rel="stylesheet">
  <link href="dash/statement_files/daterangepicker.css" rel="stylesheet">
  <link href="dash/statement_files/dropzone.css" rel="stylesheet">
  <link href="dash/statement_files/dataTables.bootstrap.min.css" rel="stylesheet">
  <link href="dash/statement_files/fullcalendar.min.css" rel="stylesheet">
  <link href="dash/statement_files/perfect-scrollbar.min.css" rel="stylesheet">
  <link href="dash/statement_files/slick.css" rel="stylesheet">
  <link href="dash/statement_files/main.css" rel="stylesheet">
  <link href="css/new-ui-bridge.css" rel="stylesheet">
  <script src="dash/statement_files/jspdf.debug.js.download" integrity="sha384-NaWTHo/8YCBYJ59830LTz/P4aQZK1sS0SneOgAvhsIl3zBu8r9RevNg5lHCHAuQ/" crossorigin="anonymous"></script>
  <style>
    .alert-success {
      color: #090909;
      background-color: #e2e9e1;
      border-color: #36b927;
    }

    .img {
      display: block;
      margin-left: auto;
      margin-right: auto;
    }

    .table-responsive {
      display: block;
      width: 95%;
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
      -ms-overflow-style: -ms-autohiding-scrollbar;
    }

    h1,
    h2,
    h3,
    h4,
    h5,
    h6,
    .h1,
    .h2,
    .h3,
    .h4,
    .h5,
    .h6 {
      font-family: initial;
      font-weight: 500;
      line-height: 1.2;
      color: #0a0a0b;
    }

    .btn-success {

      color:

        #fff;

      background-color:
        #00afe9;

      border-color:

        #00afe9;

    }
  </style>
  <link rel="stylesheet" type="text/css" href="dash/statement_files/clock_style.css">
  <script type="text/javascript">
    window.onload = setInterval(clock, 1000);

    function clock() {
      var d = new Date();

      var date = d.getDate();

      var month = d.getMonth();
      var montharr = ["Jan", "Feb", "Mar", "April", "May", "June", "July", "Aug", "Sep", "Oct", "Nov", "Dec"];
      month = montharr[month];

      var year = d.getFullYear();

      var day = d.getDay();
      var dayarr = ["Sun", "Mon", "Tues", "Wed", "Thurs", "Fri", "Sat"];
      day = dayarr[day];

      var hour = d.getHours();
      var min = d.getMinutes();
      var sec = d.getSeconds();

      document.getElementById("date").innerHTML = day + " " + date + " " + month + " " + year;
      document.getElementById("time").innerHTML = hour + ":" + min + ":" + sec;
    }
  </script>
  <style type="text/css">
    /* Chart.js */
    @-webkit-keyframes chartjs-render-animation {
      from {
        opacity: 0.99
      }

      to {
        opacity: 1
      }
    }

    @keyframes chartjs-render-animation {
      from {
        opacity: 0.99
      }

      to {
        opacity: 1
      }
    }

    .chartjs-render-monitor {
      -webkit-animation: chartjs-render-animation 0.001s;
      animation: chartjs-render-animation 0.001s;
    }
  </style>
  <style>
    .cke {
      visibility: hidden;
    }
  </style>
</head>

<body class="menu-position-side menu-side-left full-screen with-content-panel">
  <div class="all-wrapper with-side-panel solid-bg-all">
    <div class="search-with-suggestions-w">
      <div class="search-with-suggestions-modal">
        <script type="text/javascript">
          function demoFromHTML() {
            var pdf = new jsPDF('p', 'pt', 'letter');
            // source can be HTML-formatted string, or a reference
            // to an actual DOM element from which the text will be scraped.
            source = $('#customers')[0];

            // we support special element handlers. Register them with jQuery-style 
            // ID selector for either ID or node name. ("#iAmID", "div", "span" etc.)
            // There is no support for any other type of selectors 
            // (class, of compound) at this time.
            specialElementHandlers = {
              // element with id of "bypass" - jQuery style selector
              '#bypassme': function(element, renderer) {
                // true = "handled elsewhere, bypass text extraction"
                return true
              }
            };
            margins = {
              top: 80,
              bottom: 60,
              left: 40,
              width: 522
            };
            // all coords and widths are in jsPDF instance's declared units
            // 'inches' in this case
            pdf.fromHTML(
              source, // HTML string or DOM elem ref.
              margins.left, // x coord
              margins.top, { // y coord
                'width': margins.width, // max width of content on PDF
                'elementHandlers': specialElementHandlers
              },
              function(dispose) {
                // dispose: object with X, Y of the last line add to the PDF 
                //          this allow the insertion of new lines after html
                pdf.save('Test.pdf');
              }, margins);
          }
        </script>
        <div class="search-suggestions-group">
          <div class="ssg-header">
            <div class="ssg-icon">
              <div class="os-icon os-icon-box"></div>
            </div>
            <div class="ssg-name">
            </div>
            <div class="ssg-info">
            </div>
          </div>
          <div class="ssg-content">
            <div class="ssg-items ssg-items-boxed">
              <a class="ssg-item" href="users_profile_big.html">
                <div class="item-media" style="background-image: url(img/company6.png)"></div>
                <div class="item-name">
                </div>
              </a><a class="ssg-item" href="users_profile_big.html">
                <div class="item-media" style="background-image: url(img/company7.png)"></div>
                <div class="item-name">
                  <span></span>
                </div>
              </a>
            </div>
          </div>
        </div>
        <div class="search-suggestions-group">
          <div class="ssg-header">
            <div class="ssg-icon">
              <div class="os-icon os-icon-users"></div>
            </div>
            <div class="ssg-name">
            </div>
            <div class="ssg-info">
            </div>
          </div>
          <div class="ssg-content">
            <div class="ssg-items ssg-items-list">
              <a class="ssg-item" href="users_profile_big.html">
                <div class="item-media" style="background-image: url(admin/pic/<?php echo $row['acc_no']; ?>.jpg)"></div>
                <div class="item-name">
                  <span></span>s
                </div>
              </a><a class="ssg-item" href="users_profile_big.html">
                <div class="item-media" style="background-image: url(img/avatar2.jpg)"></div>
                <div class="item-name">
                  Th<span>omas</span> Mullier
                </div>
              </a><a class="ssg-item" href="users_profile_big.html">
                <div class="item-media" style="background-image: url(img/avatar3.jpg)"></div>
                <div class="item-name">
                  Kim C<span>olli</span>ns
                </div>
              </a>
            </div>
          </div>
        </div>
        <div class="search-suggestions-group">
          <div class="ssg-header">
            <div class="ssg-icon">
              <div class="os-icon os-icon-folder"></div>
            </div>
            <div class="ssg-name">
              Files
            </div>
            <div class="ssg-info">
              17 Total
            </div>
          </div>
          <div class="ssg-content">
            <div class="ssg-items ssg-items-blocks">
              <a class="ssg-item" href="statement.php#">
                <div class="item-icon">
                  <i class="os-icon os-icon-file-text"></i>
                </div>
                <div class="item-name">
                  Work<span>Not</span>e.txt
                </div>
              </a><a class="ssg-item" href="statement.php#">
                <div class="item-icon">
                  <i class="os-icon os-icon-film"></i>
                </div>
                <div class="item-name">
                  V<span>ideo</span>.avi
                </div>
              </a><a class="ssg-item" href="statement.php#">
                <div class="item-icon">
                  <i class="os-icon os-icon-database"></i>
                </div>
                <div class="item-name">
                  <span></span>
                </div>
              </a><a class="ssg-item" href="statement.php#">
                <div class="item-icon">
                  <i class="os-icon os-icon-image"></i>
                </div>
                <div class="item-name">
                  <span></span>
                </div>
              </a>
            </div>
            <div class="ssg-nothing-found">
              <div class="icon-w">
                <i class="os-icon os-icon-eye-off"></i>
              </div>
              <span></span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="layout-w">


      <div class="menu-mobile menu-activated-on-click color-scheme-dark">
        <div class="mm-logo-buttons-w">
          <a class="mm-logo" href="index.php"><span>ONLINE BANKING</span></a>
          <div class="mm-buttons">
            <div class="">
            </div>
            <div class="mobile-menu-trigger">
              <div class="os-icon os-icon-hamburger-menu-1"></div>
            </div>
          </div>
        </div>
        <div class="menu-and-user">
          <div class="logged-user-w">
            <div class="avatar-w">
              <img src="admin/foto/<?php echo $row['pp']; ?>" onerror="this.style.display=&#39;none&#39;" style="display: none;">

            </div>
            <div class="logged-user-info-w">
              <div class="logged-user-name">
                <?php echo $row['fname']; ?> <?php echo $row['lname']; ?> </div>
              <div class="logged-user-role">
                Account #: <?php echo $row['acc_no']; ?> </div>
            </div>
          </div>

          <ul class="main-menu">
            <li class="">
              <a href="index.php">
                <div class="icon-w">
                  <div class="os-icon os-icon-layout"></div>
                </div>
                <span>Dashboard</span>
              </a>
            </li>
            <li class="">
              <a href="profile.php">
                <div class="icon-w">
                  <div class="os-icon os-icon-user-male-circle2"></div>
                </div>
                <span>My Profile</span>
              </a>
            </li>
            <li class="">
              <a href="editpass.php">
                <div class="icon-w">
                  <div class="os-icon os-icon-newspaper"></div>
                </div>
                <span>Change Password</span>
              </a>
            </li>
            <li class="">
              <a href="statement.php">
                <div class="icon-w">
                  <div class="os-icon os-icon-newspaper"></div>
                </div>
                <span>My Statement</span>
              </a>
            </li>
            <li class="">
              <a href="send.php">
                <div class="icon-w">
                  <div class="os-icon os-icon-signs-11"></div>
                </div>
                <span>Domestic Transfer</span>
              </a>
            </li>
            <li class="">
              <a href="send.php">
                <div class="icon-w">
                  <div class="os-icon os-icon-mail-19"></div>
                </div>
                <span>Inter bank Transfer</span>
              </a>
            </li>
            <li class="">
              <a href="send.php">
                <div class="icon-w">
                  <div class="os-icon os-icon-mail-19"></div>
                </div>
                <span>Wire Transfer</span>
              </a>
            </li>
            <li class="">
              <a href="loan.php">
                <div class="icon-w">
                  <div class="os-icon os-icon-wallet-loaded"></div>
                </div>
                <span>Apply For Loan</span>
              </a>
            </li>
            <li class="">
              <a href="inbox.php">
                <div class="icon-w">
                  <div class="os-icon os-icon-mail"></div>
                </div>
                <span>Messages</span>
              </a>
            </li>
            <li class="">
              <a href="ticket.php">
                <div class="icon-w">
                  <div class="os-icon os-icon-mail"></div>
                </div>
                <span>Tickets</span>
              </a>
            </li>
            <li class="">
              <a href="https://tawk.to/chat/<?php echo $rowp['tawk2']; ?>">
                <div class="icon-w">
                  <div class="os-icon os-icon-mail"></div>
                </div>
                <span>Contact Us</span>
              </a>
            </li>
            <li class="">
              <a href="logout.php">
                <div class="icon-w">
                  <div class="os-icon os-icon-lock"></div>
                </div>
                <span>Logout</span>
              </a>
            </li>
          </ul>

          <div class="mobile-menu-magic">
            <img alt="" src="dash/statement_files/SEAL.gif">
            <div class="btn-w">
            </div>
          </div>
        </div>
      </div>

      <div class="menu-w color-scheme-dark color-style-bright menu-position-side menu-side-left menu-layout-full sub-menu-style-over sub-menu-color-bright selected-menu-color-light menu-activated-on-hover menu-has-selected-link">
        <center> <img src="img/logo.png" alt="logo" width="170" height="50"></center>
        <div class="logo-w">
          <a class="logo">
            <div class="logo-element"></div>
            <div class="logo-label">
              MY ONLINE BANKING
            </div>
          </a>
        </div>
        <div class="logged-user-w avatar-inline">
          <div class="logged-user-i">
            <div class="avatar-w">
              <img alt="" src="admin/foto/<?php echo $row['pp']; ?>">

            </div>
            <div class="logged-user-info-w">
              <div class="logged-user-name">
                <b style="font-size:13px"> <?php echo $row['fname']; ?> <?php echo $row['lname']; ?></b>
              </div>
              <div class="logged-user-role">
                <b style="color:white;font-size:13px;font-family: inherit;">Account No: <?php echo $row['acc_no']; ?></b>
              </div>
            </div>
          </div>
        </div>
        <div class="menu-actions">































































































          <div class="messages-notifications os-dropdown-trigger os-dropdown-position-right">
            <h5 style="color:white; font-size:14px; padding-top:10px;"> ACCOUNT STATUS: <span style="color:white; font-weight:bolder;"><?php


                                                                                                                                        $stat = $row['status'];

                                                                                                                                        if ($stat == "Active" || $stat == "pincode" || $stat == "otp") {
                                                                                                                                          echo ' <div class="os-icon os-icon-check-circle"> Active</div>  ';
                                                                                                                                        } else {
                                                                                                                                          echo "<span class='tx-warning tx-bold'>";
                                                                                                                                          echo "<div class='os-icon os-icon-alert-triangle'> &nbsp;";
                                                                                                                                          echo $row['status'];
                                                                                                                                          echo "</div>";
                                                                                                                                          echo "</span>";
                                                                                                                                        }

                                                                                                                                        ?></span></h5>
          </div>

        </div>



        <h1 class="menu-page-header">
          Page Header
        </h1>
        <ul class="main-menu">
          <li class="sub-header">
            <span>PERSONAL MENU</span>
          </li>
          <li class="selected has-sub-menu">
            <a href="index.php">
              <div class="icon-w">
                <div class="os-icon os-icon-layout"></div>
              </div>
              <span>Dashboard</span>
            </a>
            <div class="sub-menu-w">
              <div class="sub-menu-icon">
              </div>
              <div class="sub-menu-i">




















              </div>
            </div>
          </li>
          <li class=" has-sub-menu">
            <a href="profile.php">
              <div class="icon-w">
                <div class="os-icon os-icon-user-male-circle2"></div>
              </div>
              <span>My Profile</span>
            </a>
            <div class="sub-menu-w">
              <div class="sub-menu-icon">
                <i class="os-icon os-icon-layers"></i>
              </div>
            </div>
          </li>
          <li class=" has-sub-menu">
            <a href="statement.php">
              <div class="icon-w">
                <div class="os-icon os-icon-newspaper"></div>
              </div>
              <span>My Statement</span>
            </a>
            <div class="sub-menu-w">
              <div class="sub-menu-icon">
                <i class="os-icon os-icon-layers"></i>
              </div>

























































            </div>
          </li>
          <li class=" has-sub-menu">
            <a href="editpass.php">
              <div class="icon-w">
                <div class="os-icon os-icon-newspaper"></div>
              </div>
              <span>Change Password</span>
            </a>
            <div class="sub-menu-w">
              <div class="sub-menu-icon">
                <i class="os-icon os-icon-layers"></i>
              </div>

























































            </div>
          </li>
          <li class="sub-header">
            <span>Transfers</span>
          </li>
          <li class=" has-sub-menu">
            <a href="send.php">
              <div class="icon-w">
                <div class="os-icon os-icon-signs-11"></div>
              </div>
              <span>Domestic Transfer</span>
            </a>
            <div class="sub-menu-w">



              <div class="sub-menu-icon">
                <i class="os-icon os-icon-package"></i>
              </div>
            </div>
          </li>
          <li class=" has-sub-menu">
            <a href="send.php">
              <div class="icon-w">
                <div class="os-icon os-icon-signs-11"></div>
              </div>
              <span>Inter Bank Transfer</span>
            </a>
            <div class="sub-menu-w">



              <div class="sub-menu-icon">
                <i class="os-icon os-icon-package"></i>
              </div>
            </div>
          </li>
          <li class=" has-sub-menu">
            <a href="send.php">
              <div class="icon-w">
                <div class="os-icon os-icon-mail-19"></div>
              </div>
              <span>Wire Transfer</span>
            </a>
            <div class="sub-menu-w">



              <div class="sub-menu-icon">
                <i class="os-icon os-icon-package"></i>
              </div>
            </div>
          </li>
          <li class="sub-header">
            <span>Personal Banking</span>
          </li>
          <li class=" has-sub-menu">
            <a href="ticket.php">
              <div class="icon-w">
                <div class="os-icon os-icon-wallet-loaded"></div>
              </div>
              <span>Create a Ticket</span>
            </a>
            <div class="sub-menu-w">



              <div class="sub-menu-icon">
                <i class="os-icon os-icon-transfer"></i>
              </div>
            </div>
          </li>
          <li class=" has-sub-menu">
            <a href="inbox.php">
              <div class="icon-w">
                <div class="os-icon os-icon-inbox"></div>
              </div>
              <span>Messages</span>
            </a>
            <div class="sub-menu-w">



              <div class="sub-menu-icon">
                <i class="os-icon os-icon-money"></i>
              </div>
            </div>
          </li>
          <li class=" has-sub-menu">
            <a href="loan.php">
              <div class="icon-w">
                <div class="os-icon os-icon-wallet-loaded"></div>
              </div>
              <span>Apply For Loan</span>
            </a>
            <div class="sub-menu-w">



              <div class="sub-menu-icon">
                <i class="os-icon os-icon-money"></i>
              </div>
            </div>
          </li>
          <li class=" has-sub-menu">
            <a href="https://tawk.to/chat/<?php echo $rowp['tawk2']; ?>">
              <div class="icon-w">
                <div class="os-icon os-icon-mail"></div>
              </div>
              <span>Contact Us</span>
            </a>
            <div class="sub-menu-w">



              <div class="sub-menu-icon">
                <i class="os-icon os-icon-package"></i>
              </div>
            </div>
          </li>
          <li class=" has-sub-menu">
            <a href="logout.php">
              <div class="icon-w">
                <div class="os-icon os-icon-lock"></div>
              </div>
              <span>Logout</span>
            </a>
            <div class="sub-menu-w">



              <div class="sub-menu-icon">
                <i class="os-icon os-icon-package"></i>
              </div>
            </div>
          </li>
        </ul>
      </div>
      <div class="content-w">

        <div class="top-bar color-scheme-transparent">

          <div class="top-menu-controls">


            <div class="messages-notifications os-dropdown-trigger os-dropdown-position-left">
              <div id="google_translate_element"></div>
              <script type="text/javascript">
                function googleTranslateElementInit() {
                  new google.translate.TranslateElement({
                    pageLanguage: 'en',
                    autoDisplay: false,
                    layout: google.translate.TranslateElement.InlineLayout.SIMPLE
                  }, 'google_translate_element');
                }
              </script>

              <style>
                .goog-te-banner-frame,
                .goog-te-banner-frame.skiptranslate,
                iframe.skiptranslate,
                iframe.goog-te-banner-frame,
                iframe[src*="translate.google.com/translate"],
                iframe[src*="translate.googleapis.com"],
                .VIpgJd-ZVi9od-ORHb,
                .VIpgJd-ZVi9od-aZ2wEe-wOHMyf,
                body>.skiptranslate {
                  display: none !important;
                  visibility: hidden !important;
                  height: 0 !important;
                  min-height: 0 !important;
                }

                html,
                body {
                  top: 0 !important;
                  margin-top: 0 !important;
                }
              </style>
              <script type="text/javascript">
                (function() {
                  function hideGoogleTranslateBanner() {
                    var selectors = [
                      '.goog-te-banner-frame',
                      '.goog-te-banner-frame.skiptranslate',
                      'iframe.skiptranslate',
                      'iframe.goog-te-banner-frame',
                      'iframe[src*="translate.google.com/translate"]',
                      'iframe[src*="translate.googleapis.com"]',
                      '.VIpgJd-ZVi9od-ORHb',
                      '.VIpgJd-ZVi9od-aZ2wEe-wOHMyf',
                      'body > .skiptranslate'
                    ];
                    for (var i = 0; i < selectors.length; i++) {
                      var nodes = document.querySelectorAll(selectors[i]);
                      for (var j = 0; j < nodes.length; j++) {
                        nodes[j].style.setProperty('display', 'none', 'important');
                        nodes[j].style.setProperty('visibility', 'hidden', 'important');
                        nodes[j].style.setProperty('height', '0', 'important');
                        nodes[j].style.setProperty('min-height', '0', 'important');
                      }
                    }
                    document.documentElement.style.setProperty('top', '0', 'important');
                    if (document.body) {
                      document.body.style.setProperty('top', '0', 'important');
                      document.body.style.setProperty('margin-top', '0', 'important');
                    }
                  }

                  document.addEventListener('DOMContentLoaded', function() {
                    hideGoogleTranslateBanner();
                    var observer = new MutationObserver(function() {
                      hideGoogleTranslateBanner();
                    });
                    observer.observe(document.documentElement, {
                      childList: true,
                      subtree: true,
                      attributes: true
                    });
                    setInterval(hideGoogleTranslateBanner, 500);
                  });
                })();
              </script>
              <!-- Google Translate Element end -->


              <script type="text/javascript" src="dash/index_files/f.txt"></script>



            </div>

            <div class="messages-notifications os-dropdown-trigger os-dropdown-position-left">

              <a href="inbox.php"><i class="os-icon os-icon-mail-14"></i></a>


            </div>

            <div class="top-icon top-settings os-dropdown-trigger os-dropdown-position-left">
              <i class="os-icon os-icon-ui-46"></i>
              <div class="os-dropdown">
                <div class="icon-w">
                  <i class="os-icon os-icon-ui-46"></i>
                </div>
                <ul>
                  <li>
                    <a href="profile.php"><i class="os-icon os-icon-ui-49"></i><span>My Profile</span></a>
                  </li>
                  <li>
                    <a href="logout.php"><i class="os-icon os-icon-lock"></i><span>Logout</span></a>
                  </li>
                </ul>
              </div>
            </div>

            <div class="logged-user-w">
              <div class="logged-user-i">
                <div class="avatar-w">
                  <img alt="" src="admin/foto/<?php echo $row['pp']; ?>">
                </div>
                <div class="logged-user-menu color-style-bright">
                  <div class="logged-user-avatar-info">
                    <div class="avatar-w">
                      <img alt="" src="admin/foto/<?php echo $row['pp']; ?>">
                    </div>
                  </div>
                  <div class="bg-icon">
                    <i class="os-icon os-icon-wallet-loaded"></i>
                  </div>

















                </div>
              </div>
            </div>

          </div>

        </div>

        <ul class="breadcrumb">
          <marquee bgcolor="#0076b6" style="color:white;">Notice: We are committed to providing a secured and convenient banking experience to all our customers through excellent services powered by state of the art technologies, however, if you notice anything <span style="color:orange;">SUSPICIOUS</span> with your online banking portal, kindly contact your account manager for immediate action <span style="color:WHITE" ;="">|</span> Thank you for banking with us! </marquee>


        </ul>
        <div class="content-panel-toggler">
          <i class="os-icon os-icon-grid-squares-22"></i><span>Sidebar</span>
        </div>
        <div class="content-i">
          <div class="content-box">
            <div class="row">
              <div class="col-sm-12">
                <div class="element-wrapper">
                  <div class="element-actions">













                  </div>
                  <h6 class="element-header">
                    YOUR ONLINE BANKING STATEMENT!
                  </h6>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-12 mg-t-20">
                <div class="element-wrapper">

                  <input type="button" id="btn" class="btn btn-success btn-sm" value="Print Statement" onclick="printtag('DivIdToPrint');">
                  <br><br>
                  <div id="DivIdToPrint">
                    <div id="dvContainer"><br>
                      <center><img src="img/sc.png" alt="eh" style="width:30%;"></center><br>
                      <center>
                        <p>Account Number: <b>(<?php echo $row['acc_no']; ?>)</b></p>
                        <p>Account Name: <b>(<?php echo $row['fname']; ?> <?php echo $row['lname']; ?>)</b> </p>
                        <p>Date:
                          <span id="datetime" class="tx-success tx-bold"> </span>
                          <script>
                            var dt = new Date();
                            document.getElementById("datetime").innerHTML = dt.toLocaleString();
                          </script>
                          </span>
                        </p>
                        </ul>
                      </center>
                      <div class="element-box-tp">

                        <div class="controls-above-table">
                          <div class="row">
                            <div class="col-sm-6">

                            </div>
                            <div class="col-sm-6">
















                            </div>
                          </div>
                        </div>

                        <h6 class="element-header">
                          TRANSFER HISTORY
                        </h6>
                        <div class="table-responsive">
                          <script>
                            $(document).ready(function() {
                              $('#table').DataTable();
                            });
                          </script>
                          <?php
                          $acc_no = $_SESSION['acc_no'];
                          $email = $row['email'];
                          $his = $reg_user->runQuery("SELECT * FROM transfer WHERE email = '$email' order by date desc");
                          $his->execute(array(":acc_no" => $_SESSION['acc_no']));
                          while ($rows = $his->fetch(PDO::FETCH_ASSOC)) { ?>
                            <table id="table" class="display table table-striped table-condensed table-bordered bg-white" data-show-header="true" data-pagination="true" data-id-field="name" data-page-list="[5, 10, 25, 50, 100, ALL]" data-page-size="5">
                              <thead>
                                <tr>

                                  <th style="width:3%">AMOUNT</th>
                                  <th style="width:3%">RECIEVING ACCOUNT AND NAME</th>

                                  <th style="width:3%">BANK</th>
                                  <th style="width:3%">DESCRIPTION</th>
                                  <th style="width:3%">DATE/TIME</th>
                                  <th style="width:3%">STATUS</th>
                                </tr>
                              </thead>
                              <tbody>
                                <tr>
                                  <td>
                                    <?php echo $row['currency']; ?><?php echo number_format($rows['amount'], 2); ?>
                                  </td>

                                  <td>
                                    <?php echo $rows['acc_no']; ?>
                                    <br><?php echo $rows['acc_name']; ?>
                                  </td>


                                  <td>
                                    <?php echo $rows['bank_name']; ?>
                                  </td>

                                  <td>
                                    <?php echo $rows['remarks']; ?>
                                  </td>

                                  <td>
                                    <?php echo $rows['date']; ?>
                                  </td>

                                  <td>
                                    <?php echo $rows['status']; ?>
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          <?php } ?>

                          <br>
                          <div id="content" class="content">
                            <h6 class="element-header">
                              TRANSACTION STATEMENT
                            </h6>
                            <p>Here is your Credit and Debit Transaction Statement</p>

                            <div class="table-responsive">
                              <?php
                              $acc_no = $_SESSION['acc_no'];
                              $debcre = $reg_user->runQuery("SELECT * FROM alerts WHERE uname = '$acc_no' order by date desc");
                              $debcre->execute();
                              while ($rows = $debcre->fetch(PDO::FETCH_ASSOC)) { ?>
                                <table id="datatables-default" class="table table-striped table-condensed table-bordered bg-white">
                                  <thead>
                                    <tr>

                                      <th style="width:3%">TYPE</th>
                                      <th style="width:3%">AMOUNT <b> )</b></th>
                                      <th style="width:3%">TO/FROM</th>
                                      <th style="width:3%">DESCRIPTION</th>
                                      <th style="width:3%">DATE/TIME</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    <tr>
                                      <td>
                                        <?php echo $rows['type']; ?>
                                      </td>
                                      <td>
                                        <?php echo $row['currency']; ?> <?php echo number_format($rows['amount'], 2); ?>

                                      </td>
                                      <td>
                                        <?php echo $rows['sender_name']; ?>
                                      </td>
                                      <td>
                                        <?php echo $rows['remarks']; ?>
                                      </td>
                                      <td>
                                        <?php echo $rows['date']; ?> <?php echo $rows['time']; ?>
                                      </td>
                                    </tr>
                                  </tbody>
                                </table>
                              <?php } ?>
                            </div>
                          </div>
                        </div>
                        <br>
                        <div class="element-content">
                          <div class="row">
                            <div class="col-sm-4 col-xxxl-3">
                              <a class="element-box el-tablo" href="statement.php#">
                                <div class="label">
                                  Book Balance
                                </div>
                                <div class="value"><span style="color:orange;font-size:20px;">
                                    <?php echo $row['currency']; ?> <?php echo $row['t_bal']; ?>
                                  </span></div>
                              </a>
                            </div>
                            <div class="col-sm-4 col-xxxl-3">
                              <a class="element-box el-tablo" href="statement.php#">
                                <div class="label">
                                  Available Balance
                                </div>
                                <div class="value"><span style="color:green;font-size:20px;">
                                    <?php echo $row['currency']; ?> <?php echo $row['a_bal']; ?></span></div>
                              </a>
                            </div>
                            <div class="col-sm-4 col-xxxl-3">
                              <a class="element-box el-tablo" href="statement.php#">
                                <div class="label">
                                  Account Logged in from:
                                </div>
                                <div class="value"><span style="color:green;font-size:20px;">
                                    <?php
                                    echo '  ' . $_SERVER['REMOTE_ADDR'];
                                    ?>
                                  </span></div>
                              </a>
                            </div>
                            <div class="d-none d-xxxl-block col-xxxl-3">
                              <a class="element-box el-tablo" href="statement.php#">
                                <div class="label">
                                  Refunds Processed
                                </div>
                                <div class="value">
                                  $294
                                </div>
                                <div class="trending trending-up-basic">
                                  <span>12%</span><i class="os-icon os-icon-arrow-up2"></i>
                                </div>
                              </a>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="display-type"></div>
                  </div>

                  <footer class="page-footer font-small blue">
                    <br>
                    <br>
                    <br>
                    <br><br><br><br><br>

                    <div class="footer-copyright text-center py-3">Copyright © <script type="text/javascript">
                        var d = new Date()
                        document.write(d.getFullYear())
                      </script>. All Rights Reserved.
                    </div>
                  </footer>

                  <script>
                    var data = [{
                        name: "bootstrap-table",
                        stargazers_count: "526",
                        forks_count: "122",
                        description: "An extended Bootstrap table with radio, checkbox, sort, pagination, and other added features. (supports twitter bootstrap v2 and v3) "
                      },
                      {
                        name: "multiple-select",
                        stargazers_count: "288",
                        forks_count: "150",
                        description: "A jQuery plugin to select multiple elements with checkboxes :)"
                      },
                      {
                        name: "bootstrap-show-password",
                        stargazers_count: "32",
                        forks_count: "11",
                        description: "Show/hide password plugin for twitter bootstrap."
                      },
                      {
                        name: "blog",
                        stargazers_count: "13",
                        forks_count: "4",
                        description: "my blog"
                      },
                      {
                        name: "scutech-redmine",
                        stargazers_count: "6",
                        forks_count: "3",
                        description: "Redmine notification tools for chrome extension."
                      },
                      {
                        name: "scutech-redmine1",
                        stargazers_count: "6",
                        forks_count: "3",
                        description: "Redmine notification tools for chrome extension."
                      }
                    ];

                    function nameFormatter(value) {
                      return '<a href="statement.php/' + value + '">' + value + '</a>';
                    }

                    function starsFormatter(value) {
                      return '<i class="glyphicon glyphicon-star"></i> ' + value;
                    }

                    function forksFormatter(value) {
                      return '<i class="glyphicon glyphicon-cutlery"></i> ' + value;
                    }

                    $(function() {
                      $('#table').bootstrapTable({
                        data: data
                      });
                    });
                  </script>
                  <script>
                    function printtag(tagid) {
                      var hashid = "#" + tagid;
                      var tagname = $(hashid).prop("tagName").toLowerCase();
                      var attributes = "";
                      var attrs = document.getElementById(tagid).attributes;
                      $.each(attrs, function(i, elem) {
                        attributes += " " + elem.name + " ='" + elem.value + "' ";
                      })
                      var divToPrint = $(hashid).html();
                      var head = "<html><head>" + $("head").html() + "</head>";
                      var allcontent = head + "<body  onload='window.print()' >" + "<" + tagname + attributes + ">" + divToPrint + "</" + tagname + ">" + "</body></html>";
                      var newWin = window.open('', 'Print-Window');
                      newWin.document.open();
                      newWin.document.write(allcontent);
                      newWin.document.close();
                      // setTimeout(function(){newWin.close();},10);
                    }
                  </script>
                  <script src="dash/statement_files/jszip.min.js.download"></script>
                  <script src="dash/statement_files/pdfmake.min.js.download"></script>
                  <script src="dash/statement_files/vfs_fonts.js.download"></script>
                  <script src="dash/statement_files/jquery.dataTables.min.js.download"></script>
                  <script src="dash/statement_files/dataTables.bootstrap.min.js.download"></script>
                  <script src="dash/statement_files/dataTables.autoFill.min.js.download"></script>
                  <script src="dash/statement_files/autoFill.bootstrap.min.js.download"></script>
                  <script src="dash/statement_files/dataTables.buttons.min.js.download"></script>
                  <script src="dash/statement_files/buttons.bootstrap.min.js.download"></script>
                  <script src="dash/statement_files/buttons.colVis.min.js.download"></script>
                  <script src="dash/statement_files/buttons.flash.min.js.download"></script>
                  <script src="dash/statement_files/buttons.html5.min.js.download"></script>
                  <script src="dash/statement_files/buttons.print.min.js.download"></script>
                  <script src="dash/statement_files/dataTables.colReorder.min.js.download"></script>
                  <script src="dash/statement_files/dataTables.fixedColumns.min.js.download"></script>
                  <script src="dash/statement_files/dataTables.fixedHeader.min.js.download"></script>
                  <script src="dash/statement_files/dataTables.keyTable.min.js.download"></script>
                  <script src="dash/statement_files/dataTables.responsive.min.js.download"></script>
                  <script src="dash/statement_files/responsive.bootstrap.min.js.download"></script>
                  <script src="dash/statement_files/dataTables.rowGroup.min.js.download"></script>
                  <script src="dash/statement_files/dataTables.rowReorder.min.js.download"></script>
                  <script src="dash/statement_files/dataTables.scroller.min.js.download"></script>
                  <script src="dash/statement_files/dataTables.select.min.js.download"></script>
                  <script src="dash/statement_files/table-data.demo.min.js.download"></script>
                  <script src="dash/statement_files/apps.min.js.download"></script>
                  <script src="dash/statement_files/jquery.min.js(1).download"></script>
                  <script src="dash/statement_files/popper.min.js.download"></script>
                  <script src="dash/statement_files/moment.js.download"></script>
                  <script src="dash/statement_files/Chart.min.js.download"></script>
                  <script src="dash/statement_files/select2.full.min.js.download"></script>
                  <script src="dash/statement_files/jquery.barrating.min.js.download"></script>
                  <script src="dash/statement_files/ckeditor.js.download"></script>
                  <script src="dash/statement_files/validator.min.js.download"></script>
                  <script src="dash/statement_files/daterangepicker.js.download"></script>
                  <script src="dash/statement_files/ion.rangeSlider.min.js.download"></script>
                  <script src="dash/statement_files/dropzone.js.download"></script>
                  <script src="dash/statement_files/mindmup-editabletable.js.download"></script>
                  <script src="dash/statement_files/jquery.dataTables.min.js(1).download"></script>
                  <script src="dash/statement_files/dataTables.bootstrap.min.js(1).download"></script>
                  <script src="dash/statement_files/fullcalendar.min.js.download"></script>
                  <script src="dash/statement_files/perfect-scrollbar.jquery.min.js.download"></script>
                  <script src="dash/statement_files/tether.min.js.download"></script>
                  <script src="dash/statement_files/slick.min.js.download"></script>
                  <script src="dash/statement_files/util.js.download"></script>
                  <script src="dash/statement_files/alert.js.download"></script>
                  <script src="dash/statement_files/button.js.download"></script>
                  <script src="dash/statement_files/carousel.js.download"></script>
                  <script src="dash/statement_files/collapse.js.download"></script>
                  <script src="dash/statement_files/dropdown.js.download"></script>
                  <script src="dash/statement_files/modal.js.download"></script>
                  <script src="dash/statement_files/tab.js.download"></script>
                  <script src="dash/statement_files/tooltip.js.download"></script>
                  <script src="dash/statement_files/popover.js.download"></script>
                  <script src="dash/statement_files/demo_customizer.js.download"></script>
                  <script src="dash/statement_files/main.js.download"></script>
                  <script>
                    (function(i, s, o, g, r, a, m) {
                      i['GoogleAnalyticsObject'] = r;
                      i[r] = i[r] || function() {
                        (i[r].q = i[r].q || []).push(arguments)
                      }, i[r].l = 1 * new Date();
                      a = s.createElement(o),
                        m = s.getElementsByTagName(o)[0];
                      a.async = 1;
                      a.src = g;
                      m.parentNode.insertBefore(a, m)
                    })(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');

                    ga('create', 'UA-XXXXXXX-9', 'auto');
                    ga('send', 'pageview');
                  </script>
                  <script>
                    $(document).ready(function() {
                      App.init();
                      TableData.init();
                    });
                  </script>


                  </h6>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php echo $rowp['tawk']; ?>
  <script src="js/new-ui-shell.js"></script>
</body>

</html>