<?php
$sourcePage = 'Locations.php';
require __DIR__ . '/_shared/bootstrap.php';
require __DIR__ . '/_shared/header.php';
$pageTitle = 'Our Locations';
?>

<section class="mb-8 rounded-3xl overflow-hidden border p-8 md:p-12 text-center" style="border-color:var(--line); background:linear-gradient(135deg, var(--primary2), var(--primary))">
	<p class="text-xs uppercase tracking-[0.2em] text-white/85 font-semibold">Visit Our Branches</p>
	<h1 class="mt-4 text-4xl md:text-5xl font-black text-white">Find a Location Near You</h1>
	<p class="mt-4 text-base text-white/90 max-w-2xl mx-auto">With branches across the region, we're never far away. Stop by to meet our team.</p>
</section>

<?php
$branches = [];
try {
	$bRes = $GLOBALS['conn']->query("SELECT id, branch_name, address, phone, is_active FROM site_branches WHERE is_active=1 ORDER BY sort_order, id");
	if ($bRes) {
		while ($b = $bRes->fetch_assoc()) {
			$branches[] = $b;
		}
	}
} catch (Throwable $e) {}

if (count($branches) > 0):
?>
<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
	<?php foreach ($branches as $branch): ?>
	<article class="rounded-2xl border overflow-hidden p-6" style="border-color:var(--line); background:var(--surface)">
		<div class="flex items-start gap-3 mb-4">
			<div class="text-2xl font-black" style="color:var(--accent)">📍</div>
			<div class="flex-1">
				<h3 class="font-bold text-lg" style="color:var(--primary2)"><?= htmlspecialchars((string)($branch['branch_name'] ?? '')) ?></h3>
			</div>
		</div>
		<div class="space-y-3 text-sm" style="color:var(--muted)">
			<p><?= nl2br(htmlspecialchars((string)($branch['address'] ?? ''))) ?></p>
			<?php if (!empty($branch['phone'])): ?>
			<a href="tel:<?= htmlspecialchars((string)($branch['phone'] ?? '')) ?>" class="block font-semibold" style="color:var(--primary)">
				<?= htmlspecialchars((string)($branch['phone'] ?? '')) ?>
			</a>
			<?php endif; ?>
		</div>
		<a href="Contact-Us.php" class="inline-flex mt-5 text-sm font-bold" style="color:var(--primary)">Get Directions →</a>
	</article>
	<?php endforeach; ?>
</div>
<?php else: ?>
<div class="rounded-2xl border overflow-hidden p-8 text-center mb-8" style="border-color:var(--line); background:var(--surface)">
	<p class="text-lg font-semibold mb-2" style="color:var(--primary2)">Our Main Office</p>
	<p class="text-sm mb-4" style="color:var(--muted)"><?= htmlspecialchars((string)($site['addr'] ?? 'Address not configured')) ?></p>
	<p class="text-sm font-semibold" style="color:var(--primary)">
		<?= htmlspecialchars((string)($site['phone'] ?? '(555) 000-0000')) ?>
	</p>
</div>
<?php endif; ?>

<section class="mb-8 rounded-3xl border overflow-hidden p-8 md:p-12" style="border-color:var(--line); background:linear-gradient(135deg, var(--primary2) 0%, var(--primary) 50%, var(--accent))">
	<div class="text-center">
		<p class="text-xs uppercase tracking-[0.2em] text-white/85 font-semibold">Schedule a Visit</p>
		<h2 class="mt-4 text-3xl md:text-4xl font-black text-white">Ready to Meet Our Team?</h2>
		<p class="mt-4 text-base text-white/90 max-w-2xl mx-auto">Book an appointment with a specialist to discuss your banking needs in person.</p>
		<a href="Contact-Us.php" class="inline-flex mt-6 px-8 py-3 rounded-lg text-sm font-bold text-slate-950" style="background:var(--accent)">Schedule Appointment</a>
	</div>
</section>

<?php require __DIR__ . '/_shared/footer.php'; ?>
