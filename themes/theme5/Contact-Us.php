<?php
$sourcePage = 'Contact-Us.php';
require __DIR__ . '/_shared/bootstrap.php';
require __DIR__ . '/_shared/header.php';
$pageTitle = 'Contact Us';
?>

<section class="mb-8 rounded-3xl overflow-hidden border p-8 md:p-12 text-center" style="border-color:var(--line); background:linear-gradient(135deg, var(--primary2), var(--primary))">
	<p class="text-xs uppercase tracking-[0.2em] text-white/85 font-semibold">Get In Touch</p>
	<h1 class="mt-4 text-4xl md:text-5xl font-black text-white">We're Here to Help</h1>
	<p class="mt-4 text-base text-white/90 max-w-2xl mx-auto">Reach out to our specialized team. We respond within 2 business hours.</p>
</section>

<div class="grid md:grid-cols-2 gap-8 mb-8">
	<article class="rounded-2xl border overflow-hidden" style="border-color:var(--line); background:var(--surface)">
		<div class="p-8">
			<h2 class="text-2xl font-black mb-6" style="color:var(--primary2)">Send Us a Message</h2>
			<form method="POST" class="space-y-4">
				<div>
					<label class="block text-sm font-semibold mb-2" style="color:var(--ink)">Your Name *</label>
					<input type="text" name="name" required minlength="2" class="w-full px-4 py-2.5 rounded-lg border text-sm" style="border-color:var(--line); background:var(--bg)" placeholder="Full name">
				</div>
				<div>
					<label class="block text-sm font-semibold mb-2" style="color:var(--ink)">Email Address *</label>
					<input type="email" name="email" required class="w-full px-4 py-2.5 rounded-lg border text-sm" style="border-color:var(--line); background:var(--bg)" placeholder="your@email.com">
				</div>
				<div>
					<label class="block text-sm font-semibold mb-2" style="color:var(--ink)">Department</label>
					<select name="department" class="w-full px-4 py-2.5 rounded-lg border text-sm" style="border-color:var(--line); background:var(--bg)">
						<option value="general">General Inquiry</option>
						<option value="business">Business Banking</option>
						<option value="personal">Personal Banking</option>
						<option value="support">Technical Support</option>
						<option value="other">Other</option>
					</select>
				</div>
				<div>
					<label class="block text-sm font-semibold mb-2" style="color:var(--ink)">Phone (Optional)</label>
					<input type="tel" name="phone" class="w-full px-4 py-2.5 rounded-lg border text-sm" style="border-color:var(--line); background:var(--bg)" placeholder="(555) 000-0000">
				</div>
				<div>
					<label class="block text-sm font-semibold mb-2" style="color:var(--ink)">Message *</label>
					<textarea name="message" required minlength="8" rows="5" class="w-full px-4 py-2.5 rounded-lg border text-sm resize-none" style="border-color:var(--line); background:var(--bg)" placeholder="Tell us how we can help..."></textarea>
				</div>
				<button type="submit" class="w-full px-6 py-3 rounded-lg text-sm font-bold text-slate-950" style="background:var(--accent)">Send Message</button>
			</form>
		</div>
	</article>

	<article class="rounded-2xl border overflow-hidden" style="border-color:var(--line); background:var(--surface)">
		<div class="p-8 h-full flex flex-col justify-between">
			<div>
				<h2 class="text-2xl font-black mb-8" style="color:var(--primary2)">Contact Information</h2>
				<div class="space-y-6">
					<div>
						<p class="text-xs uppercase tracking-[0.2em] font-semibold mb-2" style="color:var(--muted)">Main Number</p>
						<a href="tel:<?= htmlspecialchars((string)($site['phone'] ?? '')) ?>" class="text-2xl font-bold" style="color:var(--primary)">
							<?= htmlspecialchars((string)($site['phone'] ?? '(555) 000-0000')) ?>
						</a>
					</div>
					<div>
						<p class="text-xs uppercase tracking-[0.2em] font-semibold mb-2" style="color:var(--muted)">Email</p>
						<a href="mailto:<?= htmlspecialchars((string)($site['email'] ?? '')) ?>" class="text-lg font-semibold break-all" style="color:var(--primary)">
							<?= htmlspecialchars((string)($site['email'] ?? 'support@bank.com')) ?>
						</a>
					</div>
					<div>
						<p class="text-xs uppercase tracking-[0.2em] font-semibold mb-2" style="color:var(--muted)">Hours</p>
						<p class="text-sm" style="color:var(--ink)">Monday – Friday: 8:00 AM – 6:00 PM<br>Saturday: 10:00 AM – 4:00 PM<br>Sunday: Closed</p>
					</div>
					<?php
					try {
						$locRes = $GLOBALS['conn']->query("SELECT COUNT(*) as cnt FROM site_branches WHERE is_active=1");
						$locCnt = $locRes ? ($locRes->fetch_assoc()['cnt'] ?? 1) : 1;
					} catch (Throwable $e) {
						$locCnt = 1;
					}
					?>
					<div>
						<p class="text-xs uppercase tracking-[0.2em] font-semibold mb-2" style="color:var(--muted)">Branches</p>
						<p class="text-lg font-bold" style="color:var(--primary)"><?= htmlspecialchars((string)$locCnt) ?> Locations</p>
						<a href="Locations.php" class="text-sm font-semibold mt-2" style="color:var(--primary)">Find a branch →</a>
					</div>
				</div>
			</div>
		</div>
	</article>
</div>

<section class="mb-8 rounded-3xl border overflow-hidden p-8 md:p-12 text-center" style="border-color:var(--line); background:linear-gradient(135deg, var(--primary2) 0%, var(--primary) 50%, var(--accent))">
	<p class="text-xs uppercase tracking-[0.2em] text-white/85 font-semibold">Quick Response Guaranteed</p>
	<h2 class="mt-4 text-3xl md:text-4xl font-black text-white">Your Inquiry Matters</h2>
	<p class="mt-4 text-base text-white/90">We'll respond to every message within 2 business hours. For urgent matters, call our main line directly.</p>
</section>

<?php require __DIR__ . '/_shared/footer.php'; ?>
