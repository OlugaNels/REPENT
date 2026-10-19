<?php
/**
 * ============================================================
 * REPENT† — Global Header / Navigation
 * File: includes/header.php
 *
 * PURPOSE: Included at the top of EVERY page.
 * Starts the session, loads auth helpers, outputs the
 * HTML <head>, announcement bar, and main navigation bar.
 * ============================================================
 */

require_once __DIR__ . '/auth.php';

$user      = getCurrentUser();
$cartCount = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cartCount += (int)($item['qty'] ?? 1);
    }
}

// Build correct relative base path depending on whether
// we are in the root folder or inside /pages/
$inPages  = strpos($_SERVER['PHP_SELF'], '/pages/') !== false;
$basePath = $inPages ? '../' : '';
$activePage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REPENT† — Faith-Based Clothing &amp; Spiritual Growth</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;0,700;1,300;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $basePath ?>assets/css/style.css">
</head>
<body>

<!-- ANNOUNCEMENT BAR -->
<div class="announcement-bar">
    <p>✝ Free shipping on orders over R800 &nbsp;|&nbsp; New collection now available ✝</p>
</div>

<!-- MAIN NAVIGATION -->
<nav class="navbar" id="navbar">
    <div class="nav-container">

        <!-- Brand Logo — SVG inline cross-T logo -->
        <a href="<?= $basePath ?>index.php" class="nav-logo">
            <?php $logoColor = 'black'; $logoHeight = 28; include __DIR__ . '/logo.php'; ?>
        </a>

        <!-- Navigation Links -->
        <ul class="nav-links" id="navLinks">
            <li><a href="<?= $basePath ?>index.php"            class="<?= $activePage==='index.php'      ?'active':'' ?>">Home</a></li>
            <li><a href="<?= $basePath ?>pages/shop.php"       class="<?= $activePage==='shop.php'       ?'active':'' ?>">Shop</a></li>
            <li><a href="<?= $basePath ?>pages/devotional.php" class="<?= $activePage==='devotional.php' ?'active':'' ?>">Devotional</a></li>
            <li><a href="<?= $basePath ?>pages/prayer.php"     class="<?= $activePage==='prayer.php'     ?'active':'' ?>">Prayer</a></li>
            <li><a href="<?= $basePath ?>pages/newsletter.php" class="<?= $activePage==='newsletter.php' ?'active':'' ?>">Newsletter</a></li>
            <?php if ($user): ?>
                <?php if (isAdmin()): ?>
                <li><a href="<?= $basePath ?>pages/admin.php"  class="<?= $activePage==='admin.php'      ?'active':'' ?>">Admin</a></li>
                <?php endif; ?>
                <li><a href="<?= $basePath ?>pages/logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="<?= $basePath ?>pages/login.php"    class="<?= $activePage==='login.php'    ?'active':'' ?>">Login</a></li>
                <li><a href="<?= $basePath ?>pages/register.php" class="<?= $activePage==='register.php' ?'active':'' ?>">Register</a></li>
            <?php endif; ?>
        </ul>

        <!-- Right-side icons -->
        <div class="nav-icons">
            <?php if ($user): ?>
                <span class="nav-user">
                    Hi, <?= htmlspecialchars(explode(' ', $user['name'])[0]) ?>
                    <?= isAdmin() ? '<span class="admin-badge">Admin</span>' : '' ?>
                </span>
            <?php endif; ?>

            <!-- Cart icon with item count badge -->
            <a href="<?= $basePath ?>pages/cart.php" class="cart-icon" title="Cart">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <path d="M16 10a4 4 0 01-8 0"/>
                </svg>
                <?php if ($cartCount > 0): ?>
                    <span class="cart-badge"><?= $cartCount ?></span>
                <?php endif; ?>
            </a>

            <!-- Hamburger for mobile -->
            <button class="hamburger" id="hamburger" aria-label="Toggle Menu">
                <span></span><span></span><span></span>
            </button>
        </div>

    </div>
</nav>
