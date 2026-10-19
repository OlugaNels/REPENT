<?php
/**
 * ============================================================
 * REPENT† — API authentication
 * File: api/auth_helper.php
 *
 * The app sends  Authorization: Bearer <token>.
 * A token is  base64url(payload).base64url(HMAC-SHA256(payload))  where
 * payload = {uid, tv, exp}. It is signed with a server secret, so it
 * cannot be forged, and it carries the user's `token_version`: changing
 * a password or logging out raises that number, which instantly
 * invalidates every older token. No token table is needed.
 * ============================================================
 */
require_once __DIR__ . '/../includes/config.php';

const TOKEN_LIFETIME = 30 * 24 * 3600; // 30 days

function b64u_enc(string $s): string { return rtrim(strtr(base64_encode($s), '+/', '-_'), '='); }
function b64u_dec(string $s): string { return (string)base64_decode(strtr($s, '-_', '+/'), true); }

function issue_token(array $user): string {
    $payload = b64u_enc(json_encode(['uid' => (int)$user['id'], 'tv' => (int)$user['token_version'], 'exp' => time() + TOKEN_LIFETIME]));
    $sig = b64u_enc(hash_hmac('sha256', $payload, repent_secret(), true));
    return "$payload.$sig";
}

function get_bearer_token(): ?string {
    $h = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    if (!$h && function_exists('getallheaders')) {
        foreach (getallheaders() as $k => $v) if (strcasecmp($k, 'Authorization') === 0) $h = $v;
    }
    return preg_match('/^Bearer\s+(\S+)$/i', $h, $m) ? $m[1] : null;
}

/** Returns the users row for a valid token, or null. */
function user_from_token($conn, ?string $token): ?array {
    if (!$token || substr_count($token, '.') !== 1) return null;
    [$payload, $sig] = explode('.', $token);
    $expected = b64u_enc(hash_hmac('sha256', $payload, repent_secret(), true));
    if (!hash_equals($expected, $sig)) return null;

    $data = json_decode(b64u_dec($payload), true);
    if (!is_array($data) || (int)($data['exp'] ?? 0) < time()) return null;

    $uid = (int)($data['uid'] ?? 0);
    $stmt = $conn->prepare(
        "SELECT id, fullname, email, role, auth_provider, phone, address, city, postal_code, preferred_category, token_version, created_at
         FROM users WHERE id = ? LIMIT 1"
    );
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    if (!$user || (int)$user['token_version'] !== (int)($data['tv'] ?? -1)) return null;
    return $user;
}

function require_auth($conn): array {
    $token = get_bearer_token();
    if (!$token) json_error('Please sign in first.', 401);
    $user = user_from_token($conn, $token);
    if (!$user) json_error('Your session has expired. Please sign in again.', 401);
    return $user;
}

function require_admin_auth($conn): array {
    $user = require_auth($conn);
    if ($user['role'] !== 'admin') json_error('Admin access required.', 403);
    return $user;
}

/** The user fields that are safe to send to the app. */
function user_public(array $u): array {
    return [
        'id'                 => (int)$u['id'],
        'fullname'           => $u['fullname'],
        'email'              => $u['email'],
        'role'               => $u['role'],
        'auth_provider'      => $u['auth_provider'] ?? 'local',
        'phone'              => $u['phone'] ?? '',
        'address'            => $u['address'] ?? '',
        'city'               => $u['city'] ?? '',
        'postal_code'        => $u['postal_code'] ?? '',
        'preferred_category' => $u['preferred_category'] ?? 'all',
    ];
}

/** Standard login/register response body. */
function auth_response(array $user, int $status = 200): void {
    json_response(['success' => true, 'token' => issue_token($user), 'user' => user_public($user)], $status);
}
