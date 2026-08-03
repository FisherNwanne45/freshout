<?php
/**
 * Helper to provision customer_accounts entry with IBAN when a new wallet is created
 * Call this after successfully inserting into account_balances
 */

if (!function_exists('provision_wallet_iban')) {
    function provision_wallet_iban(mysqli $conn, string $ownerAccNo, string $currencyCode, $walletId = null): bool {
        if (!function_exists('fw_customer_accounts_has_iban_columns')) {
            return false; // IBAN columns don't exist
        }

        if (!fw_customer_accounts_has_iban_columns($conn)) {
            return false;
        }

        try {
            $walletNo = $ownerAccNo . '-' . strtoupper($currencyCode);
            $ownerEsc = $conn->real_escape_string($ownerAccNo);
            $codeEsc = $conn->real_escape_string($currencyCode);
            $walletNoEsc = $conn->real_escape_string($walletNo);

            // Insert or update customer_accounts row
            $conn->query("INSERT INTO customer_accounts (owner_acc_no, account_no, currency_code, balance, status, is_primary)
                          VALUES ('{$ownerEsc}', '{$walletNoEsc}', '{$codeEsc}', 0, 'active', 0)
                          ON DUPLICATE KEY UPDATE status = 'active'");

            // Fetch the wallet ID if not provided
            if ($walletId === null) {
                $walletRowRes = $conn->query("SELECT id FROM customer_accounts WHERE owner_acc_no = '{$ownerEsc}' AND currency_code = '{$codeEsc}' LIMIT 1");
                if ($walletRowRes && $walletRowRes->num_rows > 0) {
                    $walletRow = $walletRowRes->fetch_assoc();
                    $walletId = (int)($walletRow['id'] ?? 0);
                }
            }

            if ($walletId > 0) {
                // Generate IBAN
                if (!function_exists('fw_generate_iban')) {
                    require_once __DIR__ . '/iban-tools.php';
                }

                // Load IBAN settings
                if (!function_exists('fw_setting_get')) {
                    return false; // fw_setting_get should be in iban-tools.php
                }

                $ibanCountry = fw_setting_get($conn, 'iban_country', 'GB');
                $ibanBankCode = fw_setting_get($conn, 'iban_bank_code', 'FWLT');
                $ibanData = fw_generate_iban($ownerAccNo, $currencyCode, $walletId, $ibanCountry, $ibanBankCode);

                if (isset($ibanData['iban']) && !empty($ibanData['iban'])) {
                    $ibanEsc = $conn->real_escape_string($ibanData['iban']);
                    $bbanEsc = $conn->real_escape_string($ibanData['bban']);
                    $displayEsc = $conn->real_escape_string($ibanData['display']);
                    $conn->query("UPDATE customer_accounts
                                  SET iban = '{$ibanEsc}',
                                      bban = '{$bbanEsc}',
                                      account_display = '{$displayEsc}'
                                  WHERE id = {$walletId}");
                    return true;
                }
            }
        } catch (Throwable $e) {
            error_log('provision_wallet_iban error: ' . $e->getMessage());
            return false;
        }

        return false;
    }
}
