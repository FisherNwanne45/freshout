<?php
require __DIR__ . '/bootstrap.php';
require __DIR__ . '/header.php';
$sourcePage = $sourcePage ?? basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
$sourcePath = dirname(__DIR__, 2) . '/theme1/' . $sourcePage;
    // Use dedicated home template for homepage
    if ($sourcePage === 'index.php') {
      require __DIR__ . '/home.php';
    } else {
      // Pass-through legacy rendering for interior pages
      $sourcePath = dirname(__DIR__, 2) . '/theme1/' . $sourcePage;
      $middle = theme1_render_middle($sourcePath);
?>
<article class="legacy-wrap max-w-5xl mx-auto rounded-3xl p-4 md:p-8 border shadow-sm" style="background:var(--surface); border-color:var(--line)">
  <?= $middle ?>
</article>
<?php
    }
    require __DIR__ . '/footer.php';
