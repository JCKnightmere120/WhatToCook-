<?php
$dbPath = __DIR__ . '/../database/database.sqlite';
if (! file_exists($dbPath)) {
    echo "DB not found at $dbPath\n";
    exit(1);
}
$db = new PDO('sqlite:' . $dbPath);
$stmt = $db->query('SELECT count(*) as c FROM sessions');
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo "sessions=" . ($row['c'] ?? 0) . "\n";
