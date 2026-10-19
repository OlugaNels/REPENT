<?php
/** POST /api/login.php  { email, password } -> { success, token, user } */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth_helper.php';
require_method('POST');
require_db($conn);

$body     = read_json_body();
$email    = strtolower(str_field($body, 'email', 150));
$password = is_string($body['password'] ?? null) ? $body['password'] : '';
if ($email === '' || $password === '') json_error('Please enter both email and password.', 422);

$stmt = $conn->prepare(
    "SELECT id, fullname, email, password, role, auth_provider, phone, address, city, postal_code, preferred_category, token_version
     FROM users WHERE email = ? LIMIT 1"
);
$stmt->bind_param('s', $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user || !password_verify($password, $user['password'])) {
    json_error('Incorrect email or password.', 401);
}
auth_response($user);
