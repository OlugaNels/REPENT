<?php
/** GET /api/devotionals.php -> { success, devotionals[] } */
require_once __DIR__ . '/config.php';
require_method('GET');
require_db($conn);
$res = $conn->query("SELECT id, title, verse, reference, content, topic, created_at FROM devotionals ORDER BY created_at DESC, id DESC");
$out = [];
while ($r = $res->fetch_assoc()) { $r['id'] = (int)$r['id']; $out[] = $r; }
json_response(['success' => true, 'devotionals' => $out]);
