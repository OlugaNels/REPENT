<?php
/**
 * POST /api/change_password.php  { current_password, new_password }
 * Verifies the current password, stores the new bcrypt hash, and bumps
 * token_version so every other device is signed out. Returns a fresh token
 * so this device stays signed in.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth_helper.php';
require_method('POST');
require_db($conn);
$user = require_auth($conn);

if (($user['auth_provider'] ?? 'local') === 'google') {
    json_error('This account signs in with Google, so it has no REPENT† password to change.', 400);
}
$b   = read_json_body();
$cur = is_string($b['current_password'] ?? null) ? $b['current_password'] : '';
$new = is_string($b['new_password'] ?? null) ? $b['new_password'] : '';
if ($cur === '' || $new === '') json_error('Please fill in both password fields.', 422);

$stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
if (!$row || !password_verify($cur, $row['password'])) json_error('Your current password is incorrect.', 403);
if ($cur === $new)                                       json_error('The new password must be different from the current one.', 422);
if ($p = password_problem($new))                          json_error($p, 422);

$hash = password_hash($new, PASSWORD_BCRYPT);
$stmt = $conn->prepare("UPDATE users SET password = ?, token_version = token_version + 1 WHERE id = ?");
$stmt->bind_param('si', $hash, $user['id']);
$stmt->execute();
$user['token_version'] = (int)$user['token_version'] + 1;
auth_response($user);
