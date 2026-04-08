<?php

class SmsGateway
{
    private $db;
    private $appConfig;

    public function __construct($db, $appConfig = [])
    {
        $this->db = $db;
        $this->appConfig = is_array($appConfig) ? $appConfig : [];
    }

    public function sendTemplate($phone, $templateType, $data = [], $subject = '')
    {
        $message = $this->buildMessage($templateType, $data, $subject);
        return $this->send($phone, $message);
    }

    public function send($phone, $message)
    {
        $enabled = $this->getSetting('sms_enabled', $this->appConfig['sms']['enabled'] ?? '0');
        if (!$this->isTruthy($enabled)) {
            error_log('SMS gateway is disabled.');
            return false;
        }

        $normalizedPhone = $this->normalizePhone($phone);
        if ($normalizedPhone === '') {
            error_log('SMS send aborted: empty phone number.');
            return false;
        }

        $provider = strtolower((string)$this->getSetting('sms_provider', $this->appConfig['sms']['provider'] ?? 'textbelt'));
        switch ($provider) {
            case 'twilio':
                return $this->sendViaTwilio($normalizedPhone, $message);
            case 'termii':
                return $this->sendViaTermii($normalizedPhone, $message);
            case 'textbelt':
            default:
                return $this->sendViaTextbelt($normalizedPhone, $message);
        }
    }

    private function buildMessage($templateType, $data, $subject)
    {
        $bankName = (string)$this->getSetting('sms_brand_name', $this->appConfig['sms']['brand_name'] ?? 'Banking System');
        $type = (string)$templateType;

        if ($type === 'registration_welcome') {
            $name = trim((string)($data['fname'] ?? 'Customer'));
            $acc = (string)($data['acc_no'] ?? '');
            return "{$bankName}: Welcome {$name}. Your account {$acc} is ready. Log in to get started.";
        }

        if ($type === 'debit_alert') {
            $amount = (string)($data['amount'] ?? '0');
            $currency = (string)($data['currency'] ?? '');
            $bal = (string)($data['balance'] ?? '0');
            return "{$bankName} Debit Alert: {$currency} {$amount} debited. Bal: {$currency} {$bal}.";
        }

        if ($type === 'application_approved') {
            return "{$bankName}: Your account application was approved. You can now sign in.";
        }

        if ($type === 'application_declined') {
            return "{$bankName}: Your account application was declined. Contact support for details.";
        }

        if ($type === 'otp_code') {
            $otp = (string)($data['otp'] ?? '');
            $expiry = (string)($data['expiry_min'] ?? '10');
            return "{$bankName}: Your OTP is {$otp}. Expires in {$expiry} minutes.";
        }

        if ($type === 'ticket_created') {
            $ticket = (string)($data['ticket_id'] ?? '');
            return "{$bankName}: Ticket {$ticket} was created. Our team will respond shortly.";
        }

        if ($type === 'loan_alert') {
            $loanId = (string)($data['loan_id'] ?? '');
            return "{$bankName}: Loan request {$loanId} received and under review.";
        }

        if ($type === 'transaction_alert') {
            $t = (string)($data['transaction_type'] ?? 'Transaction');
            $amount = (string)($data['amount'] ?? '0');
            $currency = (string)($data['currency'] ?? '');
            return "{$bankName}: {$t} alert. Amount: {$currency} {$amount}.";
        }

        $base = trim((string)$subject) !== '' ? trim((string)$subject) : 'Notification';
        return "{$bankName}: {$base}";
    }

    private function sendViaTwilio($to, $message)
    {
        if (!function_exists('curl_init')) {
            error_log('SMS Twilio failed: cURL extension not available.');
            return false;
        }

        $sid = (string)$this->getSetting('twilio_sid', $this->appConfig['sms']['twilio_sid'] ?? '');
        $token = (string)$this->getSetting('twilio_token', $this->appConfig['sms']['twilio_token'] ?? '');
        $from = (string)$this->getSetting('twilio_from', $this->appConfig['sms']['twilio_from'] ?? '');

        if ($sid === '' || $token === '' || $from === '') {
            error_log('SMS Twilio failed: missing credentials.');
            return false;
        }

        $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";
        $payload = http_build_query([
            'To' => $to,
            'From' => $from,
            'Body' => $message
        ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_USERPWD, $sid . ':' . $token);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);

        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            return true;
        }

        error_log('SMS Twilio failed: HTTP ' . $httpCode . ' ' . $err . ' ' . (string)$response);
        return false;
    }

    private function sendViaTermii($to, $message)
    {
        if (!function_exists('curl_init')) {
            error_log('SMS Termii failed: cURL extension not available.');
            return false;
        }

        $apiKey = (string)$this->getSetting('termii_api_key', $this->appConfig['sms']['termii_api_key'] ?? '');
        $from = (string)$this->getSetting('termii_sender', $this->appConfig['sms']['termii_sender'] ?? 'N-Alert');

        if ($apiKey === '') {
            error_log('SMS Termii failed: missing api key.');
            return false;
        }

        $url = 'https://api.ng.termii.com/api/sms/send';
        $payload = json_encode([
            'api_key' => $apiKey,
            'to' => $to,
            'from' => $from,
            'sms' => $message,
            'type' => 'plain',
            'channel' => 'generic'
        ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);

        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            return true;
        }

        error_log('SMS Termii failed: HTTP ' . $httpCode . ' ' . $err . ' ' . (string)$response);
        return false;
    }

    private function sendViaTextbelt($to, $message)
    {
        if (!function_exists('curl_init')) {
            error_log('SMS Textbelt failed: cURL extension not available.');
            return false;
        }

        $key = (string)$this->getSetting('textbelt_key', $this->appConfig['sms']['textbelt_key'] ?? 'textbelt');
        $url = 'https://textbelt.com/text';
        $payload = http_build_query([
            'phone' => $to,
            'message' => $message,
            'key' => $key
        ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);

        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            $parsed = json_decode((string)$response, true);
            if (is_array($parsed) && !empty($parsed['success'])) {
                return true;
            }
        }

        error_log('SMS Textbelt failed: HTTP ' . $httpCode . ' ' . $err . ' ' . (string)$response);
        return false;
    }

    private function normalizePhone($phone)
    {
        $p = trim((string)$phone);
        if ($p === '') {
            return '';
        }

        $p = preg_replace('/[^\d+]/', '', $p);
        if ($p === null) {
            return '';
        }

        if ($p !== '' && $p[0] !== '+') {
            if (strlen($p) === 11 && strpos($p, '0') === 0) {
                $p = '+234' . substr($p, 1);
            }
        }

        return $p;
    }

    private function isTruthy($value)
    {
        $v = strtolower(trim((string)$value));
        return in_array($v, ['1', 'true', 'yes', 'on', 'enabled'], true);
    }

    private function getSetting($key, $default = null)
    {
        $val = $this->querySetting('setting_key', 'setting_value', $key);
        if ($val !== null) {
            return $val;
        }

        $legacy = $this->querySetting('key', 'value', $key);
        if ($legacy !== null) {
            return $legacy;
        }

        return $default;
    }

    private function querySetting($keyCol, $valueCol, $key)
    {
        try {
            if ($this->db instanceof PDO) {
                $sql = "SELECT {$valueCol} AS v FROM site_settings WHERE {$keyCol} = :k LIMIT 1";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([':k' => $key]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return $row && array_key_exists('v', $row) ? $row['v'] : null;
            }

            if ($this->db instanceof mysqli) {
                $sql = "SELECT {$valueCol} AS v FROM site_settings WHERE {$keyCol} = ? LIMIT 1";
                $stmt = $this->db->prepare($sql);
                if (!$stmt) {
                    return null;
                }
                $stmt->bind_param('s', $key);
                $stmt->execute();
                $res = $stmt->get_result();
                $row = $res ? $res->fetch_assoc() : null;
                $stmt->close();
                return $row && array_key_exists('v', $row) ? $row['v'] : null;
            }
        } catch (Exception $e) {
            return null;
        }

        return null;
    }
}
