<?php
/**
 * ============================================================
 * REPENT† — Database connection
 * File: includes/db.php
 *
 * Opens the MySQL connection used by both the website and the API.
 * If MySQL is not reachable $conn is set to null (the website then
 * shows demo data; the API answers with a clear 503 error).
 * Credentials come from includes/config.php.
 * ============================================================
 */
require_once __DIR__ . '/config.php';

define_if_missing('DB_HOST', repent_setting('DB_HOST', 'localhost'));
define_if_missing('DB_USER', repent_setting('DB_USER', 'root'));
define_if_missing('DB_PASS', repent_setting('DB_PASS', ''));
define_if_missing('DB_NAME', repent_setting('DB_NAME', 'repent_db'));
// Online hosts (e.g. Aiven) use a non-default port and require an encrypted connection.
define_if_missing('DB_PORT', repent_setting('DB_PORT', '3306'));
define_if_missing('DB_SSL', repent_setting('DB_SSL', '0'));

function define_if_missing(string $name, string $value): void {
    if (!defined($name)) define($name, $value);
}

// Automated tests can hand in a ready-made connection (see tests/).
if (!empty($GLOBALS['REPENT_TEST_CONN'])) {
    $conn = $GLOBALS['REPENT_TEST_CONN'];
    return;
}

// Make mysqli throw exceptions (PHP 8.1+ default) so failures are catchable
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = mysqli_init();
    $flags = 0;
    if (DB_SSL === '1') {
        // Encrypt the connection without pinning a CA file (the host's certificate is not verified).
        $conn->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
        $conn->ssl_set(null, null, null, null, null);
        $flags = MYSQLI_CLIENT_SSL;
    }
    $conn->real_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, (int)DB_PORT, null, $flags);
    $conn->set_charset('utf8mb4');
} catch (Throwable $e) {
    $conn = null;
}
