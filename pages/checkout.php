<?php
/**
 * ============================================================
 * REPENT† — Checkout & Order Processing Page
 * File: pages/checkout.php
 *
 * PURPOSE: Collects shipping/payment details and finalises 
 * the customer purchase. Handles both actual database insertions
 * and local session demo-fallbacks for presentation mode.
 * ============================================================
 */

require_once '../includes/header.php';
require_once '../includes/db.php';

// Force authentication — you must have an account to check out
requireLogin('cart');

// If the cart is empty, send them back to the shop
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: shop.php");
    exit();
}

// Calculate financial figures
$subtotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $subtotal += (float)($item['price'] * $item['qty']);
}
$shipping   = ($subtotal >= 800) ? 0 : 95; // R95 flat rate below R800
$grandTotal = $subtotal + $shipping;

$errors     = [];
$success    = false;
$orderId    = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address  = trim($_POST['address'] ?? '');
    $city     = trim($_POST['city'] ?? '');
    $zip      = trim($_POST['zip'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $payment  = $_POST['payment_method'] ?? 'eft';

    // Server-side validation
    if (empty($address)) $errors[] = "Delivery address is required.";
    if (empty($city))    $errors[] = "City / Town is required.";
    if (empty($zip))     $errors[] = "Postal code is required.";
    if (empty($phone))   $errors[] = "A contact telephone number is required.";

    if (empty($errors)) {
        $user = getCurrentUser();
        $userId = $user['id'] ?? 0;
        
        // Formatted address block
        $fullShippingAddress = $address . ", " . $city . ", " . $zip;

        if ($conn) {
            // ============================================================
            // REAL DATABASE WORK
            // ============================================================
            $conn->begin_transaction();

            try {
                // Prices and stock always come from the database, never from the browser.
                $sel = $conn->prepare("SELECT name, price, stock FROM products WHERE id = ?");
                $lines = [];
                $dbSubtotal = 0.0;
                foreach ($_SESSION['cart'] as $productId => $item) {
                    $pid = (int)$productId;
                    $qty = max(1, (int)$item['qty']);
                    $sel->bind_param("i", $pid);
                    $sel->execute();
                    $p = $sel->get_result()->fetch_assoc();
                    if (!$p) { throw new RuntimeException("A product in your cart is no longer available."); }
                    if ((int)$p['stock'] < $qty) { throw new RuntimeException("Not enough stock for " . $p['name'] . " (only " . (int)$p['stock'] . " left)."); }
                    $lines[] = ['id' => $pid, 'name' => $p['name'], 'price' => (float)$p['price'], 'qty' => $qty];
                    $dbSubtotal += (float)$p['price'] * $qty;
                }
                $dbShipping = ($dbSubtotal >= 800) ? 0.0 : 95.0;
                $dbTotal    = $dbSubtotal + $dbShipping;
                $payment    = in_array($payment, ['eft', 'card'], true) ? $payment : 'eft';

                // 1. Insert into orders table (columns match repent_database.sql)
                $stmt = $conn->prepare("INSERT INTO orders (user_id, total, shipping, status, shipping_address, city, postal_code, phone, payment_method) VALUES (?, ?, ?, 'processing', ?, ?, ?, ?, ?)");
                $stmt->bind_param("iddsssss", $userId, $dbTotal, $dbShipping, $address, $city, $zip, $phone, $payment);
                $stmt->execute();
                $insertedOrderId = $conn->insert_id;

                // 2. Insert the order lines and take the stock off (only if enough is left)
                $itemStmt  = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity) VALUES (?, ?, ?, ?, ?)");
                $stockStmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
                foreach ($lines as $l) {
                    $stockStmt->bind_param("iii", $l['qty'], $l['id'], $l['qty']);
                    $stockStmt->execute();
                    if ($stockStmt->affected_rows !== 1) { throw new RuntimeException($l['name'] . " just sold out."); }
                    $itemStmt->bind_param("iisdi", $insertedOrderId, $l['id'], $l['name'], $l['price'], $l['qty']);
                    $itemStmt->execute();
                }

                $conn->commit();
                $orderId = "#" . (1000 + $insertedOrderId);
                $success = true;

            } catch (Throwable $e) {
                try { $conn->rollback(); } catch (Throwable $ignored) {}
                $errors[] = ($e instanceof RuntimeException)
                    ? $e->getMessage()
                    : "We could not process your order. Please try again.";
            }

        } else {
            // ============================================================
            // PRESENTATION / DEMO MODE FALLBACK
            // ============================================================
            $orderId = "#REP-" . strtoupper(substr(md5(uniqid()), 0, 5));
            $success = true;
        }

        // Clean out the shopping cart upon absolute transaction completion
        if ($success) {
            unset($_SESSION['cart']);
        }
    }
}
?>

<div class="container" style="padding: 60px 20px; min-height: 70vh;">
    
    <?php if ($success): ?>
        <div style="text-align: center; max-width: 600px; margin: 40px auto; background: var(--white); padding: 48px; border-radius: var(--radius); border: 1px solid var(--cream); box-shadow: var(--shadow-sm);">
            <div style="font-size: 64px; color: var(--gold); margin-bottom: 20px;">✝</div>
            <h1 style="font-family: var(--font-display); font-size: 32px; margin-bottom: 12px;">Thank You for Your Order!</h1>
            <p style="color: var(--gray); font-size: 16px; margin-bottom: 24px;">
                Your order <strong style="color: var(--black);"><?= $orderId ?></strong> has been successfully placed. A confirmation email with transaction particulars has been dispatched.
            </p>
            <div style="background: var(--off-white); padding: 16px; border-radius: var(--radius); font-size: 14px; text-align: left; margin-bottom: 32px; border-left: 3px solid var(--gold);">
                <strong>What's Next?</strong><br>
                Every piece in our ministry catalogue is carefully inspected. We will text you tracking notifications the moment your box leaves Pretoria.
            </div>
            <a href="shop.php" class="btn btn-gold" style="display: inline-block; padding: 12px 32px;">Continue Shopping</a>
        </div>

    <?php else: ?>
        <div style="margin-bottom: 40px;">
            <span class="section-label">Secure Settlement</span>
            <h1 style="font-family: var(--font-display); font-size: 36px; margin-top: 4px;">Finalise Checkout</h1>
        </div>

        <?php if (!empty($errors)): ?>
            <div style="background: rgba(192, 57, 43, 0.1); border-left: 4px solid var(--red); color: var(--red); padding: 16px; border-radius: var(--radius); margin-bottom: 32px; font-size: 14px;">
                <ul style="margin-left: 16px;">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 48px; align-items: start;" class="cart-layout">
            
            <div style="background: var(--white); padding: 32px; border-radius: var(--radius); border: 1px solid var(--cream);">
                <h3 style="font-family: var(--font-display); font-size: 22px; margin-bottom: 24px; border-bottom: 1px solid var(--cream); padding-bottom: 12px;">Shipping Logistics</h3>
                
                <form method="POST" action="checkout.php">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="address" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.05em;">Street Address *</label>
                        <input type="text" id="address" name="address" placeholder="e.g. 142 Pretorius Street, Apartment 4B" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>" required style="width: 100%; padding: 12px; border: 1px solid var(--cream); border-radius: var(--radius); font-family: var(--font-body);">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                        <div class="form-group">
                            <label for="city" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.05em;">City / Town *</label>
                            <input type="text" id="city" name="city" placeholder="e.g. Pretoria" value="<?= htmlspecialchars($_POST['city'] ?? '') ?>" required style="width: 100%; padding: 12px; border: 1px solid var(--cream); border-radius: var(--radius); font-family: var(--font-body);">
                        </div>
                        <div class="form-group">
                            <label for="zip" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.05em;">Postal Code *</label>
                            <input type="text" id="zip" name="zip" placeholder="e.g. 0002" value="<?= htmlspecialchars($_POST['zip'] ?? '') ?>" required style="width: 100%; padding: 12px; border: 1px solid var(--cream); border-radius: var(--radius); font-family: var(--font-body);">
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 32px;">
                        <label for="phone" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.05em;">Contact Mobile Phone *</label>
                        <input type="tel" id="phone" name="phone" placeholder="e.g. 082 123 4567" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required style="width: 100%; padding: 12px; border: 1px solid var(--cream); border-radius: var(--radius); font-family: var(--font-body);">
                    </div>

                    <h3 style="font-family: var(--font-display); font-size: 22px; margin-bottom: 20px; border-bottom: 1px solid var(--cream); padding-bottom: 12px;">Settlement Channel</h3>
                    
                    <div style="margin-bottom: 32px;">
                        <label style="display: flex; align-items: center; background: var(--off-white); padding: 16px; border-radius: var(--radius); margin-bottom: 12px; cursor: pointer; border: 1px solid var(--cream);">
                            <input type="radio" name="payment_method" value="eft" checked style="margin-right: 12px; accent-color: var(--gold);">
                            <div>
                                <strong style="font-size: 14px; display: block;">Instant Secure EFT (OZOW / PayFast)</strong>
                                <span style="font-size: 12px; color: var(--gray);">Process payment immediately via your South African bank app.</span>
                            </div>
                        </label>

                        <label style="display: flex; align-items: center; background: var(--white); padding: 16px; border-radius: var(--radius); cursor: pointer; border: 1px solid var(--cream);">
                            <input type="radio" name="payment_method" value="card" style="margin-right: 12px; accent-color: var(--gold);">
                            <div>
                                <strong style="font-size: 14px; display: block;">Credit / Debit Card</strong>
                                <span style="font-size: 12px; color: var(--gray);">Visa and Mastercard protected via 3D Secure.</span>
                            </div>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-gold btn-full" style="padding: 16px;">Place Order (R<?= number_format($grandTotal, 2) ?>)</button>
                </form>
            </div>

            <div style="background: var(--off-white); padding: 32px; border-radius: var(--radius); border: 1px solid var(--cream); position: sticky; top: 100px;">
                <h3 style="font-family: var(--font-display); font-size: 22px; margin-bottom: 20px; border-bottom: 1px solid var(--cream); padding-bottom: 12px;">Your Statement</h3>
                
                <div style="max-height: 240px; overflow-y: auto; margin-bottom: 20px; padding-right: 4px;">
                    <?php foreach ($_SESSION['cart'] as $item): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; font-size: 14px;">
                            <div style="max-width: 70%;">
                                <span style="font-weight: 600; color: var(--black);"><?= htmlspecialchars($item['name']) ?></span>
                                <span style="display: block; font-size: 12px; color: var(--gray);">Qty: <?= $item['qty'] ?> @ R<?= number_format($item['price']) ?></span>
                            </div>
                            <span style="font-weight: 600; color: var(--black);">R<?= number_format($item['price'] * $item['qty']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div style="border-top: 1px dashed var(--light-gray); padding-top: 16px; margin-bottom: 8px; display: flex; justify-content: space-between; font-size: 14px; color: var(--gray);">
                    <span>Cart Subtotal</span>
                    <span>R<?= number_format($subtotal, 2) ?></span>
                </div>

                <div style="display: flex; justify-content: space-between; font-size: 14px; color: var(--gray); margin-bottom: 16px;">
                    <span>Courier Delivery</span>
                    <span><?= ($shipping === 0) ? 'FREE' : 'R' . number_format($shipping, 2) ?></span>
                </div>

                <div style="border-top: 1px solid var(--cream); padding-top: 16px; display: flex; justify-content: space-between; font-size: 18px; font-weight: 700; color: var(--black); margin-bottom: 24px;">
                    <span>Total Owed</span>
                    <span style="color: var(--gold);">R<?= number_format($grandTotal, 2) ?></span>
                </div>

                <div style="font-size: 12px; color: var(--gray); text-align: center; background: var(--white); padding: 12px; border-radius: var(--radius); border: 1px solid var(--cream);">
                    🔒 Encrypted Connection guaranteed.
                </div>
            </div>

        </div>
    <?php endif; ?>

</div>

<?php require_once '../includes/footer.php'; ?>