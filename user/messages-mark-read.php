<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['acc_no']) || !isset($_SESSION['mname'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'unauthorized']);
    exit();
}

require_once __DIR__ . '/class.user.php';

try {
    $user = new USER();
    $accNo = (string)$_SESSION['acc_no'];

    try {
        $user->runQuery('ALTER TABLE message ADD COLUMN is_read TINYINT(1) NOT NULL DEFAULT 0')->execute();
    } catch (Throwable $e) {
    }

    $accountStmt = $user->runQuery('SELECT uname FROM account WHERE acc_no = :acc_no LIMIT 1');
    $accountStmt->execute([':acc_no' => $accNo]);
    $account = $accountStmt->fetch(PDO::FETCH_ASSOC);

    if (!$account) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'account_not_found']);
        exit();
    }

    $uname = (string)($account['uname'] ?? '');
    if ($uname === '') {
        echo json_encode(['ok' => false, 'error' => 'invalid_user']);
        exit();
    }

    $msgId = isset($_POST['message_id']) ? (int)$_POST['message_id'] : 0;
    if ($msgId > 0) {
        $markOne = $user->runQuery('UPDATE message SET is_read = 1 WHERE id = :id AND reci_name = :uname');
        $markOne->execute([':id' => $msgId, ':uname' => $uname]);
    } else {
        $markAll = $user->runQuery('UPDATE message SET is_read = 1 WHERE reci_name = :uname');
        $markAll->execute([':uname' => $uname]);
    }

    $countStmt = $user->runQuery('SELECT COUNT(*) AS total FROM message WHERE reci_name = :uname AND is_read = 0');
    $countStmt->execute([':uname' => $uname]);
    $remaining = (int)($countStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

    echo json_encode(['ok' => true, 'remaining' => $remaining]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'server_error']);
}
