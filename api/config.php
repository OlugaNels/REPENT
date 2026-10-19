<?php
/**
 * ============================================================
 * REPENT† — API bootstrap
 * File: api/config.php
 *
 * Included first by every api/*.php endpoint. Sets JSON + CORS headers,
 * opens the database, and provides small helpers so each endpoint stays
 * short. Any unexpected error becomes a clean JSON 500 (never a stack
 * trace), so the app can never be crashed by a bad request.
 * ============================================================
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('X-Content-Type-Options: nosniff');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit();
}

ini_set('display_errors', '0');
set_exception_handler(function (Throwable $e) {
    error_log('REPENT API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Something went wrong on the server. Please try again.']);
});

require_once __DIR__ . '/../includes/db.php';

function json_response($data, int $status = 200): void {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit();
}

function json_error(string $message, int $status = 400): void {
    json_response(['success' => false, 'error' => $message], $status);
}

/** Stops with 503 if MySQL is not reachable. */
function require_db($conn): void {
    if (!$conn) json_error('The database is not available right now. Please try again shortly.', 503);
}

/** Stops with 405 unless the request uses one of the given methods. */
function require_method(string ...$allowed): string {
    $m = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if (!in_array($m, $allowed, true)) {
        header('Allow: ' . implode(', ', $allowed));
        json_error(implode(' or ', $allowed) . ' required.', 405);
    }
    return $m;
}

/** Decodes the JSON request body. A malformed body is a 400, not a crash. */
function read_json_body(): array {
    $raw = file_get_contents('php://input');
    if ($raw !== false && trim($raw) !== '') {
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) json_error('The request body is not valid JSON.', 400);
        return $decoded;
    }
    return $_POST ?: [];
}

/** Reads a trimmed string field, never null, capped at $max characters. */
function str_field(array $body, string $key, int $max = 255): string {
    $v = $body[$key] ?? '';
    if (!is_scalar($v)) return '';
    return mb_substr(trim((string)$v), 0, $max);
}

/**
 * Password rule used everywhere (register, change password):
 * at least 8 characters, with at least one letter and one number.
 * Returns an error message, or null if the password is fine.
 */
function password_problem(string $pw): ?string {
    if (strlen($pw) < 8)               return 'Password must be at least 8 characters.';
    if (strlen($pw) > 72)              return 'Password must be at most 72 characters.';
    if (!preg_match('/[A-Za-z]/', $pw)) return 'Password must contain at least one letter.';
    if (!preg_match('/\d/', $pw))       return 'Password must contain at least one number.';
    return null;
}

/** Phone numbers: digits, spaces, + - ( ), 7 to 20 characters. */
function phone_ok(string $p): bool {
    return (bool)preg_match('/^[0-9 +\-()]{7,20}$/', $p);
}

/** True if a mysqli exception is a duplicate-key error (MySQL 1062). */
function is_duplicate(Throwable $e): bool {
    return $e instanceof mysqli_sql_exception && (int)$e->getCode() === 1062
        || stripos($e->getMessage(), 'unique') !== false || stripos($e->getMessage(), 'duplicate') !== false;
}
