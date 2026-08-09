<?php

/**
 * Installation Wizard — Fresh Banking Platform
 * Place at project root. Once installation is complete, this file and
 * install.lock protect the app from accidental re-installation.
 */

/* ──────────────────────────────────────────────────────────────
   LOCK CHECK
────────────────────────────────────────────────────────────── */
$lockFile = __DIR__ . '/install.lock';
if (file_exists($lockFile)) {
  http_response_code(403);
  die('<!DOCTYPE html><html><head><title>Already Installed</title>
<style>body{font-family:sans-serif;display:flex;height:100vh;align-items:center;justify-content:center;background:#f1f5f9;}
.card{background:#fff;padding:2rem 3rem;border-radius:1rem;box-shadow:0 4px 24px #0002;text-align:center;max-width:420px}
h2{color:#ef4444;margin:0 0 .75rem} p{color:#64748b;margin:0 0 1.5rem}
a{display:inline-block;padding:.55rem 1.5rem;background:#1d4ed8;color:#fff;border-radius:.5rem;text-decoration:none;font-weight:600}
</style></head><body><div class="card">
<h2>&#128274; Already Installed</h2>
<p>The installation wizard has already been completed. Remove <code>install.lock</code> from the server root only if you intend to re-run the installer.</p>
<a href="user/login.php">Go to Login</a></div></body></html>');
}

/* ──────────────────────────────────────────────────────────────
   SESSION & STEP ROUTING
────────────────────────────────────────────────────────────── */
session_start();
if (!isset($_SESSION['install'])) $_SESSION['install'] = [];

$step    = isset($_POST['step']) ? (int)$_POST['step'] : (isset($_SESSION['install']['step']) ? (int)$_SESSION['install']['step'] : 1);
$errors  = [];
$success = [];
$log     = [];

/* ──────────────────────────────────────────────────────────────
   HELPERS
────────────────────────────────────────────────────────────── */
function install_h(string $s): string
{
  return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
function install_val(string $key, string $default = ''): string
{
  return install_h($_SESSION['install'][$key] ?? $default);
}
function install_field_err(array $errors, string $field): string
{
  foreach ($errors as $e) {
    if (isset($e['field']) && $e['field'] === $field)
      return '<p class="text-red-500 text-xs mt-1">' . install_h($e['msg']) . '</p>';
  }
  return '';
}
function install_add_err(array &$errors, string $field, string $msg): void
{
  $errors[] = ['field' => $field, 'msg' => $msg];
}
function install_redirect_step(int $n): void
{
  $_SESSION['install']['step'] = $n;
  header('Location: ' . $_SERVER['PHP_SELF']);
  exit;
}

function install_prepare_logo_setting(string $rawPath): string
{
  $rawPath = trim($rawPath);
  if ($rawPath === '') {
    return '';
  }

  $fileName = basename($rawPath);
  if ($fileName === '') {
    return '';
  }

  $targetDir = __DIR__ . '/user/admin/site';
  if (!is_dir($targetDir)) {
    @mkdir($targetDir, 0755, true);
  }

  $sourceCandidates = [
    __DIR__ . '/' . ltrim($rawPath, '/'),
    __DIR__ . '/img/' . $fileName,
    __DIR__ . '/user/admin/site/' . $fileName,
  ];

  $sourcePath = '';
  foreach ($sourceCandidates as $candidate) {
    if (is_file($candidate)) {
      $sourcePath = $candidate;
      break;
    }
  }
  if ($sourcePath === '') {
    return '';
  }

  $targetPath = $targetDir . '/' . $fileName;
  if (!is_file($targetPath)) {
    @copy($sourcePath, $targetPath);
  }

  return is_file($targetPath) ? ('admin/site/' . $fileName) : '';
}

function install_prepare_favicon_setting(string $rawPath): string
{
  $rawPath = trim($rawPath);
  if ($rawPath === '') {
    return '';
  }

  $fileName = basename($rawPath);
  if ($fileName === '') {
    return '';
  }

  $targetDir = __DIR__ . '/user/admin/site';
  if (!is_dir($targetDir)) {
    @mkdir($targetDir, 0755, true);
  }

  $sourceCandidates = [
    __DIR__ . '/' . ltrim($rawPath, '/'),
    __DIR__ . '/img/' . $fileName,
    __DIR__ . '/user/admin/site/' . $fileName,
  ];

  $sourcePath = '';
  foreach ($sourceCandidates as $candidate) {
    if (is_file($candidate)) {
      $sourcePath = $candidate;
      break;
    }
  }
  if ($sourcePath === '') {
    return '';
  }

  $targetPath = $targetDir . '/' . $fileName;
  if (!is_file($targetPath)) {
    @copy($sourcePath, $targetPath);
  }
  if (!is_file($targetPath)) {
    return '';
  }

  $faviconMirrorPaths = [
    __DIR__ . '/user/img/favicon.png',
    __DIR__ . '/user/img/favicon-32x32.png',
    __DIR__ . '/user/img/favicon-96x96.png',
    __DIR__ . '/user/img/favicon-16x16.png',
    __DIR__ . '/img/favicon.png',
    __DIR__ . '/img/favicon-32x32.png',
    __DIR__ . '/img/favicon-96x96.png',
    __DIR__ . '/img/favicon-16x16.png',
    __DIR__ . '/themes/theme1/img/favicon-32x32.png',
    __DIR__ . '/themes/theme1/img/favicon-96x96.png',
    __DIR__ . '/themes/theme1/img/favicon-16x16.png',
    __DIR__ . '/themes/theme1/images/favicon.png',
  ];
  foreach ($faviconMirrorPaths as $path) {
    $dir = dirname($path);
    if (!is_dir($dir)) {
      @mkdir($dir, 0755, true);
    }
    if (is_dir($dir) && is_writable($dir)) {
      @copy($targetPath, $path);
    }
  }

  // site_favicon setting stores filename, not a relative folder path.
  return $fileName;
}

/* ──────────────────────────────────────────────────────────────
   STEP HANDLERS (POST processors)
────────────────────────────────────────────────────────────── */

// ── Step 1: Welcome — just advance
if ($step === 1 && $_SERVER['REQUEST_METHOD'] === 'POST') {
  install_redirect_step(2);
}

// ── Step 2: Database Configuration
if ($step === 2 && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $db_host = trim($_POST['db_host'] ?? '127.0.0.1');
  $db_port = trim($_POST['db_port'] ?? '3306');
  $db_name = trim($_POST['db_name'] ?? '');
  $db_user = trim($_POST['db_user'] ?? '');
  $db_pass = $_POST['db_pass'] ?? '';

  if ($db_host === '') install_add_err($errors, 'db_host', 'Database host is required.');
  if ($db_name === '') install_add_err($errors, 'db_name', 'Database name is required.');
  if ($db_user === '') install_add_err($errors, 'db_user', 'Database user is required.');

  if (empty($errors)) {
    $port = (int)$db_port ?: 3306;
    $conn = @mysqli_connect($db_host . ':' . $port, $db_user, $db_pass);
    if (!$conn) {
      install_add_err($errors, 'db_host', 'Cannot connect to MySQL: ' . mysqli_connect_error());
    } else {
      // Create DB if missing
      $sn = mysqli_real_escape_string($conn, $db_name);
      mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `$sn` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
      if (!mysqli_select_db($conn, $db_name)) {
        install_add_err($errors, 'db_name', 'Database could not be created or selected: ' . mysqli_error($conn));
      } else {
        $_SESSION['install']['db_host'] = $db_host;
        $_SESSION['install']['db_port'] = (string)$port;
        $_SESSION['install']['db_name'] = $db_name;
        $_SESSION['install']['db_user'] = $db_user;
        $_SESSION['install']['db_pass'] = $db_pass;
        mysqli_close($conn);
        install_redirect_step(3);
      }
    }
  }
  $_SESSION['install']['db_host'] = $db_host;
  $_SESSION['install']['db_port'] = $db_port;
  $_SESSION['install']['db_name'] = $db_name;
  $_SESSION['install']['db_user'] = $db_user;
}

// ── Step 3: Admin Account + Site Info
if ($step === 3 && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $admin_user  = trim($_POST['admin_user']  ?? '');
  $admin_pass  = $_POST['admin_pass']        ?? '';
  $admin_pass2 = $_POST['admin_pass2']       ?? '';
  $admin_email = trim($_POST['admin_email']  ?? '');
  $site_name   = trim($_POST['site_name']    ?? '');
  $site_addr   = trim($_POST['site_addr']    ?? '');
  $site_phone  = trim($_POST['site_phone']   ?? '');
  $site_email  = trim($_POST['site_email']   ?? '');
  $site_color  = trim($_POST['site_color']   ?? '#1d4ed8');
  $site_url    = trim($_POST['site_url']     ?? '');

  if ($admin_user  === '') install_add_err($errors, 'admin_user',  'Admin username is required.');
  if (strlen($admin_user) < 3) install_add_err($errors, 'admin_user', 'Username must be at least 3 characters.');
  if ($admin_pass  === '') install_add_err($errors, 'admin_pass',  'Password is required.');
  if (strlen($admin_pass) < 6)  install_add_err($errors, 'admin_pass',  'Password must be at least 6 characters.');
  if ($admin_pass !== $admin_pass2) install_add_err($errors, 'admin_pass2', 'Passwords do not match.');
  if ($admin_email === '') install_add_err($errors, 'admin_email', 'Admin email is required.');
  elseif (!filter_var($admin_email, FILTER_VALIDATE_EMAIL)) install_add_err($errors, 'admin_email', 'Invalid email address.');
  if ($site_name   === '') install_add_err($errors, 'site_name',   'Bank/site name is required.');
  if ($site_url    === '') install_add_err($errors, 'site_url',    'Site URL is required.');
  // Basic colour validation
  if (!preg_match('/^#[0-9a-fA-F]{3,8}$/', $site_color)) $site_color = '#1d4ed8';

  if (empty($errors)) {
    $_SESSION['install']['admin_user']  = $admin_user;
    $_SESSION['install']['admin_pass']  = $admin_pass;   // plain — written as md5 at install time
    $_SESSION['install']['admin_email'] = $admin_email;
    $_SESSION['install']['site_name']   = $site_name;
    $_SESSION['install']['site_addr']   = $site_addr;
    $_SESSION['install']['site_phone']  = $site_phone;
    $_SESSION['install']['site_email']  = $site_email;
    $_SESSION['install']['site_color']  = $site_color;
    $_SESSION['install']['site_url']    = $site_url;
    install_redirect_step(4);
  }
}

// ── Step 4: Branding Uploads
if ($step === 4 && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $imgDir = __DIR__ . '/img/';
  if (!is_dir($imgDir)) @mkdir($imgDir, 0755, true);

  $allowedMimes = ['image/png', 'image/jpeg', 'image/jpg', 'image/gif', 'image/webp', 'image/svg+xml', 'image/x-icon', 'image/vnd.microsoft.icon'];

  function install_handle_upload(string $field, string $imgDir, array $allowed, array &$errors): string
  {
    if (empty($_FILES[$field]['tmp_name']) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) return '';
    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
      install_add_err($errors, $field, 'Upload error code ' . $_FILES[$field]['error']);
      return '';
    }
    if ($_FILES[$field]['size'] > 2 * 1024 * 1024) {
      install_add_err($errors, $field, 'File must be under 2 MB.');
      return '';
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($_FILES[$field]['tmp_name']);
    if (!in_array($mime, $allowed, true)) {
      install_add_err($errors, $field, 'File type not allowed (' . $mime . '). Use PNG, JPG, GIF, WEBP, SVG, or ICO.');
      return '';
    }
    $ext  = pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
    $name = $field . '_' . bin2hex(random_bytes(6)) . '.' . strtolower($ext);
    $dest = $imgDir . $name;
    if (!move_uploaded_file($_FILES[$field]['tmp_name'], $dest)) {
      install_add_err($errors, $field, 'Could not save uploaded file.');
      return '';
    }
    return 'img/' . $name;
  }

  $auth_logo      = install_handle_upload('auth_logo',      $imgDir, $allowedMimes, $errors);
  $dashboard_logo = install_handle_upload('dashboard_logo', $imgDir, $allowedMimes, $errors);
  $frontend_logo  = install_handle_upload('frontend_logo',  $imgDir, $allowedMimes, $errors);
  $admin_logo     = install_handle_upload('admin_logo',     $imgDir, $allowedMimes, $errors);
  $favicon        = install_handle_upload('favicon',        $imgDir, $allowedMimes, $errors);

  // If no files uploaded for optional fields, keep previous values from the session.
  if ($auth_logo === '') $auth_logo = $_SESSION['install']['auth_logo_url'] ?? '';
  if ($dashboard_logo === '') $dashboard_logo = $_SESSION['install']['dashboard_logo_url'] ?? '';
  if ($frontend_logo === '') $frontend_logo = $_SESSION['install']['frontend_logo_url'] ?? '';
  if ($admin_logo === '') $admin_logo = $_SESSION['install']['admin_logo_url'] ?? '';
  if ($favicon === '') $favicon = $_SESSION['install']['site_favicon'] ?? '';

  // Keep legacy fields populated for templates expecting frontend/admin naming.
  if ($frontend_logo === '' && $auth_logo !== '') {
    $frontend_logo = $auth_logo;
  }
  if ($admin_logo === '' && $dashboard_logo !== '') {
    $admin_logo = $dashboard_logo;
  }

  if (empty($errors)) {
    $_SESSION['install']['auth_logo_url'] = $auth_logo;
    $_SESSION['install']['dashboard_logo_url'] = $dashboard_logo;
    $_SESSION['install']['frontend_logo_url'] = $frontend_logo;
    $_SESSION['install']['admin_logo_url']    = $admin_logo;
    $_SESSION['install']['site_favicon']      = $favicon;
    install_redirect_step(5);
  }
}

// ── Step 5: SMTP
if ($step === 5 && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $smtp_host      = trim($_POST['smtp_host']      ?? '');
  $smtp_port      = trim($_POST['smtp_port']      ?? '465');
  $smtp_secure    = trim($_POST['smtp_secure']    ?? 'ssl');
  $smtp_username  = trim($_POST['smtp_username']  ?? '');
  $smtp_password  = $_POST['smtp_password']        ?? '';
  $smtp_from      = trim($_POST['smtp_from']      ?? '');
  $smtp_from_name = trim($_POST['smtp_from_name'] ?? '');
  $smtp_reply_to  = trim($_POST['smtp_reply_to']  ?? '');

  // SMTP is optional — only validate if host is provided
  if ($smtp_host !== '') {
    if ((int)$smtp_port < 1 || (int)$smtp_port > 65535) install_add_err($errors, 'smtp_port', 'Invalid port.');
    if ($smtp_from !== '' && !filter_var($smtp_from, FILTER_VALIDATE_EMAIL)) install_add_err($errors, 'smtp_from', 'Invalid From address.');
    if ($smtp_reply_to !== '' && !filter_var($smtp_reply_to, FILTER_VALIDATE_EMAIL)) install_add_err($errors, 'smtp_reply_to', 'Invalid Reply-To address.');
  }

  if (empty($errors)) {
    $_SESSION['install']['smtp_host']      = $smtp_host;
    $_SESSION['install']['smtp_port']      = $smtp_port;
    $_SESSION['install']['smtp_secure']    = in_array($smtp_secure, ['ssl', 'tls', ''], true) ? $smtp_secure : 'ssl';
    $_SESSION['install']['smtp_username']  = $smtp_username;
    $_SESSION['install']['smtp_password']  = $smtp_password;
    $_SESSION['install']['smtp_from']      = $smtp_from;
    $_SESSION['install']['smtp_from_name'] = $smtp_from_name;
    $_SESSION['install']['smtp_reply_to']  = $smtp_reply_to;
    install_redirect_step(6);
  }
}

// ── Step 6: Review (GET) — nothing to process, just display

// ── Step 7: EXECUTE INSTALL
if ($step === 7 && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $d = $_SESSION['install'];
  $log = [];
  $ok  = true;

  // ── 7a. Write config.php
  $configPath = __DIR__ . '/config.php';
  $configContent = file_get_contents($configPath);

  if ($configContent !== false) {
    // Replace just the db array values robustly
    $configContent = preg_replace(
      "/'host'\s*=>\s*'[^']*'/",
      "'host' => '" . addslashes($d['db_host']) . "'",
      $configContent
    );
    // Port is stored as an integer literal in config.php (no quotes around value).
    $configContent = preg_replace(
      "/'port'\s*=>\s*\d+/",
      "'port' => " . (int)$d['db_port'],
      $configContent
    );
    $configContent = preg_replace(
      "/'dbname'\s*=>\s*'[^']*'/",
      "'dbname' => '" . addslashes($d['db_name']) . "'",
      $configContent
    );
    $configContent = preg_replace(
      "/'username'\s*=>\s*'[^']*'/",
      "'username' => '" . addslashes($d['db_user']) . "'",
      $configContent
    );
    $configContent = preg_replace(
      "/'password'\s*=>\s*'[^']*'/",
      "'password' => '" . addslashes($d['db_pass']) . "'",
      $configContent
    );
    // Also update the 'name' key (the DB name field in config.php).
    $configContent = preg_replace(
      "/'name'\s*=>\s*'[^']*'/",
      "'name' => '" . addslashes($d['db_name']) . "'",
      $configContent
    );
    if (file_put_contents($configPath, $configContent) !== false) {
      $log[] = ['ok', 'config.php — database credentials written'];
    } else {
      $log[] = ['warn', 'config.php — could not write (check file permissions). Update DB credentials manually.'];
    }
  } else {
    $log[] = ['warn', 'config.php — could not read file.'];
  }

  // ── 7b. Connect to DB
  $port = (int)($d['db_port'] ?? 3306);
  $conn = @mysqli_connect($d['db_host'] . ':' . $port, $d['db_user'], $d['db_pass'], $d['db_name']);
  if (!$conn) {
    $log[] = ['err', 'DB connection failed: ' . mysqli_connect_error()];
    $ok = false;
    goto render;
  }
  mysqli_set_charset($conn, 'utf8mb4');
  $log[] = ['ok', 'Database connection established.'];

  // Also write user/config.php and user/dbconfig.php if they exist
  foreach (['user/config.php', 'user/dbconfig.php'] as $relPath) {
    $fp = __DIR__ . '/' . $relPath;
    if (!file_exists($fp)) continue;
    $fc = file_get_contents($fp);
    if ($fc === false) continue;
    $fc = preg_replace("/'host'\s*=>\s*'[^']*'/",    "'host' => '"     . addslashes($d['db_host']) . "'", $fc);
    $fc = preg_replace("/'port'\s*=>\s*'[^']*'/",    "'port' => '"     . addslashes($d['db_port']) . "'", $fc);
    $fc = preg_replace("/'dbname'\s*=>\s*'[^']*'/",  "'dbname' => '"   . addslashes($d['db_name']) . "'", $fc);
    $fc = preg_replace("/'username'\s*=>\s*'[^']*'/", "'username' => '" . addslashes($d['db_user']) . "'", $fc);
    $fc = preg_replace("/'password'\s*=>\s*'[^']*'/", "'password' => '" . addslashes($d['db_pass']) . "'", $fc);
    // Also handle simple $host/$user/$pass/$db variable forms
    $fc = preg_replace('/(\$dbHost\s*=\s*")[^"]*"/',   '$1' . addslashes($d['db_host']) . '"', $fc);
    $fc = preg_replace('/(\$dbUser\s*=\s*")[^"]*"/',   '$1' . addslashes($d['db_user']) . '"', $fc);
    $fc = preg_replace('/(\$dbPass\s*=\s*")[^"]*"/',   '$1' . addslashes($d['db_pass']) . '"', $fc);
    $fc = preg_replace('/(\$dbName\s*=\s*")[^"]*"/',   '$1' . addslashes($d['db_name']) . '"', $fc);
    if (file_put_contents($fp, $fc) !== false)
      $log[] = ['ok', $relPath . ' — DB credentials updated'];
    else
      $log[] = ['warn', $relPath . ' — could not write'];
  }

  // ── 7c. Create legacy tables
  require_once __DIR__ . '/user/partials/schema.php';
  foreach (fw_schema_legacy_tables() as $sql) {
    $res = mysqli_query($conn, $sql);
    if (!$res) {
      $log[] = ['err', 'Legacy table creation failed: ' . mysqli_error($conn)];
      $ok = false;
    }
  }
  if ($ok) $log[] = ['ok', 'Legacy tables — created or already exist.'];

  // Ensure admin password column can store modern hashes (bcrypt/argon).
  if ($ok) {
    $lenRes = mysqli_query(
      $conn,
      "SELECT CHARACTER_MAXIMUM_LENGTH AS max_len
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = 'admin'
           AND COLUMN_NAME = 'upass'
         LIMIT 1"
    );
    if ($lenRes) {
      $lenRow = mysqli_fetch_assoc($lenRes);
      $maxLen = (int)($lenRow['max_len'] ?? 0);
      if ($maxLen > 0 && $maxLen < 255) {
        if (mysqli_query($conn, "ALTER TABLE `admin` MODIFY COLUMN `upass` VARCHAR(255) NOT NULL")) {
          $log[] = ['ok', 'Admin password column normalized to VARCHAR(255).'];
        } else {
          $log[] = ['warn', 'Admin password column normalization failed: ' . mysqli_error($conn)];
        }
      }
    }
  }

  // ── 7d. Create feature tables
  foreach (fw_schema_feature_tables() as $sql) {
    $res = mysqli_query($conn, $sql);
    if (!$res) {
      $log[] = ['err', 'Feature table creation failed: ' . mysqli_error($conn)];
      $ok = false;
    }
  }
  if ($ok) $log[] = ['ok', 'Feature tables — created or already exist.'];

  // ── 7e. Seed reference data
  foreach (fw_schema_seed_data() as $sql) {
    $res = mysqli_query($conn, $sql);
    if (!$res) {
      $log[] = ['warn', 'Seed data warning: ' . mysqli_error($conn)];
    }
  }
  $log[] = ['ok', 'Seed data — currencies, account types, exchange rates, transfer settings inserted.'];

  $authLogoSetting = install_prepare_logo_setting((string)($d['auth_logo_url'] ?? ''));
  $dashboardLogoSetting = install_prepare_logo_setting((string)($d['dashboard_logo_url'] ?? ''));
  $frontendLogoSetting = install_prepare_logo_setting((string)($d['frontend_logo_url'] ?? ''));
  $adminLogoSetting = install_prepare_logo_setting((string)($d['admin_logo_url'] ?? ''));
  $faviconSetting = install_prepare_favicon_setting((string)($d['site_favicon'] ?? ''));

  if ($authLogoSetting === '') {
    $authLogoSetting = $frontendLogoSetting;
  }
  if ($dashboardLogoSetting === '') {
    $dashboardLogoSetting = $adminLogoSetting !== '' ? $adminLogoSetting : $frontendLogoSetting;
  }

  // Keep legacy keys in sync for older themes/pages.
  if ($frontendLogoSetting === '' && $authLogoSetting !== '') {
    $frontendLogoSetting = $authLogoSetting;
  }
  if ($adminLogoSetting === '' && $dashboardLogoSetting !== '') {
    $adminLogoSetting = $dashboardLogoSetting;
  }

  $siteImageFile = '';
  if ($frontendLogoSetting !== '') {
    $siteImageFile = basename($frontendLogoSetting);
  } elseif ($adminLogoSetting !== '') {
    $siteImageFile = basename($adminLogoSetting);
  }

  if ($siteImageFile !== '') {
    $siteLogoSource = __DIR__ . '/user/admin/site/' . $siteImageFile;
    if (is_file($siteLogoSource)) {
      @copy($siteLogoSource, __DIR__ . '/img/logo.png');
      @copy($siteLogoSource, __DIR__ . '/img/sc.png');
      @copy($siteLogoSource, __DIR__ . '/themes/theme1/images/logo.png');
      @copy($siteLogoSource, __DIR__ . '/themes/theme1/images/logo-footer.png');
    }
  }

  // ── 7f. Insert site row (id=1, no AUTO_INCREMENT on id)
  $site_name  = mysqli_real_escape_string($conn, $d['site_name']  ?? '');
  $site_addr  = mysqli_real_escape_string($conn, $d['site_addr']  ?? '');
  $site_phone = mysqli_real_escape_string($conn, $d['site_phone'] ?? '');
  $site_email = mysqli_real_escape_string($conn, $d['site_email'] ?? '');
  $site_color = mysqli_real_escape_string($conn, $d['site_color'] ?? '#1d4ed8');
  $site_url   = mysqli_real_escape_string($conn, $d['site_url']   ?? '');
  $site_year  = date('Y');

  $site_image = mysqli_real_escape_string($conn, $siteImageFile);

  mysqli_query($conn, "INSERT INTO `site`
        (id, image, qr, name, addr, phone, email, tawk, tawkk, tawk2, year, url, urlh, login, color, code1, code2, code3, code1b, code2b, code3b)
      VALUES (1, '$site_image', '', '$site_name', '$site_addr', '$site_phone', '$site_email', '', '', '', '$site_year',
                '$site_url', '$site_url', 'user', '$site_color', '', '', '', '', '', '')
        ON DUPLICATE KEY UPDATE
            name  = VALUES(name),  addr  = VALUES(addr),  phone = VALUES(phone),
        email = VALUES(email), image = IF(VALUES(image) <> '', VALUES(image), image), color = VALUES(color), url   = VALUES(url),
            urlh  = VALUES(urlh),  year  = VALUES(year)");
  if (mysqli_error($conn)) {
    $log[] = ['warn', 'Site row: ' . mysqli_error($conn)];
  } else {
    $log[] = ['ok', 'Site info — saved to `site` table.'];
  }

  // ── 7g. Insert admin account
  $admin_user  = mysqli_real_escape_string($conn, $d['admin_user']  ?? 'admin');
  $admin_pass  = md5($d['admin_pass'] ?? '');
  $admin_email = mysqli_real_escape_string($conn, $d['admin_email'] ?? '');

  // Only insert if no admin rows exist
  $existing = mysqli_query($conn, "SELECT COUNT(*) as c FROM `admin`");
  $ec = $existing ? (int)mysqli_fetch_assoc($existing)['c'] : 0;
  if ($ec === 0) {
    mysqli_query($conn, "INSERT INTO `admin` (uname, upass, email, verified_count)
            VALUES ('$admin_user', '$admin_pass', '$admin_email', 'Y')");
    if (mysqli_error($conn)) {
      $log[] = ['warn', 'Admin insert: ' . mysqli_error($conn)];
    } else {
      $log[] = ['ok', 'Admin account created.'];
    }
  } else {
    $log[] = ['ok', "Admin account — existing admin(s) preserved ($ec found), new account not created."];
  }

  // ── 7h. site_settings — write all defaults + installer-specific values
  $defaults = fw_schema_default_site_settings();
  // Override with installer-provided values
  $overrides = [
    'smtp_host'         => $d['smtp_host']      ?? '',
    'smtp_port'         => $d['smtp_port']      ?? '465',
    'smtp_secure'       => $d['smtp_secure']    ?? 'ssl',
    'smtp_username'     => $d['smtp_username']  ?? '',
    'smtp_password'     => $d['smtp_password']  ?? '',
    'smtp_from'         => $d['smtp_from']      ?? '',
    'smtp_from_name'    => $d['smtp_from_name'] ?? '',
    'smtp_reply_to'     => $d['smtp_reply_to']  ?? '',
    'auth_logo_url'     => $authLogoSetting,
    'dashboard_logo_url' => $dashboardLogoSetting,
    'frontend_logo_url' => $frontendLogoSetting,
    'admin_logo_url'    => $adminLogoSetting,
    'site_favicon'      => $faviconSetting,
  ];
  $settings = array_merge($defaults, $overrides);

  // If the user skipped SMTP setup (no host provided), preserve any existing SMTP
  // credentials in the DB rather than overwriting them with empty strings.
  $smtpProvided = !empty($overrides['smtp_host']);

  $ssOk = true;
  foreach ($settings as $key => $value) {
    $k = mysqli_real_escape_string($conn, $key);
    $v = mysqli_real_escape_string($conn, (string)$value);

    $isSmtp     = strncmp($key, 'smtp_', 5) === 0;
    $isBranding = in_array($key, ['auth_logo_url', 'dashboard_logo_url', 'frontend_logo_url', 'admin_logo_url', 'site_favicon'], true);

    if (($isSmtp && !$smtpProvided) || ($isBranding && $value === '')) {
      // INSERT IGNORE — do not overwrite already-configured credentials / logos with empty strings
      $r = mysqli_query($conn, "INSERT IGNORE INTO `site_settings` (`key`, `value`) VALUES ('$k', '$v')");
    } else {
      $r = mysqli_query($conn, "INSERT INTO `site_settings` (`key`, `value`) VALUES ('$k', '$v')
                 ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");
    }
    if (!$r) {
      $ssOk = false;
      $log[] = ['warn', "site_setting '$key': " . mysqli_error($conn)];
    }
  }
  if ($ssOk) $log[] = ['ok', 'Site settings — ' . count($settings) . ' key(s) written.'];

  mysqli_close($conn);

  // ── 7i. Write install.lock
  file_put_contents($lockFile, date('Y-m-d H:i:s') . "\n");
  $log[] = ['ok', 'install.lock created — wizard is now disabled.'];

  $_SESSION['install']['done'] = true;
  $_SESSION['install']['log']  = $log;
  install_redirect_step(8);
}

// ── Step 8: Completion page
if ($step === 8) {
  $log = $_SESSION['install']['log'] ?? [];
}

render:
/* ──────────────────────────────────────────────────────────────
   HTML OUTPUT
────────────────────────────────────────────────────────────── */
$currentStep = $step;
$totalSteps  = 7; // 1–7 visible; 8 = done page

$stepTitles = [
  1 => 'Welcome',
  2 => 'Database',
  3 => 'Admin & Site',
  4 => 'Branding',
  5 => 'Email (SMTP)',
  6 => 'Review',
  7 => 'Install',
  8 => 'Done',
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Installation Wizard <?= $currentStep <= 7 ? '— Step ' . $currentStep : '— Complete' ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    *,
    *::before,
    *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background: #f1f5f9;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: flex-start;
      padding: 2rem 1rem 4rem;
    }

    .wizard {
      width: 100%;
      max-width: 640px;
    }

    .wizard-header {
      text-align: center;
      margin-bottom: 2rem;
    }

    .wizard-header h1 {
      font-size: 1.6rem;
      font-weight: 700;
      color: #1e293b;
    }

    .wizard-header p {
      color: #64748b;
      font-size: .9rem;
      margin-top: .35rem;
    }

    /* Stepper */
    .stepper {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0;
      margin-bottom: 2rem;
      flex-wrap: wrap;
    }

    .step-item {
      display: flex;
      flex-direction: column;
      align-items: center;
      min-width: 60px;
    }

    .step-circle {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: .8rem;
      font-weight: 700;
      border: 2px solid #cbd5e1;
      color: #94a3b8;
      background: #fff;
      transition: all .2s;
    }

    .step-circle.active {
      background: #1d4ed8;
      border-color: #1d4ed8;
      color: #fff;
    }

    .step-circle.done {
      background: #16a34a;
      border-color: #16a34a;
      color: #fff;
    }

    .step-label {
      font-size: .68rem;
      color: #94a3b8;
      margin-top: .3rem;
      text-align: center;
    }

    .step-label.active {
      color: #1d4ed8;
      font-weight: 600;
    }

    .step-label.done {
      color: #16a34a;
    }

    .step-line {
      flex: 1;
      height: 2px;
      background: #e2e8f0;
      min-width: 16px;
      max-width: 48px;
      margin-bottom: 20px;
    }

    .step-line.done {
      background: #16a34a;
    }

    /* Card */
    .card {
      background: #fff;
      border-radius: 1rem;
      padding: 2rem 2.25rem 2.25rem;
      box-shadow: 0 4px 24px rgba(0, 0, 0, .07);
    }

    .card h2 {
      font-size: 1.2rem;
      font-weight: 700;
      color: #1e293b;
      margin-bottom: .3rem;
    }

    .card .subtitle {
      color: #64748b;
      font-size: .875rem;
      margin-bottom: 1.5rem;
    }

    /* Form elements */
    .form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
    }

    .form-grid.one {
      grid-template-columns: 1fr;
    }

    .field {
      display: flex;
      flex-direction: column;
      gap: .3rem;
    }

    .field label {
      font-size: .82rem;
      font-weight: 600;
      color: #374151;
    }

    .field input,
    .field select {
      border: 1px solid #d1d5db;
      border-radius: .5rem;
      padding: .55rem .8rem;
      font-size: .9rem;
      color: #1e293b;
      outline: none;
      transition: border-color .15s;
      width: 100%;
    }

    .field input:focus,
    .field select:focus {
      border-color: #1d4ed8;
      box-shadow: 0 0 0 3px #dbeafe;
    }

    .field input.error,
    .field select.error {
      border-color: #ef4444;
    }

    .field .hint {
      font-size: .75rem;
      color: #94a3b8;
    }

    .field .err {
      font-size: .75rem;
      color: #ef4444;
      margin-top: -.1rem;
    }

    /* Buttons */
    .btn {
      display: inline-flex;
      align-items: center;
      gap: .5rem;
      padding: .6rem 1.5rem;
      border-radius: .5rem;
      font-weight: 600;
      font-size: .9rem;
      cursor: pointer;
      border: none;
      transition: background .15s, transform .1s;
    }

    .btn:active {
      transform: scale(.98);
    }

    .btn-primary {
      background: #1d4ed8;
      color: #fff;
    }

    .btn-primary:hover {
      background: #1e40af;
    }

    .btn-secondary {
      background: #f1f5f9;
      color: #374151;
      border: 1px solid #e2e8f0;
    }

    .btn-secondary:hover {
      background: #e2e8f0;
    }

    .btn-success {
      background: #16a34a;
      color: #fff;
    }

    .btn-success:hover {
      background: #15803d;
    }

    .btn-footer {
      display: flex;
      justify-content: space-between;
      margin-top: 1.75rem;
    }

    /* Alerts */
    .alert {
      padding: .75rem 1rem;
      border-radius: .5rem;
      font-size: .875rem;
      margin-bottom: 1rem;
    }

    .alert-err {
      background: #fef2f2;
      border: 1px solid #fca5a5;
      color: #b91c1c;
    }

    .alert-warn {
      background: #fffbeb;
      border: 1px solid #fcd34d;
      color: #92400e;
    }

    .alert-ok {
      background: #f0fdf4;
      border: 1px solid #86efac;
      color: #15803d;
    }

    /* System checks */
    .check-list {
      display: flex;
      flex-direction: column;
      gap: .6rem;
    }

    .check-item {
      display: flex;
      align-items: center;
      gap: .75rem;
      padding: .6rem .9rem;
      border-radius: .5rem;
      font-size: .875rem;
    }

    .check-item.pass {
      background: #f0fdf4;
      color: #16a34a;
    }

    .check-item.fail {
      background: #fef2f2;
      color: #ef4444;
      font-weight: 600;
    }

    .check-item.warn {
      background: #fffbeb;
      color: #b45309;
    }

    /* Review table */
    .review-table {
      width: 100%;
      border-collapse: collapse;
      font-size: .875rem;
    }

    .review-table th {
      text-align: left;
      padding: .5rem .75rem;
      color: #64748b;
      font-weight: 600;
      font-size: .75rem;
      text-transform: uppercase;
      border-bottom: 1px solid #e2e8f0;
      background: #f8fafc;
    }

    .review-table td {
      padding: .6rem .75rem;
      border-bottom: 1px solid #f0f4f8;
      color: #1e293b;
      vertical-align: top;
    }

    .review-table tr:last-child td {
      border-bottom: none;
    }

    /* Log list */
    .log-list {
      display: flex;
      flex-direction: column;
      gap: .4rem;
      max-height: 380px;
      overflow-y: auto;
    }

    .log-item {
      display: flex;
      align-items: flex-start;
      gap: .65rem;
      font-size: .82rem;
      padding: .45rem .75rem;
      border-radius: .4rem;
    }

    .log-item.ok {
      background: #f0fdf4;
      color: #16a34a;
    }

    .log-item.warn {
      background: #fffbeb;
      color: #b45309;
    }

    .log-item.err {
      background: #fef2f2;
      color: #b91c1c;
      font-weight: 600;
    }

    /* File upload */
    .file-zone {
      border: 2px dashed #d1d5db;
      border-radius: .6rem;
      padding: 1rem;
      text-align: center;
      font-size: .8rem;
      color: #94a3b8;
      cursor: pointer;
      transition: border-color .2s, background .2s;
    }

    .file-zone:hover {
      border-color: #1d4ed8;
      background: #eff6ff;
      color: #1d4ed8;
    }

    .file-zone input[type=file] {
      display: none;
    }

    .file-zone span {
      display: block;
      margin-top: .3rem;
      font-size: .7rem;
    }

    /* Completion */
    .done-icon {
      font-size: 3.5rem;
      text-align: center;
      margin-bottom: 1rem;
    }

    .done-actions {
      display: flex;
      gap: 1rem;
      justify-content: center;
      flex-wrap: wrap;
      margin-top: 1.75rem;
    }

    @media (max-width: 540px) {
      .form-grid {
        grid-template-columns: 1fr;
      }

      .card {
        padding: 1.5rem 1.25rem;
      }
    }
  </style>
</head>

<body>
  <div class="wizard">
    <!-- Header -->
    <div class="wizard-header">
      <h1><i class="fa-solid fa-landmark" style="color:#1d4ed8;margin-right:.4rem"></i>Installation Wizard
      </h1>
      <p>Set up your banking platform in a few simple steps.</p>
    </div>

    <!-- Stepper -->
    <?php if ($currentStep <= 7): $steps = [1, 2, 3, 4, 5, 6, 7]; ?>
      <div class="stepper">
        <?php foreach ($steps as $i => $n):
          $cls = $n < $currentStep ? 'done' : ($n === $currentStep ? 'active' : '');
          $icon = $n < $currentStep ? '<i class="fa-solid fa-check"></i>' : $n;
        ?>
          <?php if ($i > 0): ?>
            <div class="step-line <?= $n <= $currentStep ? 'done' : '' ?>"></div>
          <?php endif; ?>
          <div class="step-item">
            <div class="step-circle <?= $cls ?>"><?= $icon ?></div>
            <div class="step-label <?= $cls ?>"><?= $stepTitles[$n] ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Card -->
    <div class="card">

      <?php /* ============================================================
     STEP 1 — WELCOME / SYSTEM CHECKS
     ============================================================ */ if ($currentStep === 1): ?>

        <h2><i class="fa-solid fa-circle-check" style="color:#1d4ed8;margin-right:.5rem"></i>Welcome</h2>
        <p class="subtitle">Before we begin, let's verify your server environment.</p>
        <?php
        $checks = [];
        // PHP version
        $phpOk = version_compare(PHP_VERSION, '7.4.0', '>=');
        $checks[] = [$phpOk ? 'pass' : 'fail', 'fa-brands fa-php', 'PHP ' . PHP_VERSION . ($phpOk ? ' — OK' : ' — PHP 7.4+ required')];
        // MySQLi
        $myOk = extension_loaded('mysqli');
        $checks[] = [$myOk ? 'pass' : 'fail', 'fa-solid fa-database', 'MySQLi extension — ' . ($myOk ? 'loaded' : 'NOT found (required)')];
        // PDO
        $pdoOk = extension_loaded('pdo_mysql');
        $checks[] = [$pdoOk ? 'pass' : 'warn', 'fa-solid fa-server', 'PDO MySQL extension — ' . ($pdoOk ? 'loaded' : 'not found (recommended)')];
        // GD
        $gdOk = extension_loaded('gd');
        $checks[] = [$gdOk ? 'pass' : 'warn', 'fa-solid fa-image', 'GD image extension — ' . ($gdOk ? 'loaded' : 'not found (needed for uploads)')];
        // FileInfo
        $fiOk = extension_loaded('fileinfo');
        $checks[] = [$fiOk ? 'pass' : 'warn', 'fa-solid fa-file', 'FileInfo extension — ' . ($fiOk ? 'loaded' : 'not found (needed for uploads)')];
        // Config writable
        $cfgW = is_writable(__DIR__ . '/config.php');
        $checks[] = [$cfgW ? 'pass' : 'warn', 'fa-solid fa-gear', 'config.php writable — ' . ($cfgW ? 'yes' : 'no (DB credentials must be set manually)')];
        // img/ writable
        $imgW = is_dir(__DIR__ . '/img/') && is_writable(__DIR__ . '/img/');
        $checks[] = [$imgW ? 'pass' : 'warn', 'fa-solid fa-folder-open', 'img/ directory writable — ' . ($imgW ? 'yes' : 'no (logo/favicon uploads will fail)')];
        // Root writable (for install.lock)
        $rootW = is_writable(__DIR__);
        $checks[] = [$rootW ? 'pass' : 'warn', 'fa-solid fa-lock', 'Project root writable — ' . ($rootW ? 'yes' : 'no (install.lock cannot be created)')];

        $canContinue = $phpOk && $myOk;
        ?>
        <div class="check-list">
          <?php foreach ($checks as [$status, $icon, $msg]): ?>
            <div class="check-item <?= $status ?>">
              <i class="<?= $icon ?> fa-fw"></i> <?= install_h($msg) ?>
            </div>
          <?php endforeach; ?>
        </div>
        <?php if (!$canContinue): ?>
          <div class="alert alert-err" style="margin-top:1rem">
            <i class="fa-solid fa-triangle-exclamation"></i> Critical requirements not met. Fix the errors above
            before proceeding.
          </div>
        <?php endif; ?>
        <form method="POST">
          <input type="hidden" name="step" value="1">
          <div class="btn-footer" style="justify-content:flex-end">
            <button class="btn btn-primary" <?= !$canContinue ? 'disabled' : '' ?>>Continue <i
                class="fa-solid fa-arrow-right"></i></button>
          </div>
        </form>

      <?php /* ============================================================
     STEP 2 — DATABASE CONFIGURATION
     ============================================================ */ elseif ($currentStep === 2): ?>

        <h2><i class="fa-solid fa-database" style="color:#1d4ed8;margin-right:.5rem"></i>Database Configuration
        </h2>
        <p class="subtitle">Enter your MySQL connection details. The database will be created if it does not
          exist.</p>
        <?php if (!empty($errors)): ?>
          <div class="alert alert-err"><i class="fa-solid fa-triangle-exclamation"></i> Please fix the errors
            below.</div>
        <?php endif; ?>
        <form method="POST">
          <input type="hidden" name="step" value="2">
          <div class="form-grid" style="margin-bottom:1rem">
            <div class="field" style="grid-column:span 2">
              <label>Database Host</label>
              <input type="text" name="db_host" value="<?= install_val('db_host', '127.0.0.1') ?>"
                placeholder="127.0.0.1">
              <?= install_field_err($errors, 'db_host') ?>
            </div>
            <div class="field">
              <label>Port</label>
              <input type="number" name="db_port" value="<?= install_val('db_port', '3306') ?>"
                placeholder="3306">
              <?= install_field_err($errors, 'db_port') ?>
            </div>
            <div class="field">
              <label>Database Name</label>
              <input type="text" name="db_name" value="<?= install_val('db_name') ?>"
                placeholder="fresh_bank">
              <?= install_field_err($errors, 'db_name') ?>
            </div>
            <div class="field">
              <label>Database User</label>
              <input type="text" name="db_user" value="<?= install_val('db_user', 'root') ?>"
                placeholder="root" autocomplete="username">
              <?= install_field_err($errors, 'db_user') ?>
            </div>
            <div class="field">
              <label>Database Password</label>
              <input type="password" name="db_pass" value="" placeholder="(leave blank if none)"
                autocomplete="current-password">
              <?= install_field_err($errors, 'db_pass') ?>
            </div>
          </div>
          <div class="btn-footer">
            <a href="?step=1" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back</a>
            <button class="btn btn-primary">Test & Continue <i class="fa-solid fa-arrow-right"></i></button>
          </div>
        </form>

      <?php /* ============================================================
     STEP 3 — ADMIN ACCOUNT + SITE INFO
     ============================================================ */ elseif ($currentStep === 3): ?>

        <h2><i class="fa-solid fa-user-shield" style="color:#1d4ed8;margin-right:.5rem"></i>Admin Account &amp;
          Site Info</h2>
        <p class="subtitle">Create the first admin account and configure basic site details.</p>
        <?php if (!empty($errors)): ?>
          <div class="alert alert-err"><i class="fa-solid fa-triangle-exclamation"></i> Please fix the errors
            below.</div>
        <?php endif; ?>
        <form method="POST">
          <input type="hidden" name="step" value="3">

          <p
            style="font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;margin-bottom:.75rem">
            <i class="fa-solid fa-user-tie fa-fw"></i> Administrator
          </p>
          <div class="form-grid" style="margin-bottom:1.25rem">
            <div class="field">
              <label>Username</label>
              <input type="text" name="admin_user" value="<?= install_val('admin_user', 'admin') ?>"
                autocomplete="username">
              <?= install_field_err($errors, 'admin_user') ?>
            </div>
            <div class="field">
              <label>Email</label>
              <input type="email" name="admin_email" value="<?= install_val('admin_email') ?>"
                autocomplete="email">
              <?= install_field_err($errors, 'admin_email') ?>
            </div>
            <div class="field">
              <label>Password</label>
              <input type="password" name="admin_pass" value="" autocomplete="new-password">
              <?= install_field_err($errors, 'admin_pass') ?>
            </div>
            <div class="field">
              <label>Confirm Password</label>
              <input type="password" name="admin_pass2" value="" autocomplete="new-password">
              <?= install_field_err($errors, 'admin_pass2') ?>
            </div>
          </div>

          <p
            style="font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;margin-bottom:.75rem">
            <i class="fa-solid fa-landmark fa-fw"></i> Bank / Site Info
          </p>
          <div class="form-grid">
            <div class="field" style="grid-column:span 2">
              <label>Bank/Site Name</label>
              <input type="text" name="site_name" value="<?= install_val('site_name') ?>"
                placeholder="First National Bank">
              <?= install_field_err($errors, 'site_name') ?>
            </div>
            <div class="field" style="grid-column:span 2">
              <label>Site URL</label>
              <input type="url" name="site_url" value="<?= install_val('site_url') ?>"
                placeholder="https://yourbank.com">
              <?= install_field_err($errors, 'site_url') ?>
            </div>
            <div class="field">
              <label>Phone</label>
              <input type="text" name="site_phone" value="<?= install_val('site_phone') ?>">
              <?= install_field_err($errors, 'site_phone') ?>
            </div>
            <div class="field">
              <label>Contact Email</label>
              <input type="email" name="site_email" value="<?= install_val('site_email') ?>">
              <?= install_field_err($errors, 'site_email') ?>
            </div>
            <div class="field" style="grid-column:span 2">
              <label>Address</label>
              <input type="text" name="site_addr" value="<?= install_val('site_addr') ?>"
                placeholder="123 Main St, City, Country">
              <?= install_field_err($errors, 'site_addr') ?>
            </div>
            <div class="field">
              <label>Brand Colour</label>
              <input type="color" name="site_color" value="<?= install_val('site_color', '#1d4ed8') ?>"
                style="height:2.4rem;cursor:pointer">
              <?= install_field_err($errors, 'site_color') ?>
            </div>
          </div>
          <div class="btn-footer">
            <a href="?step=2" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back</a>
            <button class="btn btn-primary">Continue <i class="fa-solid fa-arrow-right"></i></button>
          </div>
        </form>

      <?php /* ============================================================
     STEP 4 — BRANDING
     ============================================================ */ elseif ($currentStep === 4): ?>

        <h2><i class="fa-solid fa-palette" style="color:#1d4ed8;margin-right:.5rem"></i>Branding</h2>
        <p class="subtitle">Upload your logos and favicon. All files are optional — you can change these later
          in Admin &rarr; Settings.</p>
        <?php if (!empty($errors)): ?>
          <div class="alert alert-err"><i class="fa-solid fa-triangle-exclamation"></i> Please fix the errors
            below.</div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
          <input type="hidden" name="step" value="4">
          <div class="form-grid one" style="gap:1.25rem">

            <div class="field">
              <label>Auth Logo <span style="font-weight:400;color:#94a3b8">(optional —
                  login/register/OTP/forgot password)</span></label>
              <label class="file-zone" id="lz-auth_logo">
                <input type="file" name="auth_logo" accept="image/*"
                  onchange="previewFile(this,'lz-auth_logo','prev-auth_logo')">
                <i class="fa-solid fa-cloud-arrow-up fa-lg"></i>
                <span id="prev-auth_logo">
                  <?= install_val('auth_logo_url') ? '&#10003; ' . install_val('auth_logo_url') . ' (re-upload to replace)' : 'Click or drag PNG / JPG / SVG — max 2 MB' ?>
                </span>
              </label>
              <?= install_field_err($errors, 'auth_logo') ?>
            </div>

            <div class="field">
              <label>Dashboard Logo <span style="font-weight:400;color:#94a3b8">(optional — customer
                  dashboard pages)</span></label>
              <label class="file-zone" id="lz-dashboard_logo">
                <input type="file" name="dashboard_logo" accept="image/*"
                  onchange="previewFile(this,'lz-dashboard_logo','prev-dashboard_logo')">
                <i class="fa-solid fa-cloud-arrow-up fa-lg"></i>
                <span id="prev-dashboard_logo">
                  <?= install_val('dashboard_logo_url') ? '&#10003; ' . install_val('dashboard_logo_url') . ' (re-upload to replace)' : 'Click or drag PNG / JPG / SVG — max 2 MB' ?>
                </span>
              </label>
              <?= install_field_err($errors, 'dashboard_logo') ?>
            </div>

            <div class="field">
              <label>Frontend / Customer Logo (Legacy) <span
                  style="font-weight:400;color:#94a3b8">(optional fallback for older
                  templates)</span></label>
              <label class="file-zone" id="lz-frontend_logo">
                <input type="file" name="frontend_logo" accept="image/*"
                  onchange="previewFile(this,'lz-frontend_logo','prev-frontend_logo')">
                <i class="fa-solid fa-cloud-arrow-up fa-lg"></i>
                <span id="prev-frontend_logo">
                  <?= install_val('frontend_logo_url') ? '&#10003; ' . install_val('frontend_logo_url') . ' (re-upload to replace)' : 'Click or drag PNG / JPG / SVG — max 2 MB' ?>
                </span>
              </label>
              <?= install_field_err($errors, 'frontend_logo') ?>
            </div>

            <div class="field">
              <label>Admin Panel Logo (Legacy) <span style="font-weight:400;color:#94a3b8">(optional
                  fallback for older templates)</span></label>
              <label class="file-zone" id="lz-admin_logo">
                <input type="file" name="admin_logo" accept="image/*"
                  onchange="previewFile(this,'lz-admin_logo','prev-admin_logo')">
                <i class="fa-solid fa-cloud-arrow-up fa-lg"></i>
                <span id="prev-admin_logo">
                  <?= install_val('admin_logo_url') ? '&#10003; ' . install_val('admin_logo_url') . ' (re-upload to replace)' : 'Click or drag PNG / JPG / SVG — max 2 MB' ?>
                </span>
              </label>
              <?= install_field_err($errors, 'admin_logo') ?>
            </div>

            <div class="field">
              <label>Favicon <span style="font-weight:400;color:#94a3b8">(optional — .ico, .png,
                  .svg)</span></label>
              <label class="file-zone" id="lz-favicon">
                <input type="file" name="favicon" accept=".ico,.png,.svg,.gif"
                  onchange="previewFile(this,'lz-favicon','prev-favicon')">
                <i class="fa-solid fa-cloud-arrow-up fa-lg"></i>
                <span id="prev-favicon">
                  <?= install_val('site_favicon') ? '&#10003; ' . install_val('site_favicon') . ' (re-upload to replace)' : 'Click or drag ICO / PNG / SVG — max 2 MB' ?>
                </span>
              </label>
              <?= install_field_err($errors, 'favicon') ?>
            </div>
          </div>
          <div class="btn-footer">
            <a href="?step=3" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back</a>
            <button class="btn btn-primary">Continue <i class="fa-solid fa-arrow-right"></i></button>
          </div>
        </form>
        <script>
          function previewFile(input, zoneId, previewId) {
            const el = document.getElementById(previewId);
            if (input.files && input.files[0]) {
              el.textContent = '✓ ' + input.files[0].name;
              document.getElementById(zoneId).style.borderColor = '#16a34a';
            }
          }
        </script>

      <?php /* ============================================================
     STEP 5 — SMTP
     ============================================================ */ elseif ($currentStep === 5): ?>

        <h2><i class="fa-solid fa-envelope" style="color:#1d4ed8;margin-right:.5rem"></i>Email / SMTP</h2>
        <p class="subtitle">SMTP is optional. Leave the host blank to skip email notifications for now — you can
          configure this in Admin &rarr; Settings.</p>
        <?php if (!empty($errors)): ?>
          <div class="alert alert-err"><i class="fa-solid fa-triangle-exclamation"></i> Please fix the errors
            below.</div>
        <?php endif; ?>
        <form method="POST">
          <input type="hidden" name="step" value="5">
          <div class="form-grid">
            <div class="field" style="grid-column:span 2">
              <label>SMTP Host <span style="font-weight:400;color:#94a3b8">(optional)</span></label>
              <input type="text" name="smtp_host" value="<?= install_val('smtp_host') ?>"
                placeholder="smtp.gmail.com">
              <?= install_field_err($errors, 'smtp_host') ?>
            </div>
            <div class="field">
              <label>Port</label>
              <input type="number" name="smtp_port" value="<?= install_val('smtp_port', '465') ?>"
                placeholder="465">
              <?= install_field_err($errors, 'smtp_port') ?>
            </div>
            <div class="field">
              <label>Security</label>
              <select name="smtp_secure">
                <?php foreach (['ssl' => 'SSL', 'tls' => 'TLS', '' => 'None'] as $v => $l): ?>
                  <option value="<?= $v ?>"
                    <?= install_val('smtp_secure', 'ssl') === $v ? 'selected' : '' ?>><?= $l ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="field">
              <label>Username</label>
              <input type="email" name="smtp_username" value="<?= install_val('smtp_username') ?>"
                placeholder="user@example.com">
            </div>
            <div class="field">
              <label>Password</label>
              <input type="password" name="smtp_password" value="" autocomplete="new-password">
            </div>
            <div class="field">
              <label>From Address</label>
              <input type="email" name="smtp_from" value="<?= install_val('smtp_from') ?>"
                placeholder="noreply@yourbank.com">
              <?= install_field_err($errors, 'smtp_from') ?>
            </div>
            <div class="field">
              <label>From Name</label>
              <input type="text" name="smtp_from_name" value="<?= install_val('smtp_from_name') ?>"
                placeholder="First National Bank">
            </div>
            <div class="field" style="grid-column:span 2">
              <label>Reply-To <span style="font-weight:400;color:#94a3b8">(optional)</span></label>
              <input type="email" name="smtp_reply_to" value="<?= install_val('smtp_reply_to') ?>"
                placeholder="support@yourbank.com">
              <?= install_field_err($errors, 'smtp_reply_to') ?>
            </div>
          </div>
          <div class="btn-footer">
            <a href="?step=4" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back</a>
            <button class="btn btn-primary">Continue <i class="fa-solid fa-arrow-right"></i></button>
          </div>
        </form>

      <?php /* ============================================================
     STEP 6 — REVIEW
     ============================================================ */ elseif ($currentStep === 6): ?>

        <h2><i class="fa-solid fa-clipboard-check" style="color:#1d4ed8;margin-right:.5rem"></i>Review
          Configuration</h2>
        <p class="subtitle">Confirm your settings before installation begins.</p>

        <table class="review-table">
          <thead>
            <tr>
              <th colspan="2">Database</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Host</td>
              <td><?= install_val('db_host') ?>:<?= install_val('db_port', '3306') ?></td>
            </tr>
            <tr>
              <td>Database</td>
              <td><?= install_val('db_name') ?></td>
            </tr>
            <tr>
              <td>User</td>
              <td><?= install_val('db_user') ?></td>
            </tr>
            <tr>
              <td>Password</td>
              <td><?= install_val('db_pass') !== '' ? '••••••••' : '(none)' ?></td>
            </tr>
          </tbody>
          <thead>
            <tr>
              <th colspan="2">Admin Account</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Username</td>
              <td><?= install_val('admin_user') ?></td>
            </tr>
            <tr>
              <td>Email</td>
              <td><?= install_val('admin_email') ?></td>
            </tr>
            <tr>
              <td>Password</td>
              <td>••••••••</td>
            </tr>
          </tbody>
          <thead>
            <tr>
              <th colspan="2">Site Info</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Bank Name</td>
              <td><?= install_val('site_name') ?></td>
            </tr>
            <tr>
              <td>Site URL</td>
              <td><?= install_val('site_url') ?></td>
            </tr>
            <tr>
              <td>Phone</td>
              <td><?= install_val('site_phone') ?: '—' ?></td>
            </tr>
            <tr>
              <td>Email</td>
              <td><?= install_val('site_email') ?: '—' ?></td>
            </tr>
            <tr>
              <td>Address</td>
              <td><?= install_val('site_addr') ?: '—' ?></td>
            </tr>
            <tr>
              <td>Brand Colour</td>
              <td>
                <span
                  style="display:inline-block;width:16px;height:16px;border-radius:3px;background:<?= install_val('site_color', '#1d4ed8') ?>;vertical-align:middle;margin-right:.35rem;border:1px solid #e2e8f0"></span>
                <?= install_val('site_color', '#1d4ed8') ?>
              </td>
            </tr>
          </tbody>
          <thead>
            <tr>
              <th colspan="2">Branding</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Auth Logo</td>
              <td><?= install_val('auth_logo_url') ?: '— (not set)' ?></td>
            </tr>
            <tr>
              <td>Dashboard Logo</td>
              <td><?= install_val('dashboard_logo_url') ?: '— (not set)' ?></td>
            </tr>
            <tr>
              <td>Frontend Logo</td>
              <td><?= install_val('frontend_logo_url') ?: '— (not set)' ?></td>
            </tr>
            <tr>
              <td>Admin Logo</td>
              <td><?= install_val('admin_logo_url') ?: '— (not set)' ?></td>
            </tr>
            <tr>
              <td>Favicon</td>
              <td><?= install_val('site_favicon') ?: '— (not set)' ?></td>
            </tr>
          </tbody>
          <thead>
            <tr>
              <th colspan="2">SMTP</th>
            </tr>
          </thead>
          <tbody>
            <?php if (install_val('smtp_host') !== ''): ?>
              <tr>
                <td>Host</td>
                <td><?= install_val('smtp_host') ?>:<?= install_val('smtp_port', '465') ?>
                  (<?= install_val('smtp_secure', 'ssl') ?>)</td>
              </tr>
              <tr>
                <td>From</td>
                <td><?= install_val('smtp_from_name') ?> &lt;<?= install_val('smtp_from') ?>&gt;</td>
              </tr>
            <?php else: ?>
              <tr>
                <td colspan="2" style="color:#94a3b8">SMTP not configured — email notifications will be
                  disabled.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>

        <div class="alert alert-warn" style="margin-top:1.25rem">
          <i class="fa-solid fa-triangle-exclamation"></i>
          <strong>This will write to your database and config files.</strong> Make sure these details are
          correct before continuing.
        </div>

        <form method="POST">
          <input type="hidden" name="step" value="7">
          <div class="btn-footer">
            <a href="?step=5" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back</a>
            <button class="btn btn-success"><i class="fa-solid fa-rocket"></i> Install Now</button>
          </div>
        </form>

      <?php /* ============================================================
     STEP 7 — INSTALLING (redirect loop hits step 7 via POST, then goes to 8)
     This page only renders if there was a fatal DB connect error via goto render
     ============================================================ */ elseif ($currentStep === 7): ?>

        <h2><i class="fa-solid fa-spinner fa-spin"
            style="color:#1d4ed8;margin-right:.5rem"></i>Installing&hellip;</h2>
        <p class="subtitle">Please wait while the database and configuration are being set up.</p>
        <?php if (!empty($log)): ?>
          <div class="log-list" style="margin-top:1rem">
            <?php foreach ($log as [$type, $msg]): ?>
              <div class="log-item <?= $type ?>">
                <i
                  class="fa-solid <?= $type === 'ok' ? 'fa-check' : ($type === 'warn' ? 'fa-triangle-exclamation' : 'fa-xmark') ?> fa-fw"></i>
                <?= install_h($msg) ?>
              </div>
            <?php endforeach; ?>
          </div>
          <?php if (!empty(array_filter($log, fn($l) => $l[0] === 'err'))): ?>
            <div class="alert alert-err" style="margin-top:1rem">
              <i class="fa-solid fa-xmark"></i> Installation failed. Review errors above and try again.
            </div>
            <div class="btn-footer" style="margin-top:1.25rem">
              <a href="?step=6" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back to
                Review</a>
            </div>
          <?php endif; ?>
        <?php endif; ?>

      <?php /* ============================================================
     STEP 8 — COMPLETE
     ============================================================ */ elseif ($currentStep === 8): ?>

        <div class="done-icon">&#127881;</div>
        <h2 style="text-align:center;margin-bottom:.3rem">Installation Complete!</h2>
        <p class="subtitle" style="text-align:center">Your banking platform has been installed successfully.</p>

        <?php if (!empty($log)): ?>
          <div class="log-list" style="margin-top:1rem;margin-bottom:1.25rem">
            <?php foreach ($log as [$type, $msg]): ?>
              <div class="log-item <?= $type ?>">
                <i
                  class="fa-solid <?= $type === 'ok' ? 'fa-check' : ($type === 'warn' ? 'fa-triangle-exclamation' : 'fa-xmark') ?> fa-fw"></i>
                <?= install_h($msg) ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <div class="alert alert-ok">
          <i class="fa-solid fa-lock"></i>
          <strong>install.lock has been created.</strong> This wizard is now disabled. For security, you may
          also delete <code>install.php</code> from your server.
        </div>

        <div class="done-actions">
          <a href="user/login.php" class="btn btn-primary"><i class="fa-solid fa-right-to-bracket"></i> Go to
            Customer Login</a>
          <a href="user/admin/" class="btn btn-secondary"><i class="fa-solid fa-shield-halved"></i> Admin
            Panel</a>
        </div>

      <?php endif; ?>
    </div><!-- /card -->
  </div><!-- /wizard -->
</body>

</html>