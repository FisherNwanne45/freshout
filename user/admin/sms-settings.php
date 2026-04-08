<?php
session_start();
require_once __DIR__ . '/class.admin.php';
include_once __DIR__ . '/session.php';
require_once dirname(__DIR__, 2) . '/config.php';
$authThemeFile = dirname(__DIR__) . '/auth-theme.php';
if (file_exists($authThemeFile)) {
    require_once $authThemeFile;
}

if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit();
}

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
    $safeVal = $conn->real_escape_string($value);
    try {
        $conn->query("INSERT INTO site_settings (`key`, `value`) VALUES ('" . $safeKey . "', '" . $safeVal . "') ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");
        return !$conn->error;
    } catch (Throwable $e) {
        return false;
    }
}

$message = '';
$alert_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_sms'])) {
    $settings = [
        'sms_enabled' => isset($_POST['sms_enabled']) ? '1' : '0',
        'sms_provider' => trim($_POST['sms_provider'] ?? 'textbelt'),
        'sms_brand_name' => trim($_POST['sms_brand_name'] ?? 'Banking System'),
        'twilio_sid' => trim($_POST['twilio_sid'] ?? ''),
        'twilio_token' => trim($_POST['twilio_token'] ?? ''),
        'twilio_from' => trim($_POST['twilio_from'] ?? ''),
        'termii_api_key' => trim($_POST['termii_api_key'] ?? ''),
        'termii_sender' => trim($_POST['termii_sender'] ?? 'N-Alert'),
        'textbelt_key' => trim($_POST['textbelt_key'] ?? 'textbelt')
    ];

    $ok = true;
    foreach ($settings as $k => $v) {
        if (!ss_set($conn, $k, $v)) {
            $ok = false;
            break;
        }
    }

    if ($ok) {
        $alert_type = 'success';
        $message = 'SMS gateway settings saved successfully.';
    } else {
        $alert_type = 'danger';
        $message = 'Failed to save SMS gateway settings.';
    }
}

$current = [
    'sms_enabled' => ss_get($conn, 'sms_enabled', '0'),
    'sms_provider' => ss_get($conn, 'sms_provider', 'textbelt'),
    'sms_brand_name' => ss_get($conn, 'sms_brand_name', 'Banking System'),
    'twilio_sid' => ss_get($conn, 'twilio_sid', ''),
    'twilio_token' => ss_get($conn, 'twilio_token', ''),
    'twilio_from' => ss_get($conn, 'twilio_from', ''),
    'termii_api_key' => ss_get($conn, 'termii_api_key', ''),
    'termii_sender' => ss_get($conn, 'termii_sender', 'N-Alert'),
    'textbelt_key' => ss_get($conn, 'textbelt_key', 'textbelt')
];

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
$pageTitle = 'SMS Gateway';
require_once __DIR__ . '/partials/admin-shell-open.php';
?>
<?php if($message): ?>
<div class="mb-4 px-4 py-3 rounded-lg text-sm <?= $alert_type==='success' ? 'bg-green-50 border border-green-200 text-green-700' : 'bg-red-50 border border-red-200 text-red-700' ?>"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 max-w-2xl">
  <h2 class="font-semibold text-gray-800 mb-5">SMS Gateway Settings</h2>
  <form method="POST" class="space-y-5">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div class="sm:col-span-2 flex items-center gap-3">
        <label class="relative inline-flex items-center cursor-pointer">
          <input type="checkbox" name="sms_enabled" <?= $current['sms_enabled']==='1'?'checked':'' ?> class="sr-only peer">
          <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600 after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
        </label>
        <span class="text-sm font-medium text-gray-700">Enable SMS Notifications</span>
      </div>
      <div><label class="block text-xs font-medium text-gray-700 mb-1">SMS Provider</label>
        <select name="sms_provider" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="showSmsProv(this.value)">
          <option value="textbelt" <?= $current['sms_provider']==='textbelt'?'selected':'' ?>>TextBelt</option>
          <option value="twilio" <?= $current['sms_provider']==='twilio'?'selected':'' ?>>Twilio</option>
          <option value="termii" <?= $current['sms_provider']==='termii'?'selected':'' ?>>Termii</option>
        </select></div>
      <div><label class="block text-xs font-medium text-gray-700 mb-1">Brand / Sender Name</label>
        <input type="text" name="sms_brand_name" value="<?= htmlspecialchars($current['sms_brand_name']) ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
    </div>
    <div id="prov-textbelt" class="border border-gray-200 rounded-xl p-4 <?= $current['sms_provider']!=='textbelt'?'hidden':'' ?>">
      <h3 class="text-xs font-semibold text-gray-600 uppercase mb-3">TextBelt Config</h3>
      <div><label class="block text-xs font-medium text-gray-700 mb-1">API Key</label>
        <input type="text" name="textbelt_key" value="<?= htmlspecialchars($current['textbelt_key']) ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="textbelt"></div>
    </div>
    <div id="prov-twilio" class="border border-gray-200 rounded-xl p-4 <?= $current['sms_provider']!=='twilio'?'hidden':'' ?>">
      <h3 class="text-xs font-semibold text-gray-600 uppercase mb-3">Twilio Config</h3>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div><label class="block text-xs font-medium text-gray-700 mb-1">Account SID</label>
          <input type="text" name="twilio_sid" value="<?= htmlspecialchars($current['twilio_sid']) ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
        <div><label class="block text-xs font-medium text-gray-700 mb-1">Auth Token</label>
          <input type="text" name="twilio_token" value="<?= htmlspecialchars($current['twilio_token']) ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
        <div><label class="block text-xs font-medium text-gray-700 mb-1">From Number</label>
          <input type="text" name="twilio_from" value="<?= htmlspecialchars($current['twilio_from']) ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="+1234567890"></div>
      </div>
    </div>
    <div id="prov-termii" class="border border-gray-200 rounded-xl p-4 <?= $current['sms_provider']!=='termii'?'hidden':'' ?>">
      <h3 class="text-xs font-semibold text-gray-600 uppercase mb-3">Termii Config</h3>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div><label class="block text-xs font-medium text-gray-700 mb-1">API Key</label>
          <input type="text" name="termii_api_key" value="<?= htmlspecialchars($current['termii_api_key']) ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
        <div><label class="block text-xs font-medium text-gray-700 mb-1">Sender ID</label>
          <input type="text" name="termii_sender" value="<?= htmlspecialchars($current['termii_sender']) ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="N-Alert"></div>
      </div>
    </div>
    <div><button type="submit" name="save_sms" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors cursor-pointer">Save SMS Settings</button></div>
  </form>
</div>
<script>
function showSmsProv(v) {
  ['textbelt','twilio','termii'].forEach(function(p) {
    document.getElementById('prov-'+p).classList.toggle('hidden', p!==v);
  });
}
</script>

<?php require_once __DIR__ . '/partials/admin-shell-close.php'; ?>
