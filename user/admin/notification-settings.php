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

// Handle notification settings update
$message = '';
$alert_type = '';
$activeTab = $_POST['active_tab'] ?? ($_GET['tab'] ?? 'channels');

function ss_get(mysqli $conn, $key, $default = null) {
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

$templateHelperPath = dirname(__DIR__) . '/notification-template-helper.php';
if (file_exists($templateHelperPath)) {
    require_once $templateHelperPath;
}

$notification_templates = function_exists('notification_template_catalog')
    ? notification_template_catalog()
    : [
        'registration_welcome' => ['name' => 'Registration Welcome', 'description' => 'Welcome message after account registration.', 'default_subject' => 'Welcome to {{bank_name}}'],
        'debit_alert' => ['name' => 'Debit Alert', 'description' => 'Debit and transfer completion alerts.', 'default_subject' => 'Debit Alert: Transfer Initiated'],
        'application_approved' => ['name' => 'Application Approved', 'description' => 'Sent when an account application is approved.', 'default_subject' => 'Application Approved'],
        'application_declined' => ['name' => 'Application Declined', 'description' => 'Sent when an account application is declined.', 'default_subject' => 'Application Declined'],
        'ticket_created' => ['name' => 'Support Ticket Created', 'description' => 'Confirmation after user opens a support ticket.', 'default_subject' => 'Support Ticket Confirmation'],
        'loan_alert' => ['name' => 'Loan Alert', 'description' => 'Loan application submission and updates.', 'default_subject' => 'Loan Application Received'],
        'transaction_alert' => ['name' => 'Transaction Alert', 'description' => 'General transaction activity alerts.', 'default_subject' => 'Transaction Alert'],
        'otp_code' => ['name' => 'OTP Code', 'description' => 'One-time password notifications.', 'default_subject' => 'Your OTP Code'],
    ];
$notification_template_defaults = function_exists('notification_template_default_overrides')
    ? notification_template_default_overrides()
    : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_notifications'])) {
    $notifications = [
        'registration_welcome' => isset($_POST['registration_welcome']),
        'debit_alert' => isset($_POST['debit_alert']),
        'application_approved' => isset($_POST['application_approved']),
        'application_declined' => isset($_POST['application_declined']),
        'ticket_alert' => isset($_POST['ticket_alert']),
        'loan_alert' => isset($_POST['loan_alert']),
        'transaction_alert' => isset($_POST['transaction_alert']),
    ];

    $success = true;
    foreach ($notifications as $key => $value) {
        $value_str = $value ? 'enabled' : 'disabled';
        if (!ss_set($conn, $key, $value_str)) {
            $success = false;
            break;
        }
    }
    
    if ($success) {
        $alert_type = 'success';
        $message = 'Notification settings updated successfully!';
    } else {
        $alert_type = 'danger';
        $message = 'Failed to save notification settings.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_notification_templates'])) {
    $activeTab = 'templates';
    $templateSubjects = isset($_POST['template_subject']) && is_array($_POST['template_subject']) ? $_POST['template_subject'] : [];
    $templateBodies = isset($_POST['template_body']) && is_array($_POST['template_body']) ? $_POST['template_body'] : [];

    $success = true;
    foreach ($notification_templates as $key => $meta) {
        $subjectVal = trim((string)($templateSubjects[$key] ?? ''));
        $bodyVal = trim((string)($templateBodies[$key] ?? ''));

        if (!ss_set($conn, 'notify_tpl_subject_' . $key, $subjectVal) || !ss_set($conn, 'notify_tpl_body_' . $key, $bodyVal)) {
            $success = false;
            break;
        }
    }

    if ($success) {
        $alert_type = 'success';
        $message = 'Notification templates updated successfully!';
    } else {
        $alert_type = 'danger';
        $message = 'Failed to save notification templates.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['prepopulate_notification_templates'])) {
    $activeTab = 'templates';
    $success = true;
    foreach ($notification_templates as $key => $meta) {
        $defaultSubject = (string)($notification_template_defaults[$key]['subject'] ?? ($meta['default_subject'] ?? ''));
        $defaultBody = (string)($notification_template_defaults[$key]['body'] ?? '');
        if (!ss_set($conn, 'notify_tpl_subject_' . $key, $defaultSubject) || !ss_set($conn, 'notify_tpl_body_' . $key, $defaultBody)) {
            $success = false;
            break;
        }
    }

    if ($success) {
        $alert_type = 'success';
        $message = 'Notification templates prepopulated with default content.';
    } else {
        $alert_type = 'danger';
        $message = 'Failed to prepopulate notification templates.';
    }
}

// Load current notification settings
$notification_status = [];
$notifications_list = [
    'registration_welcome' => ['name' => 'Registration Welcome', 'description' => 'Send welcome email after account registration'],
    'debit_alert' => ['name' => 'Debit Alerts', 'description' => 'Send alerts when money is withdrawn'],
    'application_approved' => ['name' => 'Application Approved', 'description' => 'Notify when account application is approved'],
    'application_declined' => ['name' => 'Application Declined', 'description' => 'Notify when account application is declined'],
    'ticket_alert' => ['name' => 'Support Tickets', 'description' => 'Send confirmation when ticket is created'],
    'loan_alert' => ['name' => 'Loan Alerts', 'description' => 'Send alerts for loan application updates'],
    'transaction_alert' => ['name' => 'Transaction Alerts', 'description' => 'Send alerts for all transaction activities'],
];

foreach ($notifications_list as $key => $info) {
    $val = ss_get($conn, $key, 'enabled');
    $notification_status[$key] = strtolower((string)$val) === 'enabled';
}

$template_subject_values = [];
$template_body_values = [];
foreach ($notification_templates as $key => $meta) {
    $template_subject_values[$key] = (string)ss_get($conn, 'notify_tpl_subject_' . $key, '');
    $template_body_values[$key] = (string)ss_get($conn, 'notify_tpl_body_' . $key, '');
}

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
$pageTitle = 'Notification Settings';
require_once __DIR__ . '/partials/admin-shell-open.php';
?>
<?php if($message): ?>
<div class="mb-4 px-4 py-3 rounded-lg text-sm <?= $alert_type==='success' ? 'bg-green-50 border border-green-200 text-green-700' : 'bg-red-50 border border-red-200 text-red-700' ?>"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="mb-6 border-b border-gray-200">
    <div class="flex flex-wrap gap-2">
        <a href="?tab=channels" class="px-4 py-2 text-sm rounded-t-lg border-b-2 transition-colors <?= $activeTab === 'channels' ? 'border-blue-600 text-blue-600 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700' ?>">Notification Channels</a>
        <a href="?tab=templates" class="px-4 py-2 text-sm rounded-t-lg border-b-2 transition-colors <?= $activeTab === 'templates' ? 'border-blue-600 text-blue-600 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700' ?>">Notification Template</a>
    </div>
</div>

<?php if ($activeTab === 'templates'): ?>
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h2 class="font-semibold text-gray-800 mb-1">Notification Template</h2>
    <p class="text-xs text-gray-500 mb-5">Edit subject and body for every email template triggered from user and admin areas. Leave a field blank to keep system default output.</p>

    <div class="mb-6 rounded-lg border border-blue-200 bg-blue-50 p-4 text-xs text-blue-900">
        <p class="font-semibold mb-2">Available placeholders</p>
        <p>{{bank_name}}, {{support_email}}, {{site_url}}, {{subject}}, {{year}}, {{today}}</p>
        <p class="mt-1">Template-specific placeholders are pulled from runtime data (for example {{fname}}, {{lname}}, {{amount}}, {{currency}}, {{acc_no}}, {{otp}}, {{ticket_id}}, {{loan_id}}, {{status}}).</p>
    </div>

    <form method="POST" class="space-y-6">
        <input type="hidden" name="active_tab" value="templates">
        <?php foreach($notification_templates as $key => $meta): ?>
        <div class="rounded-xl border border-gray-200 p-4">
            <div class="mb-3">
                <p class="text-sm font-semibold text-gray-800"><?= htmlspecialchars($meta['name'] ?? $key) ?></p>
                <p class="text-xs text-gray-500"><?= htmlspecialchars($meta['description'] ?? '') ?></p>
            </div>

            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Subject Override</label>
                    <input type="text" name="template_subject[<?= htmlspecialchars($key) ?>]" value="<?= htmlspecialchars($template_subject_values[$key] ?? '') ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="<?= htmlspecialchars((string)($meta['default_subject'] ?? '')) ?>">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Body Override (HTML supported)</label>
                    <textarea name="template_body[<?= htmlspecialchars($key) ?>]" rows="6" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="&lt;p&gt;Dear {{fname}},&lt;/p&gt;"><?= htmlspecialchars($template_body_values[$key] ?? '') ?></textarea>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <div class="pt-2">
            <button type="submit" name="prepopulate_notification_templates" class="inline-flex items-center gap-2 bg-gray-700 hover:bg-gray-800 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors cursor-pointer mr-2">Prepopulate Default Templates</button>
            <button type="submit" name="save_notification_templates" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors cursor-pointer">Save Notification Templates</button>
        </div>
    </form>
</div>
<?php else: ?>
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 max-w-2xl">
    <h2 class="font-semibold text-gray-800 mb-1">Email Notification Channels</h2>
    <p class="text-xs text-gray-500 mb-5">Toggle which events trigger automatic email notifications.</p>
    <form method="POST" class="space-y-0">
        <input type="hidden" name="active_tab" value="channels">
        <?php foreach($notifications_list as $key => $info): ?>
        <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
            <div>
                <p class="text-sm font-medium text-gray-800"><?= htmlspecialchars($info['name']) ?></p>
                <p class="text-xs text-gray-500"><?= htmlspecialchars($info['description']) ?></p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer flex-shrink-0 ml-4">
                <input type="checkbox" name="<?= htmlspecialchars($key) ?>"
                    <?= ($notification_status[$key] ?? false) ? 'checked' : '' ?> class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600 after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
            </label>
        </div>
        <?php endforeach; ?>
        <div class="pt-4">
            <button type="submit" name="save_notifications" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors cursor-pointer">Save Notification Settings</button>
        </div>
    </form>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/partials/admin-shell-close.php'; ?>
