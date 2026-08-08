<?php

function fw_wallet_schema_ready(mysqli $conn): void
{
    static $ready = false;
    if ($ready) {
        return;
    }

    try {
        $conn->query("ALTER TABLE account_balances ADD COLUMN total_balance DECIMAL(20,8) NOT NULL DEFAULT 0 AFTER balance");
    } catch (Throwable $e) {
    }

    try {
        $conn->query("ALTER TABLE account_balances ADD COLUMN available_balance DECIMAL(20,8) NOT NULL DEFAULT 0 AFTER total_balance");
    } catch (Throwable $e) {
    }

    // Backfill rows created before the split between total and available balances.
    try {
        $conn->query("UPDATE account_balances
                      SET total_balance = balance
                      WHERE total_balance = 0 AND balance <> 0");
        $conn->query("UPDATE account_balances
                      SET available_balance = CASE
                        WHEN total_balance <> 0 THEN total_balance
                        ELSE balance
                      END
                      WHERE available_balance = 0 AND (total_balance <> 0 OR balance <> 0)");
    } catch (Throwable $e) {
    }

    $ready = true;
}

function fw_wallet_set(mysqli $conn, string $accNo, string $currencyCode, float $totalBalance, float $availableBalance): bool
{
    $accNo = trim($accNo);
    $currencyCode = strtoupper(trim($currencyCode));
    if ($accNo === '' || !preg_match('/^[A-Z0-9]{2,10}$/', $currencyCode)) {
        return false;
    }

    fw_wallet_schema_ready($conn);

    $safeAcc = $conn->real_escape_string($accNo);
    $safeCode = $conn->real_escape_string($currencyCode);
    $safeTotal = (float)$totalBalance;
    $safeAvailable = (float)$availableBalance;
    if ($safeAvailable > $safeTotal) {
        $safeAvailable = $safeTotal;
    }

    $sql = "INSERT INTO account_balances (acc_no, currency_code, balance, total_balance, available_balance)
            VALUES ('{$safeAcc}', '{$safeCode}', {$safeTotal}, {$safeTotal}, {$safeAvailable})
            ON DUPLICATE KEY UPDATE
              balance = VALUES(balance),
              total_balance = VALUES(total_balance),
              available_balance = VALUES(available_balance)";

    return (bool)$conn->query($sql);
}

function fw_wallet_seed_from_legacy(mysqli $conn, string $accNo, string $currencyCode): void
{
    $accNo = trim($accNo);
    $currencyCode = strtoupper(trim($currencyCode));
    if ($accNo === '' || !preg_match('/^[A-Z0-9]{2,10}$/', $currencyCode)) {
        return;
    }

    $safeAcc = $conn->real_escape_string($accNo);
    $res = $conn->query("SELECT t_bal, a_bal FROM account WHERE acc_no = '{$safeAcc}' LIMIT 1");
    if (!$res || $res->num_rows === 0) {
        return;
    }

    $row = $res->fetch_assoc() ?: [];
    $total = (float)($row['t_bal'] ?? 0);
    $available = (float)($row['a_bal'] ?? $total);
    fw_wallet_set($conn, $accNo, $currencyCode, $total, $available);
}

function fw_wallet_get(mysqli $conn, string $accNo, string $currencyCode): ?array
{
    $accNo = trim($accNo);
    $currencyCode = strtoupper(trim($currencyCode));
    if ($accNo === '' || !preg_match('/^[A-Z0-9]{2,10}$/', $currencyCode)) {
        return null;
    }

    fw_wallet_schema_ready($conn);

    $safeAcc = $conn->real_escape_string($accNo);
    $safeCode = $conn->real_escape_string($currencyCode);
    $res = $conn->query("SELECT balance, total_balance, available_balance
                        FROM account_balances
                        WHERE acc_no = '{$safeAcc}' AND currency_code = '{$safeCode}'
                        LIMIT 1");
    if (!$res || $res->num_rows === 0) {
        return null;
    }

    $row = $res->fetch_assoc() ?: [];
    $total = (float)($row['total_balance'] ?? 0);
    $available = (float)($row['available_balance'] ?? 0);
    $legacy = (float)($row['balance'] ?? 0);

    if ($total == 0.0 && $legacy != 0.0) {
        $total = $legacy;
    }
    if ($available == 0.0 && ($total != 0.0 || $legacy != 0.0)) {
        $available = $total != 0.0 ? $total : $legacy;
    }

    return [
        'total_balance' => $total,
        'available_balance' => $available,
    ];
}

function fw_wallet_all_for_account(mysqli $conn, string $accNo): array
{
    $accNo = trim($accNo);
    if ($accNo === '') {
        return [];
    }

    fw_wallet_schema_ready($conn);

    $safeAcc = $conn->real_escape_string($accNo);
    $map = [];
    $res = $conn->query("SELECT currency_code, balance, total_balance, available_balance
                        FROM account_balances
                        WHERE acc_no = '{$safeAcc}'");
    if (!$res) {
        return $map;
    }

    while ($row = $res->fetch_assoc()) {
        $code = strtoupper(trim((string)($row['currency_code'] ?? '')));
        if ($code === '' || !preg_match('/^[A-Z0-9]{2,10}$/', $code)) {
            continue;
        }

        $total = (float)($row['total_balance'] ?? 0);
        $available = (float)($row['available_balance'] ?? 0);
        $legacy = (float)($row['balance'] ?? 0);

        if ($total == 0.0 && $legacy != 0.0) {
            $total = $legacy;
        }
        if ($available == 0.0 && ($total != 0.0 || $legacy != 0.0)) {
            $available = $total != 0.0 ? $total : $legacy;
        }

        $map[$code] = [
            'total_balance' => $total,
            'available_balance' => $available,
        ];
    }

    return $map;
}

function fw_wallet_sync_legacy_account(mysqli $conn, string $accNo, string $currencyCode): void
{
    $accNo = trim($accNo);
    $currencyCode = strtoupper(trim($currencyCode));
    if ($accNo === '' || !preg_match('/^[A-Z0-9]{2,10}$/', $currencyCode)) {
        return;
    }

    $wallet = fw_wallet_get($conn, $accNo, $currencyCode);
    if (!$wallet) {
        return;
    }

    $safeAcc = $conn->real_escape_string($accNo);
    $safeCode = $conn->real_escape_string($currencyCode);
    $total = (float)$wallet['total_balance'];
    $available = (float)$wallet['available_balance'];

    $conn->query("UPDATE account
                  SET currency = '{$safeCode}', t_bal = {$total}, a_bal = {$available}
                  WHERE acc_no = '{$safeAcc}'");
}
