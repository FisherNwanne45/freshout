<?php
// ── Shell Close Partial ────────────────────────────────────────────────────────
// Closes the content wrapper + main, outputs scripts + mobile nav.
?>
        </div><!-- /content-wrap -->
    </main>

    <script>
        (function () {
            var sidebar = document.getElementById('app-sidebar');
            var closeBtn = document.getElementById('sidebar-close');
            if (closeBtn && sidebar) {
                closeBtn.addEventListener('click', function () {
                    sidebar.classList.add('-translate-x-full');
                });
            }
        }());
    </script>
    <script src="js/new-ui-shell.js?v=20260407"></script>

    <!-- Mobile bottom nav -->
    <nav class="mobile-bottom-nav fixed bottom-3 left-3 right-3 z-40 lg:hidden">
        <div class="mobile-bottom-nav__inner grid grid-cols-5">
            <?php
            $cur = basename((string)($_SERVER['SCRIPT_NAME'] ?? ''));
            $mobileItems = [
                ['index.php',     'Home',    '<path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/>'],
                ['send.php',      'Send',    '<path d="M4 7h12"/><path d="M12 3l4 4-4 4"/><path d="M20 17H8"/><path d="M12 13l-4 4 4 4"/>'],
                ['statement.php', 'History', '<rect x="5" y="3" width="14" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h6"/>'],
                ['inbox.php',     'Inbox',   '<path d="M3 7h18v10H3z"/><path d="M3 8l9 6 9-6"/>'],
                ['profile.php',   'Profile', '<circle cx="12" cy="8" r="4"/><path d="M4 20c1.8-3.7 5-5.5 8-5.5s6.2 1.8 8 5.5"/>'],
            ];
            $sendPages = ['send.php','transfer-auth.php','otp_auth.php','pincode.php'];
            foreach ($mobileItems as [$href, $label, $paths]):
                $active = ($href === 'send.php') ? in_array($cur, $sendPages, true) : $cur === $href;
            ?>
            <a href="<?= $href ?>" class="mobile-bottom-nav__item<?= $active ? ' is-active' : '' ?>">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><?= $paths ?></svg>
                <span><?= $label ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </nav>

<?php
    $liveChatScript = '';
    if (isset($siteRow) && is_array($siteRow) && isset($siteRow['tawk'])) {
        $liveChatScript = (string)$siteRow['tawk'];
    } elseif (isset($site) && is_array($site) && isset($site['tawk'])) {
        $liveChatScript = (string)$site['tawk'];
    } elseif (isset($conn) && $conn instanceof mysqli) {
        try {
            $siteRes = $conn->query("SELECT tawk FROM site ORDER BY id ASC LIMIT 1");
            if ($siteRes && $siteRes->num_rows > 0) {
                $siteData = $siteRes->fetch_assoc();
                $liveChatScript = (string)($siteData['tawk'] ?? '');
            }
        } catch (Throwable $e) {
        }
    }
    $liveChatScript = trim($liveChatScript);
    $liveChatScript = preg_replace('/<script>\s*$/i', '', $liveChatScript) ?? $liveChatScript;
?>
<?php if ($liveChatScript !== ''): ?>
    <?= $liveChatScript ?>
<?php endif; ?>
</body>
</html>
