<?php
/** GET /api/product.php?id=1 -> { success, product } */
require_once __DIR__ . '/config.php';
require_method('GET');
require_db($conn);
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) json_error('A valid product id is required.', 422);

$stmt = $conn->prepare("SELECT id, name, description, price, category, image, stock, badge FROM products WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$p = $stmt->get_result()->fetch_assoc();
if (!$p) json_error('Product not found.', 404);
$p['id'] = (int)$p['id']; $p['price'] = (float)$p['price']; $p['stock'] = (int)$p['stock'];
json_response(['success' => true, 'product' => $p]);
