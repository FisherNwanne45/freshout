<?php

// Extracted dashboard bootstrap logic to reduce index.php {main} complexity for language server inference.

$flashError = '';
$flashSuccess = '';

if (isset($_GET['dormant'])) {
    $dormantMsg = 'Your account is currently dormant or inactive. Transfers and certain operations are not permitted. Please contact support for assistance.';
    try {
        $columns = [];
        $colResult = $conn->query('SHOW COLUMNS FROM site_settings');
        if ($colResult) {
            while ($col = $colResult->fetch_assoc()) {
                $fieldName = strtolower((string)($col['Field'] ?? ''));
                if ($fieldName !== '') {
                    $columns[$fieldName] = true;
                }
            }
        }

        $keyColumn = isset($columns['setting_key']) ? 'setting_key' : (isset($columns['key']) ? 'key' : '');
        $valueColumn = isset($columns['setting_value']) ? 'setting_value' : (isset($columns['value']) ? 'value' : '');

        if ($keyColumn !== '' && $valueColumn !== '') {
            $lookupStmt = $conn->prepare("SELECT `$valueColumn` AS v FROM site_settings WHERE `$keyColumn` = ? LIMIT 1");
            if ($lookupStmt) {
                foreach (['dormant_transfer_message', 'dormant_message'] as $lookupKey) {
                    $lookupStmt->bind_param('s', $lookupKey);
                    if ($lookupStmt->execute()) {
                        $ds = $lookupStmt->get_result();
                        if ($ds && $ds->num_rows > 0) {
                            $dr = $ds->fetch_assoc();
                            $candidate = trim((string)($dr['v'] ?? ''));
                            if ($candidate !== '') {
                                $dormantMsg = $candidate;
                                break;
                            }
                        }
                    }
                }
                $lookupStmt->close();
            }
        }
    } catch (Throwable $e) {}
    $flashError = $dormantMsg;
}

$baseCurrency = strtoupper(trim((string)($row['currency'] ?? 'USD')));
$baseBalance = (float)($row['a_bal'] ?? $row['t_bal'] ?? 0);
if (!preg_match('/^[A-Z0-9]{2,10}$/', $baseCurrency)) {
    $baseCurrency = 'USD';
}

try {
    $seedWallet = $reg_user->runQuery(
        'INSERT INTO account_balances (acc_no, currency_code, balance)
         VALUES (:acc_no, :currency_code, :balance)
         ON DUPLICATE KEY UPDATE currency_code = VALUES(currency_code)'
    );
    $seedWallet->execute([
        ':acc_no' => $accNo,
        ':currency_code' => $baseCurrency,
        ':balance' => $baseBalance,
    ]);
} catch (Throwable $e) {
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_wallet'])) {
    $newCode = strtoupper(trim((string)($_POST['new_currency'] ?? '')));
    if (!preg_match('/^[A-Z0-9]{2,10}$/', $newCode)) {
        $flashError = 'Invalid currency code.';
    } else {
        // Verify the currency is in the currencies table and is active
        $checkCur = $reg_user->runQuery('SELECT code FROM currencies WHERE code = :c AND is_active = 1 LIMIT 1');
        $checkCur->execute([':c' => $newCode]);
        if (!$checkCur->fetch()) {
            $flashError = 'Currency not available.';
        } else {
            try {
                $addW = $reg_user->runQuery(
                    'INSERT INTO account_balances (acc_no, currency_code, balance) VALUES (:an, :c, 0)
                     ON DUPLICATE KEY UPDATE acc_no = VALUES(acc_no)'
                );
                $addW->execute([':an' => $accNo, ':c' => $newCode]);
                $flashSuccess = $newCode . ' account opened successfully.';
            } catch (Throwable $e) {
                $flashError = 'Could not open account. Please try again.';
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wallet_exchange'])) {
    $fromCode = strtoupper(trim((string)($_POST['from_currency'] ?? '')));
    $toCode = strtoupper(trim((string)($_POST['to_currency'] ?? '')));
    $amount = (float)($_POST['exchange_amount'] ?? 0);

    if (!preg_match('/^[A-Z0-9]{2,10}$/', $fromCode) || !preg_match('/^[A-Z0-9]{2,10}$/', $toCode)) {
        $flashError = 'Invalid wallet currency selection.';
    } elseif ($fromCode === $toCode) {
        $flashError = 'Select different source and destination wallets.';
    } elseif ($amount <= 0) {
        $flashError = 'Enter an amount greater than zero.';
    } else {
        try {
            $reg_user->runQuery('START TRANSACTION')->execute();

            $fromWalletStmt = $reg_user->runQuery('SELECT balance FROM account_balances WHERE acc_no = :acc_no AND currency_code = :currency_code FOR UPDATE');
            $fromWalletStmt->execute([':acc_no' => $accNo, ':currency_code' => $fromCode]);
            $fromWallet = $fromWalletStmt->fetch(PDO::FETCH_ASSOC);
            if (!$fromWallet) {
                throw new RuntimeException('Source wallet does not exist.');
            }

            $toWalletStmt = $reg_user->runQuery('SELECT balance FROM account_balances WHERE acc_no = :acc_no AND currency_code = :currency_code FOR UPDATE');
            $toWalletStmt->execute([':acc_no' => $accNo, ':currency_code' => $toCode]);
            $toWallet = $toWalletStmt->fetch(PDO::FETCH_ASSOC);
            if (!$toWallet) {
                $createWallet = $reg_user->runQuery('INSERT INTO account_balances (acc_no, currency_code, balance) VALUES (:acc_no, :currency_code, 0)');
                $createWallet->execute([':acc_no' => $accNo, ':currency_code' => $toCode]);
            }

            if ((float)$fromWallet['balance'] < $amount) {
                throw new RuntimeException('Insufficient balance in source wallet.');
            }

            $rate = null;
            $rateStmt = $reg_user->runQuery('SELECT rate FROM exchange_rates WHERE from_code = :from_code AND to_code = :to_code LIMIT 1');
            $rateStmt->execute([':from_code' => $fromCode, ':to_code' => $toCode]);
            $rateRow = $rateStmt->fetch(PDO::FETCH_ASSOC);
            if ($rateRow && (float)$rateRow['rate'] > 0) {
                $rate = (float)$rateRow['rate'];
            } else {
                $invStmt = $reg_user->runQuery('SELECT rate FROM exchange_rates WHERE from_code = :from_code AND to_code = :to_code LIMIT 1');
                $invStmt->execute([':from_code' => $toCode, ':to_code' => $fromCode]);
                $invRow = $invStmt->fetch(PDO::FETCH_ASSOC);
                if ($invRow && (float)$invRow['rate'] > 0) {
                    $rate = 1 / (float)$invRow['rate'];
                }
            }

            if ($rate === null || $rate <= 0) {
                throw new RuntimeException("No exchange rate configured for {$fromCode} to {$toCode}.");
            }

            $convertedAmount = $amount * $rate;

            $deduct = $reg_user->runQuery('UPDATE account_balances SET balance = balance - :amount WHERE acc_no = :acc_no AND currency_code = :currency_code');
            $deduct->execute([':amount' => $amount, ':acc_no' => $accNo, ':currency_code' => $fromCode]);

            $credit = $reg_user->runQuery('UPDATE account_balances SET balance = balance + :amount WHERE acc_no = :acc_no AND currency_code = :currency_code');
            $credit->execute([':amount' => $convertedAmount, ':acc_no' => $accNo, ':currency_code' => $toCode]);

            try {
                $alerts = $reg_user->runQuery('INSERT INTO alerts (uname, type, amount, sender_name, remarks, date, time) VALUES (:uname, :type, :amount, :sender_name, :remarks, :date, :time)');
                $alerts->execute([
                    ':uname' => $accNo,
                    ':type' => 'Wallet Exchange',
                    ':amount' => $amount,
                    ':sender_name' => $fromCode . ' -> ' . $toCode,
                    ':remarks' => 'Rate ' . number_format($rate, 8) . ', credited ' . number_format($convertedAmount, 8),
                    ':date' => date('Y-m-d'),
                    ':time' => date('H:i:s'),
                ]);
            } catch (Throwable $e) {
            }

            $reg_user->runQuery('COMMIT')->execute();
            $flashSuccess = "Exchange complete: {$fromCode} " . number_format($amount, 2) . " -> {$toCode} " . number_format($convertedAmount, 2);

            try {
                $reg_user->send_mail((string)($row['email'] ?? ''), '', 'Transaction Complete: Wallet Exchange', 'debit_alert', [
                    'fname' => (string)($row['fname'] ?? ''),
                    'name' => trim((string)($row['fname'] ?? '') . ' ' . (string)($row['lname'] ?? '')),
                    'currency' => $fromCode,
                    'amount' => number_format((float)$amount, 2),
                    'description' => $fromCode . ' to ' . $toCode . ' exchange completed',
                    'balance' => number_format((float)$activeAccountBalance, 2),
                    'date' => date('Y-m-d H:i:s'),
                    'phone' => (string)($row['phone'] ?? ''),
                ]);
            } catch (Throwable $e) {
            }
        } catch (Throwable $e) {
            try {
                $reg_user->runQuery('ROLLBACK')->execute();
            } catch (Throwable $rollbackError) {
            }
            $flashError = $e->getMessage();
        }
    }
}

$walletsRes = $reg_user->runQuery('SELECT ab.currency_code, ab.balance, c.symbol, c.name, c.is_crypto, ca.iban
                                   FROM account_balances ab
                                   LEFT JOIN currencies c ON c.code = ab.currency_code
                                   LEFT JOIN customer_accounts ca ON ca.owner_acc_no = ab.acc_no AND ca.currency_code = ab.currency_code
                                   WHERE ab.acc_no = :acc_no
                                   ORDER BY c.is_crypto, c.sort_order, ab.currency_code');
$walletsRes->execute([':acc_no' => $accNo]);
$wallets = $walletsRes->fetchAll(PDO::FETCH_ASSOC);

$availableCurrenciesStmt = $reg_user->runQuery('SELECT code, symbol, name, is_crypto FROM currencies WHERE is_active = 1 ORDER BY is_crypto, sort_order, code');
$availableCurrenciesStmt->execute();
$availableCurrencies = $availableCurrenciesStmt->fetchAll(PDO::FETCH_ASSOC);

$ratesRes = $reg_user->runQuery('SELECT from_code, to_code, rate FROM exchange_rates');
$ratesRes->execute();
$rates = $ratesRes->fetchAll(PDO::FETCH_ASSOC);

$recentTransferStmt = $reg_user->runQuery('SELECT * FROM transfer WHERE email = :email ORDER BY id DESC LIMIT 8');
$recentTransferStmt->execute([':email' => (string)$row['email']]);
$recentTransfers = $recentTransferStmt->fetchAll(PDO::FETCH_ASSOC);

$alertsStmt = $reg_user->runQuery('SELECT * FROM alerts WHERE uname = :acc_no ORDER BY date DESC, time DESC LIMIT 8');
$alertsStmt->execute([':acc_no' => $accNo]);
$recentAlerts = $alertsStmt->fetchAll(PDO::FETCH_ASSOC);

// ── Unified activity feed: merge transfers + alerts, sort by most recent date ──
$unifiedActivity = [];
foreach ($recentTransfers as $tx) {
    $ts    = strtotime((string)($tx['date'] ?? ''));
    $txCur = strtoupper(trim((string)($tx['currency_code'] ?: $row['currency'] ?: 'USD')));
    $unifiedActivity[] = [
        'source'     => 'transfer',
        'direction'  => 'debit',
        'type_label' => 'Debit',
        'amount'     => (float)($tx['amount'] ?? 0),
        'currency'   => $txCur,
        'description'=> trim((string)($tx['acc_name'] ?: $tx['bank_name'] ?: '—')),
        'remarks'    => (string)($tx['remarks'] ?? ''),
        'status'     => (string)($tx['status'] ?? 'Pending'),
        'sort_key'   => $ts ?: 0,
        'date_str'   => $ts ? date('d F, Y', $ts) : (string)($tx['date'] ?? '—'),
    ];
}
foreach ($recentAlerts as $al) {
    $alertDate     = trim((string)($al['date'] ?? ''));
    $alertTime     = trim((string)($al['time'] ?? ''));
    $alertDatetime = $alertDate . ($alertTime !== '' ? ' ' . $alertTime : ' 00:00:00');
    $ts            = strtotime($alertDatetime) ?: 0;
    $txCur         = strtoupper(trim((string)($row['currency'] ?: 'USD')));
    $unifiedActivity[] = [
        'source'     => 'alert',
        'direction'  => 'credit',
        'type_label' => 'Credit',
        'amount'     => (float)($al['amount'] ?? 0),
        'currency'   => $txCur,
        'description'=> trim((string)($al['sender_name'] ?? '—')),
        'remarks'    => (string)($al['remarks'] ?? ''),
        'status'     => null,
        'sort_key'   => $ts,
        'date_str'   => $ts > 0 ? date('d F, Y', $ts) : $alertDate,
    ];
}
usort($unifiedActivity, static fn($a, $b) => $b['sort_key'] <=> $a['sort_key']);
$unifiedActivity = array_slice($unifiedActivity, 0, 5);

$fullName = trim((string)($row['fname'] ?? '') . ' ' . (string)($row['lname'] ?? ''));
if ($fullName === '') {
    $fullName = (string)$row['uname'];
}

$messageCount = 0;
$ticketCount = 0;
$loanSummary = [
    'submitted' => 0,
    'under_review' => 0,
    'approved' => 0,
    'rejected' => 0,
    'disbursed' => 0,
    'active' => 0,
    'closed' => 0,
];
$recentLoans = [];
$tdSummary = ['active_count' => 0, 'active_value' => 0.0, 'matured_count' => 0];
$investSummary = ['has_account' => false, 'account_ref' => '', 'market_value' => 0.0, 'position_count' => 0];
$roboSummary = ['enabled' => false, 'risk_band' => 'N/A', 'model_name' => 'Not configured', 'frequency' => 'N/A'];
try {
    try {
        $msgCountStmt = $reg_user->runQuery('SELECT COUNT(*) AS total FROM message WHERE reci_name = :uname AND is_read = 0');
        $msgCountStmt->execute([':uname' => (string)($row['uname'] ?? '')]);
        $messageCount = (int)($msgCountStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
    } catch (Throwable $e) {
        $msgCountStmt = $reg_user->runQuery('SELECT COUNT(*) AS total FROM message WHERE reci_name = :uname');
        $msgCountStmt->execute([':uname' => (string)($row['uname'] ?? '')]);
        $messageCount = (int)($msgCountStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
    }
} catch (Throwable $e) {
}
try {
        $reg_user->runQuery("CREATE TABLE IF NOT EXISTS term_deposits (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            deposit_ref VARCHAR(32) NOT NULL,
            acc_no VARCHAR(50) NOT NULL,
            principal DECIMAL(18,2) NOT NULL,
            annual_rate DECIMAL(7,4) NOT NULL,
            tenor_months INT NOT NULL,
            start_date DATE NOT NULL,
            maturity_date DATE NOT NULL,
            maturity_amount DECIMAL(18,2) NOT NULL,
            status VARCHAR(30) NOT NULL DEFAULT 'active',
            payout_mode VARCHAR(20) NOT NULL DEFAULT 'payout',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            UNIQUE KEY uq_term_deposit_ref (deposit_ref),
            KEY idx_term_deposit_acc_status (acc_no, status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4")->execute();

        $tdAgg = $reg_user->runQuery('SELECT
                SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) AS active_count,
                SUM(CASE WHEN status = "active" THEN maturity_amount ELSE 0 END) AS active_value,
                SUM(CASE WHEN status = "matured" THEN 1 ELSE 0 END) AS matured_count
            FROM term_deposits WHERE acc_no = :acc_no');
        $tdAgg->execute([':acc_no' => $accNo]);
        $tdRow = $tdAgg->fetch(PDO::FETCH_ASSOC) ?: [];
        $tdSummary['active_count'] = (int)($tdRow['active_count'] ?? 0);
        $tdSummary['active_value'] = (float)($tdRow['active_value'] ?? 0);
        $tdSummary['matured_count'] = (int)($tdRow['matured_count'] ?? 0);
} catch (Throwable $e) {
}

try {
        $reg_user->runQuery("CREATE TABLE IF NOT EXISTS investment_accounts (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            account_ref VARCHAR(32) NOT NULL,
            acc_no VARCHAR(50) NOT NULL,
            base_currency VARCHAR(10) NOT NULL DEFAULT 'USD',
            risk_profile VARCHAR(30) NOT NULL DEFAULT 'moderate',
            status VARCHAR(30) NOT NULL DEFAULT 'active',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            UNIQUE KEY uq_investment_account_ref (account_ref),
            KEY idx_investment_acc_status (acc_no, status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4")->execute();

        $reg_user->runQuery("CREATE TABLE IF NOT EXISTS investment_positions (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            investment_account_id BIGINT UNSIGNED NOT NULL,
            symbol VARCHAR(30) NOT NULL,
            instrument_type VARCHAR(20) NOT NULL,
            quantity DECIMAL(24,8) NOT NULL DEFAULT 0,
            avg_price DECIMAL(18,6) NOT NULL DEFAULT 0,
            market_price DECIMAL(18,6) NOT NULL DEFAULT 0,
            market_value DECIMAL(18,2) NOT NULL DEFAULT 0,
            updated_at DATETIME NOT NULL,
            KEY idx_position_account (investment_account_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4")->execute();

        $invAcc = $reg_user->runQuery('SELECT id, account_ref FROM investment_accounts WHERE acc_no = :acc_no ORDER BY id DESC LIMIT 1');
        $invAcc->execute([':acc_no' => $accNo]);
        $invRow = $invAcc->fetch(PDO::FETCH_ASSOC);
        if ($invRow) {
                $investSummary['has_account'] = true;
                $investSummary['account_ref'] = (string)($invRow['account_ref'] ?? '');
                $invPos = $reg_user->runQuery('SELECT COUNT(*) AS c, COALESCE(SUM(market_value),0) AS v FROM investment_positions WHERE investment_account_id = :id');
                $invPos->execute([':id' => (int)$invRow['id']]);
                $pv = $invPos->fetch(PDO::FETCH_ASSOC) ?: [];
                $investSummary['position_count'] = (int)($pv['c'] ?? 0);
                $investSummary['market_value'] = (float)($pv['v'] ?? 0);
        }
} catch (Throwable $e) {
}

try {
        $reg_user->runQuery("CREATE TABLE IF NOT EXISTS robo_profiles (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            acc_no VARCHAR(50) NOT NULL,
            score INT NOT NULL,
            risk_band VARCHAR(30) NOT NULL,
            model_name VARCHAR(60) NOT NULL,
            rebalancing_frequency VARCHAR(30) NOT NULL DEFAULT 'quarterly',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            KEY idx_robo_profile_acc (acc_no)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4")->execute();

        $rb = $reg_user->runQuery('SELECT risk_band, model_name, rebalancing_frequency FROM robo_profiles WHERE acc_no = :acc_no ORDER BY id DESC LIMIT 1');
        $rb->execute([':acc_no' => $accNo]);
        $rbRow = $rb->fetch(PDO::FETCH_ASSOC);
        if ($rbRow) {
                $roboSummary['enabled'] = true;
                $roboSummary['risk_band'] = (string)($rbRow['risk_band'] ?? 'N/A');
                $roboSummary['model_name'] = (string)($rbRow['model_name'] ?? 'N/A');
                $roboSummary['frequency'] = (string)($rbRow['rebalancing_frequency'] ?? 'N/A');
        }
} catch (Throwable $e) {
}
try {
    $reg_user->runQuery("CREATE TABLE IF NOT EXISTS loan_applications (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        application_ref VARCHAR(32) NOT NULL,
        acc_no VARCHAR(50) NOT NULL,
        email VARCHAR(190) NOT NULL,
        full_name VARCHAR(190) NOT NULL,
        purpose VARCHAR(255) NOT NULL,
        amount DECIMAL(18,2) NOT NULL DEFAULT 0.00,
        currency_code VARCHAR(10) NOT NULL DEFAULT 'USD',
        details TEXT NULL,
        status VARCHAR(30) NOT NULL DEFAULT 'submitted',
        admin_note TEXT NULL,
        reviewed_by VARCHAR(190) NULL,
        reviewed_at DATETIME NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        UNIQUE KEY uq_loan_ref (application_ref),
        KEY idx_loan_acc_status (acc_no, status),
        KEY idx_loan_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4")->execute();

    $loanStatusStmt = $reg_user->runQuery('SELECT status, COUNT(*) AS total
        FROM loan_applications
        WHERE acc_no = :acc_no
        GROUP BY status');
    $loanStatusStmt->execute([':acc_no' => $accNo]);
    foreach ($loanStatusStmt->fetchAll(PDO::FETCH_ASSOC) as $statusRow) {
        $k = strtolower(trim((string)($statusRow['status'] ?? '')));
        if ($k !== '' && isset($loanSummary[$k])) {
            $loanSummary[$k] = (int)($statusRow['total'] ?? 0);
        }
    }

    $recentLoanStmt = $reg_user->runQuery('SELECT application_ref, purpose, amount, currency_code, status, created_at
        FROM loan_applications
        WHERE acc_no = :acc_no
        ORDER BY id DESC
        LIMIT 3');
    $recentLoanStmt->execute([':acc_no' => $accNo]);
    $recentLoans = $recentLoanStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
}
try {
    $ticketCountStmt = $reg_user->runQuery('SELECT COUNT(*) AS total FROM ticket WHERE tc = :tc');
    $ticketCountStmt->execute([':tc' => $accNo]);
    $ticketCount = (int)($ticketCountStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
} catch (Throwable $e) {
}

$contactUrl = '#';
if (!empty($siteRow['tawk2'])) {
    $contactUrl = 'https://tawk.to/chat/' . ltrim((string)$siteRow['tawk2'], '/');
}
$menuLinkCount = 12;

$displayCurrency = $baseCurrency !== '' ? $baseCurrency : 'USD';
$primaryBalance = (float)($row['a_bal'] ?? 0);

$activeAccountCode = strtoupper(trim((string)($_GET['account_currency'] ?? $displayCurrency)));
if ($activeAccountCode === '') {
    $activeAccountCode = $displayCurrency;
}
$activeAccountBalance = $primaryBalance;
$activeAccountIsCrypto = 0;
foreach ($wallets as $wallet) {
    if (strtoupper((string)($wallet['currency_code'] ?? '')) === $activeAccountCode) {
        $activeAccountBalance = (float)($wallet['balance'] ?? 0);
        $activeAccountIsCrypto = (int)($wallet['is_crypto'] ?? 0);
        break;
    }
}

$walletCount = count($wallets);
$walletTotal = 0.0;
foreach ($wallets as $wallet) {
    $walletTotal += (float)($wallet['balance'] ?? 0);
}

$rateLookup = [];
foreach ($rates as $rateRow) {
    $fromCode = strtoupper(trim((string)($rateRow['from_code'] ?? '')));
    $toCode = strtoupper(trim((string)($rateRow['to_code'] ?? '')));
    $rawRate = (float)($rateRow['rate'] ?? 0);
    if ($fromCode === '' || $toCode === '' || $rawRate <= 0) {
        continue;
    }
    if (!isset($rateLookup[$fromCode])) {
        $rateLookup[$fromCode] = [];
    }
    $rateLookup[$fromCode][$toCode] = $rawRate;
}
$convertAmount = static function (float $amount, string $fromCode, string $toCode) use ($rateLookup): ?float {
    $from = strtoupper(trim($fromCode));
    $to = strtoupper(trim($toCode));
    if ($amount == 0.0) {
        return 0.0;
    }
    if ($from === '' || $to === '') {
        return null;
    }
    if ($from === $to) {
        return $amount;
    }
    if (isset($rateLookup[$from][$to]) && (float)$rateLookup[$from][$to] > 0) {
        return $amount * (float)$rateLookup[$from][$to];
    }
    if (isset($rateLookup[$to][$from]) && (float)$rateLookup[$to][$from] > 0) {
        return $amount / (float)$rateLookup[$to][$from];
    }
    return null;
};

$walletTotalInActive = 0.0;
foreach ($wallets as $wallet) {
    $fromCode = strtoupper(trim((string)($wallet['currency_code'] ?? '')));
    $converted = $convertAmount((float)($wallet['balance'] ?? 0), $fromCode, $activeAccountCode);
    if ($converted !== null) {
        $walletTotalInActive += $converted;
    }
}

// ── Ledger Balance: the active wallet's posted balance ──
$ledgerBalance = $activeAccountBalance;

// ── Net Flow: this month's credits/debits natively in the active wallet's currency ──
$monthStart = date('Y-m-01');
$monthEnd   = date('Y-m-t');
$monthCredits = 0.0;
$monthDebits  = 0.0;
// Credits come from `alerts` which has no currency_code — amounts are in the base ($displayCurrency).
// Only count them when the active wallet matches the base currency.
if ($activeAccountCode === $displayCurrency) {
    try {
        $creditsStmt = $reg_user->runQuery(
            'SELECT COALESCE(SUM(amount), 0) AS total FROM alerts WHERE uname = :acc_no AND date >= :start AND date <= :end'
        );
        $creditsStmt->execute([':acc_no' => $accNo, ':start' => $monthStart, ':end' => $monthEnd]);
        $monthCredits = (float)($creditsStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
    } catch (Throwable $e) {
        $monthCredits = 0.0;
    }
}
// Debits come from `transfer`. Rows with no currency_code are treated as $displayCurrency.
// Only include rows whose effective currency matches the active wallet.
try {
    $debitsStmt = $reg_user->runQuery(
        "SELECT amount, currency_code FROM transfer WHERE email = :email AND date >= :start AND date <= :end AND (status IS NULL OR LOWER(status) NOT IN ('rejected','failed','cancelled'))"
    );
    $debitsStmt->execute([':email' => (string)($row['email'] ?? ''), ':start' => $monthStart, ':end' => $monthEnd]);
    foreach ($debitsStmt->fetchAll(PDO::FETCH_ASSOC) as $debit) {
        $dCur = strtoupper(trim((string)($debit['currency_code'] ?: $displayCurrency)));
        if ($dCur !== $activeAccountCode) continue; // skip other-currency transfers
        $monthDebits += (float)($debit['amount'] ?? 0);
    }
} catch (Throwable $e) {
    $monthDebits = 0.0;
}
$netFlow = $monthCredits - $monthDebits;

$rawStatus = trim((string)($row['status'] ?? 'Active'));
$statusLower = strtolower($rawStatus);
$displayStatus = $rawStatus;
$statusColor = '#64748b';
if ($statusLower === 'active' || strpos($statusLower, 'active') !== false) {
    $statusColor = '#16a34a';
}
if ($statusLower === 'pincode') {
    $displayStatus = 'Active';
    $statusColor = '#16a34a';
}
if ($statusLower === 'otp') {
    $displayStatus = 'Active';
    $statusColor = '#16a34a';
}
if (strpos($statusLower, 'dormant') !== false || strpos($statusLower, 'inactive') !== false) {
    $statusColor = '#f59e0b';
}
if ($statusLower === 'closed') {
    $statusColor = '#6b7280';
}
if ($statusLower === 'disabled') {
    $statusColor = '#dc2626';
}

$accountFlag = '';
$isCryptoAccount = 0;
$statusCurrencyCode = strtoupper(trim((string)$activeAccountCode));
if ($statusCurrencyCode === '') {
    $statusCurrencyCode = strtoupper(trim((string)$displayCurrency));
}
try {
    $flagStmt = $reg_user->runQuery('SELECT flag_code, is_crypto FROM currencies WHERE code = :code LIMIT 1');
    $flagStmt->execute([':code' => $statusCurrencyCode]);
    $flagRow = $flagStmt->fetch(PDO::FETCH_ASSOC) ?: [];
    $accountFlag = strtoupper(trim((string)($flagRow['flag_code'] ?? '')));
    $isCryptoAccount = (int)($flagRow['is_crypto'] ?? 0);
} catch (Throwable $e) {
}

$flagFallbacks = [
    'USD' => 'US',
    'EUR' => 'EU',
    'GBP' => 'GB',
    'JPY' => 'JP',
    'CAD' => 'CA',
    'AUD' => 'AU',
    'CHF' => 'CH',
    'CNY' => 'CN',
    'HKD' => 'HK',
    'SGD' => 'SG',
];
if ($accountFlag === '' && isset($flagFallbacks[$statusCurrencyCode])) {
    $accountFlag = $flagFallbacks[$statusCurrencyCode];
}
$knownCryptoCodes = ['BTC', 'ETH', 'USDT', 'XRP', 'LTC', 'BNB', 'SOL', 'USDC'];
if ($isCryptoAccount !== 1 && in_array($statusCurrencyCode, $knownCryptoCodes, true)) {
    $isCryptoAccount = 1;
}

$walletByCode = [];
foreach ($wallets as $wallet) {
    $walletByCode[strtoupper((string)($wallet['currency_code'] ?? ''))] = $wallet;
}
$availableByCode = [];
foreach ($availableCurrencies as $cur) {
    $availableByCode[strtoupper((string)($cur['code'] ?? ''))] = $cur;
}

$primarySecondaryCode = $activeAccountCode === 'EUR' ? 'USD' : 'EUR';
if (!isset($availableByCode[$primarySecondaryCode])) {
    $primarySecondaryCode = $activeAccountCode === 'USD' ? 'EUR' : 'USD';
}

$preferredCryptoCode = 'BTC';
foreach (['BTC', 'ETH', 'USDT', 'USDC', 'SOL'] as $candidateCrypto) {
    if (isset($availableByCode[$candidateCrypto]) || isset($walletByCode[$candidateCrypto])) {
        $preferredCryptoCode = $candidateCrypto;
        break;
    }
}

$spotlightCodes = [];

// 1. Always lead with the active wallet
$_activeCode = strtoupper(trim((string)$activeAccountCode));
if ($_activeCode !== '') {
    $spotlightCodes[] = $_activeCode;
}

// 2. Fill remaining slots with other EXISTING wallets (user has real accounts)
foreach ($wallets as $wallet) {
    if (count($spotlightCodes) >= 3) break;
    $code = strtoupper(trim((string)($wallet['currency_code'] ?? '')));
    if ($code === '' || in_array($code, $spotlightCodes, true)) continue;
    $spotlightCodes[] = $code;
}

// 3. Only if still under 3, suggest uncreated recommended currencies as placeholders
if (count($spotlightCodes) < 3) {
    $_existingCodes = array_keys($walletByCode);
    $_recommended = [];
    foreach ([$primarySecondaryCode, $preferredCryptoCode] as $_rec) {
        $_c = strtoupper(trim((string)$_rec));
        if ($_c !== '' && !in_array($_c, $spotlightCodes, true) && !in_array($_c, $_existingCodes, true)) {
            $_recommended[] = $_c;
        }
    }
    foreach ($availableCurrencies as $cur) {
        $_c = strtoupper(trim((string)($cur['code'] ?? '')));
        if ($_c !== '' && !in_array($_c, $spotlightCodes, true) && !in_array($_c, $_existingCodes, true)) {
            $_recommended[] = $_c;
        }
    }
    foreach ($_recommended as $_c) {
        if (count($spotlightCodes) >= 3) break;
        $spotlightCodes[] = $_c;
    }
}

$spotlightCodes = array_slice($spotlightCodes, 0, 3);

$spotlightWalletCards = [];
foreach ($spotlightCodes as $code) {
    $existingWallet = $walletByCode[$code] ?? null;
    $currencyMeta = $availableByCode[$code] ?? null;
    $isCrypto = (int)($existingWallet['is_crypto'] ?? ($currencyMeta['is_crypto'] ?? 0));
    $spotlightWalletCards[] = [
        'code' => $code,
        'exists' => $existingWallet !== null,
        'balance' => (float)($existingWallet['balance'] ?? 0),
        'symbol' => (string)($existingWallet['symbol'] ?? ($currencyMeta['symbol'] ?? $code)),
        'name' => (string)($existingWallet['name'] ?? ($currencyMeta['name'] ?? $code)),
        'is_crypto' => $isCrypto,
        'iban' => (string)($existingWallet['iban'] ?? ''),
        'is_active' => $code === $activeAccountCode,
    ];
}
$accountFlag = strtoupper($accountFlag);
$badgeCode = $statusCurrencyCode;
$accountBadgeSrc = '';
(function () use ($badgeCode, $isCryptoAccount, &$accountBadgeSrc) {
    if (!preg_match('/^[A-Z0-9]{2,10}$/', $badgeCode)) { return; }
    $cryptoBadges = [
        'BTC'  => ['bg' => '#f7931a', 'fg' => '#ffffff'],
        'ETH'  => ['bg' => '#627eea', 'fg' => '#ffffff'],
        'USDT' => ['bg' => '#26a17b', 'fg' => '#ffffff'],
        'XRP'  => ['bg' => '#23292f', 'fg' => '#ffffff'],
        'LTC'  => ['bg' => '#345d9d', 'fg' => '#ffffff'],
        'BNB'  => ['bg' => '#f3ba2f', 'fg' => '#111827'],
        'SOL'  => ['bg' => '#111827', 'fg' => '#6dffa7'],
        'USDC' => ['bg' => '#2775ca', 'fg' => '#ffffff'],
    ];
    $fiatToFlag = ['USD'=>'US','EUR'=>'EU','GBP'=>'GB','JPY'=>'JP','CAD'=>'CA','AUD'=>'AU','CHF'=>'CH','CNY'=>'CN','HKD'=>'HK','SGD'=>'SG','NOK'=>'NO','SEK'=>'SE','DKK'=>'DK','NZD'=>'NZ'];
    if ($isCryptoAccount === 1 && isset($cryptoBadges[$badgeCode])) {
        $c = $cryptoBadges[$badgeCode];
        $lbl = htmlspecialchars($badgeCode, ENT_QUOTES, 'UTF-8');
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="28" viewBox="0 0 40 28">'
             . '<rect x="1" y="1" width="38" height="26" rx="6" fill="' . $c['bg'] . '" stroke="#cbd5e1"/>'
             . '<text x="20" y="18" text-anchor="middle" fill="' . $c['fg'] . '" font-size="9" font-family="Arial,sans-serif" font-weight="700">' . $lbl . '</text>'
             . '</svg>';
        $accountBadgeSrc = 'data:image/svg+xml;base64,' . base64_encode($svg);
        return;
    }
    $flagCode = isset($fiatToFlag[$badgeCode]) ? $fiatToFlag[$badgeCode] : $badgeCode;
    $flagCode = strtolower($flagCode);
    $flagFile = __DIR__ . '/assets/flags/' . $flagCode . '.svg';
    if (is_file($flagFile)) {
        $svg = file_get_contents($flagFile);
        if ($svg !== false && strlen($svg) > 10) {
            $accountBadgeSrc = 'data:image/svg+xml;base64,' . base64_encode($svg);
            return;
        }
    }
    // Fallback text badge
    $lbl = htmlspecialchars(substr($badgeCode, 0, 4), ENT_QUOTES, 'UTF-8');
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="28" viewBox="0 0 40 28">'
         . '<rect x="1" y="1" width="38" height="26" rx="6" fill="#f8fafc" stroke="#cbd5e1"/>'
         . '<text x="20" y="18" text-anchor="middle" fill="#334155" font-size="9" font-family="Arial,sans-serif" font-weight="700">' . $lbl . '</text>'
         . '</svg>';
    $accountBadgeSrc = 'data:image/svg+xml;base64,' . base64_encode($svg);
})();
$identityLine = trim($statusCurrencyCode . ' #' . $accNo);

$cardDigits = preg_replace('/\D+/', '', (string)($row['ccard'] ?? ''));
$cardLast4 = strlen($cardDigits) >= 4 ? substr($cardDigits, -4) : '2402';
$cardMasked = '5301 98** **** ' . $cardLast4;
$cardExpiry = trim((string)($row['ccdate'] ?? '08/29'));
if ($cardExpiry === '') {
    $cardExpiry = '08/29';
}

$walletChart = [];
foreach ($wallets as $wallet) {
    $amount = (float)($wallet['balance'] ?? 0);
    if ($amount <= 0) {
        continue;
    }
    $walletChart[] = [
        'code' => (string)($wallet['currency_code'] ?? ''),
        'balance' => $amount,
    ];
}
usort($walletChart, static function ($a, $b) {
    return $b['balance'] <=> $a['balance'];
});
$walletChart = array_slice($walletChart, 0, 5);

$chartColors = ['#0f766e', '#0369a1', '#7c3aed', '#c2410c', '#475569'];
$chartTotal = 0.0;
foreach ($walletChart as $slice) {
    $chartTotal += (float)$slice['balance'];
}

$conicParts = [];
$legend = [];
$start = 0.0;
foreach ($walletChart as $idx => $slice) {
    $color = $chartColors[$idx % count($chartColors)];
    $pct = $chartTotal > 0 ? (((float)$slice['balance'] / $chartTotal) * 100) : 0;
    $end = $start + $pct;
    $conicParts[] = $color . ' ' . number_format($start, 2, '.', '') . '% ' . number_format($end, 2, '.', '') . '%';
    $legend[] = [
        'code' => $slice['code'],
        'percent' => $pct,
        'color' => $color,
    ];
    $start = $end;
}
$walletPieStyle = empty($conicParts) ? 'conic-gradient(#cbd5e1 0 100%)' : 'conic-gradient(' . implode(', ', $conicParts) . ')';

$shellExtraHead = '<style>.ticker-track{animation:tickerScroll 26s linear infinite;width:max-content}@keyframes tickerScroll{0%{transform:translateX(0)}100%{transform:translateX(-50%)}}</style>';
require_once __DIR__ . '/shell-data.php';
$shellPageTitle = 'Dashboard';
require_once __DIR__ . '/shell-open.php';
