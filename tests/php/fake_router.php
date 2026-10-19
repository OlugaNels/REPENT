<?php
/**
 * TEST ONLY — router for `php -S`. Plugs the SQLite-backed FakeMysqli into
 * includes/db.php and a fake Google verifier, then runs the requested script.
 * Never used by the real site.
 */
require __DIR__ . '/FakeMysqli.php';
$GLOBALS['REPENT_TEST_CONN'] = new FakeMysqli(getenv('REPENT_TEST_DB'));
// Fake Google: "good:<sub>:<email>:<name>" is accepted, everything else is rejected.
$GLOBALS['REPENT_GOOGLE_VERIFIER'] = function (string $t): ?array {
    $p = explode(':', $t, 4);
    return ($p[0] === 'good' && count($p) === 4) ? ['sub' => $p[1], 'email' => strtolower($p[2]), 'name' => $p[3]] : null;
};
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$root = realpath($_SERVER['DOCUMENT_ROOT']);
$file = realpath($root . $path);
if (!$file || strpos($file, $root) !== 0 || !is_file($file) || substr($file, -4) !== '.php') return false;
$_SERVER['SCRIPT_NAME'] = $path; $_SERVER['PHP_SELF'] = $path; $_SERVER['SCRIPT_FILENAME'] = $file;
chdir(dirname($file));
require $file;
