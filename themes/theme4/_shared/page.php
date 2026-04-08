<?php
require __DIR__ . '/bootstrap.php';
require __DIR__ . '/header.php';
$sourcePage = $sourcePage ?? basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
if ($sourcePage === 'index.php') {
    require __DIR__ . '/home.php';
} else {
    $sourcePath = dirname(__DIR__, 2) . '/theme1/' . $sourcePage;
    $middle = theme1_render_middle($sourcePath);
    ?>
    <section class="max-w-6xl mx-auto mb-5 rounded-3xl border px-6 py-6 md:px-8" style="background:color-mix(in srgb, var(--accent) 13%, #fff); border-color:var(--line)">
      <p class="text-xs uppercase tracking-[0.18em]" style="color:var(--muted)">Customer Resources</p>
      <h2 class="mt-2 text-2xl md:text-3xl font-black" style="color:var(--primary2)"><?= htmlspecialchars((string)$pageTitle) ?></h2>
    </section>
    <article class="legacy-wrap max-w-6xl mx-auto rounded-3xl p-4 md:p-8 border shadow-sm" style="background:var(--surface); border-color:var(--line)">
      <?= $middle ?>
    </article>
    <?php
}
require __DIR__ . '/footer.php';
