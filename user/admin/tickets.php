<?php
session_start();
require_once 'class.admin.php';
include_once 'session.php';

if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit();
}

$user_home = new USER();

try {
    $user_home->runQuery("CREATE TABLE IF NOT EXISTS ticket_replies (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ticket_id INT NOT NULL,
        sender_role VARCHAR(20) NOT NULL DEFAULT 'admin',
        sender_name VARCHAR(150) DEFAULT NULL,
        msg TEXT NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_ticket (ticket_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4")->execute();
} catch (Throwable $e) {
}

$alert = '';
$alertType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim((string)($_POST['action'] ?? ''));
    $ticketId = (int)($_POST['ticket_id'] ?? 0);

    if ($ticketId > 0 && ($action === 'mark_open' || $action === 'mark_closed')) {
      $nextStatus = $action === 'mark_open' ? 'Pending' : 'Replied';
        $upd = $user_home->runQuery('UPDATE ticket SET status = :status WHERE id = :id LIMIT 1');
        $upd->execute([':status' => $nextStatus, ':id' => $ticketId]);
        $alert = 'Ticket status updated successfully.';
        $alertType = 'success';
    }

    if ($ticketId > 0 && $action === 'delete_ticket') {
        $delReplies = $user_home->runQuery('DELETE FROM ticket_replies WHERE ticket_id = :ticket_id');
        $delReplies->execute([':ticket_id' => $ticketId]);

        $del = $user_home->runQuery('DELETE FROM ticket WHERE id = :id LIMIT 1');
        $del->execute([':id' => $ticketId]);

        $alert = 'Ticket and conversation history deleted.';
        $alertType = 'success';
    }

    if ($ticketId > 0 && $action === 'send_reply') {
        $replyBody = trim((string)($_POST['reply_body'] ?? ''));
        $closeOnReply = (int)($_POST['close_on_reply'] ?? 1) === 1;
        if ($replyBody === '') {
            $alert = 'Reply message is required.';
            $alertType = 'error';
        } else {
            $senderName = trim((string)($_SESSION['email'] ?? 'Support'));
            $insReply = $user_home->runQuery('INSERT INTO ticket_replies (ticket_id, sender_role, sender_name, msg) VALUES (:ticket_id, :sender_role, :sender_name, :msg)');
            $insReply->execute([
                ':ticket_id' => $ticketId,
                ':sender_role' => 'admin',
                ':sender_name' => $senderName,
                ':msg' => $replyBody,
            ]);

            $nextStatus = $closeOnReply ? 'Replied' : 'Pending';
            $upd = $user_home->runQuery('UPDATE ticket SET status = :status WHERE id = :id LIMIT 1');
            $upd->execute([':status' => $nextStatus, ':id' => $ticketId]);

            $alert = $closeOnReply
                ? 'Reply posted to ticket center and ticket marked as replied.'
                : 'Reply posted to ticket center.';
            $alertType = 'success';
        }
    }
}

$statusFilter = strtolower(trim((string)($_GET['status'] ?? 'pending')));
if (!in_array($statusFilter, ['pending', 'replied', 'all'], true)) {
  $statusFilter = 'pending';
}

$search = trim((string)($_GET['q'] ?? ''));
$where = [];
$params = [];

if ($statusFilter === 'pending') {
  $where[] = "LOWER(COALESCE(status, 'pending')) = 'pending'";
} elseif ($statusFilter === 'replied') {
  $where[] = "LOWER(COALESCE(status, 'pending')) = 'replied'";
}

if ($search !== '') {
    $where[] = '(tc LIKE :q OR sender_name LIKE :q OR mail LIKE :q OR subject LIKE :q OR msg LIKE :q)';
    $params[':q'] = '%' . $search . '%';
}

$sql = 'SELECT id, tc, sender_name, mail, subject, msg, status, date FROM ticket';
if (!empty($where)) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY id DESC LIMIT 300';

$listStmt = $user_home->runQuery($sql);
$listStmt->execute($params);
$tickets = $listStmt->fetchAll(PDO::FETCH_ASSOC);

$openCountStmt = $user_home->runQuery("SELECT COUNT(*) AS c FROM ticket WHERE LOWER(COALESCE(status, 'pending')) = 'pending'");
$openCountStmt->execute();
$openCount = (int)($openCountStmt->fetch(PDO::FETCH_ASSOC)['c'] ?? 0);

$closedCountStmt = $user_home->runQuery("SELECT COUNT(*) AS c FROM ticket WHERE LOWER(COALESCE(status, 'pending')) = 'replied'");
$closedCountStmt->execute();
$closedCount = (int)($closedCountStmt->fetch(PDO::FETCH_ASSOC)['c'] ?? 0);

$selectedTicketId = (int)($_GET['ticket'] ?? 0);
$selectedTicket = null;
if ($selectedTicketId > 0) {
    foreach ($tickets as $ticketRow) {
        if ((int)($ticketRow['id'] ?? 0) === $selectedTicketId) {
            $selectedTicket = $ticketRow;
            break;
        }
    }
}
if (!$selectedTicket && !empty($tickets)) {
    $selectedTicket = $tickets[0];
    $selectedTicketId = (int)($selectedTicket['id'] ?? 0);
}

$threadReplies = [];
if ($selectedTicketId > 0) {
    $replyStmt = $user_home->runQuery('SELECT id, sender_role, sender_name, msg, created_at FROM ticket_replies WHERE ticket_id = :ticket_id ORDER BY id ASC');
    $replyStmt->execute([':ticket_id' => $selectedTicketId]);
    $threadReplies = $replyStmt->fetchAll(PDO::FETCH_ASSOC);
}

$pageTitle = 'Support Tickets';
require_once __DIR__ . '/partials/admin-shell-open.php';
?>

<?php if ($alert !== ''): ?>
<div class="mb-4 px-4 py-3 rounded-lg text-sm <?= $alertType === 'success' ? 'bg-green-50 border border-green-200 text-green-700' : 'bg-red-50 border border-red-200 text-red-700' ?>">
  <?= htmlspecialchars($alert) ?>
</div>
<?php endif; ?>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
  <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 mb-5">
    <div>
      <h2 class="font-semibold text-gray-800">Customer Ticket Center</h2>
      <p class="text-xs text-gray-500 mt-1">Review ticket conversations, post professional responses, and manage pending or replied status.</p>
    </div>
    <form method="get" class="flex flex-wrap items-center gap-2">
      <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>">
      <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search ticket ID, sender, email or subject"
             class="rounded-lg border border-gray-300 px-3 py-2 text-sm w-64 focus:outline-none focus:ring-2 focus:ring-blue-500">
      <button type="submit" class="inline-flex items-center gap-2 bg-slate-700 hover:bg-slate-800 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">Search</button>
    </form>
  </div>

  <div class="flex flex-wrap items-center gap-2 mb-5 text-xs font-semibold">
    <a href="tickets.php?status=pending" class="inline-flex items-center rounded-full px-3 py-1 <?= $statusFilter === 'pending' ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-700' ?>">Pending: <?= $openCount ?></a>
    <a href="tickets.php?status=replied" class="inline-flex items-center rounded-full px-3 py-1 <?= $statusFilter === 'replied' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700' ?>">Replied: <?= $closedCount ?></a>
    <a href="tickets.php?status=all" class="inline-flex items-center rounded-full px-3 py-1 <?= $statusFilter === 'all' ? 'bg-slate-700 text-white' : 'bg-slate-100 text-slate-700' ?>">All</a>
  </div>

  <?php if (empty($tickets)): ?>
  <div class="rounded-lg border border-dashed border-gray-300 p-8 text-center text-sm text-gray-500">
    No tickets match the current filter.
  </div>
  <?php else: ?>
  <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
    <div class="xl:col-span-5 border border-gray-200 rounded-xl overflow-hidden">
      <div class="max-h-[640px] overflow-y-auto divide-y divide-gray-100">
        <?php foreach ($tickets as $t): ?>
        <?php
          $tid = (int)($t['id'] ?? 0);
          $active = $selectedTicketId === $tid;
          $isOpen = strtolower((string)($t['status'] ?? 'pending')) === 'pending';
          $snippet = trim((string)($t['msg'] ?? ''));
          if (strlen($snippet) > 96) {
            $snippet = substr($snippet, 0, 96) . '...';
          }
        ?>
        <a href="tickets.php?status=<?= urlencode($statusFilter) ?>&q=<?= urlencode($search) ?>&ticket=<?= $tid ?>"
           class="block px-4 py-3 <?= $active ? 'bg-blue-50 border-l-4 border-blue-600' : 'hover:bg-gray-50 border-l-4 border-transparent' ?>">
          <div class="flex items-start justify-between gap-2">
            <p class="text-sm font-semibold text-gray-800">#<?= htmlspecialchars((string)($t['tc'] ?: $tid)) ?> - <?= htmlspecialchars((string)($t['subject'] ?? 'No subject')) ?></p>
            <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium <?= $isOpen ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' ?>">
              <?= $isOpen ? 'Pending' : 'Replied' ?>
            </span>
          </div>
          <p class="text-xs text-gray-500 mt-1"><?= htmlspecialchars((string)($t['sender_name'] ?? 'Unknown sender')) ?><?= !empty($t['mail']) ? ' • ' . htmlspecialchars((string)$t['mail']) : '' ?></p>
          <p class="text-xs text-gray-500 mt-1"><?= htmlspecialchars($snippet) ?></p>
          <p class="text-[11px] text-gray-400 mt-1"><?= htmlspecialchars((string)($t['date'] ?? '')) ?></p>
        </a>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="xl:col-span-7 border border-gray-200 rounded-xl p-5 bg-gray-50/40">
      <?php if (!$selectedTicket): ?>
      <p class="text-sm text-gray-500">Select a ticket to view the conversation.</p>
      <?php else: ?>
      <?php $selectedIsOpen = strtolower((string)($selectedTicket['status'] ?? 'pending')) === 'pending'; ?>

      <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
        <div>
          <h3 class="text-base font-semibold text-gray-800">Ticket #<?= htmlspecialchars((string)($selectedTicket['tc'] ?: $selectedTicket['id'])) ?></h3>
          <p class="text-xs text-gray-500">Opened on <?= htmlspecialchars((string)($selectedTicket['date'] ?? '')) ?></p>
        </div>
        <div class="flex items-center gap-2">
          <form method="post">
            <input type="hidden" name="ticket_id" value="<?= (int)$selectedTicket['id'] ?>">
            <input type="hidden" name="action" value="<?= $selectedIsOpen ? 'mark_closed' : 'mark_open' ?>">
            <button type="submit" class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium rounded-lg <?= $selectedIsOpen ? 'bg-emerald-600 hover:bg-emerald-700 text-white' : 'bg-amber-500 hover:bg-amber-600 text-white' ?>">
              <?= $selectedIsOpen ? 'Mark Replied' : 'Reopen as Pending' ?>
            </button>
          </form>
          <form method="post" onsubmit="return confirm('Delete this ticket and all thread replies?')">
            <input type="hidden" name="ticket_id" value="<?= (int)$selectedTicket['id'] ?>">
            <input type="hidden" name="action" value="delete_ticket">
            <button type="submit" class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium rounded-lg bg-red-500 hover:bg-red-600 text-white">Delete</button>
          </form>
        </div>
      </div>

      <div class="bg-white rounded-lg border border-gray-200 p-4 mb-4">
        <p class="text-xs uppercase tracking-wide text-gray-500 mb-1">Customer</p>
        <p class="text-sm font-medium text-gray-800"><?= htmlspecialchars((string)($selectedTicket['sender_name'] ?? 'Unknown sender')) ?></p>
        <p class="text-xs text-gray-500"><?= htmlspecialchars((string)($selectedTicket['mail'] ?? 'No email supplied')) ?></p>
      </div>

      <div class="bg-white rounded-lg border border-gray-200 p-4 mb-5">
        <p class="text-xs uppercase tracking-wide text-gray-500 mb-1">Conversation</p>
        <div class="space-y-3 max-h-[320px] overflow-y-auto pr-1">
          <div class="rounded-lg border border-blue-100 bg-blue-50 p-3">
            <p class="text-[11px] font-semibold text-blue-700 mb-1">Customer • Initial Ticket</p>
            <p class="text-sm font-medium text-gray-800 mb-1"><?= htmlspecialchars((string)($selectedTicket['subject'] ?? 'No subject')) ?></p>
            <div class="text-sm text-gray-700 whitespace-pre-wrap leading-6"><?= htmlspecialchars((string)($selectedTicket['msg'] ?? '')) ?></div>
          </div>

          <?php foreach ($threadReplies as $rep): ?>
          <?php $isAdmin = strtolower((string)($rep['sender_role'] ?? 'admin')) === 'admin'; ?>
          <div class="rounded-lg border p-3 <?= $isAdmin ? 'border-emerald-100 bg-emerald-50' : 'border-gray-200 bg-white' ?>">
            <p class="text-[11px] font-semibold mb-1 <?= $isAdmin ? 'text-emerald-700' : 'text-gray-600' ?>">
              <?= $isAdmin ? 'Support Team' : 'Customer' ?><?= !empty($rep['sender_name']) ? ' • ' . htmlspecialchars((string)$rep['sender_name']) : '' ?>
              <?php if (!empty($rep['created_at'])): ?>
                <span class="font-normal text-gray-500"> • <?= htmlspecialchars((string)$rep['created_at']) ?></span>
              <?php endif; ?>
            </p>
            <div class="text-sm text-gray-700 whitespace-pre-wrap leading-6"><?= htmlspecialchars((string)($rep['msg'] ?? '')) ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="bg-white rounded-lg border border-gray-200 p-4">
        <h4 class="text-sm font-semibold text-gray-800 mb-3">Post Support Reply</h4>
        <form method="post" class="space-y-3">
          <input type="hidden" name="action" value="send_reply">
          <input type="hidden" name="ticket_id" value="<?= (int)$selectedTicket['id'] ?>">

          <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Reply Message</label>
            <textarea name="reply_body" rows="6" required
                      placeholder="Provide a clear, professional response with next steps..."
                      class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
          </div>

          <label class="inline-flex items-center gap-2 text-xs text-gray-600">
            <input type="checkbox" name="close_on_reply" value="1" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            Mark ticket as closed after reply
          </label>

          <button type="submit" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            Publish Reply
          </button>
        </form>
      </div>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/partials/admin-shell-close.php'; ?>
