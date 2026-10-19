<?php
/** POST /api/logout.php — signs this account out everywhere (raises token_version). */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth_helper.php';
require_method('POST');
require_db($conn);
$user = user_from_token($conn, get_bearer_token());
if ($user) {
    $stmt = $conn->prepare("UPDATE users SET token_version = token_version + 1 WHERE id = ?");
    $stmt->bind_param('i', $user['id']);
    $stmt->execute();
}
json_response(['success' => true]);
