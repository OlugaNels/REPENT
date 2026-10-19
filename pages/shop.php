<?php
/**
 * ============================================================
 * REPENT† — Shop / Product Catalogue Page
 * File: pages/shop.php
 *
 * PURPOSE: Displays all products with category filters.
 * - Reads the ?category= URL parameter to filter products.
 * - In production, products come from the database.
 * - In demo mode, a static array of products is used.
 * - Add to Cart submits to cart.php via POST.
 * ============================================================
 */

require_once '../includes/header.php';
require_once '../includes/db.php';

// Read the category filter from the URL (default = 'all')
$category = $_GET['category'] ?? 'all';

// ---------- Product Data (Static for Demo) ----------
// In production these would be fetched with:
// SELECT * FROM products WHERE category = '$category'
$allProducts = [
    ['id'=>1,  'name'=>'Disciples Tee',          'price'=>350, 'category'=>'tshirts', 'image'=>'tshirt-white.jpg',       'desc'=>'Go and Make Disciples of All Nations — Matthew 28:19.',  'badge'=>'New'],
    ['id'=>2,  'name'=>'Be Still Tee (Pink)',     'price'=>320, 'category'=>'tshirts', 'image'=>'products-collection.jpg','desc'=>'Be Still and Know That I Am God — Psalm 46:10.',          'badge'=>'Popular'],
    ['id'=>3,  'name'=>'Be Still Tee (Blue)',     'price'=>320, 'category'=>'tshirts', 'image'=>'products-flat.jpg',      'desc'=>'Be Still and Know That I Am God — Psalm 46:10.',          'badge'=>''],
    ['id'=>4,  'name'=>'REPENT† Classic Tee',     'price'=>320, 'category'=>'tshirts', 'image'=>'people-wearing.jpg',     'desc'=>'REPENT† classic tee — bold and clean statement piece.',   'badge'=>''],
    ['id'=>5,  'name'=>'REPENT† Hoodie (Red)',    'price'=>550, 'category'=>'hoodies', 'image'=>'hoodies-collection.jpg', 'desc'=>'Heavyweight hoodie with Go and Make Disciples print.',    'badge'=>'Bestseller'],
    ['id'=>6,  'name'=>'REPENT† Hoodie (Pink)',   'price'=>550, 'category'=>'hoodies', 'image'=>'hoodies-collection.jpg', 'desc'=>'Soft pink hoodie with REPENT† logo on chest.',            'badge'=>''],
    ['id'=>7,  'name'=>'REPENT† Crewneck',        'price'=>480, 'category'=>'hoodies', 'image'=>'hoodies-collection.jpg', 'desc'=>'Red crewneck sweatshirt in Blue, Red & Beige.',           'badge'=>''],
    ['id'=>8,  'name'=>'Cap — Coral',             'price'=>280, 'category'=>'caps',    'image'=>'cap-coral.jpg',          'desc'=>'Washed coral dad cap with embroidered REPENT† logo.',     'badge'=>''],
    ['id'=>9,  'name'=>'Cap — Olive',             'price'=>280, 'category'=>'caps',    'image'=>'cap-olive.jpg',          'desc'=>'Washed olive/khaki dad cap.',                              'badge'=>''],
    ['id'=>10, 'name'=>'Cap — Yellow',            'price'=>280, 'category'=>'caps',    'image'=>'cap-yellow.jpg',         'desc'=>'Vintage yellow washed dad cap.',                           'badge'=>''],
    ['id'=>11, 'name'=>'Cap — Mint',              'price'=>280, 'category'=>'caps',    'image'=>'cap-mint.jpg',           'desc'=>'Fresh mint/teal washed dad cap.',                          'badge'=>'New'],
    ['id'=>12, 'name'=>'Cap — Khaki',             'price'=>280, 'category'=>'caps',    'image'=>'cap-khaki.jpg',          'desc'=>'Stone washed khaki dad cap.',                              'badge'=>''],
];

// Filter products by category
if ($category === 'all') {
    $products = $allProducts;
} else {
    $products = array_filter($allProducts, fn($p) => $p['category'] === $category);
}
?>

<!-- ===== PAGE HERO ===== -->
<div class="page-hero">
    <img src="../assets/images/logo.png" alt="REPENT†" class="page-hero-logo">
    <h1 class="page-hero-title">Shop</h1>
    <p class="page-hero-sub">Faith-inspired clothing for everyday wear</p>
</div>

<!-- Flash message after adding to cart -->
<?php if (isset($_SESSION['cart_message'])): ?>
    <div class="container" style="padding-top:16px;">
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['cart_message']) ?></div>
    </div>
    <?php unset($_SESSION['cart_message']); // Clear the message after displaying ?>
<?php endif; ?>

<!-- ===== SHOP LAYOUT: Sidebar + Product Grid ===== -->
<div class="shop-layout">

    <!-- LEFT: Filter Sidebar -->
    <aside class="shop-sidebar">

        <!-- Category Filter -->
        <div class="filter-group">
            <h4>Category</h4>
            <?php
            $categories = [
                'all'     => 'All Products',
                'tshirts' => 'T-Shirts',
                'hoodies' => 'Hoodies & Crewnecks',
                'caps'    => 'Caps',
            ];
            foreach ($categories as $val => $label): ?>
                <label class="filter-option">
                    <input type="radio" name="cat" value="<?= $val ?>"
                           <?= $category === $val ? 'checked' : '' ?>
                           onchange="window.location.href='shop.php?category='+this.value">
                    <?= $label ?>
                </label>
            <?php endforeach; ?>
        </div>

        <!-- Colour Swatches (visual filter — can be wired to DB) -->
        <div class="filter-group">
            <h4>Colour</h4>
            <div class="color-swatches">
                <div class="color-dot" style="background:#fff;border:1px solid #ccc;" title="White"></div>
                <div class="color-dot" style="background:#111;" title="Black"></div>
                <div class="color-dot" style="background:#e0829d;" title="Pink"></div>
                <div class="color-dot" style="background:#7ab0d4;" title="Blue"></div>
                <div class="color-dot" style="background:#e07b5e;" title="Coral"></div>
                <div class="color-dot" style="background:#d4b896;" title="Beige"></div>
                <div class="color-dot" style="background:#8b9467;" title="Olive"></div>
                <div class="color-dot" style="background:#d4a922;" title="Yellow"></div>
                <div class="color-dot" style="background:#5dd3b0;" title="Mint"></div>
                <div class="color-dot" style="background:#e53935;" title="Red"></div>
            </div>
        </div>

        <!-- Price Range Filter -->
        <div class="filter-group">
            <h4>Price Range</h4>
            <label class="filter-option"><input type="checkbox"> Under R300</label>
            <label class="filter-option"><input type="checkbox"> R300 – R400</label>
            <label class="filter-option"><input type="checkbox"> R400 – R600</label>
        </div>

        <!-- Size Filter -->
        <div class="filter-group">
            <h4>Size</h4>
            <?php foreach (['XS','S','M','L','XL','XXL'] as $sz): ?>
                <label class="filter-option">
                    <input type="checkbox"> <?= $sz ?>
                </label>
            <?php endforeach; ?>
        </div>

    </aside><!-- end sidebar -->

    <!-- RIGHT: Product Grid -->
    <main>

        <!-- Results count + sort bar -->
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:12px;">
            <p style="font-size:14px;color:var(--gray);">
                Showing <?= count($products) ?> product<?= count($products) !== 1 ? 's' : '' ?>
            </p>
            <select style="padding:8px 14px;border:1.5px solid var(--cream);border-radius:4px;font-size:13px;">
                <option>Sort: Featured</option>
                <option>Price: Low to High</option>
                <option>Price: High to Low</option>
                <option>Newest First</option>
            </select>
        </div>

        <?php if (empty($products)): ?>
        <!-- Empty state if no products match the filter -->
        <div class="empty-state">
            <div class="empty-state-icon">🛍️</div>
            <h3>No products found</h3>
            <p><a href="shop.php" style="color:var(--gold);">View all products</a></p>
        </div>

        <?php else: ?>
        <!-- Product Cards Grid -->
        <div class="products-grid">
            <?php foreach ($products as $p): ?>

            <div class="product-card">

                <!-- Product Image with optional badge -->
                <div class="product-card-image">
                    <img src="../assets/images/<?= htmlspecialchars($p['image']) ?>"
                         alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
                    <?php if (!empty($p['badge'])): ?>
                        <span class="product-badge"><?= htmlspecialchars($p['badge']) ?></span>
                    <?php endif; ?>
                </div>

                <!-- Product Details -->
                <div class="product-card-body">
                    <h3 class="product-card-name"><?= htmlspecialchars($p['name']) ?></h3>
                    <p class="product-card-desc"><?= htmlspecialchars($p['desc']) ?></p>

                    <!-- Size selector (S / M / L / XL) -->
                    <div class="size-group" style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:14px;">
                        <?php foreach (['S','M','L','XL'] as $sz): ?>
                            <button type="button" class="size-btn"
                                    style="padding:4px 10px;border:1.5px solid #ddd;background:none;font-size:12px;cursor:pointer;border-radius:3px;transition:all 0.2s;">
                                <?= $sz ?>
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <!-- Price + Add to Cart -->
                    <div class="product-card-footer">
                        <div class="product-price">R<?= number_format($p['price']) ?></div>

                        <!-- Add to Cart form — posts to cart.php -->
                        <form method="POST" action="cart.php" style="margin:0;">
                            <input type="hidden" name="action"     value="add">
                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                            <input type="hidden" name="name"       value="<?= htmlspecialchars($p['name']) ?>">
                            <input type="hidden" name="price"      value="<?= $p['price'] ?>">
                            <input type="hidden" name="image"      value="<?= htmlspecialchars($p['image']) ?>">
                            <button type="submit" class="btn btn-primary btn-sm add-to-cart-btn">
                                Add to Cart
                            </button>
                        </form>
                    </div>
                </div>

            </div><!-- end .product-card -->
            <?php endforeach; ?>
        </div><!-- end .products-grid -->
        <?php endif; ?>

    </main><!-- end product main -->
</div><!-- end .shop-layout -->

<!-- Active size button style -->
<style>
.size-btn.active, .size-btn:hover {
    background: var(--black) !important;
    color: var(--white);
    border-color: var(--black) !important;
}
</style>

<?php require_once '../includes/footer.php'; ?>
