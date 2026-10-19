<?php
/**
 * ============================================================
 * REPENT† — Homepage
 * File: index.php
 *
 * PURPOSE: The main landing page of the REPENT† website.
 * Displays the hero section, featured products, category
 * browsing, daily devotionals preview, testimonials,
 * and a newsletter call-to-action.
 * ============================================================
 */

require_once 'includes/header.php';
?>

<!-- ===== HERO SECTION ===== -->
<section class="hero">

    <!-- Left: Text Content -->
    <div class="hero-content">
        <div class="hero-eyebrow">Faith-Based Clothing &amp; Spiritual Growth</div>
        <h1 class="hero-title">REPEN<span class="cross">T</span>†</h1>
        <p class="hero-verse">
            "Do not conform to the pattern of this world,<br>
            but be transformed by the renewing of your mind."
        </p>
        <p class="hero-desc">
            Wear clothing that carries a message. Every piece we create is designed to
            reflect biblical values, spark conversations, and honour God in everyday life.
        </p>
        <div class="hero-btns">
            <a href="pages/shop.php" class="btn btn-gold">Shop the Collection</a>
            <a href="pages/devotional.php" class="btn btn-outline-light">Daily Devotional</a>
        </div>
    </div>

    <!-- Right: Brand Logo Panel — clean CSS logo display -->
    <div class="hero-logo-panel">
        <div class="hero-logo-display">
            <?php $logoColor = "black"; $logoHeight = 80; include "includes/logo.php"; ?>
            <p class="hero-logo-sub">Faith-Based Clothing</p>
        </div>
    </div>
    </div>

</section>

<!-- ===== FEATURED PRODUCTS ===== -->
<section class="section" style="background:var(--off-white);">
    <div class="container">

        <div class="section-header">
            <span class="section-label">New Arrivals</span>
            <h2 class="section-title">Featured Collection</h2>
            <p class="section-subtitle">
                Clothing that speaks faith louder than words. Each piece carries a message worth wearing.
            </p>
        </div>

        <!-- Products grid — 4 featured items -->
        <div class="products-grid">

            <!-- Product: Disciples Tee -->
            <div class="product-card">
                <div class="product-card-image">
                    <img src="assets/images/tshirt-white.jpg" alt="Disciples Tee" loading="lazy">
                    <span class="product-badge">New</span>
                </div>
                <div class="product-card-body">
                    <h3 class="product-card-name">Disciples Tee</h3>
                    <p class="product-card-desc">Go and Make Disciples of All Nations — Matthew 28:19.</p>
                    <div class="product-card-footer">
                        <div class="product-price">R350 <span>/ tee</span></div>
                        <a href="pages/shop.php" class="btn btn-primary btn-sm">View</a>
                    </div>
                </div>
            </div>

            <!-- Product: Be Still Tee -->
            <div class="product-card">
                <div class="product-card-image">
                    <img src="assets/images/products-collection.jpg" alt="Be Still Tee" loading="lazy">
                    <span class="product-badge">Popular</span>
                </div>
                <div class="product-card-body">
                    <h3 class="product-card-name">Be Still Tee</h3>
                    <p class="product-card-desc">Be Still and Know That I Am God — Psalm 46:10. Multiple colours.</p>
                    <div class="product-card-footer">
                        <div class="product-price">R320 <span>/ tee</span></div>
                        <a href="pages/shop.php" class="btn btn-primary btn-sm">View</a>
                    </div>
                </div>
            </div>

            <!-- Product: Hoodie -->
            <div class="product-card">
                <div class="product-card-image">
                    <img src="assets/images/hoodies-collection.jpg" alt="REPENT Hoodie" loading="lazy">
                    <span class="product-badge">Bestseller</span>
                </div>
                <div class="product-card-body">
                    <h3 class="product-card-name">REPENT† Hoodie</h3>
                    <p class="product-card-desc">Classic REPENT† hoodie in Red, Pink, and Charcoal.</p>
                    <div class="product-card-footer">
                        <div class="product-price">R550 <span>/ hoodie</span></div>
                        <a href="pages/shop.php" class="btn btn-primary btn-sm">View</a>
                    </div>
                </div>
            </div>

            <!-- Product: Cap -->
            <div class="product-card">
                <div class="product-card-image">
                    <img src="assets/images/cap-coral.jpg" alt="REPENT Cap" loading="lazy">
                </div>
                <div class="product-card-body">
                    <h3 class="product-card-name">REPENT† Cap</h3>
                    <p class="product-card-desc">Washed dad caps with embroidered REPENT† logo. 6 colourways.</p>
                    <div class="product-card-footer">
                        <div class="product-price">R280 <span>/ cap</span></div>
                        <a href="pages/shop.php" class="btn btn-primary btn-sm">View</a>
                    </div>
                </div>
            </div>

        </div><!-- end products-grid -->

        <!-- View all link -->
        <div class="text-center mt-8">
            <a href="pages/shop.php" class="btn btn-outline">View All Products</a>
        </div>

    </div>
</section>

<!-- ===== CATEGORY BROWSING ===== -->
<section class="category-section">
    <div class="container">
        <div class="section-header" style="margin-bottom:40px;">
            <span class="section-label" style="color:var(--gold);">Browse By</span>
            <h2 class="section-title" style="color:var(--white);">Shop By Category</h2>
        </div>
        <!-- Three category cards linking to filtered shop page -->
        <div class="categories-grid">
            <div class="category-card" onclick="location.href='pages/shop.php?category=tshirts'">
                <img src="assets/images/people-wearing.jpg" alt="T-Shirts">
                <div class="category-card-content">
                    <div class="category-name">T-Shirts</div>
                    <div class="category-count">Bible verse prints</div>
                </div>
            </div>
            <div class="category-card" onclick="location.href='pages/shop.php?category=hoodies'">
                <img src="assets/images/hoodies-collection.jpg" alt="Hoodies">
                <div class="category-card-content">
                    <div class="category-name">Hoodies &amp; Crewnecks</div>
                    <div class="category-count">Warm &amp; bold</div>
                </div>
            </div>
            <div class="category-card" onclick="location.href='pages/shop.php?category=caps'">
                <img src="assets/images/cap-mint.jpg" alt="Caps">
                <div class="category-card-content">
                    <div class="category-name">Caps</div>
                    <div class="category-count">6 colourways</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== FAITH BANNER ===== -->
<section class="faith-banner">
    <div class="faith-banner-label">Our Mission</div>
    <h2 class="faith-banner-title">"Therefore, if anyone is in Christ, he is a new creation."</h2>
    <p class="faith-banner-ref">— 2 Corinthians 5:17</p>
    <a href="pages/devotional.php" class="btn btn-gold">Read Today's Devotional</a>
</section>

<!-- ===== DEVOTIONALS PREVIEW ===== -->
<section class="section" style="background:var(--white);">
    <div class="container">
        <div class="section-header">
            <span class="section-label">Spiritual Growth</span>
            <h2 class="section-title">Daily Devotionals</h2>
            <p class="section-subtitle">Scripture-based reflections to guide your walk with God every day.</p>
        </div>
        <div class="devotionals-grid">
            <div class="devotional-card">
                <div class="devotional-card-top">
                    <div class="devotional-verse-tag">Strength</div>
                    <div class="devotional-verse-text">"I can do all things through Christ who strengthens me."</div>
                    <div class="devotional-verse-ref">— Philippians 4:13</div>
                </div>
                <div class="devotional-card-body">
                    <h3 class="devotional-card-title">Strength in Surrender</h3>
                    <p class="devotional-card-excerpt">True strength is not found in self-reliance. When we acknowledge our weakness, His power is made perfect in us...</p>
                    <a href="pages/devotional.php" class="devotional-card-link">Read More →</a>
                </div>
            </div>
            <div class="devotional-card">
                <div class="devotional-card-top">
                    <div class="devotional-verse-tag">Peace</div>
                    <div class="devotional-verse-text">"Be still, and know that I am God."</div>
                    <div class="devotional-verse-ref">— Psalm 46:10</div>
                </div>
                <div class="devotional-card-body">
                    <h3 class="devotional-card-title">Finding Peace in the Storm</h3>
                    <p class="devotional-card-excerpt">God invites us into stillness. Not passivity, but a confident rest in the knowledge that He is in control...</p>
                    <a href="pages/devotional.php" class="devotional-card-link">Read More →</a>
                </div>
            </div>
            <div class="devotional-card">
                <div class="devotional-card-top">
                    <div class="devotional-verse-tag">Repentance</div>
                    <div class="devotional-verse-text">"Repent, for the kingdom of heaven has come near."</div>
                    <div class="devotional-verse-ref">— Matthew 4:17</div>
                </div>
                <div class="devotional-card-body">
                    <h3 class="devotional-card-title">The Call to Repentance</h3>
                    <p class="devotional-card-excerpt">Repentance is a complete turning away from the old life and a wholehearted turning toward God and His ways...</p>
                    <a href="pages/devotional.php" class="devotional-card-link">Read More →</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== TESTIMONIALS ===== -->
<section class="section testimonials">
    <div class="container">
        <div class="section-header">
            <span class="section-label">Community</span>
            <h2 class="section-title">What People Are Saying</h2>
        </div>
        <div class="testimonials-grid">
            <div class="testimonial-card">
                <div class="testimonial-stars">★★★★★</div>
                <p class="testimonial-text">"Wearing REPENT† has opened so many conversations about faith. The quality is amazing and the message is even better."</p>
                <div class="testimonial-author">Thandeka M.</div>
                <div class="testimonial-label">Johannesburg</div>
            </div>
            <div class="testimonial-card">
                <div class="testimonial-stars">★★★★★</div>
                <p class="testimonial-text">"I bought two hoodies and they are the softest I've ever worn. The 'Be Still' tee gets so many compliments at church."</p>
                <div class="testimonial-author">Sipho K.</div>
                <div class="testimonial-label">Pretoria</div>
            </div>
            <div class="testimonial-card">
                <div class="testimonial-stars">★★★★★</div>
                <p class="testimonial-text">"The prayer request feature is such a blessing. Knowing someone is praying with you is a powerful thing."</p>
                <div class="testimonial-author">Grace N.</div>
                <div class="testimonial-label">Cape Town</div>
            </div>
        </div>
    </div>
</section>

<!-- ===== NEWSLETTER / PRAYER CTA ===== -->
<section class="section" style="background:var(--black);text-align:center;">
    <div class="container">
        <span class="section-label">Stay Connected</span>
        <h2 class="section-title" style="color:var(--white);margin-bottom:16px;">Join the REPENT† Community</h2>
        <p style="color:rgba(255,255,255,0.55);max-width:460px;margin:0 auto 32px;font-size:15px;">
            Receive weekly devotionals, new product drops, and ministry updates straight to your inbox.
        </p>
        <a href="pages/newsletter.php" class="btn btn-gold">Subscribe to Newsletter</a>
        <a href="pages/prayer.php" class="btn btn-outline-light" style="margin-left:12px;">Submit a Prayer Request</a>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
