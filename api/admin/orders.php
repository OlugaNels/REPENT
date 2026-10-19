<?php
/** GET /api/admin/orders.php (admin) · PUT { order_id, status } */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth_helper.php';
$method = require_method('GET', 'PUT');
require_db($conn);
require_admin_auth($conn);

if ($method === 'GET') {
    $res = $conn->query("SELECT o.id, u.fullname AS customer, o.total, o.status, o.created_at
                         FROM orders o JOIN users u ON u.id = o.user_id ORDER BY o.created_at DESC, o.id DESC");
    $out = [];
    while ($r = $res->fetch_assoc()) {
        $out[] = ['id' => (int)$r['id'], 'customer' => $r['customer'], 'total' => (float)$r['total'],
                  'status' => $r['status'], 'created_at' => $r['created_at']];
    }
    json_response(['success' => true, 'orders' => $out]);
}
$b = read_json_body();
$id = (int)($b['order_id'] ?? 0);
$status = (string)($b['status'] ?? '');
if ($id <= 0 || !in_array($status, ['pending', 'processing', 'shipped', 'delivered', 'cancelled'], true)) {
    json_error('A valid order_id and status are required.', 422);
}
$stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
$stmt->bind_param('si', $status, $id);
$stmt->execute();
json_response(['success' => true]);
