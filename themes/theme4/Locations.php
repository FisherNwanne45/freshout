<?php
require __DIR__ . '/_shared/bootstrap.php';

$pageTitle = 'Locations';
$branches = [];

try {
		$tableExists = false;
		$tblRes = $conn->query("SHOW TABLES LIKE 'site_branches'");
		if ($tblRes instanceof mysqli_result && $tblRes->num_rows > 0) {
				$tableExists = true;
		}

		if ($tableExists) {
				$branchRes = $conn->query("SELECT branch_name, address, phone FROM site_branches WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
				if ($branchRes instanceof mysqli_result) {
						while ($row = $branchRes->fetch_assoc()) {
								$name = trim((string)($row['branch_name'] ?? ''));
								$address = trim((string)($row['address'] ?? ''));
								$phone = trim((string)($row['phone'] ?? ''));
								if ($address === '') {
										continue;
								}
								$branches[] = [
										'name' => $name === '' ? 'Branch Office' : $name,
										'address' => $address,
										'phone' => $phone,
								];
						}
				}
		}
} catch (Throwable $e) {
}

if (!$branches) {
		$fallbackAddress = trim((string)($site['addr'] ?? ''));
		if ($fallbackAddress !== '') {
				$branches[] = [
						'name' => (string)($site['name'] ?? 'Main Office'),
						'address' => $fallbackAddress,
						'phone' => trim((string)($site['phone'] ?? '')),
				];
		}
}

require __DIR__ . '/_shared/header.php';
?>

<section class="max-w-6xl mx-auto mb-6 rounded-3xl border px-6 py-7 md:px-8 md:py-8" style="background:color-mix(in srgb, var(--accent) 13%, #fff); border-color:var(--line)">
	<h2 class="text-2xl md:text-4xl font-black" style="color:var(--primary2)">Branch & ATM Locations</h2>
	<p class="mt-2 max-w-3xl text-sm md:text-base" style="color:var(--muted)">Explore available branch offices and service points. Contact our client desk for account-specific guidance before your visit.</p>
</section>

<section class="max-w-6xl mx-auto grid md:grid-cols-3 gap-4 mb-6">
	<div class="rounded-2xl border p-5" style="border-color:var(--line); background:var(--surface)">
		<p class="text-xs uppercase tracking-[0.18em]" style="color:var(--muted)">Client Support</p>
		<p class="mt-2 text-sm" style="color:var(--ink)"><?= htmlspecialchars((string)($site['phone'] ?? '')) ?></p>
		<p class="text-sm" style="color:var(--muted)"><?= htmlspecialchars((string)($site['email'] ?? '')) ?></p>
	</div>
	<div class="rounded-2xl border p-5" style="border-color:var(--line); background:var(--surface)">
		<p class="text-xs uppercase tracking-[0.18em]" style="color:var(--muted)">Business Hours</p>
		<p class="mt-2 text-sm" style="color:var(--muted)">Mon - Fri: 8:30 AM - 5:00 PM</p>
		<p class="text-sm" style="color:var(--muted)">Holiday schedules vary by branch.</p>
	</div>
	<div class="rounded-2xl border p-5" style="border-color:var(--line); background:var(--surface)">
		<p class="text-xs uppercase tracking-[0.18em]" style="color:var(--muted)">Before You Visit</p>
		<p class="mt-2 text-sm" style="color:var(--muted)">Bring valid identification for account service and scheduled advisory meetings.</p>
	</div>
</section>

<section class="max-w-6xl mx-auto rounded-3xl border p-5 md:p-8" style="border-color:var(--line); background:var(--surface)">
	<div class="flex items-center justify-between gap-3 flex-wrap">
		<h3 class="text-2xl font-black" style="color:var(--ink)">Available Locations</h3>
		<p class="text-sm" style="color:var(--muted)"><?= count($branches) ?> location<?= count($branches) === 1 ? '' : 's' ?> listed</p>
	</div>

	<?php if ($branches): ?>
		<div class="mt-5 grid md:grid-cols-2 gap-4">
			<?php foreach ($branches as $branch): ?>
				<article class="rounded-2xl border p-5" style="border-color:var(--line)">
					<h4 class="text-lg font-extrabold" style="color:var(--ink)"><?= htmlspecialchars($branch['name']) ?></h4>
					<p class="mt-2 text-sm leading-relaxed" style="color:var(--muted)"><?= nl2br(htmlspecialchars($branch['address'])) ?></p>
					<?php if ($branch['phone'] !== ''): ?>
						<p class="mt-3 text-sm font-semibold" style="color:var(--ink)"><?= htmlspecialchars($branch['phone']) ?></p>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
		</div>
	<?php else: ?>
		<div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
			No branch locations are configured yet. Please update locations in the admin data source.
		</div>
	<?php endif; ?>
</section>

<?php require __DIR__ . '/_shared/footer.php'; ?>
