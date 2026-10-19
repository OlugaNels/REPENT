<?php
/** GET /api/admin/subscribers.php (admin) · DELETE { id } */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth_helper.php';
$method = require_method('GET', 'DELETE');
require_db($conn);
require_admin_auth($conn);
if ($method === 'GET') {
    $res = $conn->query("SELECT id, name, email, frequency, subscribed_at FROM newsletter_subscribers ORDER BY subscribed_at DESC, id DESC");
    $out = [];
    while ($r = $res->fetch_assoc()) { $r['id'] = (int)$r['id']; $out[] = $r; }
    json_response(['success' => true, 'subscribers' => $out]);
}
$id = (int)(read_json_body()['id'] ?? 0);
if ($id <= 0) json_error('A valid id is required.', 422);
$stmt = $conn->prepare("DELETE FROM newsletter_subscribers WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
json_response(['success' => true]);
