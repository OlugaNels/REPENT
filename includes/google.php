<?php
/**
 * ============================================================
 * REPENT† — Google sign-in helpers (shared by the website and the API)
 * File: includes/google.php
 *
 * The Android app (Credential Manager) and the website (Google Identity
 * Services) both obtain a Google *ID token*. Both send it to the server,
 * which checks it with Google and then signs the person in. Because both
 * go through find_or_create_google_user(), one Google account is one
 * REPENT† account on the app AND the website.
 * ============================================================
 */
require_once __DIR__ . '/config.php';

/**
 * Verifies a Google ID token. Returns ['sub','email','name'] or null.
 */
function google_verify_id_token(string $idToken): ?array {
    // Test hook: only active when the test runner sets this environment flag
    if (getenv('REPENT_ALLOW_TEST_HOOKS') === '1' && isset($GLOBALS['REPENT_GOOGLE_VERIFIER'])) {
        return ($GLOBALS['REPENT_GOOGLE_VERIFIER'])($idToken);
    }
    // An ID token is three base64url parts; reject anything else without calling Google
    if (strlen($idToken) < 20 || strlen($idToken) > 4096 || !preg_match('/^[A-Za-z0-9_\-]+\.[A-Za-z0-9_\-]+\.[A-Za-z0-9_\-]*$/', $idToken)) {
        return null;
    }
    $allowed = repent_google_client_ids();
    if (!$allowed) return null;

    $url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($idToken);
    $json = null;
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 8, CURLOPT_CONNECTTIMEOUT => 5]);
        $json = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code !== 200) return null;
    } else {
        $ctx = stream_context_create(['http' => ['timeout' => 8, 'ignore_errors' => true]]);
        $json = @file_get_contents($url, false, $ctx);
    }
    $c = is_string($json) ? json_decode($json, true) : null;
    if (!is_array($c)) return null;

    $issOk = in_array($c['iss'] ?? '', ['accounts.google.com', 'https://accounts.google.com'], true);
    $audOk = in_array($c['aud'] ?? '', $allowed, true);
    $expOk = (int)($c['exp'] ?? 0) > time();
    $verified = ($c['email_verified'] ?? '') === 'true' || ($c['email_verified'] ?? false) === true;
    if (!$issOk || !$audOk || !$expOk || !$verified || empty($c['sub']) || empty($c['email'])) return null;

    return ['sub' => (string)$c['sub'], 'email' => strtolower((string)$c['email']), 'name' => (string)($c['name'] ?? '')];
}

/**
 * Finds the REPENT† user for a verified Google identity, linking or creating it.
 * Returns the full users row (id, fullname, email, role, ...).
 */
function find_or_create_google_user($conn, array $claims): array {
    $cols = "id, fullname, email, role, auth_provider, phone, address, city, postal_code, preferred_category, token_version";

    $stmt = $conn->prepare("SELECT $cols FROM users WHERE google_sub = ? LIMIT 1");
    $stmt->bind_param('s', $claims['sub']);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    if ($user) return $user;

    // Same verified email already registered the normal way: link the Google account to it
    $stmt = $conn->prepare("SELECT $cols FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param('s', $claims['email']);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    if ($user) {
        $stmt = $conn->prepare("UPDATE users SET google_sub = ? WHERE id = ?");
        $stmt->bind_param('si', $claims['sub'], $user['id']);
        $stmt->execute();
        return $user;
    }

    // Brand-new customer. The password is random and unknown: they sign in with Google.
    $name = trim($claims['name']) !== '' ? mb_substr(trim($claims['name']), 0, 100) : explode('@', $claims['email'])[0];
    $hash = password_hash(bin2hex(random_bytes(24)), PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO users (fullname, email, password, role, auth_provider, google_sub) VALUES (?, ?, ?, 'customer', 'google', ?)");
    $stmt->bind_param('ssss', $name, $claims['email'], $hash, $claims['sub']);
    $stmt->execute();
    $id = (int)$conn->insert_id;

    $stmt = $conn->prepare("SELECT $cols FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}
