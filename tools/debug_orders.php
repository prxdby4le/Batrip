<?php
require_once __DIR__ . '/../includes/db.php';

$stmt = $pdo->query('SELECT * FROM orders ORDER BY id DESC LIMIT 10');
$orders = $stmt->fetchAll();
?>
<pre><?php print_r($orders); ?></pre>