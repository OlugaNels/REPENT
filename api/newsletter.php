<?php
/** POST /api/newsletter.php { name, email, frequency } -> { success, status: subscribed|already } */
require_once __DIR__ . '/config.php';
require_method('POST');
require_db($conn);
$b     = read_json_body();
$name  = str_field($b, 'name', 100);
$email = strtolower(str_field($b, 'email', 150));
$freq  = str_field($b, 'frequency', 10);
if (!in_array($freq, ['daily', 'weekly', 'monthly'], true)) $freq = 'weekly';

if ($name === '')                                json_error('Your name is required.', 422);
if (!filter_var($email, FILTER_VALIDATE_EMAIL))  json_error('A valid email address is required.', 422);

try {
    $stmt = $conn->prepare("INSERT INTO newsletter_subscribers (name, email, frequency) VALUES (?, ?, ?)");
    $stmt->bind_param('sss', $name, $email, $freq);
    $stmt->execute();
} catch (Throwable $e) {
    if (is_duplicate($e)) json_response(['success' => true, 'status' => 'already']);
    throw $e;
}
json_response(['success' => true, 'status' => 'subscribed'], 201);
