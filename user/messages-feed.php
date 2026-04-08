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

    $accountStmt = $user->runQuery('SELECT uname, fname, lname, pp, currency FROM account WHERE acc_no = :acc_no LIMIT 1');
    $accountStmt->execute([':acc_no' => $accNo]);
    $account = $accountStmt->fetch(PDO::FETCH_ASSOC);

    if (!$account) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'account_not_found']);
        exit();
    }

    $uname = (string)($account['uname'] ?? '');
    $fullName = trim((string)($account['fname'] ?? '') . ' ' . (string)($account['lname'] ?? ''));
    if ($fullName === '') {
        $fullName = $uname !== '' ? $uname : 'Customer';
    }
    $photo = (string)($account['pp'] ?? 'user.png');
    $currencyCode = strtoupper(trim((string)($account['currency'] ?? 'USD')));

    $isCrypto = 0;
    $flagCode = '';
    try {
        $flagStmt = $user->runQuery('SELECT flag_code, is_crypto FROM currencies WHERE code = :code LIMIT 1');
        $flagStmt->execute([':code' => $currencyCode]);
        $flagRow = $flagStmt->fetch(PDO::FETCH_ASSOC) ?: [];
        $isCrypto = (int)($flagRow['is_crypto'] ?? 0);
        $flagCode = strtoupper(trim((string)($flagRow['flag_code'] ?? '')));
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
    if ($flagCode === '' && isset($flagFallbacks[$currencyCode])) {
        $flagCode = $flagFallbacks[$currencyCode];
    }
    $knownCryptoCodes = ['BTC', 'ETH', 'USDT', 'XRP', 'LTC', 'BNB', 'SOL', 'USDC'];
    if ($isCrypto !== 1 && in_array($currencyCode, $knownCryptoCodes, true)) {
        $isCrypto = 1;
    }
    $badgeCode = $currencyCode;
    $badgeSrc = '';
    if (preg_match('/^[A-Z0-9]{2,10}$/', $badgeCode)) {
        $cryptoBadges = ['BTC'=>['bg'=>'#f7931a','fg'=>'#ffffff'],'ETH'=>['bg'=>'#627eea','fg'=>'#ffffff'],'USDT'=>['bg'=>'#26a17b','fg'=>'#ffffff'],'XRP'=>['bg'=>'#23292f','fg'=>'#ffffff'],'LTC'=>['bg'=>'#345d9d','fg'=>'#ffffff'],'BNB'=>['bg'=>'#f3ba2f','fg'=>'#111827'],'SOL'=>['bg'=>'#111827','fg'=>'#6dffa7'],'USDC'=>['bg'=>'#2775ca','fg'=>'#ffffff']];
        $fiatMap = ['USD'=>'US','EUR'=>'EU','GBP'=>'GB','JPY'=>'JP','CAD'=>'CA','AUD'=>'AU','CHF'=>'CH','CNY'=>'CN','HKD'=>'HK','SGD'=>'SG','NOK'=>'NO','SEK'=>'SE','DKK'=>'DK','NZD'=>'NZ'];
        if ($isCrypto === 1 && isset($cryptoBadges[$badgeCode])) {
            $c = $cryptoBadges[$badgeCode];
            $lbl = htmlspecialchars($badgeCode, ENT_QUOTES, 'UTF-8');
            $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="28" viewBox="0 0 40 28"><rect x="1" y="1" width="38" height="26" rx="6" fill="' . $c['bg'] . '" stroke="#cbd5e1"/><text x="20" y="18" text-anchor="middle" fill="' . $c['fg'] . '" font-size="9" font-family="Arial,sans-serif" font-weight="700">' . $lbl . '</text></svg>';
            $badgeSrc = 'data:image/svg+xml;base64,' . base64_encode($svg);
        } else {
            $fc = strtolower(isset($fiatMap[$badgeCode]) ? $fiatMap[$badgeCode] : $badgeCode);
            $flagFile = __DIR__ . '/assets/flags/' . $fc . '.svg';
            if (is_file($flagFile)) {
                $svgData = file_get_contents($flagFile);
                if ($svgData !== false && strlen($svgData) > 10) {
                    $badgeSrc = 'data:image/svg+xml;base64,' . base64_encode($svgData);
                }
            }
            if ($badgeSrc === '') {
                $lbl = htmlspecialchars(substr($badgeCode, 0, 4), ENT_QUOTES, 'UTF-8');
                $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="28" viewBox="0 0 40 28"><rect x="1" y="1" width="38" height="26" rx="6" fill="#f8fafc" stroke="#cbd5e1"/><text x="20" y="18" text-anchor="middle" fill="#334155" font-size="9" font-family="Arial,sans-serif" font-weight="700">' . $lbl . '</text></svg>';
                $badgeSrc = 'data:image/svg+xml;base64,' . base64_encode($svg);
            }
        }
    }

    $total = 0;
    try {
        $countStmt = $user->runQuery('SELECT COUNT(*) AS total FROM message WHERE reci_name = :uname AND is_read = 0');
        $countStmt->execute([':uname' => $uname]);
        $total = (int)($countStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
    } catch (Throwable $e) {
        $countStmt = $user->runQuery('SELECT COUNT(*) AS total FROM message WHERE reci_name = :uname');
        $countStmt->execute([':uname' => $uname]);
        $total = (int)($countStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
    }

    $msgStmt = $user->runQuery('SELECT id, sender_name, subject, msg, date, COALESCE(is_read, 0) AS is_read FROM message WHERE reci_name = :uname ORDER BY id DESC LIMIT 5');
    $msgStmt->execute([':uname' => $uname]);
    $rows = $msgStmt->fetchAll(PDO::FETCH_ASSOC);

    $translatorLanguages = 'en,es,fr,de,it,pt,ru,zh-CN';
    try {
        $langStmt = $user->runQuery("SELECT setting_value FROM site_settings WHERE setting_key = 'translator_languages' LIMIT 1");
        $langStmt->execute();
        $langVal = (string)($langStmt->fetch(PDO::FETCH_ASSOC)['setting_value'] ?? '');
        if ($langVal !== '') {
            $translatorLanguages = $langVal;
        }
    } catch (Throwable $e) {
        try {
            $langStmt = $user->runQuery("SELECT `value` FROM site_settings WHERE `key` = 'translator_languages' LIMIT 1");
            $langStmt->execute();
            $langVal = (string)($langStmt->fetch(PDO::FETCH_ASSOC)['value'] ?? '');
            if ($langVal !== '') {
                $translatorLanguages = $langVal;
            }
        } catch (Throwable $e2) {
        }
    }

    $languageMap = [
        'en' => 'EN',
        'es' => 'ES',
        'fr' => 'FR',
        'de' => 'DE',
        'it' => 'IT',
        'pt' => 'PT',
        'ru' => 'RU',
        'zh-CN' => 'ZH',
        'zh-TW' => 'ZH-TW',
        'ar' => 'AR',
        'ja' => 'JA',
        'ko' => 'KO',
        'tr' => 'TR',
        'hi' => 'HI',
        'nl' => 'NL',
    ];

    $translatorOptions = [];
    foreach (explode(',', $translatorLanguages) as $langCodeRaw) {
        $langCode = trim($langCodeRaw);
        if (!preg_match('/^[a-z]{2}(?:-[A-Z]{2})?$/', $langCode)) {
            continue;
        }
        if (isset($translatorOptions[$langCode])) {
            continue;
        }
        $translatorOptions[$langCode] = [
            'code' => $langCode,
            'label' => $languageMap[$langCode] ?? strtoupper($langCode),
        ];
    }
    if (!isset($translatorOptions['en'])) {
        $translatorOptions = ['en' => ['code' => 'en', 'label' => 'EN']] + $translatorOptions;
    }

    $formatRelativeTime = function ($rawDate): string {
        $ts = strtotime((string)$rawDate);
        if (!$ts) {
            return '';
        }
        $diff = time() - $ts;
        if ($diff < 60) {
            return 'just now';
        }
        if ($diff < 3600) {
            $m = (int)floor($diff / 60);
            return $m . ' min ago';
        }
        if ($diff < 86400) {
            $h = (int)floor($diff / 3600);
            return $h . ' hr ago';
        }
        if ($diff < 604800) {
            $d = (int)floor($diff / 86400);
            return $d . ' day' . ($d > 1 ? 's' : '') . ' ago';
        }
        return date('d M Y', $ts);
    };

    $messages = [];
    foreach ($rows as $row) {
        $rawDate = (string)($row['date'] ?? '');
        $messages[] = [
            'id' => (int)($row['id'] ?? 0),
            'sender' => (string)($row['sender_name'] ?? 'System'),
            'subject' => (string)($row['subject'] ?? 'Notification'),
            'snippet' => mb_substr(trim((string)($row['msg'] ?? '')), 0, 80),
            'isRead' => ((int)($row['is_read'] ?? 0) === 1),
            'date' => $rawDate,
            'relativeDate' => $formatRelativeTime($rawDate),
        ];
    }

    echo json_encode([
        'ok' => true,
        'fullName' => $fullName,
        'photo' => $photo,
        'badgeCode' => $badgeCode,
        'badgeSrc' => $badgeSrc,
        'messageCount' => $total,
        'messages' => $messages,
        'translatorOptions' => array_values($translatorOptions),
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'server_error']);
}
