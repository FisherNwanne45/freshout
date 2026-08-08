<?php
$root = __DIR__ . '/../themes/theme6';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
$updated = [];
foreach ($iterator as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }
    $path = $file->getPathname();
    $content = file_get_contents($path);
    $oldSrc = 'src="<?php echo $url; ?>/admin/assets/images/logo/<?php echo $image; ?>"';
    $newSrc = 'src="<?php echo $logo_url ?: ($url . \'/admin/assets/images/logo/\' . $image); ?>"';
    $oldSrcset = 'srcset="<?php echo $url; ?>/admin/assets/images/logo/<?php echo $image; ?> 329w, <?php echo $url; ?>/admin/assets/images/logo/<?php echo $image; ?> 300w, <?php echo $url; ?>/admin/assets/images/logo/<?php echo $image; ?> 1024w, <?php echo $url; ?>/admin/assets/images/logo/<?php echo $image; ?> 768w, <?php echo $url; ?>/admin/assets/images/logo/<?php echo $image; ?> 1047w"';
    $newSrcset = 'srcset="<?php echo $logo_url ?: ($url . \'/admin/assets/images/logo/\' . $image); ?> 329w, <?php echo $logo_url ?: ($url . \'/admin/assets/images/logo/\' . $image); ?> 300w, <?php echo $logo_url ?: ($url . \'/admin/assets/images/logo/\' . $image); ?> 1024w, <?php echo $logo_url ?: ($url . \'/admin/assets/images/logo/\' . $image); ?> 768w, <?php echo $logo_url ?: ($url . \'/admin/assets/images/logo/\' . $image); ?> 1047w"';
    $newContent = str_replace($oldSrc, $newSrc, $content);
    $newContent = str_replace($oldSrcset, $newSrcset, $newContent);
    if ($newContent !== $content) {
        file_put_contents($path, $newContent);
        $updated[] = $path;
    }
}
foreach ($updated as $path) {
    echo $path . PHP_EOL;
}
