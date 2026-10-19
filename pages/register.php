<?php
/**
 * ============================================================
 * REPENT† — Registration Page
 * File: pages/register.php
 *
 * PURPOSE: Allows new users to create an account.
 * - Validates all input fields server-side.
 * - Hashes the password using bcrypt before storing.
 * - Works in demo mode (no DB) for presentation purposes.
 * ============================================================
 */

require_once '../includes/header.php';
require_once '../includes/db.php';

// If already logged in, redirect away
if (isLoggedIn()) {
    header("Location: ../index.php");
    exit();
}

// ---------- Variables ----------
$errors  = [];
$success = '';

// ---------- Handle form submission ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Collect and sanitise form inputs
    $fullname = trim($_POST['fullname'] ?? '');
    $email    = trim(strtolower($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm']  ?? '';

    // ----- Validation rules -----
    if (empty($fullname))                                    $errors[] = "Full name is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))          $errors[] = "Please enter a valid email address.";
    if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password))
                                                             $errors[] = "Password must be at least 8 characters and contain a letter and a number.";
    if ($password !== $confirm)                              $errors[] = "Passwords do not match.";

    // Only proceed if no validation errors
    if (empty($errors)) {

        if ($conn) {
            // ----- Real database registration -----

            // Check if email is already registered
            $check = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $check->bind_param("s", $email);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $errors[] = "An account with this email already exists.";
            } else {
                // Hash the password securely using bcrypt
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                $role = 'customer'; // Default role for new users

                // Insert new user into database
                $stmt = $conn->prepare(
                    "INSERT INTO users (fullname, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())"
                );
                $stmt->bind_param("ssss", $fullname, $email, $hashedPassword, $role);

                if ($stmt->execute()) {
                    $success = "Account created! <a href='login.php'>Click here to login</a>.";
                } else {
                    $errors[] = "Registration failed. Please try again.";
                }
            }

        } else {
            // ----- Demo mode (no database) -----
            $success = "✓ Registration successful! (Demo Mode — no DB connected). <a href='login.php'>Login here</a>.";
        }
    }
}
?>

<!-- ===== REGISTRATION FORM ===== -->
<div class="form-page">
    <div class="form-card">

        <!-- Card Header -->
        <div class="form-card-header">
            <?php $logoColor = "black"; $logoHeight = 42; include "../includes/logo.php"; ?>
            <h1 class="form-card-title">Create Account</h1>
            <p class="form-card-sub">Join the REPENT† community today</p>
        </div>

        <!-- Show validation errors -->
        <?php foreach ($errors as $e): ?>
            <div class="alert alert-error"><?= htmlspecialchars($e) ?></div>
        <?php endforeach; ?>

        <!-- Show success message -->
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <!-- Registration Form -->
        <form method="POST" action="register.php">

            <!-- Full Name -->
            <div class="form-group">
                <label for="fullname">Full Name</label>
                <input type="text" id="fullname" name="fullname"
                       placeholder="e.g. Luthando Dlamini"
                       value="<?= htmlspecialchars($_POST['fullname'] ?? '') ?>"
                       required>
            </div>

            <!-- Email Address -->
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email"
                       placeholder="you@example.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       required autocomplete="email">
            </div>

            <!-- Password + Confirm side by side -->
            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password"
                           placeholder="Min. 6 characters" required>
                </div>
                <div class="form-group">
                    <label for="confirm">Confirm Password</label>
                    <input type="password" id="confirm" name="confirm"
                           placeholder="Repeat password" required>
                </div>
            </div>

            <!-- Terms agreement note -->
            <p style="font-size:12px;color:var(--gray);margin-bottom:20px;">
                By creating an account you agree to our terms of service and privacy policy.
            </p>

            <!-- Submit button -->
            <button type="submit" class="btn btn-gold btn-full">Create My Account</button>
        </form>

        <!-- Link to login -->
        <div class="form-footer-link">
            Already have an account? <a href="login.php">Sign in here</a>
        </div>

    </div><!-- end .form-card -->
</div>

<?php require_once '../includes/footer.php'; ?>
