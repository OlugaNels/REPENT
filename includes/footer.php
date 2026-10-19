<?php
/**
 * ============================================================
 * REPENT† — Global Footer
 * File: includes/footer.php
 * Included at the bottom of every page.
 * ============================================================
 */

$inPages  = strpos($_SERVER['PHP_SELF'], '/pages/') !== false;
$basePath = $inPages ? '../' : '';
?>

<footer class="footer">
    <div class="footer-container">

        <!-- Brand Column — SVG logo (white on dark footer) -->
        <div class="footer-brand">
            <a href="<?= $basePath ?>index.php" class="footer-logo-link">
                <?php $logoColor = 'white'; $logoHeight = 32; include __DIR__ . '/logo.php'; ?>
            </a>
            <p class="footer-tagline">Wear the Word. Live the Faith.</p>
            <p class="footer-verse">
                "Therefore, if anyone is in Christ,<br>he is a new creation."<br>
                <em>— 2 Corinthians 5:17</em>
            </p>
        </div>

        <!-- Quick Links -->
        <div class="footer-links">
            <h4>Quick Links</h4>
            <ul>
                <li><a href="<?= $basePath ?>index.php">Home</a></li>
                <li><a href="<?= $basePath ?>pages/shop.php">Shop</a></li>
                <li><a href="<?= $basePath ?>pages/devotional.php">Devotional</a></li>
                <li><a href="<?= $basePath ?>pages/prayer.php">Prayer Request</a></li>
                <li><a href="<?= $basePath ?>pages/newsletter.php">Newsletter</a></li>
            </ul>
        </div>

        <!-- Account Links -->
        <div class="footer-links">
            <h4>Account</h4>
            <ul>
                <li><a href="<?= $basePath ?>pages/login.php">Login</a></li>
                <li><a href="<?= $basePath ?>pages/register.php">Register</a></li>
                <li><a href="<?= $basePath ?>pages/cart.php">My Cart</a></li>
            </ul>
        </div>

        <!-- Newsletter + Social -->
        <div class="footer-newsletter">
            <h4>Stay Connected</h4>
            <p>Join our community for devotionals, new arrivals &amp; spiritual updates.</p>
            <a href="<?= $basePath ?>pages/newsletter.php" class="btn btn-outline-light btn-sm">Subscribe Now</a>
            <div class="footer-social">
                <span>Follow Us</span>
                <div class="social-icons">
                    <a href="https://www.instagram.com/repent_young.saved.free?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw==" 
       title="Instagram" 
       target="_blank" 
       rel="noopener noreferrer">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <rect x="2" y="2" width="20" height="20" rx="5"/>
            <circle cx="12" cy="12" r="4"/>
            <circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/>
        </svg>
    </a>
                   <a href="https://www.facebook.com/RPNTclo" 
   title="Facebook" 
   target="_blank" 
   rel="noopener noreferrer">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
        <path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/>
    </svg>
</a>
                    <a href="#" title="TikTok">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M9 12a4 4 0 104 4V4a5 5 0 005 5"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>

    </div>

    <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> REPENT† Faith-Based Clothing. All rights reserved.</p>
        <p class="footer-credit">Built with faith &amp; purpose ✝</p>
    </div>
</footer>

<script src="<?= $basePath ?>assets/js/main.js"></script>
</body>
</html>
