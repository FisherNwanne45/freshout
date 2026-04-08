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

<section class="max-w-6xl mx-auto mb-6 rounded-3xl border border-white/10 px-6 py-8 md:px-8 md:py-10" style="background:linear-gradient(125deg, color-mix(in srgb, var(--primary2) 70%, #020617), color-mix(in srgb, var(--primary) 68%, #0f172a));">
	<h1 class="text-3xl md:text-5xl font-black text-white">Branch & ATM Locations</h1>
	<p class="mt-3 max-w-3xl text-slate-200">Find our branch offices and banking service points. For account-specific support, contact our advisory desk before visiting.</p>
</section>

<section class="max-w-6xl mx-auto grid md:grid-cols-3 gap-5 mb-6">
	<div class="rounded-2xl border border-white/10 bg-slate-950/65 p-5">
		<p class="text-xs uppercase tracking-[0.18em] text-slate-400">Client Support</p>
		<p class="mt-2 text-lg font-bold text-white"><?= htmlspecialchars((string)($site['phone'] ?? '')) ?></p>
		<p class="mt-1 text-sm text-slate-300"><?= htmlspecialchars((string)($site['email'] ?? '')) ?></p>
	</div>
	<div class="rounded-2xl border border-white/10 bg-slate-950/65 p-5">
		<p class="text-xs uppercase tracking-[0.18em] text-slate-400">Business Hours</p>
		<p class="mt-2 text-sm text-slate-200">Mon - Fri: 8:30 AM - 5:00 PM</p>
		<p class="text-sm text-slate-300">Holiday schedules vary by branch.</p>
	</div>
	<div class="rounded-2xl border border-white/10 bg-slate-950/65 p-5">
		<p class="text-xs uppercase tracking-[0.18em] text-slate-400">Before You Visit</p>
		<p class="mt-2 text-sm text-slate-300">Bring a valid ID for account services and scheduled advisory meetings.</p>
	</div>
</section>

<section class="max-w-6xl mx-auto rounded-3xl border shadow-xl p-5 md:p-8" style="background:var(--surface); border-color:var(--line)">
	<div class="flex items-center justify-between gap-3 flex-wrap">
		<h2 class="text-2xl font-black" style="color:var(--ink)">Available Locations</h2>
		<p class="text-sm" style="color:var(--muted)"><?= count($branches) ?> location<?= count($branches) === 1 ? '' : 's' ?> listed</p>
	</div>

	<?php if ($branches): ?>
		<div class="mt-5 grid md:grid-cols-2 gap-4">
			<?php foreach ($branches as $branch): ?>
				<article class="rounded-2xl border p-5" style="border-color:var(--line)">
					<h3 class="text-lg font-extrabold" style="color:var(--ink)"><?= htmlspecialchars($branch['name']) ?></h3>
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
