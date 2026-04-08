<?php
session_start();
include_once ('session.php');
if(!isset($_SESSION['email'])){
    header("Location: login.php");
    exit();
}
require_once 'class.admin.php';

$reg_user = new USER();

// Get all transfers for dropdown filter
$transfersStmt = $reg_user->runQuery("SELECT id, amount, email, status, date FROM transfer ORDER BY date DESC LIMIT 500");
$transfersStmt->execute();
$allTransfers = $transfersStmt->fetchAll(PDO::FETCH_ASSOC);

// Check if filtering by transfer ID
$selectedTransferId = isset($_GET['transfer_id']) ? (int)$_GET['transfer_id'] : (isset($allTransfers[0]) ? $allTransfers[0]['id'] : 0);

$history = [];
if ($selectedTransferId > 0) {
    $history = $reg_user->getTransferStatusHistory($selectedTransferId);
}

// Get current transfer info
$currentTransfer = null;
if ($selectedTransferId > 0) {
    $tfStmt = $reg_user->runQuery("SELECT * FROM transfer WHERE id = :id");
    $tfStmt->execute([':id' => $selectedTransferId]);
    $currentTransfer = $tfStmt->fetch(PDO::FETCH_ASSOC);
}

$pageTitle = 'Transfer Status History';
require_once __DIR__ . '/partials/admin-shell-open.php';
?>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Transfer Status History & Audit Trail</h2>
        <p class="text-gray-600">View complete status change history for all transfers</p>
    </div>

    <!-- Transfer Selection -->
    <div class="bg-gray-50 rounded-lg p-4 mb-6 border border-gray-200">
        <form method="GET" class="flex flex-col md:flex-row gap-3 items-end">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Transfer</label>
                <select name="transfer_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" onchange="this.form.submit();">
                    <option value="">-- Choose Transfer --</option>
                    <?php foreach ($allTransfers as $tf): ?>
                        <option value="<?= (int)$tf['id'] ?>" <?php if ($selectedTransferId == $tf['id']) echo 'selected'; ?>>
                            #<?= (int)$tf['id'] ?> - <?= htmlspecialchars($tf['email']) ?> - 
                            <?= number_format((float)$tf['amount'], 2) ?> 
                            (<?= htmlspecialchars($tf['status']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <a href="transfer_rec.php" class="inline-flex items-center gap-2 bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors whitespace-nowrap">
                <i class="fa fa-list"></i> All Transfers
            </a>
        </form>
    </div>

    <?php if (!$currentTransfer): ?>
        <div class="text-center py-12 text-gray-500">
            <p class="text-lg">Select a transfer to view its status history.</p>
        </div>
    <?php else: ?>
        <!-- Current Transfer Summary -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <p class="text-xs text-blue-600 uppercase tracking-wide font-semibold">Transfer ID</p>
                <p class="text-lg font-bold text-blue-900">#<?= (int)$currentTransfer['id'] ?></p>
            </div>
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <p class="text-xs text-green-600 uppercase tracking-wide font-semibold">Amount</p>
                <p class="text-lg font-bold text-green-900"><?= number_format((float)($currentTransfer['amount'] ?? 0), 2) ?></p>
            </div>
            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                <p class="text-xs text-purple-600 uppercase tracking-wide font-semibold">Current Status</p>
                <p class="inline-flex px-2 py-1 rounded-full text-xs font-bold 
                    <?php 
                        $status = strtolower($currentTransfer['status'] ?? '');
                        if ($status === 'successful') echo 'bg-green-100 text-green-700';
                        elseif ($status === 'failed') echo 'bg-red-100 text-red-700';
                        elseif ($status === 'processing') echo 'bg-amber-100 text-amber-700';
                        else echo 'bg-blue-100 text-blue-700';
                    ?>">
                    <?= htmlspecialchars($currentTransfer['status'] ?? 'Pending') ?>
                </p>
            </div>
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <p class="text-xs text-gray-600 uppercase tracking-wide font-semibold">Email</p>
                <p class="text-sm font-medium text-gray-900 truncate"><?= htmlspecialchars($currentTransfer['email'] ?? '') ?></p>
            </div>
        </div>

        <!-- Status History Timeline -->
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h3 class="font-semibold text-gray-900">Status Change Timeline</h3>
                <p class="text-xs text-gray-600 mt-1"><?= count($history) ?> status change(s) recorded</p>
            </div>

            <?php if (empty($history)): ?>
                <div class="text-center py-12 text-gray-500">
                    <i class="fa fa-history text-3xl mb-3 text-gray-300"></i>
                    <p>No status changes recorded yet. Transfer may just have been created.</p>
                </div>
            <?php else: ?>
                <div class="divide-y divide-gray-200">
                    <?php foreach ($history as $idx => $entry): ?>
                        <div class="p-6 hover:bg-gray-50 transition-colors">
                            <div class="flex gap-4">
                                <!-- Timeline indicator -->
                                <div class="flex flex-col items-center gap-2">
                                    <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold">
                                        <?= $idx + 1 ?>
                                    </div>
                                    <?php if ($idx < count($history) - 1): ?>
                                        <div class="w-0.5 h-12 bg-gray-300"></div>
                                    <?php endif; ?>
                                </div>

                                <!-- Content -->
                                <div class="flex-1 pb-4">
                                    <!-- Status transition -->
                                    <div class="mb-3">
                                        <span class="inline-flex items-center gap-2 font-medium text-gray-900">
                                            <?php if ($entry['old_status']): ?>
                                                <span class="px-2 py-1 rounded text-xs font-medium bg-amber-100 text-amber-800">
                                                    <?= htmlspecialchars($entry['old_status']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                    Initial
                                                </span>
                                            <?php endif; ?>
                                            <i class="fa fa-arrow-right text-gray-400"></i>
                                            <span class="px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                <?= htmlspecialchars($entry['new_status']) ?>
                                            </span>
                                        </span>
                                    </div>

                                    <!-- Metadata -->
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                        <div>
                                            <p class="text-xs text-gray-500 uppercase tracking-wide">Changed By</p>
                                            <p class="font-medium text-gray-900">
                                                <?php 
                                                    $changedBy = $entry['changed_by'] ?? 'System';
                                                    $isSystem = strtolower($changedBy) === 'system';
                                                    echo htmlspecialchars($changedBy);
                                                ?>
                                                <?php if ($isSystem): ?>
                                                    <span class="ml-2 inline-flex px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-700">
                                                        Automated
                                                    </span>
                                                <?php else: ?>
                                                    <span class="ml-2 inline-flex px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">
                                                        Admin
                                                    </span>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 uppercase tracking-wide">Date & Time</p>
                                            <p class="font-medium text-gray-900">
                                                <?= htmlspecialchars(date('M d, Y', strtotime($entry['changed_at']))) ?><br>
                                                <span class="text-xs text-gray-500"><?= htmlspecialchars(date('H:i:s', strtotime($entry['changed_at']))) ?></span>
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 uppercase tracking-wide">Duration</p>
                                            <p class="font-medium text-gray-900">
                                                <?php 
                                                    if ($idx < count($history) - 1) {
                                                        $current = strtotime($entry['changed_at']);
                                                        $next = strtotime($history[$idx + 1]['changed_at']);
                                                        $diff = $next - $current;
                                                        $hours = floor($diff / 3600);
                                                        $mins = floor(($diff % 3600) / 60);
                                                        echo htmlspecialchars($hours . 'h ' . $mins . 'm');
                                                    } else {
                                                        echo 'Current';
                                                    }
                                                ?>
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Notes -->
                                    <?php if ($entry['notes']): ?>
                                        <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                            <p class="text-xs text-blue-600 uppercase tracking-wide font-semibold mb-1">Admin Notes</p>
                                            <p class="text-sm text-blue-900"><?= htmlspecialchars($entry['notes']) ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Edit Transfer Button -->
        <div class="mt-6">
            <a href="edit_tf.php?id=<?= (int)$currentTransfer['id'] ?>" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2 rounded-lg transition-colors">
                <i class="fa fa-edit"></i> Edit Transfer
            </a>
        </div>
    <?php endif; ?>
</div>
