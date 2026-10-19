<?php
/** GET /api/orders.php (auth) -> the signed-in user's orders with their items. */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth_helper.php';
require_method('GET');
require_db($conn);
$user = require_auth($conn);

$stmt = $conn->prepare(
    "SELECT id, total, shipping, shipping_address, city, postal_code, phone, payment_method, status, created_at
     FROM orders WHERE user_id = ? ORDER BY created_at DESC, id DESC"
);
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$itemStmt = $conn->prepare("SELECT product_id, product_name, price, quantity FROM order_items WHERE order_id = ? ORDER BY id");
$out = [];
foreach ($orders as $o) {
    $itemStmt->bind_param('i', $o['id']);
    $itemStmt->execute();
    $items = $itemStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $out[] = [
        'id' => (int)$o['id'], 'total' => (float)$o['total'], 'shipping' => (float)$o['shipping'],
        'shipping_address' => $o['shipping_address'], 'city' => $o['city'], 'postal_code' => $o['postal_code'],
        'phone' => $o['phone'], 'payment_method' => $o['payment_method'], 'status' => $o['status'], 'created_at' => $o['created_at'],
        'items' => array_map(fn($i) => [
            'product_id' => (int)$i['product_id'], 'product_name' => $i['product_name'],
            'price' => (float)$i['price'], 'quantity' => (int)$i['quantity'],
        ], $items),
    ];
}
json_response(['success' => true, 'orders' => $out]);
