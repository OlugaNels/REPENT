<?php
/** POST /api/prayer.php { name, email?, subject?, prayer, is_private? } -> { success } */
require_once __DIR__ . '/config.php';
require_method('POST');
require_db($conn);
$b       = read_json_body();
$name    = str_field($b, 'name', 100);
$email   = strtolower(str_field($b, 'email', 150));
$subject = str_field($b, 'subject', 100);
$prayer  = str_field($b, 'prayer', 2000);
$private = !empty($b['is_private']) ? 1 : 0;

if ($name === '')   json_error('Your name is required.', 422);
if ($prayer === '') json_error('Please enter your prayer request.', 422);
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) json_error('That email address does not look right.', 422);
$emailDb = $email === '' ? null : $email;

$stmt = $conn->prepare("INSERT INTO prayer_requests (name, email, subject, prayer, is_private) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param('ssssi', $name, $emailDb, $subject, $prayer, $private);
$stmt->execute();
json_response(['success' => true], 201);
