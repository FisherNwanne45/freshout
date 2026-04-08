#!/usr/bin/env python3
import re, os

BASE = '/Applications/XAMPP/xamppfiles/htdocs/fresh/user/admin'

def rf(path):
    with open(path, 'r', encoding='utf-8', errors='replace') as f:
        return f.read()

def wf(path, content):
    with open(path, 'w', encoding='utf-8') as f:
        f.write(content)

def extract_logic(content):
    pattern = re.compile(r'\n<!DOCTYPE html>', re.IGNORECASE)
    matches = list(pattern.finditer(content))
    if not matches:
        pattern2 = re.compile(r'\n\s*<!DOCTYPE html>', re.IGNORECASE)
        matches = list(pattern2.finditer(content))
    if not matches:
        return content
    m = matches[-1]
    php = content[:m.start()].rstrip()
    if php.endswith('?>'):
        php = php[:-2].rstrip()
    return php

def wrap(php, title, html):
    return (php + "\n"
        + "$pageTitle = '" + title + "';\n"
        + "require_once __DIR__ . '/partials/admin-shell-open.php';\n"
        + "?>\n"
        + html + "\n"
        + "<?php require_once __DIR__ . '/partials/admin-shell-close.php'; ?>\n")

I  = 'w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500'
L  = 'block text-xs font-medium text-gray-700 mb-1'
C  = 'bg-white rounded-xl shadow-sm border border-gray-200 p-6'
BP = 'inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors cursor-pointer'

# ── login.php (standalone) ────────────────────────────────────────────────────
login_orig = rf(BASE + '/login.php')
login_logic = extract_logic(login_orig)
login_logic = login_logic.replace(
    "$msg = \"<div class='alert alert-danger'>\\n\\t\\t\\t\\t\\t\\t<button class='close' data-dismiss='alert'>&times;</button>\\n\\t\\t\\t\\t\\t\\t  Invalid Email or Password!\\n                   \\n\\t\\t\\t  </div>\";",
    "$msg = \"<div class='mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm'>Invalid username or password.</div>\";"
)

login_html = """<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login</title>
  <link rel="icon" href="img/favicon.png" type="image/x-icon">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="min-h-screen bg-slate-900 flex items-center justify-center p-4">
  <div class="w-full max-w-sm">
    <div class="text-center mb-8">
      <div class="inline-flex w-16 h-16 rounded-2xl bg-blue-600 items-center justify-center mb-4">
        <i class="fa-solid fa-building-columns text-white text-2xl"></i>
      </div>
      <h1 class="text-2xl font-bold text-white">Admin Panel</h1>
      <p class="text-slate-400 text-sm mt-1">Sign in to manage the system</p>
    </div>
    <div class="bg-white rounded-2xl shadow-2xl p-8">
      <?php if(isset($msg)) echo $msg; ?>
      <form method="POST" class="space-y-4">
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Username</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 pointer-events-none">
              <i class="fa-solid fa-user text-sm"></i>
            </span>
            <input type="text" name="uname" autofocus required
              class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="admin">
          </div>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Password</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 pointer-events-none">
              <i class="fa-solid fa-lock text-sm"></i>
            </span>
            <input type="password" name="upass" required
              class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Password">
          </div>
        </div>
        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg transition-colors text-sm mt-2">
          Sign In
        </button>
      </form>
    </div>
  </div>
</body>
</html>
"""
wf(BASE + '/login.php', login_logic + "\n" + login_html)
print("OK: login.php")

# ── smtp-settings.php ─────────────────────────────────────────────────────────
smtp_orig = rf(BASE + '/smtp-settings.php')
smtp_logic = extract_logic(smtp_orig)
smtp_html = (
"<?php if($message): ?>\n"
"<div class=\"mb-4 px-4 py-3 rounded-lg text-sm "
"<?= $alert_type==='success' ? 'bg-green-50 border border-green-200 text-green-700' : 'bg-red-50 border border-red-200 text-red-700' ?>\">"
"<?= htmlspecialchars($message) ?></div>\n"
"<?php endif; ?>\n\n"
"<div class=\"" + C + " max-w-2xl\">\n"
"  <h2 class=\"font-semibold text-gray-800 mb-5\">SMTP Configuration</h2>\n"
"  <form method=\"POST\" class=\"space-y-4\">\n"
"    <div class=\"grid grid-cols-1 sm:grid-cols-2 gap-4\">\n"
"      <div class=\"sm:col-span-2\"><label class=\"" + L + "\">SMTP Host</label>\n"
"        <input type=\"text\" name=\"smtp_host\" value=\"<?= htmlspecialchars($smtp_settings['host']) ?>\" class=\"" + I + "\" placeholder=\"smtp.gmail.com\" required></div>\n"
"      <div><label class=\"" + L + "\">Port</label>\n"
"        <input type=\"number\" name=\"smtp_port\" value=\"<?= htmlspecialchars((string)$smtp_settings['port']) ?>\" class=\"" + I + "\" placeholder=\"465\"></div>\n"
"      <div><label class=\"" + L + "\">Encryption</label>\n"
"        <select name=\"smtp_secure\" class=\"" + I + "\">\n"
"          <option value=\"ssl\" <?= $smtp_settings['secure']==='ssl'?'selected':'' ?>>SSL</option>\n"
"          <option value=\"tls\" <?= $smtp_settings['secure']==='tls'?'selected':'' ?>>TLS</option>\n"
"          <option value=\"\" <?= $smtp_settings['secure']===''?'selected':'' ?>>None</option>\n"
"        </select></div>\n"
"      <div><label class=\"" + L + "\">SMTP Username</label>\n"
"        <input type=\"text\" name=\"smtp_username\" value=\"<?= htmlspecialchars($smtp_settings['username']) ?>\" class=\"" + I + "\" required></div>\n"
"      <div><label class=\"" + L + "\">SMTP Password</label>\n"
"        <input type=\"password\" name=\"smtp_password\" value=\"<?= htmlspecialchars($smtp_settings['password']) ?>\" class=\"" + I + "\"></div>\n"
"      <div><label class=\"" + L + "\">From Email</label>\n"
"        <input type=\"email\" name=\"smtp_from\" value=\"<?= htmlspecialchars($smtp_settings['from']) ?>\" class=\"" + I + "\" required></div>\n"
"      <div><label class=\"" + L + "\">From Name</label>\n"
"        <input type=\"text\" name=\"smtp_from_name\" value=\"<?= htmlspecialchars($smtp_settings['from_name']) ?>\" class=\"" + I + "\"></div>\n"
"      <div class=\"sm:col-span-2\"><label class=\"" + L + "\">Reply-To Email</label>\n"
"        <input type=\"email\" name=\"smtp_reply_to\" value=\"<?= htmlspecialchars($smtp_settings['reply_to']) ?>\" class=\"" + I + "\"></div>\n"
"    </div>\n"
"    <div class=\"pt-2\">\n"
"      <button type=\"submit\" name=\"save_smtp\" class=\"" + BP + "\">Save SMTP Settings</button>\n"
"    </div>\n"
"  </form>\n"
"</div>\n"
)
wf(BASE + '/smtp-settings.php', wrap(smtp_logic, 'SMTP Settings', smtp_html))
print("OK: smtp-settings.php")

# ── sms-settings.php ──────────────────────────────────────────────────────────
sms_orig = rf(BASE + '/sms-settings.php')
sms_logic = extract_logic(sms_orig)
sms_html = (
"<?php if($message): ?>\n"
"<div class=\"mb-4 px-4 py-3 rounded-lg text-sm "
"<?= $alert_type==='success' ? 'bg-green-50 border border-green-200 text-green-700' : 'bg-red-50 border border-red-200 text-red-700' ?>\">"
"<?= htmlspecialchars($message) ?></div>\n"
"<?php endif; ?>\n\n"
"<div class=\"" + C + " max-w-2xl\">\n"
"  <h2 class=\"font-semibold text-gray-800 mb-5\">SMS Gateway Settings</h2>\n"
"  <form method=\"POST\" class=\"space-y-5\">\n"
"    <div class=\"grid grid-cols-1 sm:grid-cols-2 gap-4\">\n"
"      <div class=\"sm:col-span-2 flex items-center gap-3\">\n"
"        <label class=\"relative inline-flex items-center cursor-pointer\">\n"
"          <input type=\"checkbox\" name=\"sms_enabled\" <?= $current['sms_enabled']==='1'?'checked':'' ?> class=\"sr-only peer\">\n"
"          <div class=\"w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600 after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full\"></div>\n"
"        </label>\n"
"        <span class=\"text-sm font-medium text-gray-700\">Enable SMS Notifications</span>\n"
"      </div>\n"
"      <div><label class=\"" + L + "\">SMS Provider</label>\n"
"        <select name=\"sms_provider\" class=\"" + I + "\" onchange=\"showSmsProv(this.value)\">\n"
"          <option value=\"textbelt\" <?= $current['sms_provider']==='textbelt'?'selected':'' ?>>TextBelt</option>\n"
"          <option value=\"twilio\" <?= $current['sms_provider']==='twilio'?'selected':'' ?>>Twilio</option>\n"
"          <option value=\"termii\" <?= $current['sms_provider']==='termii'?'selected':'' ?>>Termii</option>\n"
"        </select></div>\n"
"      <div><label class=\"" + L + "\">Brand / Sender Name</label>\n"
"        <input type=\"text\" name=\"sms_brand_name\" value=\"<?= htmlspecialchars($current['sms_brand_name']) ?>\" class=\"" + I + "\"></div>\n"
"    </div>\n"
"    <div id=\"prov-textbelt\" class=\"border border-gray-200 rounded-xl p-4 <?= $current['sms_provider']!=='textbelt'?'hidden':'' ?>\">\n"
"      <h3 class=\"text-xs font-semibold text-gray-600 uppercase mb-3\">TextBelt Config</h3>\n"
"      <div><label class=\"" + L + "\">API Key</label>\n"
"        <input type=\"text\" name=\"textbelt_key\" value=\"<?= htmlspecialchars($current['textbelt_key']) ?>\" class=\"" + I + "\" placeholder=\"textbelt\"></div>\n"
"    </div>\n"
"    <div id=\"prov-twilio\" class=\"border border-gray-200 rounded-xl p-4 <?= $current['sms_provider']!=='twilio'?'hidden':'' ?>\">\n"
"      <h3 class=\"text-xs font-semibold text-gray-600 uppercase mb-3\">Twilio Config</h3>\n"
"      <div class=\"grid grid-cols-1 sm:grid-cols-2 gap-4\">\n"
"        <div><label class=\"" + L + "\">Account SID</label>\n"
"          <input type=\"text\" name=\"twilio_sid\" value=\"<?= htmlspecialchars($current['twilio_sid']) ?>\" class=\"" + I + "\"></div>\n"
"        <div><label class=\"" + L + "\">Auth Token</label>\n"
"          <input type=\"text\" name=\"twilio_token\" value=\"<?= htmlspecialchars($current['twilio_token']) ?>\" class=\"" + I + "\"></div>\n"
"        <div><label class=\"" + L + "\">From Number</label>\n"
"          <input type=\"text\" name=\"twilio_from\" value=\"<?= htmlspecialchars($current['twilio_from']) ?>\" class=\"" + I + "\" placeholder=\"+1234567890\"></div>\n"
"      </div>\n"
"    </div>\n"
"    <div id=\"prov-termii\" class=\"border border-gray-200 rounded-xl p-4 <?= $current['sms_provider']!=='termii'?'hidden':'' ?>\">\n"
"      <h3 class=\"text-xs font-semibold text-gray-600 uppercase mb-3\">Termii Config</h3>\n"
"      <div class=\"grid grid-cols-1 sm:grid-cols-2 gap-4\">\n"
"        <div><label class=\"" + L + "\">API Key</label>\n"
"          <input type=\"text\" name=\"termii_api_key\" value=\"<?= htmlspecialchars($current['termii_api_key']) ?>\" class=\"" + I + "\"></div>\n"
"        <div><label class=\"" + L + "\">Sender ID</label>\n"
"          <input type=\"text\" name=\"termii_sender\" value=\"<?= htmlspecialchars($current['termii_sender']) ?>\" class=\"" + I + "\" placeholder=\"N-Alert\"></div>\n"
"      </div>\n"
"    </div>\n"
"    <div><button type=\"submit\" name=\"save_sms\" class=\"" + BP + "\">Save SMS Settings</button></div>\n"
"  </form>\n"
"</div>\n"
"<script>\n"
"function showSmsProv(v) {\n"
"  ['textbelt','twilio','termii'].forEach(function(p) {\n"
"    document.getElementById('prov-'+p).classList.toggle('hidden', p!==v);\n"
"  });\n"
"}\n"
"</script>\n"
)
wf(BASE + '/sms-settings.php', wrap(sms_logic, 'SMS Gateway', sms_html))
print("OK: sms-settings.php")

# ── notification-settings.php ─────────────────────────────────────────────────
notif_orig = rf(BASE + '/notification-settings.php')
notif_logic = extract_logic(notif_orig)
notif_html = (
"<?php if($message): ?>\n"
"<div class=\"mb-4 px-4 py-3 rounded-lg text-sm "
"<?= $alert_type==='success' ? 'bg-green-50 border border-green-200 text-green-700' : 'bg-red-50 border border-red-200 text-red-700' ?>\">"
"<?= htmlspecialchars($message) ?></div>\n"
"<?php endif; ?>\n\n"
"<div class=\"" + C + " max-w-2xl\">\n"
"  <h2 class=\"font-semibold text-gray-800 mb-1\">Email Notification Channels</h2>\n"
"  <p class=\"text-xs text-gray-500 mb-5\">Toggle which events trigger automatic email notifications.</p>\n"
"  <form method=\"POST\" class=\"space-y-0\">\n"
"    <?php foreach($notifications_list as $key => $info): ?>\n"
"    <div class=\"flex items-center justify-between py-3 border-b border-gray-100 last:border-0\">\n"
"      <div>\n"
"        <p class=\"text-sm font-medium text-gray-800\"><?= htmlspecialchars($info['name']) ?></p>\n"
"        <p class=\"text-xs text-gray-500\"><?= htmlspecialchars($info['description']) ?></p>\n"
"      </div>\n"
"      <label class=\"relative inline-flex items-center cursor-pointer flex-shrink-0 ml-4\">\n"
"        <input type=\"checkbox\" name=\"<?= htmlspecialchars($key) ?>\"\n"
"          <?= ($notification_status[$key] ?? false) ? 'checked' : '' ?> class=\"sr-only peer\">\n"
"        <div class=\"w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600 after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full\"></div>\n"
"      </label>\n"
"    </div>\n"
"    <?php endforeach; ?>\n"
"    <div class=\"pt-4\">\n"
"      <button type=\"submit\" name=\"save_notifications\" class=\"" + BP + "\">Save Notification Settings</button>\n"
"    </div>\n"
"  </form>\n"
"</div>\n"
)
wf(BASE + '/notification-settings.php', wrap(notif_logic, 'Notification Settings', notif_html))
print("OK: notification-settings.php")

print("\nAll done.")
