<?php
/** /api/admin/products.php (admin): GET list · POST create · PUT update · DELETE { id } */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth_helper.php';
$method = require_method('GET', 'POST', 'PUT', 'DELETE');
require_db($conn);
require_admin_auth($conn);

if ($method === 'GET') {
    $res = $conn->query("SELECT id, name, description, price, category, image, stock, badge FROM products ORDER BY id");
    $out = [];
    while ($r = $res->fetch_assoc()) { $r['id'] = (int)$r['id']; $r['price'] = (float)$r['price']; $r['stock'] = (int)$r['stock']; $out[] = $r; }
    json_response(['success' => true, 'products' => $out]);
}

$b = read_json_body();
if ($method === 'DELETE') {
    $id = (int)($b['id'] ?? 0);
    if ($id <= 0) json_error('A valid id is required.', 422);
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    json_response(['success' => true]);
}

$id    = (int)($b['id'] ?? 0);
$name  = str_field($b, 'name', 150);
$desc  = str_field($b, 'description', 2000);
$price = is_numeric($b['price'] ?? null) ? (float)$b['price'] : 0.0;
$cat   = str_field($b, 'category', 20);
$image = str_field($b, 'image', 255);
$stock = is_numeric($b['stock'] ?? null) ? (int)$b['stock'] : -1;
$badge = str_field($b, 'badge', 50);
$badgeDb = $badge === '' ? null : $badge;

if ($name === '' || $price <= 0 || $price > 100000) json_error('A name and a price above 0 are required.', 422);
if (!in_array($cat, ['tshirts', 'hoodies', 'caps'], true)) json_error('Category must be tshirts, hoodies or caps.', 422);
if ($stock < 0 || $stock > 100000)                  json_error('Stock must be 0 or more.', 422);

if ($method === 'POST') {
    $stmt = $conn->prepare("INSERT INTO products (name, description, price, category, image, stock, badge) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('ssdssis', $name, $desc, $price, $cat, $image, $stock, $badgeDb);
    $stmt->execute();
    json_response(['success' => true, 'id' => (int)$conn->insert_id], 201);
}
if ($id <= 0) json_error('A valid id is required.', 422);
$stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, category=?, image=?, stock=?, badge=? WHERE id=?");
$stmt->bind_param('ssdssisi', $name, $desc, $price, $cat, $image, $stock, $badgeDb, $id);
$stmt->execute();
json_response(['success' => true]);
