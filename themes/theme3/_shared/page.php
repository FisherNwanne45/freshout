<?php
require __DIR__ . '/bootstrap.php';
require __DIR__ . '/header.php';
$sourcePage = $sourcePage ?? basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
if ($sourcePage === 'index.php') {
    require __DIR__ . '/home.php';
} else {
    $sourcePath = dirname(__DIR__, 2) . '/theme1/' . $sourcePage;
    $middle = theme1_render_middle($sourcePath);
    $prettyTitle = trim((string)$pageTitle);
    if ($prettyTitle === '') {
        $prettyTitle = 'Information';
    }
    ?>
    <section class="max-w-6xl mx-auto mb-5 rounded-3xl border border-white/10 px-6 py-7 md:px-8 md:py-8" style="background:linear-gradient(125deg, color-mix(in srgb, var(--primary2) 70%, #020617), color-mix(in srgb, var(--primary) 68%, #0f172a));">
      <h1 class="mt-2 text-2xl md:text-4xl font-black text-white"><?= htmlspecialchars($prettyTitle) ?></h1>
      <p class="mt-2 text-sm md:text-base text-slate-200">Explore our services and resources tailored to your banking needs.</p>
    </section>

    <article class="legacy-wrap max-w-6xl mx-auto rounded-3xl p-5 md:p-9 border shadow-xl" style="background:var(--surface); border-color:var(--line)">
      <?= $middle ?>
    </article>
    <?php
}
require __DIR__ . '/footer.php';
