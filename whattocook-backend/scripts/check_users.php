<?php
$dbPath = __DIR__ . '/../database/database.sqlite';
if (! file_exists($dbPath)) {
    echo "DB not found at $dbPath\n";
    exit(1);
}
$db = new PDO('sqlite:' . $dbPath);
$stmt = $db->query('SELECT id, email, is_admin FROM users');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo sprintf("%d | %s | is_admin=%s\n", $r['id'], $r['email'], $r['is_admin']);
}
