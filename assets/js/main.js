/**
 * ============================================================
 * REPENT† — Main JavaScript File
 * File: assets/js/main.js
 *
 * PURPOSE: Handles all client-side interactivity:
 *  - Sticky navbar scroll effect
 *  - Mobile hamburger menu
 *  - Size selector buttons
 *  - Colour swatch selector
 *  - Add-to-cart button feedback animation
 *  - Auto-dismiss flash alerts
 * ============================================================
 */

// ---- Run once the DOM is fully loaded ----
document.addEventListener('DOMContentLoaded', function () {

    // --------------------------------------------------
    // 1. STICKY NAVBAR: add shadow when user scrolls down
    // --------------------------------------------------
    const navbar = document.getElementById('navbar');
    if (navbar) {
        window.addEventListener('scroll', function () {
            if (window.scrollY > 30) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }

    // --------------------------------------------------
    // 2. HAMBURGER MENU: toggle mobile nav open/close
    // --------------------------------------------------
    const hamburger = document.getElementById('hamburger');
    const navLinks  = document.getElementById('navLinks');

    if (hamburger && navLinks) {
        hamburger.addEventListener('click', function () {
            // Toggle the 'open' class which shows the nav links
            navLinks.classList.toggle('open');
            hamburger.classList.toggle('active');
        });

        // Close nav if user clicks outside of it
        document.addEventListener('click', function (e) {
            if (!navbar.contains(e.target)) {
                navLinks.classList.remove('open');
                hamburger.classList.remove('active');
            }
        });
    }

    // --------------------------------------------------
    // 3. SIZE SELECTOR: highlight the selected size button
    // --------------------------------------------------
    document.querySelectorAll('.size-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            // Remove 'active' from all siblings in the same group
            const siblings = this.closest('.size-group')?.querySelectorAll('.size-btn');
            if (siblings) {
                siblings.forEach(function (b) { b.classList.remove('active'); });
            }
            // Set this button as active
            this.classList.add('active');
        });
    });

    // --------------------------------------------------
    // 4. COLOUR SWATCH: highlight selected colour dot
    // --------------------------------------------------
    document.querySelectorAll('.color-dot').forEach(function (dot) {
        dot.addEventListener('click', function () {
            // Remove active from siblings
            const siblings = this.closest('.color-swatches')?.querySelectorAll('.color-dot');
            if (siblings) {
                siblings.forEach(function (d) { d.classList.remove('active'); });
            }
            this.classList.add('active');
        });
    });

    // --------------------------------------------------
    // 5. ADD TO CART BUTTON: visual feedback animation
    //    Shows a green "Added!" state for 1.8 seconds
    // --------------------------------------------------
    document.querySelectorAll('.add-to-cart-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const original = this.textContent;
            this.textContent  = '✓ Added!';
            this.style.background = '#2e7d32';
            const self = this;
            setTimeout(function () {
                self.textContent  = original;
                self.style.background = '';
            }, 1800);
        });
    });

    // --------------------------------------------------
    // 6. AUTO-DISMISS ALERTS: fade out after 4 seconds
    // --------------------------------------------------
    setTimeout(function () {
        document.querySelectorAll('.alert').forEach(function (alert) {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity    = '0';
            setTimeout(function () { alert.remove(); }, 500);
        });
    }, 4000);

    // --------------------------------------------------
    // 7. CART REMOVE: confirm before submitting remove form
    // --------------------------------------------------
    document.querySelectorAll('.remove-btn').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            if (!confirm('Remove this item from your cart?')) {
                e.preventDefault(); // Stop form submission if user cancels
            }
        });
    });

}); // end DOMContentLoaded
