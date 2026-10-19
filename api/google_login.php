<?php
/**
 * POST /api/google_login.php  { id_token }
 * The Android app sends the Google ID token it got from Credential Manager.
 * The server verifies it with Google, then finds/links/creates the account.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth_helper.php';
require_once __DIR__ . '/../includes/google.php';
require_method('POST');
require_db($conn);

if (!repent_google_client_ids() && getenv('REPENT_ALLOW_TEST_HOOKS') !== '1') {
    json_error('Google sign-in is not set up on the server yet (GOOGLE_WEB_CLIENT_ID is missing).', 503);
}
$idToken = str_field(read_json_body(), 'id_token', 4096);
if ($idToken === '') json_error('No Google token was provided.', 422);

$claims = google_verify_id_token($idToken);
if (!$claims) json_error('Google sign-in could not be verified. Please try again.', 401);

auth_response(find_or_create_google_user($conn, $claims));
