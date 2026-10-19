<?php
/** GET /api/products.php?category=all|tshirts|hoodies|caps -> { success, products[] } */
require_once __DIR__ . '/config.php';
require_method('GET');
require_db($conn);

$cat  = $_GET['category'] ?? 'all';
$cols = "id, name, description, price, category, image, stock, badge";
if (in_array($cat, ['tshirts', 'hoodies', 'caps'], true)) {
    $stmt = $conn->prepare("SELECT $cols FROM products WHERE category = ? ORDER BY id");
    $stmt->bind_param('s', $cat);
    $stmt->execute();
    $res = $stmt->get_result();
} else {
    $res = $conn->query("SELECT $cols FROM products ORDER BY id");
}
$out = [];
while ($r = $res->fetch_assoc()) {
    $r['id'] = (int)$r['id']; $r['price'] = (float)$r['price']; $r['stock'] = (int)$r['stock'];
    $out[] = $r;
}
json_response(['success' => true, 'products' => $out]);
