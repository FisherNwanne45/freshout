<?php
session_start();
require_once __DIR__ . '/class.admin.php';
include_once __DIR__ . '/session.php';
require_once dirname(__DIR__, 2) . '/config.php';
$authThemeFile = dirname(__DIR__) . '/auth-theme.php';
if (file_exists($authThemeFile)) {
    require_once $authThemeFile;
}

// Check admin access
if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit();
}

// Handle SMTP configuration update
$message = '';
$alert_type = '';

function ss_get(mysqli $conn, $key, $default = '') {
    $safe = $conn->real_escape_string($key);
    try {
        $res = $conn->query("SELECT setting_value FROM site_settings WHERE setting_key='" . $safe . "' LIMIT 1");
        if ($res && $res->num_rows > 0) {
            $row = $res->fetch_assoc();
            return $row['setting_value'];
        }
    } catch (Throwable $e) {
    }

    try {
        $legacy = $conn->query("SELECT `value` FROM site_settings WHERE `key`='" . $safe . "' LIMIT 1");
        if ($legacy && $legacy->num_rows > 0) {
            $row = $legacy->fetch_assoc();
            return $row['value'];
        }
    } catch (Throwable $e) {
    }

    return $default;
}

function ss_set(mysqli $conn, $key, $value) {
    try {
        $stmt = $conn->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        if ($stmt) {
            $stmt->bind_param('ss', $key, $value);
            $ok = $stmt->execute();
            $stmt->close();
            if ($ok) {
                return true;
            }
        }
    } catch (Throwable $e) {
    }

    $safeKey = $conn->real_escape_string($key);
    $safeVal = $conn->real_escape_string((string)$value);
    try {
        $conn->query("INSERT INTO site_settings (`key`, `value`) VALUES ('" . $safeKey . "', '" . $safeVal . "') ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");
        return !$conn->error;
    } catch (Throwable $e) {
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_smtp'])) {
    $smtp_host = trim($_POST['smtp_host'] ?? '');
    $smtp_port = intval($_POST['smtp_port'] ?? 465);
    $smtp_secure = trim($_POST['smtp_secure'] ?? 'ssl');
    $smtp_username = trim($_POST['smtp_username'] ?? '');
    $smtp_password = trim($_POST['smtp_password'] ?? '');
    $smtp_from = trim($_POST['smtp_from'] ?? '');
    $smtp_from_name = trim($_POST['smtp_from_name'] ?? '');
    $smtp_reply_to = trim($_POST['smtp_reply_to'] ?? '');

    if (empty($smtp_host) || empty($smtp_username) || empty($smtp_from)) {
        $alert_type = 'danger';
        $message = 'Host, Username, and From address are required.';
    } else {
        $settings = [
            'smtp_host' => $smtp_host,
            'smtp_port' => $smtp_port,
            'smtp_secure' => $smtp_secure,
            'smtp_username' => $smtp_username,
            'smtp_password' => $smtp_password,
            'smtp_from' => $smtp_from,
            'smtp_from_name' => $smtp_from_name,
            'smtp_reply_to' => $smtp_reply_to,
        ];
        
        $success = true;
        foreach ($settings as $key => $value) {
            if (!ss_set($conn, $key, (string)$value)) {
                $success = false;
                break;
            }
        }
        
        if ($success) {
            $alert_type = 'success';
            $message = 'SMTP settings updated successfully!';
        } else {
            $alert_type = 'danger';
            $message = 'Failed to save SMTP settings.';
        }
    }
}

// Load current SMTP settings
$smtp_settings = [
    'host' => ss_get($conn, 'smtp_host', $APP_CONFIG['smtp']['host'] ?? 'smtp.gmail.com'),
    'port' => (int)ss_get($conn, 'smtp_port', (string)($APP_CONFIG['smtp']['port'] ?? 465)),
    'secure' => ss_get($conn, 'smtp_secure', $APP_CONFIG['smtp']['secure'] ?? 'ssl'),
    'username' => ss_get($conn, 'smtp_username', $APP_CONFIG['smtp']['username'] ?? ''),
    'password' => ss_get($conn, 'smtp_password', $APP_CONFIG['smtp']['password'] ?? ''),
    'from' => ss_get($conn, 'smtp_from', $APP_CONFIG['smtp']['from'] ?? ''),
    'from_name' => ss_get($conn, 'smtp_from_name', $APP_CONFIG['smtp']['from_name'] ?? 'Banking System'),
    'reply_to' => ss_get($conn, 'smtp_reply_to', $APP_CONFIG['smtp']['reply_to'] ?? ''),
];

// Get site info
$site = null;
$res = $conn->query("SELECT * FROM site LIMIT 1");
if ($res && $res->num_rows > 0) {
    $site = $res->fetch_assoc();
}

$palette = [
    'navy' => '#0d1f3c',
    'navy2' => '#162847',
    'gold' => '#c9a84c',
    'light' => '#f5f6fa',
    'muted' => '#8895a7',
    'border' => '#dce3ec',
    'danger' => '#c0392b',
    'success' => '#1a7a4a'
];
if (function_exists('get_auth_color_scheme') && function_exists('get_auth_palette') && $conn instanceof mysqli) {
    $authScheme = get_auth_color_scheme($conn);
    $palette = get_auth_palette($authScheme);
}
$bankName = $site ? htmlspecialchars($site['name']) : 'Secure Banking';
$pageTitle = 'SMTP Settings';
require_once __DIR__ . '/partials/admin-shell-open.php';
?>
<?php if($message): ?>
<div class="mb-4 px-4 py-3 rounded-lg text-sm <?= $alert_type==='success' ? 'bg-green-50 border border-green-200 text-green-700' : 'bg-red-50 border border-red-200 text-red-700' ?>"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 max-w-2xl">
  <h2 class="font-semibold text-gray-800 mb-5">SMTP Configuration</h2>
  <form method="POST" class="space-y-4">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div class="sm:col-span-2"><label class="block text-xs font-medium text-gray-700 mb-1">SMTP Host</label>
        <input type="text" name="smtp_host" value="<?= htmlspecialchars($smtp_settings['host']) ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="smtp.gmail.com" required></div>
      <div><label class="block text-xs font-medium text-gray-700 mb-1">Port</label>
        <input type="number" name="smtp_port" value="<?= htmlspecialchars((string)$smtp_settings['port']) ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="465"></div>
      <div><label class="block text-xs font-medium text-gray-700 mb-1">Encryption</label>
        <select name="smtp_secure" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="ssl" <?= $smtp_settings['secure']==='ssl'?'selected':'' ?>>SSL</option>
          <option value="tls" <?= $smtp_settings['secure']==='tls'?'selected':'' ?>>TLS</option>
          <option value="" <?= $smtp_settings['secure']===''?'selected':'' ?>>None</option>
        </select></div>
      <div><label class="block text-xs font-medium text-gray-700 mb-1">SMTP Username</label>
        <input type="text" name="smtp_username" value="<?= htmlspecialchars($smtp_settings['username']) ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required></div>
      <div><label class="block text-xs font-medium text-gray-700 mb-1">SMTP Password</label>
        <input type="password" name="smtp_password" value="<?= htmlspecialchars($smtp_settings['password']) ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
      <div><label class="block text-xs font-medium text-gray-700 mb-1">From Email</label>
        <input type="email" name="smtp_from" value="<?= htmlspecialchars($smtp_settings['from']) ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required></div>
      <div><label class="block text-xs font-medium text-gray-700 mb-1">From Name</label>
        <input type="text" name="smtp_from_name" value="<?= htmlspecialchars($smtp_settings['from_name']) ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
      <div class="sm:col-span-2"><label class="block text-xs font-medium text-gray-700 mb-1">Reply-To Email</label>
        <input type="email" name="smtp_reply_to" value="<?= htmlspecialchars($smtp_settings['reply_to']) ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
    </div>
    <div class="pt-2">
      <button type="submit" name="save_smtp" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors cursor-pointer">Save SMTP Settings</button>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/partials/admin-shell-close.php'; ?>
