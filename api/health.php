<?php
/** GET /api/health.php — quick check that the API and database are up (used by the app and CI). */
require_once __DIR__ . '/config.php';
require_method('GET');
require_db($conn);
$n = (int)$conn->query("SELECT COUNT(*) AS c FROM products")->fetch_assoc()['c'];
json_response(['success' => true, 'status' => 'ok', 'products' => $n, 'time' => date('c')]);
