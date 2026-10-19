<?php
/**
 * GET /api/me.php   -> { success, user }                 (profile)
 * PUT /api/me.php   { fullname, phone, address, city, postal_code, preferred_category }
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth_helper.php';
$method = require_method('GET', 'PUT');
require_db($conn);
$user = require_auth($conn);

if ($method === 'PUT') {
    $b        = read_json_body();
    $fullname = str_field($b, 'fullname', 100);
    $phone    = str_field($b, 'phone', 30);
    $address  = str_field($b, 'address', 255);
    $city     = str_field($b, 'city', 100);
    $postal   = str_field($b, 'postal_code', 20);
    $cat      = str_field($b, 'preferred_category', 20) ?: 'all';

    if (mb_strlen($fullname) < 2)                                  json_error('Please enter your full name.', 422);
    if ($phone !== '' && !phone_ok($phone))                        json_error('Please enter a valid phone number.', 422);
    if ($postal !== '' && !preg_match('/^[A-Za-z0-9 \-]{3,10}$/', $postal)) json_error('Please enter a valid postal code.', 422);
    if (!in_array($cat, ['all', 'tshirts', 'hoodies', 'caps'], true)) json_error('Unknown category.', 422);

    $stmt = $conn->prepare("UPDATE users SET fullname=?, phone=?, address=?, city=?, postal_code=?, preferred_category=? WHERE id=?");
    $stmt->bind_param('ssssssi', $fullname, $phone, $address, $city, $postal, $cat, $user['id']);
    $stmt->execute();
    $user = array_merge($user, ['fullname' => $fullname, 'phone' => $phone, 'address' => $address,
                                'city' => $city, 'postal_code' => $postal, 'preferred_category' => $cat]);
}
json_response(['success' => true, 'user' => user_public($user)]);
