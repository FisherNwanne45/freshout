#!/usr/bin/env python3
"""
Admin panel Tailwind rebuild script.
Reads the PHP logic from each admin page (everything before the page <!DOCTYPE html>),
strips the old Bootstrap HTML, and injects new Tailwind content using the shared partials.
"""
import os, re

BASE = '/Applications/XAMPP/xamppfiles/htdocs/fresh/user/admin'

def read_file(path):
    with open(path, 'r', encoding='utf-8', errors='replace') as f:
        return f.read()

def write_file(path, content):
    with open(path, 'w', encoding='utf-8') as f:
        f.write(content)

def extract_php_logic(content):
    """
    Extract PHP logic: everything before the *page* <!DOCTYPE html>.
    The page DOCTYPE is HTML5 (<!DOCTYPE html>) in contrast to email templates
    which use XHTML (<!DOCTYPE html PUBLIC ...).
    We find the last occurrence of a line that is exactly/nearly <!DOCTYPE html>
    (not followed by PUBLIC).
    """
    # Match <!DOCTYPE html> on its own line (not XHTML PUBLIC)
    # We look for newline + optional whitespace + <!DOCTYPE html> NOT followed by PUBLIC/XHTML
    pattern = re.compile(r'\n\s*<!DOCTYPE html>\s*\n', re.IGNORECASE)
    matches = list(pattern.finditer(content))
    if not matches:
        # Fallback: also try <!DOCTYPE html>\r\n or at end of string
        pattern2 = re.compile(r'\n<!DOCTYPE html>', re.IGNORECASE)
        matches = list(pattern2.finditer(content))

    if not matches:
        return content  # couldn't find split

    # Use the LAST match (the actual page HTML, not email templates)
    m = matches[-1]
    php_logic = content[:m.start()]
    php_logic = php_logic.rstrip()
    # Remove trailing ?> close tag - we'll reopen PHP ourselves
    if php_logic.endswith('?>'):
        php_logic = php_logic[:-2].rstrip()
    return php_logic

# ─── Tailwind helper classes ───────────────────────────────────────────────────
INPUT  = 'w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500'
LABEL  = 'block text-xs font-medium text-gray-700 mb-1'
CARD   = 'bg-white rounded-xl shadow-sm border border-gray-200 p-6'
BTN_P  = 'inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors cursor-pointer'
BTN_G  = 'inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition-colors cursor-pointer'
BTN_R  = 'inline-flex items-center gap-2 bg-red-500 hover:bg-red-600 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition-colors cursor-pointer'
BTN_Y  = 'inline-flex items-center gap-2 bg-yellow-500 hover:bg-yellow-600 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition-colors cursor-pointer'
TH     = 'px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide'
TD     = 'px-3 py-3 text-sm text-gray-700'
FLASH  = "<?php if(isset(\$msg)) echo \$msg; ?>"

def wrap_page(php_logic, title, html_content):
    """Build final page content."""
    return (
        php_logic + "\n"
        + f"$pageTitle = '{title}';\n"
        + "require_once __DIR__ . '/partials/admin-shell-open.php';\n"
        + "?>\n"
        + html_content + "\n"
        + "<?php require_once __DIR__ . '/partials/admin-shell-close.php'; ?>\n"
    )

# ══════════════════════════════════════════════════════════════════════════════
# PAGE CONTENT DEFINITIONS
# ══════════════════════════════════════════════════════════════════════════════

def content_index():
    return f"""
{FLASH}

<!-- Stats cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  <div class="{CARD} !p-5">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
        <i class="fa-solid fa-users text-blue-600"></i>
      </div>
      <div>
        <p class="text-2xl font-bold text-gray-800"><?php printf("%d",$rowcount) ?></p>
        <p class="text-xs text-gray-500">Total Accounts</p>
      </div>
    </div>
  </div>
  <div class="{CARD} !p-5">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center flex-shrink-0">
        <i class="fa-solid fa-ticket text-orange-500"></i>
      </div>
      <div>
        <p class="text-2xl font-bold text-gray-800"><?php printf("%d",$rowcount1) ?></p>
        <p class="text-xs text-gray-500">Open Tickets</p>
      </div>
    </div>
  </div>
  <div class="{CARD} !p-5">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
        <i class="fa-solid fa-arrow-right-arrow-left text-green-600"></i>
      </div>
      <div>
        <p class="text-2xl font-bold text-gray-800"><?php printf("%d",$rowcount2) ?></p>
        <p class="text-xs text-gray-500">Transfers</p>
      </div>
    </div>
  </div>
  <div class="{CARD} !p-5">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-lg bg-yellow-100 flex items-center justify-center flex-shrink-0">
        <i class="fa-solid fa-clock text-yellow-500"></i>
      </div>
      <div>
        <p class="text-2xl font-bold text-gray-800"><?php printf("%d",$rowcount3) ?></p>
        <p class="text-xs text-gray-500">Pending Accounts</p>
      </div>
    </div>
  </div>
</div>

<!-- Quick actions -->
<div class="{CARD} mb-6">
  <h2 class="text-sm font-semibold text-gray-700 mb-4">Quick Actions</h2>
  <div class="flex flex-wrap gap-3">
    <a href="create_account.php" class="{BTN_P}"><i class="fa-solid fa-user-plus"></i> Add Account</a>
    <a href="view_account.php"   class="{BTN_P} !bg-slate-600 hover:!bg-slate-700"><i class="fa-solid fa-address-card"></i> View Accounts</a>
    <button onclick="adminModal('modal-history')"  class="{BTN_P} !bg-indigo-600 hover:!bg-indigo-700"><i class="fa-solid fa-list-check"></i> Add History</button>
    <button onclick="adminModal('modal-credit')"   class="{BTN_P} !bg-green-600 hover:!bg-green-700"><i class="fa-solid fa-circle-plus"></i> Credit Account</button>
    <button onclick="adminModal('modal-debit')"    class="{BTN_P} !bg-red-600 hover:!bg-red-700"><i class="fa-solid fa-circle-minus"></i> Debit Account</button>
    <a href="settings.php" class="{BTN_P} !bg-gray-500 hover:!bg-gray-600"><i class="fa-solid fa-gear"></i> Settings</a>
  </div>
</div>

<!-- Uploaded images -->
<div class="{CARD} mb-6">
  <h2 class="text-sm font-semibold text-gray-700 mb-3">Uploaded Profile Images</h2>
  <div class="flex flex-wrap gap-2">
    <?php
    $files = glob("foto/*.*");
    if ($files) foreach ($files as $image):
      $ext = strtolower(pathinfo($image, PATHINFO_EXTENSION));
      if (!in_array($ext, ['gif','jpg','jpeg','png'])) continue;
      $base = basename($image);
    ?>
    <img src="<?= htmlspecialchars($image) ?>" title="<?= htmlspecialchars($base) ?>"
         class="w-12 h-12 object-cover rounded-lg border border-gray-200">
    <?php endforeach; ?>
  </div>
</div>

<!-- ── MODAL: Add History ─────────────────────────────────────────────── -->
<div id="modal-history" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
      <h3 class="font-semibold text-gray-800">Add Debit / Credit History</h3>
      <button onclick="adminModal('modal-history')" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-xl"></i></button>
    </div>
    <form method="POST" class="p-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="{LABEL}">Select Account</label>
          <select name="uname" class="{INPUT}" required>
            <?php $stmt->execute(); while($r = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <option value="<?= htmlspecialchars($r['acc_no']) ?>"><?= htmlspecialchars($r['fname'].' '.$r['lname']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div>
          <label class="{LABEL}">Transaction Type</label>
          <select name="type" class="{INPUT}" required>
            <option value="Credit">Credit</option>
            <option value="Debit">Debit</option>
          </select>
        </div>
        <div>
          <label class="{LABEL}">Amount</label>
          <input type="number" step="0.01" name="amount" class="{INPUT}" placeholder="0.00" required>
        </div>
        <div>
          <label class="{LABEL}">To / From</label>
          <input type="text" name="sender_name" class="{INPUT}" placeholder="e.g. John Kennedy" required>
        </div>
        <div class="sm:col-span-2">
          <label class="{LABEL}">Description</label>
          <textarea name="remarks" rows="2" class="{INPUT}" placeholder="e.g. Wire Transfer" required></textarea>
        </div>
        <div>
          <label class="{LABEL}">Date</label>
          <input type="date" name="date" class="{INPUT}" required>
        </div>
        <div>
          <label class="{LABEL}">Time</label>
          <input type="time" name="time" class="{INPUT}" required>
        </div>
      </div>
      <div class="flex gap-3 justify-end mt-5">
        <button type="button" onclick="adminModal('modal-history')" class="{BTN_R}">Cancel</button>
        <button type="submit" name="his" class="{BTN_G}"><i class="fa-solid fa-check"></i> Add History</button>
      </div>
    </form>
  </div>
</div>

<!-- ── MODAL: Credit Account ──────────────────────────────────────────── -->
<div id="modal-credit" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
      <h3 class="font-semibold text-gray-800">Credit User&rsquo;s Account</h3>
      <button onclick="adminModal('modal-credit')" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-xl"></i></button>
    </div>
    <form method="POST" class="p-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="{LABEL}">Select Account to Credit</label>
          <select name="uname" class="{INPUT}" required>
            <?php $credit->execute(); while($r = $credit->fetch(PDO::FETCH_ASSOC)): ?>
            <option value="<?= htmlspecialchars($r['acc_no']) ?>"><?= htmlspecialchars($r['fname'].' '.$r['lname']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div>
          <label class="{LABEL}">From (Sender)</label>
          <input type="text" name="sender_name" class="{INPUT}" required>
          <input type="hidden" name="type" value="Credit">
        </div>
        <div>
          <label class="{LABEL}">Amount</label>
          <input type="number" step="0.01" name="amount" class="{INPUT}" placeholder="0.00" required>
        </div>
        <div>
          <label class="{LABEL}">Description</label>
          <textarea name="remarks" rows="2" class="{INPUT}" placeholder="e.g. Incoming Wire"></textarea>
        </div>
        <div>
          <label class="{LABEL}">Date</label>
          <input type="date" name="date" class="{INPUT}" required>
        </div>
        <div>
          <label class="{LABEL}">Time</label>
          <input type="time" name="time" class="{INPUT}" required>
        </div>
      </div>
      <div class="flex gap-3 justify-end mt-5">
        <button type="button" onclick="adminModal('modal-credit')" class="{BTN_R}">Cancel</button>
        <button type="submit" name="credit" class="{BTN_G}"><i class="fa-solid fa-circle-plus"></i> Credit Account</button>
      </div>
    </form>
  </div>
</div>

<!-- ── MODAL: Debit Account ───────────────────────────────────────────── -->
<div id="modal-debit" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
      <h3 class="font-semibold text-gray-800">Debit User&rsquo;s Account</h3>
      <button onclick="adminModal('modal-debit')" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-xl"></i></button>
    </div>
    <form method="POST" class="p-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="{LABEL}">Select Account to Debit</label>
          <select name="uname" class="{INPUT}" required>
            <?php $debit->execute(); while($r = $debit->fetch(PDO::FETCH_ASSOC)): ?>
            <option value="<?= htmlspecialchars($r['acc_no']) ?>"><?= htmlspecialchars($r['fname'].' '.$r['lname']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div>
          <label class="{LABEL}">Debit To</label>
          <input type="text" name="sender_name" class="{INPUT}" required>
          <input type="hidden" name="type" value="Debit">
        </div>
        <div>
          <label class="{LABEL}">Amount</label>
          <input type="number" step="0.01" name="amount" class="{INPUT}" placeholder="0.00" required>
        </div>
        <div>
          <label class="{LABEL}">Description</label>
          <textarea name="remarks" rows="2" class="{INPUT}" placeholder="e.g. Wire Transfer"></textarea>
        </div>
        <div>
          <label class="{LABEL}">Date</label>
          <input type="date" name="date" class="{INPUT}" required>
        </div>
        <div>
          <label class="{LABEL}">Time</label>
          <input type="time" name="time" class="{INPUT}" required>
        </div>
      </div>
      <div class="flex gap-3 justify-end mt-5">
        <button type="button" onclick="adminModal('modal-debit')" class="{BTN_R}">Cancel</button>
        <button type="submit" name="debit" class="{BTN_R} !bg-orange-600 hover:!bg-orange-700"><i class="fa-solid fa-circle-minus"></i> Debit Account</button>
      </div>
    </form>
  </div>
</div>

<script>
function adminModal(id) {{
  document.getElementById(id).classList.toggle('hidden');
}}
</script>
"""

def content_view_account():
    return f"""
{FLASH}
<div class="{CARD}">
  <div class="flex items-center justify-between mb-4">
    <h2 class="font-semibold text-gray-800">All Accounts</h2>
    <a href="create_account.php" class="{BTN_P} !py-1.5 !text-xs"><i class="fa-solid fa-plus"></i> New Account</a>
  </div>
  <div class="mb-4">
    <input type="text" id="acct-search" onkeyup="filterTable('acct-search','acct-table')"
      placeholder="Search accounts…"
      class="{INPUT} max-w-sm">
  </div>
  <div class="overflow-x-auto -mx-6 px-6">
    <table id="acct-table" class="min-w-full text-sm border-collapse">
      <thead>
        <tr class="bg-gray-50 border-y border-gray-200">
          <th class="{TH}">#</th>
          <th class="{TH}">Name</th>
          <th class="{TH}">Acc No</th>
          <th class="{TH}">Email</th>
          <th class="{TH}">Type</th>
          <th class="{TH}">Balance</th>
          <th class="{TH}">Currency</th>
          <th class="{TH}">Status</th>
          <th class="{TH}">Registered</th>
          <th class="{TH}">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php $n=0; while($row = $stmt->fetch(PDO::FETCH_ASSOC)): $n++; ?>
        <tr class="hover:bg-gray-50">
          <td class="{TD} text-gray-400"><?= $n ?></td>
          <td class="{TD} font-medium"><?= htmlspecialchars($row['fname'].' '.$row['lname']) ?></td>
          <td class="{TD} font-mono text-xs"><?= htmlspecialchars($row['acc_no']) ?></td>
          <td class="{TD}"><?= htmlspecialchars($row['email']) ?></td>
          <td class="{TD}"><?= htmlspecialchars($row['type']) ?></td>
          <td class="{TD} text-right font-medium"><?= number_format((float)$row['t_bal'],2) ?></td>
          <td class="{TD}"><?= htmlspecialchars($row['currency']) ?></td>
          <td class="{TD}">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
              <?= $row['status']==='Active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' ?>">
              <?= htmlspecialchars($row['status']) ?>
            </span>
          </td>
          <td class="{TD} text-xs text-gray-500"><?= htmlspecialchars($row['reg_date']) ?></td>
          <td class="{TD}">
            <div class="flex gap-1">
              <a href="edit_account.php?id=<?= $row['id'] ?>" class="{BTN_P} !py-1 !px-2 !text-xs"><i class="fa-solid fa-pen"></i></a>
              <a href="delete.php?id=<?= $row['id'] ?>" onclick="return confirm('Delete this account?')" class="{BTN_R} !py-1 !px-2"><i class="fa-solid fa-trash"></i></a>
            </div>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
<script>
function filterTable(inputId, tableId) {{
  const q = document.getElementById(inputId).value.toLowerCase();
  document.querySelectorAll('#' + tableId + ' tbody tr').forEach(row => {{
    row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
  }});
}}
</script>
"""

def content_pending_accounts():
    return f"""
{FLASH}
<div class="{CARD}">
  <h2 class="font-semibold text-gray-800 mb-4">Pending Account Applications</h2>
  <div class="overflow-x-auto -mx-6 px-6">
    <table class="min-w-full text-sm border-collapse">
      <thead>
        <tr class="bg-gray-50 border-y border-gray-200">
          <th class="{TH}">#</th>
          <th class="{TH}">Name</th>
          <th class="{TH}">Email</th>
          <th class="{TH}">Username</th>
          <th class="{TH}">Acc No</th>
          <th class="{TH}">Type</th>
          <th class="{TH}">Date</th>
          <th class="{TH}">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php $n=0; while($row = $stmt->fetch(PDO::FETCH_ASSOC)): $n++; ?>
        <tr class="hover:bg-gray-50">
          <td class="{TD} text-gray-400"><?= $n ?></td>
          <td class="{TD} font-medium"><?= htmlspecialchars($row['fname'].' '.$row['lname']) ?></td>
          <td class="{TD}"><?= htmlspecialchars($row['email']) ?></td>
          <td class="{TD}"><?= htmlspecialchars($row['uname']) ?></td>
          <td class="{TD} font-mono text-xs"><?= htmlspecialchars($row['acc_no']) ?></td>
          <td class="{TD}"><?= htmlspecialchars($row['type']) ?></td>
          <td class="{TD} text-xs text-gray-500"><?= htmlspecialchars($row['reg_date'] ?? '') ?></td>
          <td class="{TD}">
            <div class="flex gap-1">
              <a href="approve.php?id=<?= $row['id'] ?>" class="{BTN_G}"><i class="fa-solid fa-check"></i> Approve</a>
              <a href="decline.php?id=<?= $row['id'] ?>" onclick="return confirm('Decline this application?')" class="{BTN_R}"><i class="fa-solid fa-xmark"></i> Decline</a>
            </div>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
"""

def content_create_account():
    return f"""
{FLASH}
<div class="{CARD} max-w-4xl">
  <h2 class="font-semibold text-gray-800 mb-5">Create New Account</h2>
  <form method="POST" enctype="multipart/form-data">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">

      <div><label class="{LABEL}">First Name</label>
        <input type="text" name="fname" class="{INPUT}" required></div>

      <div><label class="{LABEL}">Middle Name (PIN)</label>
        <input type="text" name="mname" class="{INPUT}"></div>

      <div><label class="{LABEL}">Last Name</label>
        <input type="text" name="lname" class="{INPUT}" required></div>

      <div><label class="{LABEL}">Username</label>
        <input type="text" name="uname" class="{INPUT}" required></div>

      <div><label class="{LABEL}">Password</label>
        <input type="password" name="upass" class="{INPUT}" required></div>

      <div><label class="{LABEL}">Secondary Password</label>
        <input type="password" name="upass2" class="{INPUT}"></div>

      <div><label class="{LABEL}">Phone</label>
        <input type="text" name="phone" class="{INPUT}"></div>

      <div><label class="{LABEL}">Email</label>
        <input type="email" name="email" class="{INPUT}" required></div>

      <div><label class="{LABEL}">Account Type</label>
        <select name="type" class="{INPUT}">
          <option>Savings</option>
          <option>Checking</option>
          <option>Business</option>
          <option>Investment</option>
        </select>
      </div>

      <div><label class="{LABEL}">Occupation / Work</label>
        <input type="text" name="work" class="{INPUT}"></div>

      <div><label class="{LABEL}">Account Number</label>
        <input type="text" name="acc_no" class="{INPUT}" required></div>

      <div><label class="{LABEL}">Address</label>
        <input type="text" name="addr" class="{INPUT}"></div>

      <div><label class="{LABEL}">Gender</label>
        <select name="sex" class="{INPUT}">
          <option value="Male">Male</option>
          <option value="Female">Female</option>
        </select>
      </div>

      <div><label class="{LABEL}">Date of Birth</label>
        <input type="date" name="dob" class="{INPUT}"></div>

      <div><label class="{LABEL}">Marital Status</label>
        <select name="marry" class="{INPUT}">
          <option value="Single">Single</option>
          <option value="Married">Married</option>
          <option value="Divorced">Divorced</option>
        </select>
      </div>

      <div><label class="{LABEL}">Total Balance</label>
        <input type="number" step="0.01" name="t_bal" value="0" class="{INPUT}"></div>

      <div><label class="{LABEL}">Available Balance</label>
        <input type="number" step="0.01" name="a_bal" value="0" class="{INPUT}"></div>

      <div><label class="{LABEL}">Currency</label>
        <input type="text" name="currency" class="{INPUT}" placeholder="USD"></div>

      <div><label class="{LABEL}">COT Code</label>
        <input type="text" name="cot" class="{INPUT}"></div>

      <div><label class="{LABEL}">TAX Code</label>
        <input type="text" name="tax" class="{INPUT}"></div>

      <div><label class="{LABEL}">IMF Code</label>
        <input type="text" name="imf" class="{INPUT}"></div>

      <div><label class="{LABEL}">Registration Date</label>
        <input type="date" name="reg_date" class="{INPUT}" value="<?= date('Y-m-d') ?>"></div>

      <div><label class="{LABEL}">Profile Photo</label>
        <input type="file" name="pp" class="{INPUT} !py-1.5" accept="image/*"></div>

      <div><label class="{LABEL}">ID Document</label>
        <input type="file" name="image" class="{INPUT} !py-1.5" accept="image/*"></div>

    </div>
    <div class="flex gap-3 pt-2">
      <button type="submit" name="reg" class="{BTN_P}"><i class="fa-solid fa-user-plus"></i> Create Account</button>
      <button type="reset" class="{BTN_Y}"><i class="fa-solid fa-rotate-left"></i> Reset</button>
    </div>
  </form>
</div>
"""

def content_edit_account():
    return f"""
{FLASH}
<div class="{CARD} max-w-4xl">
  <div class="flex items-center justify-between mb-5">
    <h2 class="font-semibold text-gray-800">Edit Account</h2>
    <a href="view_account.php" class="text-sm text-blue-600 hover:underline"><i class="fa-solid fa-arrow-left mr-1"></i>Back</a>
  </div>
  <?php if(isset($row)): ?>
  <form method="POST">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">

      <div><label class="{LABEL}">First Name</label>
        <input type="text" name="fname" value="<?= htmlspecialchars($row['fname']) ?>" class="{INPUT}" required></div>

      <div><label class="{LABEL}">Middle Name (PIN)</label>
        <input type="text" name="mname" value="<?= htmlspecialchars($row['mname']) ?>" class="{INPUT}"></div>

      <div><label class="{LABEL}">Last Name</label>
        <input type="text" name="lname" value="<?= htmlspecialchars($row['lname']) ?>" class="{INPUT}" required></div>

      <div><label class="{LABEL}">Username</label>
        <input type="text" name="uname" value="<?= htmlspecialchars($row['uname']) ?>" class="{INPUT}"></div>

      <div><label class="{LABEL}">New Password (leave blank to keep)</label>
        <input type="password" name="upass" class="{INPUT}"></div>

      <div><label class="{LABEL}">Secondary Password</label>
        <input type="text" name="upass2" value="<?= htmlspecialchars($row['upass2']) ?>" class="{INPUT}"></div>

      <div><label class="{LABEL}">Phone</label>
        <input type="text" name="phone" value="<?= htmlspecialchars($row['phone']) ?>" class="{INPUT}"></div>

      <div><label class="{LABEL}">Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($row['email']) ?>" class="{INPUT}"></div>

      <div><label class="{LABEL}">Account Type</label>
        <input type="text" name="type" value="<?= htmlspecialchars($row['type']) ?>" class="{INPUT}"></div>

      <div><label class="{LABEL}">Occupation / Work</label>
        <input type="text" name="work" value="<?= htmlspecialchars($row['work']) ?>" class="{INPUT}"></div>

      <div><label class="{LABEL}">Account Number</label>
        <input type="text" name="acc_no" value="<?= htmlspecialchars($row['acc_no']) ?>" class="{INPUT}"></div>

      <div><label class="{LABEL}">Address</label>
        <input type="text" name="addr" value="<?= htmlspecialchars($row['addr']) ?>" class="{INPUT}"></div>

      <div><label class="{LABEL}">Gender</label>
        <input type="text" name="sex" value="<?= htmlspecialchars($row['sex']) ?>" class="{INPUT}"></div>

      <div><label class="{LABEL}">Date of Birth</label>
        <input type="text" name="dob" value="<?= htmlspecialchars($row['dob']) ?>" class="{INPUT}"></div>

      <div><label class="{LABEL}">Marital Status</label>
        <input type="text" name="marry" value="<?= htmlspecialchars($row['marry']) ?>" class="{INPUT}"></div>

      <div><label class="{LABEL}">Total Balance</label>
        <input type="number" step="0.01" name="t_bal" value="<?= htmlspecialchars($row['t_bal']) ?>" class="{INPUT}"></div>

      <div><label class="{LABEL}">Available Balance</label>
        <input type="number" step="0.01" name="a_bal" value="<?= htmlspecialchars($row['a_bal']) ?>" class="{INPUT}"></div>

      <div><label class="{LABEL}">Currency</label>
        <input type="text" name="currency" value="<?= htmlspecialchars($row['currency']) ?>" class="{INPUT}"></div>

      <div><label class="{LABEL}">COT Code</label>
        <input type="text" name="cot" value="<?= htmlspecialchars($row['cot']) ?>" class="{INPUT}"></div>

      <div><label class="{LABEL}">TAX Code</label>
        <input type="text" name="tax" value="<?= htmlspecialchars($row['tax']) ?>" class="{INPUT}"></div>

      <div><label class="{LABEL}">IMF Code</label>
        <input type="text" name="imf" value="<?= htmlspecialchars($row['imf']) ?>" class="{INPUT}"></div>

      <div><label class="{LABEL}">LPPI</label>
        <input type="text" name="lppi" value="<?= htmlspecialchars($row['lppi'] ?? '') ?>" class="{INPUT}"></div>

    </div>
    <input type="hidden" name="id" value="<?= htmlspecialchars($id ?? '') ?>">
    <div class="flex gap-3 pt-2">
      <button type="submit" name="update" class="{BTN_P}"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
      <button type="reset" class="{BTN_Y}"><i class="fa-solid fa-rotate-left"></i> Reset</button>
    </div>
  </form>
  <?php else: ?>
  <p class="text-gray-500">No account found. <a href="view_account.php" class="text-blue-600 hover:underline">Go back</a>.</p>
  <?php endif; ?>
</div>
"""

def content_update():
    """Update accounts page — same as view_account but oriented around editing."""
    return f"""
{FLASH}
<div class="{CARD}">
  <div class="flex items-center justify-between mb-4">
    <h2 class="font-semibold text-gray-800">Update Accounts</h2>
  </div>
  <div class="mb-4">
    <input type="text" id="upd-search" onkeyup="filterTable('upd-search','upd-table')"
      placeholder="Search accounts…" class="{INPUT} max-w-sm">
  </div>
  <div class="overflow-x-auto -mx-6 px-6">
    <table id="upd-table" class="min-w-full text-sm border-collapse">
      <thead>
        <tr class="bg-gray-50 border-y border-gray-200">
          <th class="{TH}">#</th>
          <th class="{TH}">Name</th>
          <th class="{TH}">Acc No</th>
          <th class="{TH}">Email</th>
          <th class="{TH}">Type</th>
          <th class="{TH}">Balance</th>
          <th class="{TH}">Status</th>
          <th class="{TH}">Action</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php $n=0; while($row = $stmt->fetch(PDO::FETCH_ASSOC)): $n++; ?>
        <tr class="hover:bg-gray-50">
          <td class="{TD} text-gray-400"><?= $n ?></td>
          <td class="{TD} font-medium"><?= htmlspecialchars($row['fname'].' '.$row['lname']) ?></td>
          <td class="{TD} font-mono text-xs"><?= htmlspecialchars($row['acc_no']) ?></td>
          <td class="{TD}"><?= htmlspecialchars($row['email']) ?></td>
          <td class="{TD}"><?= htmlspecialchars($row['type']) ?></td>
          <td class="{TD} text-right font-medium"><?= number_format((float)$row['t_bal'],2) ?></td>
          <td class="{TD}">
            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium
              <?= $row['status']==='Active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' ?>">
              <?= htmlspecialchars($row['status']) ?>
            </span>
          </td>
          <td class="{TD}">
            <a href="edit_account.php?id=<?= $row['id'] ?>" class="{BTN_P} !py-1 !px-3 !text-xs"><i class="fa-solid fa-pen"></i> Edit</a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
<script>
function filterTable(inputId, tableId) {{
  const q = document.getElementById(inputId).value.toLowerCase();
  document.querySelectorAll('#' + tableId + ' tbody tr').forEach(row => {{
    row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
  }});
}}
</script>
"""

def content_messages():
    return f"""
{FLASH}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

  <!-- Send message -->
  <div class="{CARD}">
    <h2 class="font-semibold text-gray-800 mb-4">Send Message</h2>
    <form method="POST" class="space-y-4">
      <div>
        <label class="{LABEL}">Recipient Account No</label>
        <input type="text" name="reci_name" class="{INPUT}" placeholder="Account number" required>
      </div>
      <div>
        <label class="{LABEL}">Subject</label>
        <input type="text" name="subject" class="{INPUT}" required>
      </div>
      <div>
        <label class="{LABEL}">Message</label>
        <textarea name="message" rows="5" class="{INPUT}" required></textarea>
      </div>
      <button type="submit" name="send" class="{BTN_P} w-full justify-center"><i class="fa-solid fa-paper-plane"></i> Send Message</button>
    </form>
  </div>

  <!-- Messages list -->
  <div class="{CARD} lg:col-span-2">
    <h2 class="font-semibold text-gray-800 mb-4">Sent Messages</h2>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm border-collapse">
        <thead>
          <tr class="bg-gray-50 border-y border-gray-200">
            <th class="{TH}">#</th>
            <th class="{TH}">To</th>
            <th class="{TH}">Subject</th>
            <th class="{TH}">Date</th>
            <th class="{TH}">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php $n=0; while($row = $msgs->fetch(PDO::FETCH_ASSOC)): $n++; ?>
          <tr class="hover:bg-gray-50">
            <td class="{TD} text-gray-400"><?= $n ?></td>
            <td class="{TD} font-mono text-xs"><?= htmlspecialchars($row['reci_name']) ?></td>
            <td class="{TD}"><?= htmlspecialchars($row['subject']) ?></td>
            <td class="{TD} text-xs text-gray-500"><?= htmlspecialchars($row['date']) ?></td>
            <td class="{TD}">
              <a href="del2.php?id=<?= $row['id'] ?>" onclick="return confirm('Delete message?')" class="{BTN_R} !py-1 !px-2"><i class="fa-solid fa-trash"></i></a>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
"""

def content_transfer_rec():
    return f"""
{FLASH}

<!-- Transfer Records -->
<div class="{CARD} mb-6">
  <h2 class="font-semibold text-gray-800 mb-4">Transfer Records</h2>
  <div class="overflow-x-auto -mx-6 px-6">
    <table class="min-w-full text-sm border-collapse">
      <thead>
        <tr class="bg-gray-50 border-y border-gray-200">
          <th class="{TH}">#</th>
          <th class="{TH}">From Acc</th>
          <th class="{TH}">To Acc</th>
          <th class="{TH}">Amount</th>
          <th class="{TH}">Description</th>
          <th class="{TH}">Date</th>
          <th class="{TH}">Status</th>
          <th class="{TH}">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php $n=0; while($r = $tran->fetch(PDO::FETCH_ASSOC)): $n++; ?>
        <tr class="hover:bg-gray-50">
          <td class="{TD} text-gray-400"><?= $n ?></td>
          <td class="{TD} font-mono text-xs"><?= htmlspecialchars($r['acc_no'] ?? '') ?></td>
          <td class="{TD} font-mono text-xs"><?= htmlspecialchars($r['reci_acc'] ?? $r['reci_name'] ?? '') ?></td>
          <td class="{TD} font-medium text-right"><?= number_format((float)($r['amount'] ?? 0),2) ?></td>
          <td class="{TD}"><?= htmlspecialchars($r['description'] ?? $r['remarks'] ?? '') ?></td>
          <td class="{TD} text-xs text-gray-500"><?= htmlspecialchars($r['date'] ?? '') ?></td>
          <td class="{TD}">
            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
              <?= htmlspecialchars($r['status'] ?? 'Pending') ?>
            </span>
          </td>
          <td class="{TD}">
            <a href="edit_tf.php?id=<?= $r['id'] ?>" class="{BTN_P} !py-1 !px-2 !text-xs"><i class="fa-solid fa-pen"></i></a>
            <a href="del.php?id=<?= $r['id'] ?>" onclick="return confirm('Delete?')" class="{BTN_R} !py-1 !px-2"><i class="fa-solid fa-trash"></i></a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Alerts / Pending Transactions -->
<div class="{CARD}">
  <h2 class="font-semibold text-gray-800 mb-4">Credit / Debit Alerts</h2>
  <div class="overflow-x-auto -mx-6 px-6">
    <table class="min-w-full text-sm border-collapse">
      <thead>
        <tr class="bg-gray-50 border-y border-gray-200">
          <th class="{TH}">#</th>
          <th class="{TH}">Account</th>
          <th class="{TH}">Name</th>
          <th class="{TH}">Type</th>
          <th class="{TH}">Amount</th>
          <th class="{TH}">From / To</th>
          <th class="{TH}">Date</th>
          <th class="{TH}">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php $n=0; while($r = $alerts->fetch(PDO::FETCH_ASSOC)): $n++; ?>
        <tr class="hover:bg-gray-50">
          <td class="{TD} text-gray-400"><?= $n ?></td>
          <td class="{TD} font-mono text-xs"><?= htmlspecialchars($r['acc_no'] ?? '') ?></td>
          <td class="{TD}"><?= htmlspecialchars(($r['fname'] ?? '').' '.($r['lname'] ?? '')) ?></td>
          <td class="{TD}">
            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
              <?= ($r['type'] ?? '') === 'Credit' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' ?>">
              <?= htmlspecialchars($r['type'] ?? '') ?>
            </span>
          </td>
          <td class="{TD} font-medium text-right"><?= number_format((float)($r['amount'] ?? 0),2) ?></td>
          <td class="{TD}"><?= htmlspecialchars($r['sender_name'] ?? '') ?></td>
          <td class="{TD} text-xs text-gray-500"><?= htmlspecialchars(($r['date'] ?? '').' '.($r['time'] ?? '')) ?></td>
          <td class="{TD}">
            <a href="edit_cd.php?id=<?= $r['id'] ?>" class="{BTN_P} !py-1 !px-2 !text-xs"><i class="fa-solid fa-pen"></i></a>
            <a href="delete.php?id=<?= $r['id'] ?>" onclick="return confirm('Delete?')" class="{BTN_R} !py-1 !px-2"><i class="fa-solid fa-trash"></i></a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
"""

def content_tickets():
    return f"""
{FLASH}
<div class="{CARD}">
  <h2 class="font-semibold text-gray-800 mb-4">Support Tickets</h2>
  <div class="overflow-x-auto -mx-6 px-6">
    <table class="min-w-full text-sm border-collapse">
      <thead>
        <tr class="bg-gray-50 border-y border-gray-200">
          <th class="{TH}">#</th>
          <th class="{TH}">Ticket ID</th>
          <th class="{TH}">Account</th>
          <th class="{TH}">Subject</th>
          <th class="{TH}">Message</th>
          <th class="{TH}">Date</th>
          <th class="{TH}">Status</th>
          <th class="{TH}">Action</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php $n=0; while($r = $stmt->fetch(PDO::FETCH_ASSOC)): $n++; ?>
        <tr class="hover:bg-gray-50">
          <td class="{TD} text-gray-400"><?= $n ?></td>
          <td class="{TD} font-mono text-xs"><?= htmlspecialchars($r['ticket_id'] ?? $r['id']) ?></td>
          <td class="{TD} font-mono text-xs"><?= htmlspecialchars($r['acc_no'] ?? $r['email'] ?? '') ?></td>
          <td class="{TD} font-medium"><?= htmlspecialchars($r['subject'] ?? '') ?></td>
          <td class="{TD} max-w-xs truncate text-gray-500"><?= htmlspecialchars($r['message'] ?? '') ?></td>
          <td class="{TD} text-xs text-gray-500"><?= htmlspecialchars($r['date'] ?? '') ?></td>
          <td class="{TD}">
            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
              <?= ($r['status'] ?? '') === 'Open' ? 'bg-orange-100 text-orange-600' : 'bg-green-100 text-green-700' ?>">
              <?= htmlspecialchars($r['status'] ?? 'Open') ?>
            </span>
          </td>
          <td class="{TD}">
            <a href="del2.php?ticket=<?= $r['id'] ?>" onclick="return confirm('Delete ticket?')" class="{BTN_R} !py-1 !px-2"><i class="fa-solid fa-trash"></i></a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
"""

def content_credit_debit_list():
    return f"""
{FLASH}
<div class="{CARD}">
  <div class="flex items-center justify-between mb-4">
    <h2 class="font-semibold text-gray-800">Credit / Debit History</h2>
  </div>
  <div class="mb-4">
    <input type="text" id="cd-search" onkeyup="filterTable('cd-search','cd-table')"
      placeholder="Search history…" class="{INPUT} max-w-sm">
  </div>
  <div class="overflow-x-auto -mx-6 px-6">
    <table id="cd-table" class="min-w-full text-sm border-collapse">
      <thead>
        <tr class="bg-gray-50 border-y border-gray-200">
          <th class="{TH}">#</th>
          <th class="{TH}">Account No</th>
          <th class="{TH}">Name</th>
          <th class="{TH}">Type</th>
          <th class="{TH}">Amount</th>
          <th class="{TH}">From / To</th>
          <th class="{TH}">Description</th>
          <th class="{TH}">Date</th>
          <th class="{TH}">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php $n=0; while($r = $credit->fetch(PDO::FETCH_ASSOC)): $n++; ?>
        <tr class="hover:bg-gray-50">
          <td class="{TD} text-gray-400"><?= $n ?></td>
          <td class="{TD} font-mono text-xs"><?= htmlspecialchars($r['acc_no'] ?? '') ?></td>
          <td class="{TD}"><?= htmlspecialchars(($r['fname'] ?? '').' '.($r['lname'] ?? '')) ?></td>
          <td class="{TD}">
            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
              <?= ($r['type'] ?? '') === 'Credit' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' ?>">
              <?= htmlspecialchars($r['type'] ?? '') ?>
            </span>
          </td>
          <td class="{TD} text-right font-medium"><?= number_format((float)($r['amount'] ?? 0), 2) ?></td>
          <td class="{TD}"><?= htmlspecialchars($r['sender_name'] ?? '') ?></td>
          <td class="{TD} max-w-xs truncate text-gray-500"><?= htmlspecialchars($r['remarks'] ?? $r['description'] ?? '') ?></td>
          <td class="{TD} text-xs text-gray-500"><?= htmlspecialchars(($r['date'] ?? '').' '.($r['time'] ?? '')) ?></td>
          <td class="{TD}">
            <a href="edit_cd.php?id=<?= $r['id'] ?>" class="{BTN_P} !py-1 !px-2 !text-xs"><i class="fa-solid fa-pen"></i></a>
            <a href="delete.php?id=<?= $r['id'] ?>" onclick="return confirm('Delete?')" class="{BTN_R} !py-1 !px-2"><i class="fa-solid fa-trash"></i></a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
<script>
function filterTable(inputId, tableId) {{
  const q = document.getElementById(inputId).value.toLowerCase();
  document.querySelectorAll('#' + tableId + ' tbody tr').forEach(row => {{
    row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
  }});
}}
</script>
"""

def content_settings():
    return f"""
<?php if(isset($msg)) echo $msg; ?>
<?php if(isset($themeMsg)) echo $themeMsg; ?>
<?php if(isset($authThemeMsg)) echo $authThemeMsg; ?>
<?php if(isset($translatorMsg)) echo $translatorMsg; ?>
<?php if(isset($dormantMsg_notice)) echo $dormantMsg_notice; ?>
<?php if(isset($currencyMsg)) echo $currencyMsg; ?>
<?php if(isset($accountTypeMsg)) echo $accountTypeMsg; ?>
<?php if(isset($rateMsg)) echo $rateMsg; ?>

<!-- Tabs -->
<div class="mb-1" id="settings-tabs">
  <div class="flex flex-wrap gap-1 border-b border-gray-200 mb-6">
    <?php
    $tabs=["theme"=>"Theme","auth_scheme"=>"Auth Scheme","dormant"=>"Dormant Msg","translator"=>"Translator","currencies"=>"Currencies","account_types"=>"Account Types","exchange_rates"=>"Exchange Rates"];
    $activeTab=$_GET['tab'] ?? 'theme';
    foreach($tabs as $k=>$v):
      $active=$activeTab===$k;
    ?>
    <a href="?tab=<?= $k ?>" class="px-4 py-2 text-sm rounded-t-lg border-b-2 transition-colors
      <?= $active ? 'border-blue-600 text-blue-600 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700' ?>">
      <?= htmlspecialchars($v) ?>
    </a>
    <?php endforeach; ?>
  </div>
</div>

<!-- ── TAB: Theme ──────────────────────────────────────────────────────── -->
<?php if($activeTab==='theme'): ?>
<div class="{CARD} max-w-lg" id="theme">
  <h2 class="font-semibold text-gray-800 mb-5">Frontend Theme</h2>
  <form method="POST" class="space-y-4">
    <div>
      <label class="{LABEL}">Select Active Theme</label>
      <select name="theme_name" class="{INPUT}">
        <?php foreach($availThemes as $t): ?>
        <option value="<?= htmlspecialchars($t) ?>" <?= $t===$activeTheme?'selected':'' ?>><?= htmlspecialchars($t) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button type="submit" name="set_theme" class="{BTN_P}"><i class="fa-solid fa-palette"></i> Apply Theme</button>
  </form>
</div>

<!-- ── TAB: Auth Scheme ─────────────────────────────────────────────────── -->
<?php elseif($activeTab==='auth_scheme'): ?>
<div class="{CARD} max-w-lg" id="auth_scheme">
  <h2 class="font-semibold text-gray-800 mb-5">Auth Pages Color Scheme</h2>
  <form method="POST" class="space-y-4">
    <div>
      <label class="{LABEL}">Color Scheme</label>
      <select name="auth_scheme" class="{INPUT}">
        <?php foreach($authSchemes as $k=>$v): ?>
        <option value="<?= htmlspecialchars($k) ?>" <?= $k===$activeAuthScheme?'selected':'' ?>><?= htmlspecialchars($v) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button type="submit" name="set_auth_scheme" class="{BTN_P}"><i class="fa-solid fa-swatchbook"></i> Save Scheme</button>
  </form>
</div>

<!-- ── TAB: Dormant ─────────────────────────────────────────────────────── -->
<?php elseif($activeTab==='dormant'): ?>
<div class="{CARD} max-w-lg">
  <h2 class="font-semibold text-gray-800 mb-5">Dormant Account Transfer Message</h2>
  <form method="POST" class="space-y-4">
    <div>
      <label class="{LABEL}">Message shown when a dormant account tries to transfer</label>
      <textarea name="dormant_message" rows="4" class="{INPUT}"><?= htmlspecialchars($dormantMessage) ?></textarea>
    </div>
    <button type="submit" name="set_dormant_message" class="{BTN_P}"><i class="fa-solid fa-floppy-disk"></i> Save Message</button>
  </form>
</div>

<!-- ── TAB: Translator ───────────────────────────────────────────────── -->
<?php elseif($activeTab==='translator'): ?>
<div class="{CARD} max-w-lg">
  <h2 class="font-semibold text-gray-800 mb-5">Translator Languages</h2>
  <form method="POST" class="space-y-4">
    <div>
      <label class="{LABEL}">Comma-separated language codes (e.g. en,fr,es,de)</label>
      <input type="text" name="translator_languages" value="<?= htmlspecialchars($translatorLanguages) ?>" class="{INPUT}" placeholder="en,fr,es,de">
    </div>
    <button type="submit" name="set_translator_languages" class="{BTN_P}"><i class="fa-solid fa-language"></i> Save Languages</button>
  </form>
</div>

<!-- ── TAB: Currencies ────────────────────────────────────────────────── -->
<?php elseif($activeTab==='currencies'): ?>
<div class="space-y-6" id="currency-mgmt">
  <!-- Add currency -->
  <div class="{CARD} max-w-2xl">
    <h2 class="font-semibold text-gray-800 mb-4">Add Currency</h2>
    <form method="POST" class="grid grid-cols-2 sm:grid-cols-3 gap-4">
      <div><label class="{LABEL}">Code (e.g. USD)</label>
        <input type="text" name="cur_code" class="{INPUT}" placeholder="USD" maxlength="4" style="text-transform:uppercase"></div>
      <div><label class="{LABEL}">Symbol</label>
        <input type="text" name="cur_symbol" class="{INPUT}" placeholder="$" maxlength="8"></div>
      <div><label class="{LABEL}">Flag Code (ISO2)</label>
        <input type="text" name="cur_flag" class="{INPUT}" placeholder="US" maxlength="4" style="text-transform:uppercase"></div>
      <div><label class="{LABEL}">Name</label>
        <input type="text" name="cur_name" class="{INPUT}" placeholder="US Dollar"></div>
      <div><label class="{LABEL}">Sort Order</label>
        <input type="number" name="cur_order" class="{INPUT}" value="99" min="0"></div>
      <div class="flex items-end">
        <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
          <input type="checkbox" name="cur_crypto" class="rounded border-gray-300"> Crypto
        </label>
      </div>
      <div class="col-span-2 sm:col-span-3">
        <button type="submit" name="add_currency" class="{BTN_P}"><i class="fa-solid fa-plus"></i> Add Currency</button>
      </div>
    </form>
  </div>
  <!-- Currencies table -->
  <div class="{CARD}">
    <h2 class="font-semibold text-gray-800 mb-4">Active Currencies</h2>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm border-collapse">
        <thead>
          <tr class="bg-gray-50 border-y border-gray-200">
            <th class="{TH}">Code</th><th class="{TH}">Symbol</th><th class="{TH}">Flag</th>
            <th class="{TH}">Name</th><th class="{TH}">Crypto</th><th class="{TH}">Active</th>
            <th class="{TH}">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php foreach($allCurrencies as $c): ?>
          <tr class="hover:bg-gray-50">
            <td class="{TD} font-mono font-bold"><?= htmlspecialchars($c['code']) ?></td>
            <td class="{TD}"><?= htmlspecialchars($c['symbol']) ?></td>
            <td class="{TD}"><?= htmlspecialchars($c['flag_code']) ?></td>
            <td class="{TD}"><?= htmlspecialchars($c['name']) ?></td>
            <td class="{TD}"><?= $c['is_crypto'] ? '<span class=\"text-purple-600 text-xs font-medium\">Yes</span>' : '<span class=\"text-gray-400 text-xs\">No</span>' ?></td>
            <td class="{TD}">
              <form method="POST" class="inline">
                <input type="hidden" name="toggle_currency" value="<?= $c['id'] ?>">
                <button type="submit" class="text-xs <?= $c['is_active'] ? 'text-green-600' : 'text-gray-400' ?> hover:underline"><?= $c['is_active'] ? 'Active':'Disabled' ?></button>
              </form>
            </td>
            <td class="{TD}">
              <form method="POST" class="inline" onsubmit="return confirm('Delete currency?')">
                <input type="hidden" name="delete_currency" value="<?= $c['id'] ?>">
                <button type="submit" class="{BTN_R} !py-0.5 !px-2"><i class="fa-solid fa-trash"></i></button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ── TAB: Account Types ─────────────────────────────────────────────── -->
<?php elseif($activeTab==='account_types'): ?>
<div class="space-y-6" id="account-types-mgmt">
  <div class="{CARD} max-w-2xl">
    <h2 class="font-semibold text-gray-800 mb-4">Add Account Type</h2>
    <form method="POST" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div><label class="{LABEL}">Label</label>
        <input type="text" name="at_label" class="{INPUT}" placeholder="Savings Account"></div>
      <div><label class="{LABEL}">Key</label>
        <input type="text" name="at_key" class="{INPUT}" placeholder="savings"></div>
      <div><label class="{LABEL}">Min Balance</label>
        <input type="number" step="0.01" name="at_balance" class="{INPUT}" value="0"></div>
      <div class="sm:col-span-3">
        <button type="submit" name="add_account_type" class="{BTN_P}"><i class="fa-solid fa-plus"></i> Add Type</button>
      </div>
    </form>
  </div>
  <div class="{CARD}">
    <h2 class="font-semibold text-gray-800 mb-4">Account Types</h2>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm border-collapse">
        <thead>
          <tr class="bg-gray-50 border-y border-gray-200">
            <th class="{TH}">Label</th><th class="{TH}">Key</th>
            <th class="{TH}">Min Balance</th><th class="{TH}">Active</th>
            <th class="{TH}">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php foreach($allAccountTypes as $at): ?>
          <tr class="hover:bg-gray-50">
            <td class="{TD} font-medium"><?= htmlspecialchars($at['label']) ?></td>
            <td class="{TD} font-mono text-xs"><?= htmlspecialchars($at['type_key']) ?></td>
            <td class="{TD} text-right"><?= number_format((float)$at['min_balance'],2) ?></td>
            <td class="{TD}">
              <form method="POST" class="inline">
                <input type="hidden" name="toggle_account_type" value="<?= $at['id'] ?>">
                <button type="submit" class="text-xs <?= $at['is_active'] ? 'text-green-600' : 'text-gray-400' ?> hover:underline"><?= $at['is_active'] ? 'Active' : 'Disabled' ?></button>
              </form>
            </td>
            <td class="{TD}">
              <button onclick="editType(<?= $at['id'] ?>,'<?= htmlspecialchars(addslashes($at['label'])) ?>',<?= $at['min_balance'] ?>)"
                class="{BTN_P} !py-0.5 !px-2 !text-xs"><i class="fa-solid fa-pen"></i></button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <!-- Edit account type inline modal -->
  <div id="edit-type-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
    <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-sm">
      <h3 class="font-semibold mb-4">Edit Account Type</h3>
      <form method="POST" class="space-y-3">
        <input type="hidden" id="at-id" name="at_id">
        <div><label class="{LABEL}">Label</label><input type="text" id="at-label-e" name="at_label_edit" class="{INPUT}"></div>
        <div><label class="{LABEL}">Min Balance</label><input type="number" step="0.01" id="at-bal-e" name="at_balance_edit" class="{INPUT}"></div>
        <div class="flex gap-3 justify-end">
          <button type="button" onclick="document.getElementById('edit-type-modal').classList.add('hidden')" class="{BTN_R}">Cancel</button>
          <button type="submit" name="update_account_type" class="{BTN_G}">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ── TAB: Exchange Rates ────────────────────────────────────────────── -->
<?php elseif($activeTab==='exchange_rates'): ?>
<div class="space-y-6" id="exchange-rates-mgmt">
  <div class="{CARD} max-w-md">
    <h2 class="font-semibold text-gray-800 mb-4">Set Exchange Rate</h2>
    <form method="POST" class="space-y-4">
      <div class="grid grid-cols-2 gap-4">
        <div><label class="{LABEL}">From Currency</label>
          <select name="rate_from" class="{INPUT}">
            <?php foreach($allCurrencies as $c): ?><option value="<?= htmlspecialchars($c['code']) ?>"><?= htmlspecialchars($c['code']) ?></option><?php endforeach; ?>
          </select></div>
        <div><label class="{LABEL}">To Currency</label>
          <select name="rate_to" class="{INPUT}">
            <?php foreach($allCurrencies as $c): ?><option value="<?= htmlspecialchars($c['code']) ?>"><?= htmlspecialchars($c['code']) ?></option><?php endforeach; ?>
          </select></div>
      </div>
      <div><label class="{LABEL}">Rate</label>
        <input type="number" step="0.000001" name="rate_value" class="{INPUT}" placeholder="1.0500" required></div>
      <button type="submit" name="upsert_rate" class="{BTN_P}"><i class="fa-solid fa-arrows-rotate"></i> Set Rate</button>
    </form>
  </div>
  <div class="{CARD}">
    <h2 class="font-semibold text-gray-800 mb-4">Current Rates</h2>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm border-collapse">
        <thead>
          <tr class="bg-gray-50 border-y border-gray-200">
            <th class="{TH}">From</th><th class="{TH}">To</th>
            <th class="{TH}">Rate</th><th class="{TH}">Source</th><th class="{TH}">Updated</th>
            <th class="{TH}">Del</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php foreach($allRates as $r): ?>
          <tr class="hover:bg-gray-50">
            <td class="{TD} font-mono font-bold"><?= htmlspecialchars($r['from_code']) ?></td>
            <td class="{TD} font-mono font-bold"><?= htmlspecialchars($r['to_code']) ?></td>
            <td class="{TD} text-right font-medium"><?= number_format((float)$r['rate'], 6) ?></td>
            <td class="{TD} text-xs text-gray-500"><?= htmlspecialchars($r['source'] ?? 'manual') ?></td>
            <td class="{TD} text-xs text-gray-500"><?= htmlspecialchars($r['updated_at'] ?? '') ?></td>
            <td class="{TD}">
              <form method="POST" class="inline" onsubmit="return confirm('Delete rate?')">
                <input type="hidden" name="delete_rate" value="<?= $r['id'] ?>">
                <button type="submit" class="{BTN_R} !py-0.5 !px-2"><i class="fa-solid fa-trash"></i></button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
function editType(id, label, balance) {{
  document.getElementById('at-id').value = id;
  document.getElementById('at-label-e').value = label;
  document.getElementById('at-bal-e').value = balance;
  document.getElementById('edit-type-modal').classList.remove('hidden');
}}
</script>
"""

def content_site():
    return f"""
{FLASH}
<div class="{CARD} max-w-xl">
  <h2 class="font-semibold text-gray-800 mb-5">Site Information</h2>
  <?php if(isset($row)): ?>
  <form method="POST" enctype="multipart/form-data" class="space-y-4">
    <div><label class="{LABEL}">Bank / Site Name</label>
      <input type="text" name="name" value="<?= htmlspecialchars($row['name'] ?? '') ?>" class="{INPUT}" required></div>
    <div><label class="{LABEL}">Phone</label>
      <input type="text" name="phone" value="<?= htmlspecialchars($row['phone'] ?? '') ?>" class="{INPUT}"></div>
    <div><label class="{LABEL}">Email</label>
      <input type="email" name="email" value="<?= htmlspecialchars($row['email'] ?? '') ?>" class="{INPUT}"></div>
    <div><label class="{LABEL}">Address</label>
      <input type="text" name="addr" value="<?= htmlspecialchars($row['addr'] ?? '') ?>" class="{INPUT}"></div>
    <div><label class="{LABEL}">Tawk.to ID</label>
      <input type="text" name="tawk" value="<?= htmlspecialchars($row['tawk'] ?? '') ?>" class="{INPUT}"></div>
    <div><label class="{LABEL}">Logo Image</label>
      <input type="file" name="image" class="{INPUT} !py-1.5" accept="image/*"></div>
    <div class="flex gap-3">
      <button type="submit" name="upgrade" class="{BTN_P}"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
    </div>
  </form>
  <?php else: ?>
  <p class="text-gray-500">Site record not found. Ensure <code>id=20</code> exists in the site table.</p>
  <?php endif; ?>
</div>
"""

# ══════════════════════════════════════════════════════════════════════════════
# messages.php needs its query variable to be $msgs
# Let's check the original file to confirm what the result variable is.
# We'll handle it in the PHP logic extraction.
# ══════════════════════════════════════════════════════════════════════════════

PAGES = [
    ('index.php',            'Dashboard',            content_index()),
    ('view_account.php',     'View Accounts',        content_view_account()),
    ('pending_accounts.php', 'Pending Accounts',     content_pending_accounts()),
    ('create_account.php',   'Create Account',       content_create_account()),
    ('edit_account.php',     'Edit Account',         content_edit_account()),
    ('update.php',           'Update Accounts',      content_update()),
    ('messages.php',         'Messages',             content_messages()),
    ('transfer_rec.php',     'Transaction Records',  content_transfer_rec()),
    ('tickets.php',          'Support Tickets',      content_tickets()),
    ('credit_debit_list.php','Credit/Debit History', content_credit_debit_list()),
    ('settings.php',         'Settings',             content_settings()),
    ('site.php',             'Site Info',            content_site()),
]

def process_page(filename, title, html_content):
    filepath = os.path.join(BASE, filename)
    if not os.path.exists(filepath):
        print(f"SKIP (not found): {filename}")
        return
    original = read_file(filepath)
    php_logic = extract_php_logic(original)
    if php_logic == original:
        print(f"WARN (no split found): {filename} — skipping")
        return
    final = wrap_page(php_logic, title, html_content)
    write_file(filepath, final)
    print(f"OK: {filename}")

for fname, title, html in PAGES:
    process_page(fname, title, html)

print("\nDone. Processed", len(PAGES), "pages.")
