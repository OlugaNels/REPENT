<?php
/**
 * POST /api/register.php   { fullname, email, password }
 * -> { success, token, user }.  The password is stored as a bcrypt hash.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth_helper.php';
require_method('POST');
require_db($conn);

$body     = read_json_body();
$fullname = str_field($body, 'fullname', 100);
$email    = strtolower(str_field($body, 'email', 150));
$password = is_string($body['password'] ?? null) ? $body['password'] : '';

if (mb_strlen($fullname) < 2)                     json_error('Please enter your full name.', 422);
if (!filter_var($email, FILTER_VALIDATE_EMAIL))   json_error('Please enter a valid email address.', 422);
if ($p = password_problem($password))             json_error($p, 422);

$hash = password_hash($password, PASSWORD_BCRYPT);
try {
    $stmt = $conn->prepare("INSERT INTO users (fullname, email, password, role, auth_provider) VALUES (?, ?, ?, 'customer', 'local')");
    $stmt->bind_param('sss', $fullname, $email, $hash);
    $stmt->execute();
} catch (Throwable $e) {
    if (is_duplicate($e)) json_error('An account with this email already exists.', 409);
    throw $e;
}
$id = (int)$conn->insert_id;
$stmt = $conn->prepare("SELECT id, fullname, email, role, auth_provider, phone, address, city, postal_code, preferred_category, token_version FROM users WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
auth_response($stmt->get_result()->fetch_assoc(), 201);
