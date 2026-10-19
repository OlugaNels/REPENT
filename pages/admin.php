<?php
/**
 * ============================================================
 * REPENT† — Admin Dashboard
 * File: pages/admin.php
 *
 * PURPOSE: Provides the admin user with an overview of:
 *  - Total orders, users, products, and prayer requests
 *  - Recent orders table
 *  - Product management (add/edit/delete)
 *  - Prayer requests inbox
 *  - Newsletter subscribers list
 *
 * ACCESS: Admin only. requireAdmin() redirects non-admins.
 * ============================================================
 */

require_once '../includes/header.php';
require_once '../includes/db.php';

// Protect this page — only admins can access it
requireAdmin();

// ---------- Fetch stats from DB (or use demo data) ----------

// Demo statistics used when no database is connected
$stats = [
    'orders'    => 24,
    'users'     => 38,
    'products'  => 12,
    'prayers'   => 15,
];

// Demo recent orders
$recentOrders = [
    ['id'=>'#1024','customer'=>'Thandeka M.','total'=>'R870','status'=>'Delivered', 'date'=>'2025-05-12'],
    ['id'=>'#1023','customer'=>'Sipho K.',   'total'=>'R550','status'=>'Shipped',   'date'=>'2025-05-11'],
    ['id'=>'#1022','customer'=>'Grace N.',   'total'=>'R640','status'=>'Processing','date'=>'2025-05-10'],
    ['id'=>'#1021','customer'=>'Thabo M.',   'total'=>'R280','status'=>'Delivered', 'date'=>'2025-05-09'],
    ['id'=>'#1020','customer'=>'Lindiwe D.', 'total'=>'R1100','status'=>'Delivered','date'=>'2025-05-08'],
];

// Demo prayer requests
$prayers = [
    ['name'=>'Anonymous','subject'=>'Health & Healing', 'date'=>'2025-05-13','private'=>true],
    ['name'=>'Thandeka', 'subject'=>'Family',           'date'=>'2025-05-12','private'=>false],
    ['name'=>'Sipho',    'subject'=>'Financial',        'date'=>'2025-05-11','private'=>false],
];

// Demo newsletter subscribers
$subscribers = [
    ['name'=>'Grace N.',   'email'=>'grace@example.com',   'frequency'=>'Weekly', 'date'=>'2025-05-10'],
    ['name'=>'Thabo M.',   'email'=>'thabo@example.com',   'frequency'=>'Daily',  'date'=>'2025-05-09'],
    ['name'=>'Lindiwe D.', 'email'=>'lindiwe@example.com', 'frequency'=>'Weekly', 'date'=>'2025-05-08'],
];

// Current active tab (orders / products / prayers / subscribers)
$activeTab = $_GET['tab'] ?? 'orders';
?>

<!-- ===== ADMIN DASHBOARD LAYOUT ===== -->
<div class="admin-layout">

    <!-- LEFT: Admin Sidebar Navigation -->
    <aside class="admin-sidebar">
        <div style="padding:0 24px 20px;border-bottom:1px solid var(--border);margin-bottom:8px;">
            <img src="../assets/images/logo.png" alt="REPENT†"
                 style="height:30px;width:auto;filter:invert(1) brightness(2);display:block;">
        </div>
        <!-- Brand logo in admin sidebar -->
        <div style="padding:20px 24px 12px;">
            <?php $logoColor = "white"; $logoHeight = 24; include "../includes/logo.php"; ?>
        </div>
        <div class="admin-sidebar-title">Admin Panel</div>

        <a href="admin.php?tab=orders"
           class="admin-nav-link <?= $activeTab === 'orders' ? 'active' : '' ?>">
            <!-- Orders icon -->
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
                <rect x="9" y="3" width="6" height="4" rx="1"/>
            </svg>
            Orders
        </a>

        <a href="admin.php?tab=products"
           class="admin-nav-link <?= $activeTab === 'products' ? 'active' : '' ?>">
            <!-- Products icon -->
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                <line x1="3" y1="6" x2="21" y2="6"/>
                <path d="M16 10a4 4 0 01-8 0"/>
            </svg>
            Products
        </a>

        <a href="admin.php?tab=prayers"
           class="admin-nav-link <?= $activeTab === 'prayers' ? 'active' : '' ?>">
            <!-- Prayer icon -->
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
            </svg>
            Prayer Requests
        </a>

        <a href="admin.php?tab=subscribers"
           class="admin-nav-link <?= $activeTab === 'subscribers' ? 'active' : '' ?>">
            <!-- Subscribers icon -->
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                <polyline points="22,6 12,13 2,6"/>
            </svg>
            Subscribers
        </a>

        <!-- Divider -->
        <div style="border-top:1px solid var(--border);margin:20px 0;"></div>

        <a href="../index.php" class="admin-nav-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
            </svg>
            Back to Site
        </a>
        <a href="logout.php" class="admin-nav-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/>
            </svg>
            Logout
        </a>
    </aside>

    <!-- RIGHT: Main Content Area -->
    <main class="admin-main">

        <!-- Dashboard Header -->
        <div class="admin-header">
            <h1>Dashboard</h1>
            <p>Welcome back, <?= htmlspecialchars(explode(' ', $user['name'])[0]) ?>. Here's what's happening with REPENT†.</p>
        </div>

        <!-- ===== STATISTICS CARDS ===== -->
        <div class="stats-grid">

            <!-- Total Orders -->
            <div class="stat-card">
                <div class="stat-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
                        <rect x="9" y="3" width="6" height="4" rx="1"/>
                    </svg>
                </div>
                <div class="stat-label">Total Orders</div>
                <div class="stat-value"><?= $stats['orders'] ?></div>
                <div class="stat-sub">+3 this week</div>
            </div>

            <!-- Registered Users -->
            <div class="stat-card">
                <div class="stat-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                    </svg>
                </div>
                <div class="stat-label">Registered Users</div>
                <div class="stat-value"><?= $stats['users'] ?></div>
                <div class="stat-sub">+5 this month</div>
            </div>

            <!-- Products in Store -->
            <div class="stat-card">
                <div class="stat-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                        <line x1="3" y1="6" x2="21" y2="6"/>
                    </svg>
                </div>
                <div class="stat-label">Products</div>
                <div class="stat-value"><?= $stats['products'] ?></div>
                <div class="stat-sub">Across 3 categories</div>
            </div>

            <!-- Prayer Requests -->
            <div class="stat-card">
                <div class="stat-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
                    </svg>
                </div>
                <div class="stat-label">Prayer Requests</div>
                <div class="stat-value"><?= $stats['prayers'] ?></div>
                <div class="stat-sub">Received this month</div>
            </div>

        </div><!-- end .stats-grid -->

        <!-- ===== TAB CONTENT ===== -->

        <!-- TAB: Orders -->
        <?php if ($activeTab === 'orders'): ?>
        <h2 style="font-family:var(--font-display);font-size:24px;margin-bottom:20px;">Recent Orders</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentOrders as $order): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($order['id']) ?></strong></td>
                    <td><?= htmlspecialchars($order['customer']) ?></td>
                    <td><?= htmlspecialchars($order['total']) ?></td>
                    <td>
                        <?php
                        // Colour-code status badges
                        $badgeClass = match($order['status']) {
                            'Delivered'  => 'badge-success',
                            'Shipped'    => 'badge-info',
                            'Processing' => 'badge-warning',
                            default      => 'badge-gray',
                        };
                        ?>
                        <span class="badge <?= $badgeClass ?>"><?= $order['status'] ?></span>
                    </td>
                    <td><?= $order['date'] ?></td>
                    <td><a href="#" style="color:var(--gold);font-size:13px;">View</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- TAB: Products -->
        <?php elseif ($activeTab === 'products'): ?>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <h2 style="font-family:var(--font-display);font-size:24px;">Products</h2>
            <a href="#" class="btn btn-gold btn-sm">+ Add Product</a>
        </div>
        <table class="admin-table">
            <thead>
                <tr><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php
                // Display demo product list
                $adminProducts = [
                    ['Disciples Tee',        'T-Shirts', 'R350', 'In Stock'],
                    ['Be Still Tee (Pink)',   'T-Shirts', 'R320', 'In Stock'],
                    ['REPENT† Hoodie (Red)',  'Hoodies',  'R550', 'Low Stock'],
                    ['REPENT† Crewneck',      'Hoodies',  'R480', 'In Stock'],
                    ['Cap — Coral',           'Caps',     'R280', 'In Stock'],
                    ['Cap — Mint',            'Caps',     'R280', 'In Stock'],
                ];
                foreach ($adminProducts as $prod): ?>
                <tr>
                    <td><strong><?= $prod[0] ?></strong></td>
                    <td><?= $prod[1] ?></td>
                    <td><?= $prod[2] ?></td>
                    <td>
                        <span class="badge <?= $prod[3] === 'Low Stock' ? 'badge-warning' : 'badge-success' ?>">
                            <?= $prod[3] ?>
                        </span>
                    </td>
                    <td>
                        <a href="#" style="color:var(--gold);font-size:13px;margin-right:12px;">Edit</a>
                        <a href="#" style="color:var(--red);font-size:13px;" onclick="return confirm('Delete this product?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- TAB: Prayer Requests -->
        <?php elseif ($activeTab === 'prayers'): ?>
        <h2 style="font-family:var(--font-display);font-size:24px;margin-bottom:20px;">Prayer Requests</h2>
        <table class="admin-table">
            <thead>
                <tr><th>Name</th><th>Topic</th><th>Date</th><th>Visibility</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php foreach ($prayers as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['name']) ?></td>
                    <td><?= htmlspecialchars($p['subject']) ?></td>
                    <td><?= $p['date'] ?></td>
                    <td>
                        <span class="badge <?= $p['private'] ? 'badge-gray' : 'badge-info' ?>">
                            <?= $p['private'] ? 'Private' : 'Public' ?>
                        </span>
                    </td>
                    <td><a href="#" style="color:var(--gold);font-size:13px;">View</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- TAB: Newsletter Subscribers -->
        <?php elseif ($activeTab === 'subscribers'): ?>
        <h2 style="font-family:var(--font-display);font-size:24px;margin-bottom:20px;">Newsletter Subscribers</h2>
        <table class="admin-table">
            <thead>
                <tr><th>Name</th><th>Email</th><th>Frequency</th><th>Subscribed</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php foreach ($subscribers as $sub): ?>
                <tr>
                    <td><?= htmlspecialchars($sub['name']) ?></td>
                    <td><?= htmlspecialchars($sub['email']) ?></td>
                    <td><?= $sub['frequency'] ?></td>
                    <td><?= $sub['date'] ?></td>
                    <td>
                        <a href="#" style="color:var(--red);font-size:13px;"
                           onclick="return confirm('Unsubscribe this user?')">Remove</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

    </main><!-- end admin-main -->
</div><!-- end admin-layout -->

<?php require_once '../includes/footer.php'; ?>
