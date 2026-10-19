<?php
/**
 * ============================================================
 * REPENT† — Shopping Cart Page
 * File: pages/cart.php
 *
 * PURPOSE: Displays the user's shopping cart and handles:
 *  - Adding items (POST from shop.php)
 *  - Updating quantities
 *  - Removing items
 *  - Displaying order subtotal, shipping, and total
 *
 * The cart is stored in the PHP SESSION as an associative
 * array keyed by product ID.
 * ============================================================
 */

require_once '../includes/header.php';
require_once '../includes/db.php';

// ---------- Handle cart actions from POST ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ----- ADD item to cart -----
    if ($action === 'add') {
        $id    = (int)($_POST['product_id'] ?? 0);
        $name  = htmlspecialchars($_POST['name']  ?? 'Product');
        $price = (float)($_POST['price'] ?? 0);
        $image = htmlspecialchars($_POST['image'] ?? '');

        // Initialise cart if it doesn't exist
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

        // If product already in cart, increase quantity; otherwise add new entry
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['qty']++;
        } else {
            $_SESSION['cart'][$id] = [
                'id'    => $id,
                'name'  => $name,
                'price' => $price,
                'image' => $image,
                'qty'   => 1,
            ];
        }

        // Flash message shown on the shop page after redirect
        $_SESSION['cart_message'] = "✓ " . $name . " added to your cart!";
        header("Location: ../pages/shop.php");
        exit();
    }

    // ----- REMOVE item from cart -----
    if ($action === 'remove') {
        $id = (int)($_POST['product_id'] ?? 0);
        unset($_SESSION['cart'][$id]);
        header("Location: cart.php");
        exit();
    }

    // ----- UPDATE item quantity -----
    if ($action === 'update') {
        $id  = (int)($_POST['product_id'] ?? 0);
        $qty = (int)($_POST['qty']        ?? 1);

        if ($qty < 1) {
            // Remove item if quantity goes below 1
            unset($_SESSION['cart'][$id]);
        } else {
            $_SESSION['cart'][$id]['qty'] = $qty;
        }
        header("Location: cart.php");
        exit();
    }
}

// ---------- Calculate totals ----------
$cart      = $_SESSION['cart'] ?? [];
$subtotal  = 0;
foreach ($cart as $item) {
    $subtotal += $item['price'] * $item['qty'];
}

// Free shipping on orders over R800
$shipping   = ($subtotal >= 800) ? 0 : 80;
$grandTotal = $subtotal + $shipping;
?>

<!-- Page Title Banner -->
<div class="page-hero">
    <h1 class="page-hero-title">Shopping Cart</h1>
    <p class="page-hero-sub">Review your selected items before checkout</p>
</div>

<div class="cart-page">

    <?php if (empty($cart)): ?>
    <!-- ===== EMPTY CART STATE ===== -->
    <div class="empty-state">
        <div class="empty-state-icon">🛍️</div>
        <h3>Your cart is empty</h3>
        <p style="color:var(--gray);margin-bottom:24px;">You haven't added any items yet.</p>
        <a href="shop.php" class="btn btn-primary">Browse Products</a>
    </div>

    <?php else: ?>
    <!-- ===== CART WITH ITEMS ===== -->
    <div class="cart-layout container">

        <!-- LEFT: Cart Items Table -->
        <div>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Remove</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart as $id => $item):
                        $lineTotal = $item['price'] * $item['qty'];
                    ?>
                    <tr data-item-id="<?= $id ?>">

                        <!-- Product image + name -->
                        <td>
                            <div style="display:flex;align-items:center;gap:16px;">
                                <img class="cart-item-img"
                                     src="../assets/images/<?= htmlspecialchars($item['image']) ?>"
                                     alt="<?= htmlspecialchars($item['name']) ?>">
                                <div>
                                    <div class="cart-item-name"><?= htmlspecialchars($item['name']) ?></div>
                                    <div class="cart-item-variant">REPENT† Collection</div>
                                </div>
                            </div>
                        </td>

                        <!-- Unit price -->
                        <td>R<?= number_format($item['price']) ?></td>

                        <!-- Quantity update form (– / + buttons submit to same page) -->
                        <td>
                            <div style="display:flex;align-items:center;gap:4px;">
                                <!-- Decrease quantity -->
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action"     value="update">
                                    <input type="hidden" name="product_id" value="<?= $id ?>">
                                    <input type="hidden" name="qty"        value="<?= $item['qty'] - 1 ?>">
                                    <button type="submit" class="qty-btn">−</button>
                                </form>

                                <span class="qty-value"><?= $item['qty'] ?></span>

                                <!-- Increase quantity -->
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action"     value="update">
                                    <input type="hidden" name="product_id" value="<?= $id ?>">
                                    <input type="hidden" name="qty"        value="<?= $item['qty'] + 1 ?>">
                                    <button type="submit" class="qty-btn">+</button>
                                </form>
                            </div>
                        </td>

                        <!-- Line total -->
                        <td data-subtotal="<?= $id ?>">R<?= number_format($lineTotal) ?></td>

                        <!-- Remove item -->
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action"     value="remove">
                                <input type="hidden" name="product_id" value="<?= $id ?>">
                                <button type="submit" class="remove-btn" title="Remove item">✕</button>
                            </form>
                        </td>

                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Continue Shopping link -->
            <div style="margin-top:24px;">
                <a href="shop.php" class="btn btn-outline btn-sm">← Continue Shopping</a>
            </div>
        </div>

        <!-- RIGHT: Order Summary Sidebar -->
        <div class="cart-summary">
            <h3>Order Summary</h3>

            <!-- Subtotal row -->
            <div class="summary-line">
                <span>Subtotal</span>
                <span>R<?= number_format($subtotal) ?></span>
            </div>

            <!-- Shipping row -->
            <div class="summary-line">
                <span>Shipping</span>
                <span><?= $shipping === 0 ? '<strong style="color:#2e7d32;">FREE</strong>' : 'R' . number_format($shipping) ?></span>
            </div>

            <!-- Free shipping progress hint -->
            <?php if ($shipping > 0): ?>
                <p style="font-size:12px;color:var(--gray);margin-bottom:8px;">
                    Add R<?= number_format(800 - $subtotal) ?> more for free shipping
                </p>
            <?php endif; ?>

            <!-- Grand Total -->
            <div class="summary-total">
                <span>Total</span>
                <span class="cart-grand-total">R<?= number_format($grandTotal) ?></span>
            </div>

            <!-- Checkout button — requires login -->
            <?php if (isLoggedIn()): ?>
                <a href="checkout.php" class="btn btn-gold btn-full">Proceed to Checkout</a>
            <?php else: ?>
                <a href="login.php?redirect=cart" class="btn btn-gold btn-full">Login to Checkout</a>
                <a href="register.php" class="btn btn-outline btn-full" style="margin-top:10px;">Create Account</a>
                <p style="font-size:12px;color:var(--gray);text-align:center;margin-top:8px;">
                    You need an account to complete your purchase.
                </p>
            <?php endif; ?>

            <!-- Security note -->
            <p style="font-size:12px;color:var(--light-gray);text-align:center;margin-top:16px;">
                🔒 Secure checkout &nbsp;|&nbsp; SSL encrypted
            </p>
        </div>

    </div><!-- end .cart-layout -->
    <?php endif; ?>

</div><!-- end .cart-page -->

<?php require_once '../includes/footer.php'; ?>
