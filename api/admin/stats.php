<?php
/** GET /api/admin/stats.php (admin) */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth_helper.php';
require_method('GET');
require_db($conn);
require_admin_auth($conn);

function count_rows($conn, string $table): int {
    return (int)$conn->query("SELECT COUNT(*) AS c FROM $table")->fetch_assoc()['c'];
}
$rev = $conn->query("SELECT COALESCE(SUM(total), 0) AS r FROM orders WHERE status <> 'cancelled'")->fetch_assoc()['r'];
json_response(['success' => true, 'stats' => [
    'orders' => count_rows($conn, 'orders'), 'users' => count_rows($conn, 'users'),
    'products' => count_rows($conn, 'products'), 'prayers' => count_rows($conn, 'prayer_requests'),
    'subscribers' => count_rows($conn, 'newsletter_subscribers'), 'revenue' => (float)$rev,
]]);
