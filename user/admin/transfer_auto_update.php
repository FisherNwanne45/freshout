<?php
/**
 * Transfer Auto-Update Cron Job
 * 
 * This script should be called periodically via cron job or system scheduler
 * Example cron (every 15 minutes): *\/15 * * * * php /path/to/transfer_auto_update.php
 * (Runs every 15 minutes)
 */

// Prevent direct access via browser - CLI only
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die('This script can only be run from the command line.');
}

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__FILE__) . '/dbconfig.php';
require_once dirname(__FILE__) . '/class.admin.php';

// Initialize admin class
$admin = new USER();

// Get transfer settings
$autoUpdateEnabled = (bool)($admin->getTransferSetting('auto_update_enabled', '1') == '1');
$targetStatus = $admin->getTransferSetting('auto_update_target_status', 'successful');

if (!$autoUpdateEnabled) {
    echo "[" . date('Y-m-d H:i:s') . "] Auto-update is disabled. Exiting.\n";
    exit(0);
}

// Log file path
$logDir = dirname(__FILE__) . '/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}
$logFile = $logDir . '/transfer_auto_update_' . date('Y-m-d') . '.log';

function log_message($message) {
    global $logFile;
    $timestamp = '[' . date('Y-m-d H:i:s') . ']';
    $logEntry = $timestamp . ' ' . $message . "\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
    echo $logEntry;
}

try {
    log_message('=== Transfer Auto-Update Process Started ===');
    
    // Get transfers pending auto-update
    $pendingTransfers = $admin->getTransfersPendingAutoUpdate();
    
    if (empty($pendingTransfers)) {
        log_message('No transfers pending auto-update.');
        exit(0);
    }
    
    log_message('Found ' . count($pendingTransfers) . ' transfer(s) pending auto-update.');
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($pendingTransfers as $transfer) {
        $transferId = (int)$transfer['id'];
        $currentStatus = $transfer['status'];
        
        try {
            if ($admin->autoUpdateTransferStatus($transferId, $targetStatus)) {
                $successCount++;
                log_message("✓ Transfer #$transferId: Updated from '$currentStatus' to '$targetStatus'");
            } else {
                $errorCount++;
                log_message("✗ Transfer #$transferId: Failed to update status");
            }
        } catch (Exception $e) {
            $errorCount++;
            log_message("✗ Transfer #$transferId: Exception - " . $e->getMessage());
        }
    }
    
    log_message('=== Transfer Auto-Update Process Completed ===');
    log_message("Success: $successCount | Errors: $errorCount");
    log_message('');
    
    exit(0);
    
} catch (Exception $e) {
    log_message('FATAL ERROR: ' . $e->getMessage());
    exit(1);
}
