<?php
include __DIR__ . '/../themes/theme6/bootstrap.php';
echo json_encode([
    'name' => $name,
    'email' => $email,
    'email2' => $email2,
    'addr' => $addr,
    'phone' => $phone,
    'livechat' => $livechat,
    'logo_url' => $logo_url,
    'login' => $login,
    'register' => $register,
], JSON_UNESCAPED_SLASHES);
