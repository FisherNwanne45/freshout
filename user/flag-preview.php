<?php
$code = strtoupper(trim((string)($_GET['code'] ?? '')));
if (!preg_match('/^[A-Z0-9]{2,10}$/', $code)) {
    $code = '';
}

header('Content-Type: image/svg+xml; charset=UTF-8');
header('Cache-Control: public, max-age=86400');

$crypto = [
    'BTC' => ['bg' => '#f7931a', 'fg' => '#ffffff', 'label' => 'BTC'],
    'ETH' => ['bg' => '#627eea', 'fg' => '#ffffff', 'label' => 'ETH'],
    'USDT' => ['bg' => '#26a17b', 'fg' => '#ffffff', 'label' => 'USDT'],
    'XRP' => ['bg' => '#23292f', 'fg' => '#ffffff', 'label' => 'XRP'],
    'LTC' => ['bg' => '#345d9d', 'fg' => '#ffffff', 'label' => 'LTC'],
    'BNB' => ['bg' => '#f3ba2f', 'fg' => '#111827', 'label' => 'BNB'],
    'SOL' => ['bg' => '#111827', 'fg' => '#6dffa7', 'label' => 'SOL'],
    'USDC' => ['bg' => '#2775ca', 'fg' => '#ffffff', 'label' => 'USDC'],
];

$fiatToFlag = [
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
    'NOK' => 'NO',
    'SEK' => 'SE',
    'DKK' => 'DK',
    'NZD' => 'NZ',
];

if ($code !== '' && isset($fiatToFlag[$code])) {
    $code = $fiatToFlag[$code];
}

if ($code !== '' && isset($crypto[$code])) {
    $meta = $crypto[$code];
    $label = htmlspecialchars($meta['label'], ENT_QUOTES, 'UTF-8');
    echo '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="28" viewBox="0 0 40 28">';
    echo '<rect x="1" y="1" width="38" height="26" rx="6" fill="' . $meta['bg'] . '" stroke="#cbd5e1"/>';
    echo '<text x="20" y="18" text-anchor="middle" fill="' . $meta['fg'] . '" font-size="9" font-family="Arial, sans-serif" font-weight="700">' . $label . '</text>';
    echo '</svg>';
    exit();
}

if (preg_match('/^[A-Z]{2}$/', $code)) {
    $flagPath = __DIR__ . '/assets/flags/' . strtolower($code) . '.svg';
    if (is_file($flagPath)) {
        readfile($flagPath);
        exit();
    }
}

$fallback = $code === '' ? '--' : substr($code, 0, 6);
$fallback = htmlspecialchars($fallback, ENT_QUOTES, 'UTF-8');
echo '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="28" viewBox="0 0 40 28">';
echo '<rect x="1" y="1" width="38" height="26" rx="6" fill="#f8fafc" stroke="#cbd5e1"/>';
echo '<text x="20" y="18" text-anchor="middle" fill="#334155" font-size="9" font-family="Arial, sans-serif" font-weight="700">' . $fallback . '</text>';
echo '</svg>';
