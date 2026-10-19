<?php
require_once '../includes/header.php';
require_once '../includes/db.php';

$success = '';
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name      = trim($_POST['name']      ?? '');
    $email     = trim($_POST['email']     ?? '');
    $frequency = $_POST['frequency']      ?? 'weekly';

    if (empty($name))  $errors[] = "Your name is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "A valid email address is required.";

    if (empty($errors)) {
        if ($conn) {
            // Check for duplicate
            $check = $conn->prepare("SELECT id FROM newsletter_subscribers WHERE email = ?");
            $check->bind_param("s", $email);
            $check->execute();
            $check->store_result();
            if ($check->num_rows > 0) {
                $success = "already";
            } else {
                $stmt = $conn->prepare("INSERT INTO newsletter_subscribers (name, email, frequency, subscribed_at) VALUES (?,?,?,NOW())");
                $stmt->bind_param("sss", $name, $email, $frequency);
                $success = $stmt->execute() ? "subscribed" : "";
                if (!$success) $errors[] = "Subscription failed. Please try again.";
            }
        } else {
            $success = "subscribed"; // Demo mode
        }
    }
}
?>

<!-- NEWSLETTER HERO -->
<div class="newsletter-hero">
    <div class="container" style="position:relative;z-index:2;">
        <div class="newsletter-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                <polyline points="22,6 12,13 2,6"/>
            </svg>
        </div>
        <h1 style="font-family:var(--font-display);font-size:clamp(44px,6vw,72px);font-weight:600;margin-bottom:14px;">Stay in the Word</h1>
        <p style="font-size:15px;color:rgba(255,255,255,0.55);max-width:480px;margin:0 auto;">Subscribe to receive weekly devotionals, scripture teachings, ministry updates, and new product drops from REPENT†.</p>
    </div>
</div>

<div class="section" style="background:var(--off-white);">
    <div class="container" style="max-width:640px;">

        <?php if ($success === 'subscribed'): ?>
        <div style="text-align:center;padding:56px 24px;background:var(--white);border-radius:8px;box-shadow:0 4px 24px rgba(0,0,0,0.06);">
            <div style="font-size:56px;margin-bottom:16px;">✉️</div>
            <h2 style="font-family:var(--font-display);font-size:32px;margin-bottom:12px;">You're Subscribed!</h2>
            <p style="color:var(--gray);font-size:15px;max-width:380px;margin:0 auto 28px;line-height:1.7;">
                Welcome to the REPENT† community! Check your inbox — your first devotional is on its way.
            </p>
            <a href="../index.php" class="btn btn-gold">Back to Home</a>
        </div>

        <?php elseif ($success === 'already'): ?>
        <div class="alert alert-info" style="text-align:center;">
            This email is already subscribed. Thank you for being part of the REPENT† community! 🙏
        </div>

        <?php else: ?>
        <div style="background:var(--white);border-radius:8px;padding:48px 44px;box-shadow:0 4px 24px rgba(0,0,0,0.06);">
            <div style="text-align:center;margin-bottom:32px;">
                <img src="../assets/images/logo.png" alt="REPENT†" class="form-logo" style="filter:none;margin-bottom:14px;">
                <h2 style="font-family:var(--font-display);font-size:28px;font-weight:600;">Subscribe to REPENT† Newsletter</h2>
                <p style="font-size:14px;color:var(--gray);margin-top:6px;">Join thousands receiving faith-filled content every week.</p>
            </div>

            <?php foreach ($errors as $e): ?>
                <div class="alert alert-error"><?= htmlspecialchars($e) ?></div>
            <?php endforeach; ?>

            <form method="POST" action="newsletter.php">
                <div class="form-group">
                    <label for="name">Your Name</label>
                    <input type="text" id="name" name="name" placeholder="Your name"
                           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="you@example.com"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="frequency">How often would you like to hear from us?</label>
                    <select id="frequency" name="frequency">
                        <option value="daily">Daily Devotional</option>
                        <option value="weekly" selected>Weekly Newsletter</option>
                        <option value="monthly">Monthly Updates</option>
                    </select>
                </div>

                <!-- Preferences -->
                <div style="margin-bottom:24px;">
                    <p style="font-size:12px;font-weight:600;letter-spacing:0.1em;text-transform:uppercase;color:var(--black);margin-bottom:12px;">I'm interested in:</p>
                    <div style="display:flex;flex-direction:column;gap:8px;">
                        <label class="filter-option"><input type="checkbox" name="pref[]" value="devotionals" checked> Daily Devotionals &amp; Scripture Reflections</label>
                        <label class="filter-option"><input type="checkbox" name="pref[]" value="products"   checked> New Product Arrivals &amp; Drops</label>
                        <label class="filter-option"><input type="checkbox" name="pref[]" value="ministry"> Ministry Updates &amp; Events</label>
                        <label class="filter-option"><input type="checkbox" name="pref[]" value="prayer"> Prayer Community Updates</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-gold btn-full">Subscribe Now</button>
                <p style="font-size:12px;color:var(--light-gray);text-align:center;margin-top:12px;">No spam. Unsubscribe anytime. We honour your inbox.</p>
            </form>
        </div>

        <!-- WHAT TO EXPECT -->
        <div style="margin-top:40px;">
            <h3 style="font-family:var(--font-display);font-size:24px;text-align:center;margin-bottom:24px;">What You'll Receive</h3>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <?php
                $perks = [
                    ['✝', 'Weekly Devotionals', 'Scripture-based readings to strengthen your faith'],
                    ['📖', 'Bible Teachings', 'Deep dives into key passages and themes'],
                    ['🛍️', 'New Arrivals', 'First access to new REPENT† clothing drops'],
                    ['🙏', 'Prayer Updates', 'Community prayer requests and praise reports'],
                ];
                foreach ($perks as $p): ?>
                <div style="background:var(--white);border-radius:6px;padding:20px;border:1px solid #eee;text-align:center;">
                    <div style="font-size:28px;margin-bottom:8px;"><?= $p[0] ?></div>
                    <div style="font-family:var(--font-display);font-size:17px;font-weight:600;margin-bottom:4px;"><?= $p[1] ?></div>
                    <div style="font-size:13px;color:var(--gray);"><?= $p[2] ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
