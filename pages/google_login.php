<?php
/**
 * ============================================================
 * REPENT† — Google sign-in (website)
 * File: pages/google_login.php
 *
 * The login page's Google button gets a Google ID token in the browser and
 * POSTs it here. We verify it with Google, find/link/create the same
 * account the Android app uses, and start the normal PHP session.
 * Answers JSON: { success, redirect } or { success:false, error }.
 * ============================================================
 */
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/google.php';

header('Content-Type: application/json; charset=utf-8');

function g_fail(string $msg, int $code): void {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $msg]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') g_fail('POST required.', 405);
if (!$conn) g_fail('The database is not available right now.', 503);
if (!repent_google_client_ids()) g_fail('Google sign-in is not set up yet.', 503);

$idToken = trim((string)($_POST['credential'] ?? ''));
$claims  = $idToken !== '' ? google_verify_id_token($idToken) : null;
if (!$claims) g_fail('Google sign-in could not be verified. Please try again.', 401);

try {
    $user = find_or_create_google_user($conn, $claims);
} catch (Throwable $e) {
    error_log('Google login failed: ' . $e->getMessage());
    g_fail('Could not sign you in. Please try again.', 500);
}

session_regenerate_id(true);
$_SESSION['user_id']    = $user['id'];
$_SESSION['user_name']  = $user['fullname'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['role']       = $user['role'];

$redirect = (($_POST['redirect'] ?? '') === 'cart') ? 'cart.php' : '../index.php';
echo json_encode(['success' => true, 'redirect' => $redirect]);
