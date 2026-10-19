<?php
require_once '../includes/header.php';
require_once '../includes/db.php';

$success = '';
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name']    ?? '');
    $email   = trim($_POST['email']   ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $prayer  = trim($_POST['prayer']  ?? '');
    $private = isset($_POST['private']) ? 1 : 0;

    if (empty($name))   $errors[] = "Your name is required.";
    if (empty($prayer)) $errors[] = "Please enter your prayer request.";

    if (empty($errors)) {
        if ($conn) {
            $stmt = $conn->prepare("INSERT INTO prayer_requests (name, email, subject, prayer, is_private, submitted_at) VALUES (?,?,?,?,?,NOW())");
            $stmt->bind_param("ssssi", $name, $email, $subject, $prayer, $private);
            if ($stmt->execute()) {
                $success = true;
            } else {
                $errors[] = "There was a problem submitting your request. Please try again.";
            }
        } else {
            $success = true; // Demo mode
        }
    }
}
?>

<!-- PAGE HERO -->
<div class="prayer-bg">
    <div class="container" style="position:relative;z-index:2;text-align:center;">
        <span style="font-size:11px;letter-spacing:0.25em;text-transform:uppercase;color:var(--gold);display:block;margin-bottom:16px;">We Pray Together</span>
        <h1 style="font-family:var(--font-display);font-size:clamp(42px,6vw,72px);font-weight:600;color:var(--white);line-height:1.1;margin-bottom:16px;">Submit a Prayer Request</h1>
        <p style="font-family:var(--font-display);font-style:italic;font-size:19px;color:rgba(255,255,255,0.55);max-width:560px;margin:0 auto;">"The effective, fervent prayer of a righteous man avails much." <span style="color:var(--gold)">— James 5:16</span></p>
    </div>
</div>

<div class="section" style="background:var(--off-white);">
    <div class="container" style="max-width:700px;">

        <?php if ($success): ?>
        <!-- SUCCESS STATE -->
        <div style="text-align:center;padding:48px 24px;background:var(--white);border-radius:8px;box-shadow:0 4px 24px rgba(0,0,0,0.06);">
            <div style="font-size:56px;margin-bottom:16px;">🙏</div>
            <h2 style="font-family:var(--font-display);font-size:32px;font-weight:600;margin-bottom:12px;">Prayer Request Received</h2>
            <p style="color:var(--gray);font-size:15px;max-width:400px;margin:0 auto 28px;line-height:1.7;">
                We have received your prayer request and our community will be lifting you up in prayer. You are not alone.
            </p>
            <p style="font-family:var(--font-display);font-style:italic;font-size:18px;color:var(--gray);margin-bottom:24px;">"Cast all your anxiety on him because he cares for you." — 1 Peter 5:7</p>
            <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
                <a href="prayer.php" class="btn btn-outline">Submit Another</a>
                <a href="../index.php" class="btn btn-gold">Back to Home</a>
            </div>
        </div>

        <?php else: ?>
        <!-- PRAYER FORM -->
        <div style="background:var(--white);border-radius:8px;padding:48px 44px;box-shadow:0 4px 24px rgba(0,0,0,0.06);">
            <div style="text-align:center;margin-bottom:32px;">
                <img src="../assets/images/logo.png" alt="REPENT†" class="form-logo" style="filter:none;">
                <h2 style="font-family:var(--font-display);font-size:28px;font-weight:600;">Share Your Prayer Need</h2>
                <p style="font-size:14px;color:var(--gray);margin-top:6px;">All prayer requests are treated with care and confidentiality.</p>
            </div>

            <?php foreach ($errors as $e): ?>
                <div class="alert alert-error"><?= htmlspecialchars($e) ?></div>
            <?php endforeach; ?>

            <form method="POST" action="prayer.php">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Your Name *</label>
                        <input type="text" id="name" name="name" placeholder="First name or anonymous"
                               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email <span style="color:var(--light-gray);font-weight:400;">(optional)</span></label>
                        <input type="email" id="email" name="email" placeholder="For a follow-up"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="subject">Prayer Topic</label>
                    <select id="subject" name="subject">
                        <option value="">Select a topic...</option>
                        <option value="Health & Healing">Health &amp; Healing</option>
                        <option value="Family & Relationships">Family &amp; Relationships</option>
                        <option value="Financial Breakthrough">Financial Breakthrough</option>
                        <option value="Salvation of a loved one">Salvation of a Loved One</option>
                        <option value="Spiritual Growth">Spiritual Growth</option>
                        <option value="Direction & Purpose">Direction &amp; Purpose</option>
                        <option value="Depression & Anxiety">Depression &amp; Anxiety</option>
                        <option value="Praise & Thanksgiving">Praise &amp; Thanksgiving</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="prayer">Your Prayer Request *</label>
                    <textarea id="prayer" name="prayer" rows="5"
                              placeholder="Share your prayer need... God hears every word."
                              required><?= htmlspecialchars($_POST['prayer'] ?? '') ?></textarea>
                </div>

                <label class="filter-option" style="margin-bottom:24px;cursor:pointer;">
                    <input type="checkbox" name="private" <?= isset($_POST['private']) ? 'checked' : '' ?>>
                    <span style="font-size:14px;color:var(--gray);">Keep this request private (seen by prayer team only)</span>
                </label>

                <button type="submit" class="btn btn-gold btn-full">🙏 Submit Prayer Request</button>
            </form>
        </div>
        <?php endif; ?>

        <!-- ENCOURAGEMENT BELOW FORM -->
        <div style="text-align:center;margin-top:40px;padding:32px;background:var(--black);border-radius:8px;color:var(--white);">
            <p style="font-family:var(--font-display);font-style:italic;font-size:20px;font-weight:300;margin-bottom:8px;">
                "Do not be anxious about anything, but in every situation, by prayer and petition, with thanksgiving, present your requests to God."
            </p>
            <p style="font-size:13px;color:var(--gold);">— Philippians 4:6</p>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
