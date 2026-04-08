<?php
session_start();
include_once ('session.php');
if(!isset($_SESSION['email'])){
    header("Location: login.php");
    exit(); 
}
require_once 'class.admin.php';

$reg_user = new USER();
$msg = '';
$successMsg = '';
$errorMsg = '';
 
if(isset($_GET['id'])){
    $id = (int)$_GET['id'];
    $stmt = $reg_user->runQuery("SELECT * FROM transfer WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$row) {
        $errorMsg = 'Transfer record not found.';
    }
}

// Handle basic transfer info update
if(isset($_POST['updatetf']) && isset($row)){
    $id = (int)$_GET['id'];
    $amount = trim($_POST['amount'] ?? '');
    $date = trim($_POST['date'] ?? '');
    $acc_no = trim($_POST['acc_no'] ?? '');
    $remarks = trim($_POST['remarks'] ?? '');
    $bank_name = trim($_POST['bank_name'] ?? '');
    $acc_name = trim($_POST['acc_name'] ?? '');
    
    $editaccount = $reg_user->runQuery("UPDATE transfer SET 
        amount = :amount, 
        date = :date, 
        remarks = :remarks, 
        bank_name = :bank_name, 
        acc_name = :acc_name 
        WHERE id = :id");
    
    if($editaccount->execute([
        ':amount' => $amount,
        ':date' => $date,
        ':remarks' => $remarks,
        ':bank_name' => $bank_name,
        ':acc_name' => $acc_name,
        ':id' => $id
    ])) {
        $successMsg = 'Transfer basic information updated successfully.';
        $stmt = $reg_user->runQuery("SELECT * FROM transfer WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $errorMsg = 'Failed to update transfer information.';
    }
}

// Handle status update
if(isset($_POST['update_status']) && isset($row)){
    $id = (int)$_GET['id'];
    $newStatus = trim($_POST['status'] ?? '');
    $statusNotes = trim($_POST['status_notes'] ?? '');
    $autoUpdate = isset($_POST['auto_update']) ? 1 : 0;
    $autoUpdateDelay = (int)($_POST['auto_update_delay'] ?? 0);
    
    if ($newStatus) {
        if ($reg_user->updateTransferStatus($id, $newStatus, $_SESSION['email'], $statusNotes, $autoUpdate, $autoUpdateDelay)) {
            $successMsg = 'Transfer status updated successfully.';
            $stmt = $reg_user->runQuery("SELECT * FROM transfer WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $errorMsg = 'Failed to update transfer status.';
        }
    } else {
        $errorMsg = 'Please select a valid status.';
    }
}

// Get status history
$statusHistory = isset($row) ? $reg_user->getTransferStatusHistory($row['id']) : [];

$pageTitle = 'Edit Transfer';
require_once __DIR__ . '/partials/admin-shell-open.php';
?>


<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">Transfer Details</h2>
        <a href="transfer_rec.php" class="inline-flex items-center gap-2 bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
            <i class="fa fa-arrow-left"></i> Back to List
        </a>
    </div>

    <?php if ($errorMsg): ?>
        <div class="rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-red-700 text-sm mb-4">
            <?= htmlspecialchars($errorMsg) ?>
        </div>
    <?php endif; ?>

    <?php if ($successMsg): ?>
        <div class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-green-700 text-sm mb-4">
            <?= htmlspecialchars($successMsg) ?>
        </div>
    <?php endif; ?>

    <?php if (!isset($row) || $errorMsg): ?>
        <div class="text-center py-6 text-gray-500">
            No transfer record found.
        </div>
    <?php else: ?>
        <!-- Tabs Navigation -->
        <div class="border-b border-gray-200 mb-6">
            <nav class="flex gap-8" aria-label="Tabs">
                <button onclick="switchTab('basic', this)" class="tab-button active py-4 px-1 border-b-2 border-blue-600 font-medium text-blue-600 hover:text-blue-700">
                    Basic Information
                </button>
                <button onclick="switchTab('status', this)" class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-gray-600 hover:text-gray-700 hover:border-gray-300">
                    Status Management
                </button>
                <button onclick="switchTab('history', this)" class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-gray-600 hover:text-gray-700 hover:border-gray-300">
                    Status History
                </button>
            </nav>
        </div>

        <!-- Tab: Basic Information -->
        <div id="basic-tab" class="tab-content">
            <form method="POST" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
                        <input type="number" step="0.01" name="amount" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" 
                               value="<?= htmlspecialchars($row['amount'] ?? '') ?>" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Destination Account</label>
                        <input type="text" name="acc_no" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" 
                               value="<?= htmlspecialchars($row['acc_no'] ?? $row['reci_acc'] ?? '') ?>" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Account Name</label>
                        <input type="text" name="acc_name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" 
                               value="<?= htmlspecialchars($row['acc_name'] ?? '') ?>" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bank Name</label>
                        <input type="text" name="bank_name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" 
                               value="<?= htmlspecialchars($row['bank_name'] ?? '') ?>" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                        <input type="date" name="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" 
                               value="<?= htmlspecialchars(substr($row['date'] ?? '', 0, 10)) ?>" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description/Remarks</label>
                        <input type="text" name="remarks" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" 
                               value="<?= htmlspecialchars($row['remarks'] ?? $row['description'] ?? '') ?>">
                    </div>
                </div>

                <!-- Additional Info Display -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 p-4 rounded-lg">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">From Account</p>
                        <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($row['acc_no'] ?? '') ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Transfer Type</p>
                        <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($row['transfer_type'] ?? $row['type'] ?? 'unknown') ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Currency</p>
                        <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($row['currency_code'] ?? 'USD') ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Created At</p>
                        <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($row['date'] ?? '') ?></p>
                    </div>
                </div>

                <button type="submit" name="updatetf" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2 rounded-lg transition-colors">
                    Update Transfer Information
                </button>
            </form>
        </div>

        <!-- Tab: Status Management -->
        <div id="status-tab" class="tab-content hidden">
            <form method="POST" class="space-y-6">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <p class="text-sm text-blue-900">
                        <strong>Current Status:</strong> 
                        <span class="inline-flex ml-2 px-3 py-1 rounded-full text-sm font-medium 
                            <?php 
                                $status = strtolower($row['status'] ?? '');
                                if ($status === 'successful') echo 'bg-green-100 text-green-700';
                                elseif ($status === 'failed') echo 'bg-red-100 text-red-700';
                                elseif ($status === 'processing') echo 'bg-amber-100 text-amber-700';
                                else echo 'bg-blue-100 text-blue-700';
                            ?>">
                            <?= htmlspecialchars($row['status'] ?? 'Pending') ?>
                        </span>
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Update Status To</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="">-- Select Status --</option>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="completed">Completed</option>
                            <option value="successful">Successful</option>
                            <option value="failed">Failed</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="reversed">Reversed</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Auto-Update</label>
                        <div class="flex items-center gap-3">
                            <input type="checkbox" name="auto_update" id="auto_update" value="1" class="w-4 h-4">
                            <label for="auto_update" class="text-sm text-gray-600">Enable auto-update after delay</label>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Auto-Update Delay (Minutes)</label>
                    <input type="number" name="auto_update_delay" min="0" value="1440" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Default: 1440 minutes (24 hours)</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Admin Notes</label>
                    <textarea name="status_notes" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm" 
                              placeholder="Add notes explaining the status change..."></textarea>
                </div>

                <div class="flex gap-3">
                    <button type="submit" name="update_status" class="bg-green-600 hover:bg-green-700 text-white font-medium px-6 py-2 rounded-lg transition-colors">
                        Update Status
                    </button>
                    <button type="button" onclick="switchTab('history')" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-6 py-2 rounded-lg transition-colors">
                        View History
                    </button>
                </div>
            </form>
        </div>

        <!-- Tab: Status History -->
        <div id="history-tab" class="tab-content hidden">
            <?php if (empty($statusHistory)): ?>
                <div class="text-center py-8 text-gray-500">
                    <p>No status history available yet.</p>
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($statusHistory as $history): ?>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <span class="inline-flex items-center gap-2 text-sm font-medium text-gray-900">
                                        <i class="fa fa-arrow-right text-blue-600"></i>
                                        <?= htmlspecialchars($history['old_status'] ?? 'N/A') ?> 
                                        <span class="text-gray-400">→</span> 
                                        <?= htmlspecialchars($history['new_status'] ?? '') ?>
                                    </span>
                                </div>
                                <span class="text-xs text-gray-500">
                                    <?= htmlspecialchars(date('M d, Y H:i', strtotime($history['changed_at']))) ?>
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 mb-2">By: <strong><?= htmlspecialchars($history['changed_by'] ?? 'System') ?></strong></p>
                            <?php if ($history['notes']): ?>
                                <p class="text-sm bg-gray-50 p-2 rounded border-l-2 border-blue-400 text-gray-700">
                                    <?= htmlspecialchars($history['notes']) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    <?php endif; ?>
</div>

<style>
    .tab-button.active {
        border-bottom-color: #2563eb;
        color: #2563eb;
    }
    
    .tab-content.hidden {
        display: none;
    }
</style>

<script>
function switchTab(tabName, btn) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    
    // Remove active class from all buttons
    document.querySelectorAll('.tab-button').forEach(el => {
        el.classList.remove('active');
        el.style.borderBottomColor = 'transparent';
        el.style.color = '#4b5563';
    });
    
    // Show selected tab
    document.getElementById(tabName + '-tab').classList.remove('hidden');
    
    // Add active class to clicked button
    if (!btn) {
        btn = document.querySelector('.tab-button[onclick*="\'' + tabName + '\'"]');
    }
    if (btn) {
        btn.classList.add('active');
        btn.style.borderBottomColor = '#2563eb';
        btn.style.color = '#2563eb';
    }
}
</script>

<?php require_once __DIR__ . '/partials/admin-shell-close.php'; ?>
