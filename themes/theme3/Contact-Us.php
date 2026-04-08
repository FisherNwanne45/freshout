<?php
require __DIR__ . '/_shared/bootstrap.php';

$pageTitle = 'Contact Us';

$departments = [
		'General Inquiry',
		'Customer Service',
		'Online Banking',
		'Marketing',
		'Loan Services',
		'Card Services',
];

$form = [
		'dpt' => 'General Inquiry',
		'name' => '',
		'email' => '',
		'phone' => '',
		'comments' => '',
];

$errors = [];
$sent = false;
$locationCount = 0;

try {
		$tblRes = $conn->query("SHOW TABLES LIKE 'site_branches'");
		if ($tblRes instanceof mysqli_result && $tblRes->num_rows > 0) {
				$countRes = $conn->query("SELECT COUNT(*) AS total FROM site_branches WHERE is_active = 1 AND TRIM(COALESCE(address, '')) <> ''");
				if ($countRes instanceof mysqli_result && $countRes->num_rows > 0) {
						$countRow = $countRes->fetch_assoc();
						$locationCount = (int)($countRow['total'] ?? 0);
				}
		}
} catch (Throwable $e) {
}

if ($locationCount < 1 && trim((string)($site['addr'] ?? '')) !== '') {
		$locationCount = 1;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
		$form['dpt'] = trim((string)($_POST['dpt'] ?? 'General Inquiry'));
		$form['name'] = trim((string)($_POST['name'] ?? ''));
		$form['email'] = trim((string)($_POST['email'] ?? ''));
		$form['phone'] = trim((string)($_POST['phone'] ?? ''));
		$form['comments'] = trim((string)($_POST['comments'] ?? ''));

		if (!in_array($form['dpt'], $departments, true)) {
				$form['dpt'] = 'General Inquiry';
		}

		if ($form['name'] === '' || strlen($form['name']) < 2) {
				$errors[] = 'Please enter your full name.';
		}

		if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
				$errors[] = 'Please enter a valid email address.';
		}

		if ($form['comments'] === '' || strlen($form['comments']) < 8) {
				$errors[] = 'Please provide a short message with at least 8 characters.';
		}

		if ($form['phone'] !== '' && !preg_match('/^[0-9\s()+\-.]{7,25}$/', $form['phone'])) {
				$errors[] = 'Please enter a valid phone number or leave it blank.';
		}

		if (!$errors) {
				$recipient = (string)($from ?? '');
				if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
						$recipient = (string)($site['email'] ?? '');
				}

				if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
						$errors[] = 'Support email is not configured. Please contact the branch by phone.';
				} else {
						$safeName = htmlspecialchars($form['name'], ENT_QUOTES, 'UTF-8');
						$safeEmail = htmlspecialchars($form['email'], ENT_QUOTES, 'UTF-8');
						$safePhone = htmlspecialchars($form['phone'] === '' ? 'Not provided' : $form['phone'], ENT_QUOTES, 'UTF-8');
						$safeDpt = htmlspecialchars($form['dpt'], ENT_QUOTES, 'UTF-8');
						$safeComments = nl2br(htmlspecialchars($form['comments'], ENT_QUOTES, 'UTF-8'));
						$safeSiteName = htmlspecialchars((string)($site['name'] ?? 'Banking Team'), ENT_QUOTES, 'UTF-8');

						$subject = 'Website Contact Request - ' . $form['dpt'];
						$headers = [];
						$headers[] = 'MIME-Version: 1.0';
						$headers[] = 'Content-type: text/html; charset=utf-8';
						$headers[] = 'From: ' . $recipient;
						$headers[] = 'Reply-To: ' . $form['email'];

						$body = '<html><body style="font-family:Arial,sans-serif;background:#f8fafc;color:#0f172a">';
						$body .= '<div style="max-width:640px;margin:24px auto;background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px">';
						$body .= '<h2 style="margin:0 0 16px 0">New Contact Request</h2>';
						$body .= '<p style="margin:0 0 16px 0">A new inquiry was submitted from ' . $safeSiteName . '.</p>';
						$body .= '<table style="width:100%;border-collapse:collapse">';
						$body .= '<tr><td style="padding:8px;border:1px solid #e2e8f0"><strong>Department</strong></td><td style="padding:8px;border:1px solid #e2e8f0">' . $safeDpt . '</td></tr>';
						$body .= '<tr><td style="padding:8px;border:1px solid #e2e8f0"><strong>Name</strong></td><td style="padding:8px;border:1px solid #e2e8f0">' . $safeName . '</td></tr>';
						$body .= '<tr><td style="padding:8px;border:1px solid #e2e8f0"><strong>Email</strong></td><td style="padding:8px;border:1px solid #e2e8f0">' . $safeEmail . '</td></tr>';
						$body .= '<tr><td style="padding:8px;border:1px solid #e2e8f0"><strong>Phone</strong></td><td style="padding:8px;border:1px solid #e2e8f0">' . $safePhone . '</td></tr>';
						$body .= '<tr><td style="padding:8px;border:1px solid #e2e8f0"><strong>Message</strong></td><td style="padding:8px;border:1px solid #e2e8f0">' . $safeComments . '</td></tr>';
						$body .= '</table></div></body></html>';

						if (mail($recipient, $subject, $body, implode("\r\n", $headers))) {
								$sent = true;
								$form = [
										'dpt' => 'General Inquiry',
										'name' => '',
										'email' => '',
										'phone' => '',
										'comments' => '',
								];
						} else {
								$errors[] = 'Your message could not be delivered right now. Please try again shortly.';
						}
				}
		}
}

require __DIR__ . '/_shared/header.php';
?>

<section class="max-w-6xl mx-auto mb-6 rounded-3xl border border-white/10 px-6 py-8 md:px-8 md:py-10" style="background:linear-gradient(125deg, color-mix(in srgb, var(--primary2) 70%, #020617), color-mix(in srgb, var(--primary) 68%, #0f172a));">
	<h1 class="text-3xl md:text-5xl font-black text-white">Contact Our Team</h1>
	<p class="mt-3 max-w-3xl text-slate-200">Reach our banking specialists for service inquiries, account support, and secure guidance. We respond as quickly as possible during business hours.</p>
</section>

<section class="max-w-6xl mx-auto grid lg:grid-cols-5 gap-6 mb-6">
	<div class="lg:col-span-2 rounded-3xl border border-white/10 bg-slate-950/65 backdrop-blur p-6 md:p-7">
		<p class="text-xs uppercase tracking-[0.18em] text-slate-400">Direct Line</p>
		<p class="mt-2 text-2xl font-extrabold text-white"><?= htmlspecialchars((string)($site['phone'] ?? '')) ?></p>
		<p class="mt-4 text-sm text-slate-300"><?= htmlspecialchars((string)($site['email'] ?? '')) ?></p>
		<p class="mt-4 text-sm text-slate-300 leading-relaxed"><?= nl2br(htmlspecialchars((string)($site['addr'] ?? ''))) ?></p>
		<a href="Locations.php" class="mt-5 block rounded-2xl border border-cyan-300/35 bg-cyan-400/10 p-4 text-sm text-cyan-100 transition hover:bg-cyan-400/15">
			<p class="font-semibold text-cyan-50">Our locations</p>
			<p class="mt-1">See all <?= (int)$locationCount ?> location<?= $locationCount === 1 ? '' : 's' ?>.</p>
		</a>
	</div>

	<div class="lg:col-span-3 rounded-3xl border shadow-xl p-6 md:p-7" style="background:var(--surface); border-color:var(--line)">
		<h2 class="text-2xl font-black" style="color:var(--ink)">Send Us a Message</h2>
		<p class="mt-2 text-sm" style="color:var(--muted)">Choose a department and share your request. A representative from <?= htmlspecialchars((string)($site['name'] ?? 'our team')) ?> will follow up.</p>

		<?php if ($sent): ?>
			<div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
				Your message has been forwarded to the appropriate department.
			</div>
		<?php endif; ?>

		<?php if ($errors): ?>
			<div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
				<?= htmlspecialchars(implode(' ', $errors)) ?>
			</div>
		<?php endif; ?>

		<form method="post" action="Contact-Us.php" class="mt-5 grid gap-4">
			<label class="grid gap-1">
				<span class="text-sm font-semibold" style="color:var(--ink)">Department</span>
				<select name="dpt" class="h-11 rounded-xl border px-3 text-sm" style="border-color:var(--line); color:var(--ink)">
					<?php foreach ($departments as $department): ?>
						<option value="<?= htmlspecialchars($department) ?>" <?= $form['dpt'] === $department ? 'selected' : '' ?>><?= htmlspecialchars($department) ?></option>
					<?php endforeach; ?>
				</select>
			</label>

			<div class="grid md:grid-cols-2 gap-4">
				<label class="grid gap-1">
					<span class="text-sm font-semibold" style="color:var(--ink)">Full Name</span>
					<input type="text" name="name" value="<?= htmlspecialchars($form['name']) ?>" required class="h-11 rounded-xl border px-3 text-sm" style="border-color:var(--line); color:var(--ink)">
				</label>
				<label class="grid gap-1">
					<span class="text-sm font-semibold" style="color:var(--ink)">Email Address</span>
					<input type="email" name="email" value="<?= htmlspecialchars($form['email']) ?>" required class="h-11 rounded-xl border px-3 text-sm" style="border-color:var(--line); color:var(--ink)">
				</label>
			</div>

			<label class="grid gap-1">
				<span class="text-sm font-semibold" style="color:var(--ink)">Phone Number</span>
				<input type="text" name="phone" value="<?= htmlspecialchars($form['phone']) ?>" placeholder="Optional" class="h-11 rounded-xl border px-3 text-sm" style="border-color:var(--line); color:var(--ink)">
			</label>

			<label class="grid gap-1">
				<span class="text-sm font-semibold" style="color:var(--ink)">Message</span>
				<textarea name="comments" rows="6" required class="rounded-xl border p-3 text-sm" style="border-color:var(--line); color:var(--ink)"><?= htmlspecialchars($form['comments']) ?></textarea>
			</label>

			<div>
				<button type="submit" class="inline-flex items-center justify-center rounded-xl px-6 py-3 text-sm font-semibold text-slate-950" style="background:var(--accent)">Submit Message</button>
			</div>
		</form>
	</div>
</section>

<?php require __DIR__ . '/_shared/footer.php'; ?>
