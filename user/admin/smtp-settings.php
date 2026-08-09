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

$templateHelperPath = dirname(__DIR__) . '/notification-template-helper.php';
if (file_exists($templateHelperPath)) {
    require_once $templateHelperPath;
}
$smtp_notification_templates = function_exists('notification_template_catalog')
    ? notification_template_catalog()
    : [];
$smtp_notification_defaults = function_exists('notification_template_default_overrides')
    ? notification_template_default_overrides()
    : [];

// Handle SMTP configuration update
$message = '';
$alert_type = '';

function ss_get(mysqli $conn, $key, $default = '')
{
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

function ss_set(mysqli $conn, $key, $value)
{
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_test_email'])) {
    $test_to       = filter_var(trim($_POST['test_email_to'] ?? ''), FILTER_VALIDATE_EMAIL);
    $test_tpl_key  = preg_replace('/[^a-z0-9_]/', '', trim($_POST['test_template'] ?? 'registration_welcome'));

    if (!$test_to) {
        $alert_type = 'danger';
        $message    = 'Please enter a valid recipient email address.';
    } else {
        // Resolve subject/body from DB or defaults
        $test_meta        = $smtp_notification_templates[$test_tpl_key] ?? ['name' => 'Test', 'default_subject' => 'Test Email from {{bank_name}}'];
        $test_subject_raw = (string)ss_get(
            $conn,
            'notify_tpl_subject_' . $test_tpl_key,
            (string)($smtp_notification_defaults[$test_tpl_key]['subject'] ?? $test_meta['default_subject'] ?? 'Test Email from {{bank_name}}')
        );
        $test_body_raw    = (string)ss_get(
            $conn,
            'notify_tpl_body_' . $test_tpl_key,
            (string)($smtp_notification_defaults[$test_tpl_key]['body'] ?? '<p>This is a test email from <strong>{{bank_name}}</strong>.</p>')
        );

        // Sample data for placeholder replacement
        $test_site_r   = $conn->query('SELECT * FROM site LIMIT 1');
        $test_site_row = ($test_site_r && $test_site_r->num_rows > 0) ? $test_site_r->fetch_assoc() : [];
        $test_bank     = htmlspecialchars($test_site_row['name'] ?? ($bankName ?? 'Banking System'));
        $test_support  = $test_site_row['email'] ?? 'support@example.com';
        $test_base_url = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http')
            . '://' . preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? 'localhost');

        $test_ctx = ['bank_name' => $test_bank, 'support_email' => $test_support, 'site_url' => $test_base_url];
        $test_tokens = array_merge($test_ctx, [
            'fname' => 'Test',
            'lname' => 'User',
            'name' => 'Test User',
            'acc_no' => '0000001234',
            'amount' => '1,250.00',
            'currency' => 'USD',
            'balance' => '8,450.00',
            'description' => 'Test Transaction',
            'date' => date('Y-m-d'),
            'type_label' => 'Savings',
            'reason' => 'Insufficient documentation',
            'ticket_id' => '#TKT-00042',
            'subject' => $test_subject_raw,
            'creation_date' => date('Y-m-d'),
            'loan_id' => '#LOAN-00017',
            'purpose' => 'Home Renovation',
            'transaction_type' => 'Domestic Transfer',
            'status' => 'Approved',
            'otp' => '839271',
            'expiry_min' => '10',
            'email' => $to = $test_to,
            'phone' => '+1-555-0100',
            'type' => 'Checking',
            'work' => 'Software Engineer',
            'addr' => '123 Main St',
            'city' => 'Springfield',
            'state' => 'IL',
            'nation' => 'USA',
            'zip' => '62701',
            'uname' => 'test.user',
            'department' => 'General',
            'comments' => 'This is a test message.',
            'year' => date('Y'),
            'today' => date('Y-m-d'),
        ]);

        if (function_exists('notification_template_replace_tokens')) {
            $test_subject  = notification_template_replace_tokens($test_subject_raw, $test_tokens);
            $test_body_c   = notification_template_replace_tokens($test_body_raw, $test_tokens);
            $test_body_html = function_exists('notification_template_wrap_html')
                ? notification_template_wrap_html($test_subject, $test_body_c, $test_ctx)
                : $test_body_c;
        } else {
            $test_subject   = $test_subject_raw;
            $test_body_html = $test_body_raw;
        }

        // Send via PHPMailer
        // Send via PHPMailer (Manually fetch from database using ss_get)
        $smtp_cfg = [
            'host'      => ss_get($conn, 'smtp_host'),
            'port'      => ss_get($conn, 'smtp_port'),
            'username'  => ss_get($conn, 'smtp_username'),
            'password'  => ss_get($conn, 'smtp_password'),
            'secure'    => ss_get($conn, 'smtp_secure'),
            'from'      => ss_get($conn, 'smtp_from'),
            'from_name' => ss_get($conn, 'smtp_from_name'),
            'reply_to'  => ss_get($conn, 'smtp_reply_to'),
        ];

        $emailSent  = false;

        $emailError = '';

        if (!empty($smtp_cfg['host'])) {
            try {
                if (!defined('FILTER_FLAG_HOST_REQUIRED'))                      define('FILTER_FLAG_HOST_REQUIRED', 0);
                if (!defined('PHPMailer\\PHPMailer\\FILTER_FLAG_HOST_REQUIRED')) define('PHPMailer\\PHPMailer\\FILTER_FLAG_HOST_REQUIRED', 0);
                $pmDir = __DIR__ . '/PHPMailer-master/src/';
                require_once $pmDir . 'Exception.php';
                require_once $pmDir . 'PHPMailer.php';
                require_once $pmDir . 'SMTP.php';

                $mailer = new \PHPMailer\PHPMailer\PHPMailer(true);

                $mailer->isSMTP();
                $mailer->Host     = (string)$smtp_cfg['host'];
                $mailer->Port     = (int)($smtp_cfg['port'] ?? 465);
                $mailer->SMTPAuth = true;
                $mailer->Username = (string)($smtp_cfg['username'] ?? '');
                $mailer->Password = (string)($smtp_cfg['password'] ?? '');
                $raw_secure = (string)($smtp_cfg['secure'] ?? 'ssl');
                // 'starttls' is stored as an alias for PHPMailer's 'tls' (STARTTLS)
                $pm_secure = ($raw_secure === 'starttls') ? 'tls' : $raw_secure;
                $mailer->SMTPSecure = in_array($pm_secure, ['ssl', 'tls'], true) ? $pm_secure : '';
                $smtp_from_addr = (string)($smtp_cfg['from'] ?? '');
                $smtp_from_name = (string)($smtp_cfg['from_name'] ?? $test_bank);
                $smtp_reply_to  = (string)($smtp_cfg['reply_to'] ?? $smtp_from_addr);
                $mailer->setFrom(
                    $smtp_from_addr ?: ('noreply@' . preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? 'localhost')),
                    $smtp_from_name
                );
                $mailer->addAddress($test_to);
                $mailer->addReplyTo($smtp_reply_to ?: $smtp_from_addr);
                $mailer->isHTML(true);
                $mailer->Subject = '[TEST] ' . $test_subject;
                $mailer->Body    = $test_body_html;
                $emailSent = $mailer->send();
            } catch (\Throwable $e) {
                $emailError = $e->getMessage();
            }
        }

        if ($emailSent) {
            $alert_type = 'success';
          $message    = 'Test email sent to ' . htmlspecialchars($test_to) . ' using the "' . htmlspecialchars($test_meta['name'] ?? $test_tpl_key) . '" template.';
        } elseif (empty($smtp_cfg['host'])) {
            $alert_type = 'danger';
            $message    = 'No SMTP host configured. Save your SMTP settings first.';
        } else {
            $alert_type = 'danger';
            $message    = 'Failed to send test email.' . ($emailError ? ' Error: ' . htmlspecialchars($emailError) : '');
        }
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

<!-- ── Top action bar ───────────────────────────────────────── -->
<div class="mb-5">
    <a href="notification-settings.php?tab=templates"
        class="inline-flex items-center gap-2 bg-white border border-gray-300 hover:border-blue-400 hover:bg-blue-50 text-gray-700 hover:text-blue-700 text-sm font-medium px-4 py-2 rounded-lg transition-colors shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
        </svg>
        Manage Notification Templates
    </a>
</div>

<?php if ($message): ?>
    <div
        class="mb-4 px-4 py-3 rounded-lg text-sm <?= $alert_type === 'success' ? 'bg-green-50 border border-green-200 text-green-700' : 'bg-red-50 border border-red-200 text-red-700' ?>">
        <?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="flex flex-col xl:flex-row gap-6 items-start">

    <!-- ── SMTP Configuration ────────────────────────────────── -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex-1 min-w-0">
        <h2 class="font-semibold text-gray-800 mb-5">SMTP Configuration</h2>
        <form method="POST" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2"><label class="block text-xs font-medium text-gray-700 mb-1">SMTP Host</label>
                    <input type="text" name="smtp_host" value="<?= htmlspecialchars($smtp_settings['host']) ?>"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="smtp.gmail.com" required>
                </div>
                <div><label class="block text-xs font-medium text-gray-700 mb-1">Port</label>
                    <input type="number" name="smtp_port"
                        value="<?= htmlspecialchars((string)$smtp_settings['port']) ?>"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="465">
                </div>
                <div><label class="block text-xs font-medium text-gray-700 mb-1">Encryption</label>
                    <select name="smtp_secure"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="ssl" <?= $smtp_settings['secure'] === 'ssl'      ? 'selected' : '' ?>>SSL / TLS
                            (port 465)</option>
                        <option value="starttls" <?= $smtp_settings['secure'] === 'starttls' ? 'selected' : '' ?>>
                            STARTTLS (port 587)</option>
                        <option value="tls" <?= $smtp_settings['secure'] === 'tls'      ? 'selected' : '' ?>>TLS
                        </option>
                        <option value="" <?= $smtp_settings['secure'] === ''         ? 'selected' : '' ?>>None</option>
                    </select>
                </div>
                <div><label class="block text-xs font-medium text-gray-700 mb-1">SMTP Username</label>
                    <input type="text" name="smtp_username" value="<?= htmlspecialchars($smtp_settings['username']) ?>"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required>
                </div>
                <div><label class="block text-xs font-medium text-gray-700 mb-1">SMTP Password</label>
                    <input type="password" name="smtp_password"
                        value="<?= htmlspecialchars($smtp_settings['password']) ?>"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div><label class="block text-xs font-medium text-gray-700 mb-1">From Email</label>
                    <input type="email" name="smtp_from" value="<?= htmlspecialchars($smtp_settings['from']) ?>"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required>
                </div>
                <div><label class="block text-xs font-medium text-gray-700 mb-1">From Name</label>
                    <input type="text" name="smtp_from_name"
                        value="<?= htmlspecialchars($smtp_settings['from_name']) ?>"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="sm:col-span-2"><label class="block text-xs font-medium text-gray-700 mb-1">Reply-To
                        Email</label>
                    <input type="email" name="smtp_reply_to" value="<?= htmlspecialchars($smtp_settings['reply_to']) ?>"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div class="pt-2">
                <button type="submit" name="save_smtp"
                    class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors cursor-pointer">Save
                    SMTP Settings</button>
            </div>
        </form>
    </div>

    <!-- ── Send Test Email ───────────────────────────────────── -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 xl:w-80 flex-shrink-0 w-full">
        <h2 class="font-semibold text-gray-800 mb-1">Send Test Email</h2>
        <p class="text-xs text-gray-500 mb-5">Send a test message using any notification template with your current SMTP
            settings. Sample placeholder data is substituted automatically.</p>
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Recipient Email</label>
                <input type="email" name="test_email_to" required
                    value="<?= htmlspecialchars($_POST['test_email_to'] ?? $_SESSION['email'] ?? '') ?>"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="you@example.com">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Template</label>
                <select name="test_template"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <?php foreach ($smtp_notification_templates as $tpl_key => $tpl_meta): ?>
                        <option value="<?= htmlspecialchars($tpl_key) ?>"
                            <?= (($_POST['test_template'] ?? 'registration_welcome') === $tpl_key) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($tpl_meta['name'] ?? $tpl_key) ?>
                        </option>
                    <?php endforeach; ?>
                    <?php if (empty($smtp_notification_templates)): ?>
                        <option value="registration_welcome">Registration Welcome</option>
                    <?php endif; ?>
                </select>
            </div>
            <div class="pt-2">
                <button type="submit" name="send_test_email"
                    class="w-full inline-flex items-center justify-center gap-2 bg-gray-700 hover:bg-gray-800 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                    Send Test Email
                </button>
            </div>
        </form>
    </div>

</div>

<?php require_once __DIR__ . '/partials/admin-shell-close.php'; ?>