<?php
// Root convenience endpoint for build migration.
// Keeps backward compatibility with deployment checklists expecting /migration.php.
header('Location: user/admin/migrate.php');
exit;
