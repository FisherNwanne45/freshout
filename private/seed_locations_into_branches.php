<?php
require dirname(__DIR__) . '/config.php';

$conn->query("CREATE TABLE IF NOT EXISTS site_branches (
  id INT AUTO_INCREMENT PRIMARY KEY,
  branch_name VARCHAR(100) NOT NULL DEFAULT '',
  address     VARCHAR(255) NOT NULL DEFAULT '',
  phone       VARCHAR(50)  NOT NULL DEFAULT '',
  sort_order  INT          NOT NULL DEFAULT 99,
  is_active   TINYINT(1)   NOT NULL DEFAULT 1,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$siteAddress = '';
$siteRes = $conn->query("SELECT addr FROM site ORDER BY id ASC LIMIT 1");
if ($siteRes && $siteRes->num_rows > 0) {
    $siteRow = $siteRes->fetch_assoc();
    $siteAddress = trim((string)($siteRow['addr'] ?? ''));
}

$branches = [
    [
        'name' => 'Turkey Branch',
        'address' => "53 Hipodrom Street, 06560 Yenimahalle, Ankara",
        'phone' => '',
        'order' => 10,
    ],
    [
        'name' => 'UK Branch',
        'address' => "C3 Vantage Office Park, Old Gloucester Road, Hambrook, Bristol, BS16 1GW",
        'phone' => '',
        'order' => 20,
    ],
    [
        'name' => 'City Center Branch',
        'address' => $siteAddress,
        'phone' => '',
        'order' => 30,
    ],
    [
        'name' => 'King City Branch',
        'address' => "432 Broadway Street, King City CA 93930",
        'phone' => '',
        'order' => 40,
    ],
    [
        'name' => 'San Luis Obispo',
        'address' => "142 Cross Street, Suite 130, San Luis Obispo, CA 93401",
        'phone' => '',
        'order' => 50,
    ],
];

$selectStmt = $conn->prepare('SELECT id FROM site_branches WHERE branch_name = ? AND address = ? LIMIT 1');
$insertStmt = $conn->prepare('INSERT INTO site_branches (branch_name, address, phone, sort_order, is_active) VALUES (?, ?, ?, ?, 1)');
$updateStmt = $conn->prepare('UPDATE site_branches SET address = ?, sort_order = ? WHERE id = ?');

$inserted = 0;
$updated = 0;
$skipped = 0;

foreach ($branches as $branch) {
    $name = (string)$branch['name'];
    $address = trim((string)$branch['address']);
    $phone = (string)$branch['phone'];
    $sortOrder = (int)$branch['order'];

    if ($address === '') {
        $skipped++;
        continue;
    }

    $existingId = 0;
    $selectStmt->bind_param('ss', $name, $address);
    $selectStmt->execute();
    $res = $selectStmt->get_result();
    if ($res && $res->num_rows > 0) {
        $skipped++;
        continue;
    }

    $matchByName = $conn->prepare('SELECT id FROM site_branches WHERE branch_name = ? LIMIT 1');
    $matchByName->bind_param('s', $name);
    $matchByName->execute();
    $resByName = $matchByName->get_result();
    if ($resByName && $resByName->num_rows > 0) {
        $row = $resByName->fetch_assoc();
        $existingId = (int)($row['id'] ?? 0);
    }
    $matchByName->close();

    if ($existingId > 0) {
        $updateStmt->bind_param('sii', $address, $sortOrder, $existingId);
        if ($updateStmt->execute()) {
            $updated++;
        }
        continue;
    }

    $insertStmt->bind_param('sssi', $name, $address, $phone, $sortOrder);
    if ($insertStmt->execute()) {
        $inserted++;
    }
}

$selectStmt->close();
$insertStmt->close();
$updateStmt->close();

echo "seed_locations_into_branches complete\n";
echo "inserted: {$inserted}\n";
echo "updated: {$updated}\n";
echo "skipped: {$skipped}\n";
