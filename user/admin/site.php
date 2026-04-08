<?php
session_start();
include_once ('session.php');
if(!isset($_SESSION['email'])){
	
header("Location: login.php");

exit(); 
}
require_once 'class.admin.php';
require dirname(__DIR__, 2) . '/config.php';

$reg_user = new USER();

function site_setting_get(mysqli $conn, string $key, string $default = ''): string {
  $safe = $conn->real_escape_string($key);
  try {
    $res = $conn->query("SELECT setting_value FROM site_settings WHERE setting_key='" . $safe . "' LIMIT 1");
    if ($res && $res->num_rows > 0) {
      $row = $res->fetch_assoc();
      return (string)($row['setting_value'] ?? $default);
    }
  } catch (Throwable $e) {
  }
  try {
    $legacy = $conn->query("SELECT `value` FROM site_settings WHERE `key`='" . $safe . "' LIMIT 1");
    if ($legacy && $legacy->num_rows > 0) {
      $row = $legacy->fetch_assoc();
      return (string)($row['value'] ?? $default);
    }
  } catch (Throwable $e) {
  }
  return $default;
}

function site_setting_set(mysqli $conn, string $key, string $value): void {
  $safeKey = $conn->real_escape_string($key);
  $safeVal = $conn->real_escape_string($value);
  try {
    $conn->query("INSERT INTO site_settings (setting_key, setting_value) VALUES ('" . $safeKey . "', '" . $safeVal . "') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
  } catch (Throwable $e) {
    $conn->query("INSERT INTO site_settings (`key`, `value`) VALUES ('" . $safeKey . "', '" . $safeVal . "') ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");
  }
}

function handle_site_upload(string $fieldName, string $destDir, array $allowedExt, int $maxBytes, string $prefix): array {
  if (!isset($_FILES[$fieldName]) || !is_array($_FILES[$fieldName])) {
    return ['file' => null, 'error' => '', 'path' => null];
  }

  $file = $_FILES[$fieldName];
  $errCode = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
  if ($errCode === UPLOAD_ERR_NO_FILE || (string)($file['name'] ?? '') === '') {
    return ['file' => null, 'error' => '', 'path' => null];
  }
  if ($errCode !== UPLOAD_ERR_OK) {
    return ['file' => null, 'error' => 'Upload failed. Please try again.', 'path' => null];
  }

  $size = (int)($file['size'] ?? 0);
  if ($size <= 0 || $size > $maxBytes) {
    return ['file' => null, 'error' => 'File is too large. Maximum size is 2MB.', 'path' => null];
  }

  $originalName = (string)($file['name'] ?? '');
  $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
  if (!in_array($ext, $allowedExt, true)) {
    return ['file' => null, 'error' => 'Unsupported file type.', 'path' => null];
  }

  $newName = $prefix . '-' . date('YmdHis') . '-' . mt_rand(1000, 9999) . '.' . $ext;

  $tmpPath = (string)($file['tmp_name'] ?? '');
  if ($tmpPath === '' || !is_file($tmpPath)) {
    return ['file' => null, 'error' => 'Temporary upload file is missing.', 'path' => null];
  }

  $candidateDirs = [
    $destDir,
    dirname(__DIR__) . '/img',
    dirname(__DIR__, 2) . '/img',
  ];

  foreach ($candidateDirs as $dir) {
    if (!is_dir($dir)) {
      @mkdir($dir, 0777, true);
    }
    if (!is_dir($dir)) {
      continue;
    }
    if (!is_writable($dir)) {
      @chmod($dir, 0777);
    }

    $destPath = rtrim($dir, '/') . '/' . $newName;
    $saved = @move_uploaded_file($tmpPath, $destPath);
    if (!$saved && is_file($tmpPath)) {
      $saved = @copy($tmpPath, $destPath);
    }
    if ($saved && is_file($destPath)) {
      return ['file' => $newName, 'error' => '', 'path' => $destPath];
    }
  }

  return ['file' => null, 'error' => 'Could not save uploaded file.', 'path' => null];
}

function normalize_livechat_embed(string $raw): string {
  $raw = trim($raw);
  if ($raw === '') {
    return '';
  }

  // Guard against accidental dangling open script tags pasted in admin.
  $raw = preg_replace('/<script>\s*$/i', '', $raw) ?? $raw;

  // If admin pasted a plain Tawk property/widget ID, convert to full embed script.
  if (stripos($raw, '<script') === false && strpos($raw, '<') === false) {
    if (preg_match('~^[A-Za-z0-9]+/[A-Za-z0-9]+$~', $raw)) {
      return "<!--Start of Tawk.to Script--><script type=\"text/javascript\">var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();(function(){var s1=document.createElement(\"script\"),s0=document.getElementsByTagName(\"script\")[0];s1.async=true;s1.src='https://embed.tawk.to/{$raw}';s1.charset='UTF-8';s1.setAttribute('crossorigin','*');s0.parentNode.insertBefore(s1,s0);})();</script><!--End of Tawk.to Script-->";
    }
  }

  return $raw;
}

if(isset($_GET['id'])){
  $id = (int)$_GET['id'];
} else {
  $id = 1;
}

$stmt = $reg_user->runQuery("SELECT * FROM site WHERE id='" . (int)$id . "'");
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) {
  $stmt = $reg_user->runQuery("SELECT * FROM site ORDER BY id ASC LIMIT 1");
  $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  $id = (int)($row['id'] ?? 1);
}

$currentFavicon = site_setting_get($conn, 'site_favicon', '');

if(isset($_POST['upgrade']))
{
  $name  = trim((string)($_POST['name'] ?? ''));
  $phone = trim((string)($_POST['phone'] ?? ''));
  $email = trim((string)($_POST['email'] ?? ''));
  $addr  = trim((string)($_POST['addr'] ?? ''));
  $tawk  = normalize_livechat_embed((string)($_POST['tawk'] ?? ''));

  $uploadDir = __DIR__ . '/site';
  $logoResult = handle_site_upload('image', $uploadDir, ['jpeg', 'jpg', 'png', 'gif', 'webp'], 2097152, 'logo');
  $favResult = handle_site_upload('favicon', $uploadDir, ['ico', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'], 2097152, 'favicon');

  $warnings = [];
  if ($logoResult['error'] !== '') {
    $warnings[] = 'Logo upload: ' . $logoResult['error'];
  }
  if ($favResult['error'] !== '') {
    $warnings[] = 'Favicon upload: ' . $favResult['error'];
  }

  $image = (string)($row['image'] ?? '');
  if (is_string($logoResult['file']) && $logoResult['file'] !== '') {
    $image = $logoResult['file'];
    $logoAbs = (string)($logoResult['path'] ?? '');
    if ($logoAbs === '') {
      $logoAbs = $uploadDir . '/' . $image;
    }
    @copy($logoAbs, dirname(__DIR__) . '/img/logo.png');
    @copy($logoAbs, dirname(__DIR__) . '/img/sc.png');
    @copy($logoAbs, dirname(__DIR__, 2) . '/themes/theme1/images/logo.png');
    @copy($logoAbs, dirname(__DIR__, 2) . '/themes/theme1/images/logo-footer.png');
  }

  if (is_string($favResult['file']) && $favResult['file'] !== '') {
    $currentFavicon = $favResult['file'];
    site_setting_set($conn, 'site_favicon', $currentFavicon);
    $favAbs = (string)($favResult['path'] ?? '');
    if ($favAbs === '') {
      $favAbs = $uploadDir . '/' . $currentFavicon;
    }
    
    // Ensure favicon is copied to all required locations
    $favicopaths = [
      dirname(__DIR__) . '/img/favicon.png',
      dirname(__DIR__) . '/img/favicon-32x32.png',
      dirname(__DIR__) . '/img/favicon-96x96.png',
      dirname(__DIR__) . '/img/favicon-16x16.png',
      __DIR__ . '/img/favicon.png',
      dirname(__DIR__, 2) . '/img/favicon.png',
      dirname(__DIR__, 2) . '/img/favicon-32x32.png',
      dirname(__DIR__, 2) . '/img/favicon-96x96.png',
      dirname(__DIR__, 2) . '/img/favicon-16x16.png',
      dirname(__DIR__, 2) . '/themes/theme1/img/favicon-32x32.png',
      dirname(__DIR__, 2) . '/themes/theme1/img/favicon-96x96.png',
      dirname(__DIR__, 2) . '/themes/theme1/img/favicon-16x16.png',
      dirname(__DIR__, 2) . '/themes/theme1/images/favicon.png',
    ];
    
    foreach ($favicopaths as $favPath) {
      $favDir = dirname($favPath);
      if (!is_dir($favDir)) {
        @mkdir($favDir, 0777, true);
      }
      if (is_dir($favDir) && is_writable($favDir)) {
        @copy($favAbs, $favPath);
      }
    }
  }

    $safeName  = $conn->real_escape_string($name);
    $safePhone = $conn->real_escape_string($phone);
    $safeEmail = $conn->real_escape_string($email);
    $safeAddr  = $conn->real_escape_string($addr);
    $safeTawk  = $conn->real_escape_string($tawk);
    $safeImage = $conn->real_escape_string($image);

    $conn->query("UPDATE site SET name='$safeName', phone='$safePhone', email='$safeEmail', addr='$safeAddr', tawk='$safeTawk', image='$safeImage' WHERE id=" . (int)$id);

    $stmt = $reg_user->runQuery("SELECT * FROM site WHERE id='" . (int)$id . "'");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

  $msg = "
    <div class='alert alert-success'>
      <button class='close' data-dismiss='alert'>&times;</button>
      <strong>Website details updated successfully.</strong>
    </div>
  ";
  if (!empty($warnings)) {
    $msg .= "<div class='alert alert-warning mt-2'><button class='close' data-dismiss='alert'>&times;</button><strong>" . htmlspecialchars(implode(' ', $warnings)) . "</strong></div>";
  }
}
$pageTitle = 'Site Info';

// ── Branches ──────────────────────────────────────────────────────────
$conn->query("CREATE TABLE IF NOT EXISTS site_branches (
  id INT AUTO_INCREMENT PRIMARY KEY,
  branch_name VARCHAR(100) NOT NULL DEFAULT '',
  address     VARCHAR(255) NOT NULL DEFAULT '',
  phone       VARCHAR(50)  NOT NULL DEFAULT '',
  sort_order  INT          NOT NULL DEFAULT 99,
  is_active   TINYINT(1)   NOT NULL DEFAULT 1,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$branchMsg = '';
$allBranches = [];
$branchRes = $conn->query("SELECT * FROM site_branches ORDER BY sort_order, id");
if ($branchRes) while ($b = $branchRes->fetch_assoc()) $allBranches[] = $b;

if (isset($_POST['add_branch'])) {
    $bName  = trim($conn->real_escape_string($_POST['branch_name']  ?? ''));
    $bAddr  = trim($conn->real_escape_string($_POST['branch_addr']  ?? ''));
    $bPhone = trim($conn->real_escape_string($_POST['branch_phone'] ?? ''));
    $bOrder = (int)($_POST['branch_order'] ?? 99);
    if ($bName && $bAddr) {
        $conn->query("INSERT INTO site_branches (branch_name,address,phone,sort_order) VALUES ('$bName','$bAddr','$bPhone',$bOrder)");
        $branchMsg = "<div class='alert alert-success'><button class='close' data-dismiss='alert'>&times;</button><strong>Branch added.</strong></div>";
    } else {
        $branchMsg = "<div class='alert alert-danger'><button class='close' data-dismiss='alert'>&times;</button><strong>Branch name and address are required.</strong></div>";
    }
    $branchRes = $conn->query("SELECT * FROM site_branches ORDER BY sort_order, id");
    $allBranches = []; while ($b = $branchRes->fetch_assoc()) $allBranches[] = $b;
}
if (isset($_POST['update_branch'])) {
    $bId    = (int)($_POST['branch_id']      ?? 0);
    $bName  = trim($conn->real_escape_string($_POST['branch_name_e']  ?? ''));
    $bAddr  = trim($conn->real_escape_string($_POST['branch_addr_e']  ?? ''));
    $bPhone = trim($conn->real_escape_string($_POST['branch_phone_e'] ?? ''));
    $bOrder = (int)($_POST['branch_order_e'] ?? 99);
    if ($bId && $bName) {
        $conn->query("UPDATE site_branches SET branch_name='$bName', address='$bAddr', phone='$bPhone', sort_order=$bOrder WHERE id=$bId");
    }
    header('Location: ' . $_SERVER['PHP_SELF'] . '?id=' . (int)($_GET['id'] ?? 20)); exit();
}
if (isset($_POST['toggle_branch'])) {
    $bId = (int)$_POST['toggle_branch'];
    $conn->query("UPDATE site_branches SET is_active = 1 - is_active WHERE id = $bId");
    header('Location: ' . $_SERVER['PHP_SELF'] . '?id=' . (int)($_GET['id'] ?? 20)); exit();
}
if (isset($_POST['delete_branch'])) {
    $bId = (int)$_POST['delete_branch'];
    $conn->query("DELETE FROM site_branches WHERE id = $bId");
    header('Location: ' . $_SERVER['PHP_SELF'] . '?id=' . (int)($_GET['id'] ?? 20)); exit();
}

require_once __DIR__ . '/partials/admin-shell-open.php';
?>

<?php if(isset($msg)) echo $msg; ?>
<div class="grid grid-cols-1 xl:grid-cols-2 gap-6 items-start">
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 max-w-none">
  <h2 class="font-semibold text-gray-800 mb-5">Site Information</h2>
  <?php if(isset($row)): ?>
  <form method="POST" enctype="multipart/form-data" class="space-y-4">
    <div><label class="block text-xs font-medium text-gray-700 mb-1">Bank / Site Name</label>
      <input type="text" name="name" value="<?= htmlspecialchars($row['name'] ?? '') ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required></div>
    <div><label class="block text-xs font-medium text-gray-700 mb-1">Phone</label>
      <input type="text" name="phone" value="<?= htmlspecialchars($row['phone'] ?? '') ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
    <div><label class="block text-xs font-medium text-gray-700 mb-1">Email</label>
      <input type="email" name="email" value="<?= htmlspecialchars($row['email'] ?? '') ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
    <div><label class="block text-xs font-medium text-gray-700 mb-1">Address</label>
      <input type="text" name="addr" value="<?= htmlspecialchars($row['addr'] ?? '') ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
    <div>
      <label class="block text-xs font-medium text-gray-700 mb-1">Live Chat Embed Script (Tawk / Any Provider)</label>
      <textarea name="tawk" rows="4" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Paste full live chat embed script. You can also paste a plain Tawk ID like 64a198a694cf5d49dc611232/1h4bjojco"><?= htmlspecialchars((string)($row['tawk'] ?? '')) ?></textarea>
      <p class="mt-1 text-xs text-gray-500">This script is rendered on user dashboard/auth pages and frontend themes.</p>
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-700 mb-1">Logo Image</label>
      <input type="file" name="image" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 !py-1.5" accept="image/*">
      <?php if (!empty($row['image']) && is_file(__DIR__ . '/site/' . $row['image'])): ?>
        <p class="mt-2 text-xs text-gray-500">Current logo:</p>
        <img src="site/<?= htmlspecialchars($row['image']) ?>" alt="Current logo" class="mt-1 h-12 w-auto rounded border border-gray-200 bg-gray-50 p-1">
      <?php endif; ?>
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-700 mb-1">Favicon</label>
      <input type="file" name="favicon" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 !py-1.5" accept=".ico,image/png,image/x-icon,image/svg+xml,image/*">
      <?php if (!empty($currentFavicon) && is_file(__DIR__ . '/site/' . $currentFavicon)): ?>
        <p class="mt-2 text-xs text-gray-500">Current favicon:</p>
        <img src="site/<?= htmlspecialchars($currentFavicon) ?>" alt="Current favicon" class="mt-1 h-8 w-8 rounded border border-gray-200 bg-gray-50 p-1">
      <?php endif; ?>
    </div>
    <div class="flex gap-3">
      <button type="submit" name="upgrade" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors cursor-pointer"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
    </div>
  </form>
  <?php endif; ?>
</div>

<?php if($branchMsg) echo $branchMsg; ?>

<!-- Branch Addresses -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 max-w-none">
  <h2 class="font-semibold text-gray-800 mb-5">Branch Addresses</h2>
  <!-- Add form -->
  <form method="POST" class="mb-6">
    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Add Branch</p>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
      <div><label class="block text-xs font-medium text-gray-700 mb-1">Branch Name <span class="text-red-500">*</span></label>
        <input type="text" name="branch_name" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Downtown Branch" required></div>
      <div><label class="block text-xs font-medium text-gray-700 mb-1">Phone</label>
        <input type="text" name="branch_phone" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="+1 555 000 0000"></div>
      <div class="sm:col-span-2"><label class="block text-xs font-medium text-gray-700 mb-1">Address <span class="text-red-500">*</span></label>
        <input type="text" name="branch_addr" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="123 Main St, City, State 00000" required></div>
      <div><label class="block text-xs font-medium text-gray-700 mb-1">Sort Order</label>
        <input type="number" name="branch_order" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" value="99" min="0"></div>
    </div>
    <button type="submit" name="add_branch" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors cursor-pointer"><i class="fa-solid fa-plus"></i> Add Branch</button>
  </form>

  <!-- Branches table -->
  <?php if(!empty($allBranches)): ?>
  <div class="overflow-x-auto">
    <table class="min-w-full text-sm border-collapse">
      <thead>
        <tr class="bg-gray-50 border-y border-gray-200">
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Branch</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Address</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Phone</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Status</th>
          <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php foreach($allBranches as $b): ?>
        <tr class="hover:bg-gray-50">
          <td class="px-3 py-3 text-sm text-gray-700 font-medium"><?= htmlspecialchars($b['branch_name']) ?></td>
          <td class="px-3 py-3 text-sm text-gray-700"><?= htmlspecialchars($b['address']) ?></td>
          <td class="px-3 py-3 text-sm text-gray-700"><?= htmlspecialchars($b['phone']) ?></td>
          <td class="px-3 py-3 text-sm">
            <form method="POST" class="inline">
              <input type="hidden" name="toggle_branch" value="<?= $b['id'] ?>">
              <button type="submit" class="text-xs <?= $b['is_active'] ? 'text-green-600' : 'text-gray-400' ?> hover:underline"><?= $b['is_active'] ? 'Active' : 'Inactive' ?></button>
            </form>
          </td>
          <td class="px-3 py-3 text-sm flex gap-2">
            <button onclick="editBranch(<?= $b['id'] ?>,'<?= htmlspecialchars(addslashes($b['branch_name'])) ?>','<?= htmlspecialchars(addslashes($b['address'])) ?>','<?= htmlspecialchars(addslashes($b['phone'])) ?>',<?= (int)$b['sort_order'] ?>)"
              class="inline-flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium px-2 py-0.5 rounded-lg cursor-pointer"><i class="fa-solid fa-pen"></i></button>
            <form method="POST" class="inline" onsubmit="return confirm('Delete this branch?')">
              <input type="hidden" name="delete_branch" value="<?= $b['id'] ?>">
              <button type="submit" class="inline-flex items-center gap-1 bg-red-500 hover:bg-red-600 text-white text-xs font-medium px-2 py-0.5 rounded-lg cursor-pointer"><i class="fa-solid fa-trash"></i></button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php else: ?>
  <p class="text-gray-400 text-sm">No branches added yet. Use the form above to add one.</p>
  <?php endif; ?>
</div>
</div>

<!-- Edit Branch Modal -->
<div id="edit-branch-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
  <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md">
    <h3 class="font-semibold mb-4 text-gray-800">Edit Branch</h3>
    <form method="POST" class="space-y-3">
      <input type="hidden" id="branch-id" name="branch_id">
      <div><label class="block text-xs font-medium text-gray-700 mb-1">Branch Name</label>
        <input type="text" id="branch-name-e" name="branch_name_e" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required></div>
      <div><label class="block text-xs font-medium text-gray-700 mb-1">Address</label>
        <input type="text" id="branch-addr-e" name="branch_addr_e" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
      <div><label class="block text-xs font-medium text-gray-700 mb-1">Phone</label>
        <input type="text" id="branch-phone-e" name="branch_phone_e" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
      <div><label class="block text-xs font-medium text-gray-700 mb-1">Sort Order</label>
        <input type="number" id="branch-order-e" name="branch_order_e" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
      <div class="flex gap-3 justify-end pt-2">
        <button type="button" onclick="document.getElementById('edit-branch-modal').classList.add('hidden')" class="inline-flex items-center bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-medium px-3 py-1.5 rounded-lg cursor-pointer">Cancel</button>
        <button type="submit" name="update_branch" class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white text-xs font-medium px-3 py-1.5 rounded-lg cursor-pointer">Save</button>
      </div>
    </form>
  </div>
</div>
<script>
function editBranch(id, name, addr, phone, order) {
  document.getElementById('branch-id').value = id;
  document.getElementById('branch-name-e').value = name;
  document.getElementById('branch-addr-e').value = addr;
  document.getElementById('branch-phone-e').value = phone;
  document.getElementById('branch-order-e').value = order;
  document.getElementById('edit-branch-modal').classList.remove('hidden');
}
</script>

<?php require_once __DIR__ . '/partials/admin-shell-close.php'; ?>
