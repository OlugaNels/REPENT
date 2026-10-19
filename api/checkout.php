<?php
/**
 * POST /api/checkout.php (auth)
 * { items:[{product_id, quantity}], shipping_address, city, postal_code, phone, payment_method }
 * Prices come from the database, never from the app. Stock is reduced with a
 * conditional UPDATE inside a transaction, so two shoppers can never buy the
 * last item at the same time.  Shipping: free from R800, otherwise R95.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth_helper.php';
require_method('POST');
require_db($conn);
$user = require_auth($conn);

$b       = read_json_body();
$address = str_field($b, 'shipping_address', 255);
$city    = str_field($b, 'city', 100);
$postal  = str_field($b, 'postal_code', 20);
$phone   = str_field($b, 'phone', 30);
$pay     = in_array($b['payment_method'] ?? '', ['eft', 'card'], true) ? $b['payment_method'] : 'eft';
$items   = $b['items'] ?? null;

if (!is_array($items) || !$items)  json_error('Your cart is empty.', 422);
if (count($items) > 50)            json_error('Too many items in one order.', 422);
if ($address === '')               json_error('Delivery address is required.', 422);
if ($city === '')                  json_error('City / Town is required.', 422);
if (!preg_match('/^[A-Za-z0-9 \-]{3,10}$/', $postal)) json_error('Please enter a valid postal code.', 422);
if (!phone_ok($phone))             json_error('Please enter a valid contact phone number.', 422);

// Merge duplicate lines and sanity-check quantities
$wanted = [];
foreach ($items as $it) {
    if (!is_array($it)) json_error('Invalid cart item.', 422);
    $pid = (int)($it['product_id'] ?? 0);
    $qty = (int)($it['quantity'] ?? 0);
    if ($pid <= 0 || $qty < 1 || $qty > 20) json_error('Each item needs a valid product and a quantity from 1 to 20.', 422);
    $wanted[$pid] = ($wanted[$pid] ?? 0) + $qty;
}

$conn->begin_transaction();
try {
    $lines = []; $subtotal = 0.0;
    $sel = $conn->prepare("SELECT id, name, price, stock FROM products WHERE id = ? LIMIT 1");
    foreach ($wanted as $pid => $qty) {
        $sel->bind_param('i', $pid);
        $sel->execute();
        $p = $sel->get_result()->fetch_assoc();
        if (!$p) { $conn->rollback(); json_error("A product in your cart no longer exists.", 422); }
        if ((int)$p['stock'] < $qty) { $conn->rollback(); json_error("Not enough stock for {$p['name']} (only {$p['stock']} left).", 409); }
        $lines[]  = ['id' => (int)$p['id'], 'name' => $p['name'], 'price' => (float)$p['price'], 'qty' => $qty];
        $subtotal += (float)$p['price'] * $qty;
    }
    $shipping = $subtotal >= 800 ? 0.0 : 95.0;
    $total    = $subtotal + $shipping;

    $ins = $conn->prepare(
        "INSERT INTO orders (user_id, total, shipping, status, shipping_address, city, postal_code, phone, payment_method)
         VALUES (?, ?, ?, 'processing', ?, ?, ?, ?, ?)"
    );
    $ins->bind_param('iddsssss', $user['id'], $total, $shipping, $address, $city, $postal, $phone, $pay);
    $ins->execute();
    $orderId = (int)$conn->insert_id;

    $itemIns = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity) VALUES (?, ?, ?, ?, ?)");
    $stock   = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
    foreach ($lines as $l) {
        $stock->bind_param('iii', $l['qty'], $l['id'], $l['qty']);
        $stock->execute();
        if ($stock->affected_rows !== 1) { $conn->rollback(); json_error("Sorry, {$l['name']} just sold out.", 409); }
        $itemIns->bind_param('iisdi', $orderId, $l['id'], $l['name'], $l['price'], $l['qty']);
        $itemIns->execute();
    }
    $conn->commit();
} catch (Throwable $e) {
    try { $conn->rollback(); } catch (Throwable $ignored) {}
    throw $e;
}
json_response(['success' => true, 'order_id' => $orderId, 'subtotal' => $subtotal, 'shipping' => $shipping, 'total' => $total], 201);
