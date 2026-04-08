<?php

if (!function_exists('fw_setting_get')) {
    function fw_setting_get(mysqli $conn, string $key, string $default = ''): string {
        $safe = $conn->real_escape_string($key);
        try {
            $cols = [];
            $colRes = $conn->query("SHOW COLUMNS FROM site_settings");
            if ($colRes) {
                while ($col = $colRes->fetch_assoc()) {
                    $name = strtolower((string)($col['Field'] ?? ''));
                    if ($name !== '') {
                        $cols[$name] = true;
                    }
                }
            }

            $k = isset($cols['setting_key']) ? 'setting_key' : 'key';
            $v = isset($cols['setting_value']) ? 'setting_value' : 'value';

            $res = $conn->query("SELECT `{$v}` AS setting_value FROM site_settings WHERE `{$k}` = '{$safe}' LIMIT 1");
            if ($res && $res->num_rows > 0) {
                $row = $res->fetch_assoc();
                return (string)($row['setting_value'] ?? $default);
            }
        } catch (Throwable $e) {
            return $default;
        }
        return $default;
    }
}

if (!function_exists('fw_customer_accounts_has_iban_columns')) {
    function fw_customer_accounts_has_iban_columns(mysqli $conn): bool {
        static $hasCols = null;
        if ($hasCols !== null) {
            return $hasCols;
        }

        $needed = ['iban', 'bban', 'account_display'];
        $seen = [];
        $res = $conn->query("SHOW COLUMNS FROM customer_accounts");
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $name = strtolower((string)($row['Field'] ?? ''));
                if ($name !== '') {
                    $seen[$name] = true;
                }
            }
        }

        foreach ($needed as $col) {
            if (!isset($seen[$col])) {
                $hasCols = false;
                return false;
            }
        }

        $hasCols = true;
        return true;
    }
}

if (!function_exists('fw_iban_mod97')) {
    function fw_iban_mod97(string $input): int {
        $remainder = 0;
        $len = strlen($input);
        for ($i = 0; $i < $len; $i++) {
            $char = $input[$i];
            if ($char >= '0' && $char <= '9') {
                $remainder = (($remainder * 10) + (int)$char) % 97;
                continue;
            }
            if ($char >= 'A' && $char <= 'Z') {
                $val = (string)(ord($char) - 55); // A=10 ... Z=35
                $remainder = (($remainder * 10) + (int)$val[0]) % 97;
                if (strlen($val) > 1) {
                    $remainder = (($remainder * 10) + (int)$val[1]) % 97;
                }
            }
        }
        return $remainder;
    }
}

if (!function_exists('fw_generate_iban')) {
    function fw_generate_iban(string $ownerAccNo, string $currencyCode, int $walletId, string $countryCode = 'GB', string $bankCode = 'FWLT'): array {
        $countryCode = strtoupper(preg_replace('/[^A-Z]/', '', $countryCode));
        if (strlen($countryCode) !== 2) {
            $countryCode = 'GB';
        }

        $bankCode = strtoupper(preg_replace('/[^A-Z0-9]/', '', $bankCode));
        if ($bankCode === '') {
            $bankCode = 'FWLT';
        }
        $bankCode = substr(str_pad($bankCode, 4, 'X'), 0, 4);

        $ownerAccNo = preg_replace('/[^0-9A-Z]/', '', strtoupper($ownerAccNo));
        $currencyCode = preg_replace('/[^0-9A-Z]/', '', strtoupper($currencyCode));

        $seed = strtoupper(hash('sha256', $ownerAccNo . '|' . $currencyCode . '|' . (string)$walletId));
        $bbanTail = substr($seed, 0, 14);
        $bban = $bankCode . $bbanTail;

        $checkBase = $bban . $countryCode . '00';
        $mod = fw_iban_mod97($checkBase);
        $checkDigits = 98 - $mod;
        $check = str_pad((string)$checkDigits, 2, '0', STR_PAD_LEFT);

        $iban = $countryCode . $check . $bban;

        return [
            'iban' => $iban,
            'bban' => $bban,
            'display' => trim(chunk_split($iban, 4, ' ')),
        ];
    }
}
