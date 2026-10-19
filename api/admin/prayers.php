<?php
/** GET /api/admin/prayers.php (admin) */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth_helper.php';
require_method('GET');
require_db($conn);
require_admin_auth($conn);
$res = $conn->query("SELECT id, name, email, subject, prayer, is_private, submitted_at FROM prayer_requests ORDER BY submitted_at DESC, id DESC");
$out = [];
while ($r = $res->fetch_assoc()) {
    $out[] = ['id' => (int)$r['id'], 'name' => $r['name'], 'email' => $r['email'], 'subject' => $r['subject'],
              'prayer' => $r['prayer'], 'is_private' => (bool)(int)$r['is_private'], 'submitted_at' => $r['submitted_at']];
}
json_response(['success' => true, 'prayers' => $out]);
