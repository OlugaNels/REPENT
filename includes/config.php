<?php
/**
 * ============================================================
 * REPENT† — Settings
 * File: includes/config.php
 *
 * Every setting is read from (1) an environment variable, then
 * (2) a constant defined in includes/config.local.php, then
 * (3) the default below. config.local.php is NOT committed to git;
 * copy config.local.example.php to create it.
 * ============================================================
 */
$repentLocal = __DIR__ . '/config.local.php';
if (is_file($repentLocal)) {
    require_once $repentLocal;
}

function repent_setting(string $key, string $default = ''): string {
    $env = getenv($key);
    if ($env !== false && $env !== '') return (string)$env;
    if (defined($key)) return (string)constant($key);
    return $default;
}

/**
 * Secret used to sign API login tokens. Taken from settings if given,
 * otherwise a random one is generated once and stored in
 * includes/app_secret.key (so a fresh install is secure with no setup).
 */
function repent_secret(): string {
    $configured = repent_setting('REPENT_APP_SECRET');
    if (strlen($configured) >= 32) return $configured;

    $file = __DIR__ . '/app_secret.key';
    if (is_file($file)) {
        $s = trim((string)@file_get_contents($file));
        if (strlen($s) >= 32) return $s;
    }
    $s = bin2hex(random_bytes(32));
    @file_put_contents($file, $s, LOCK_EX);
    return $s;
}

/** The OAuth "Web application" client ID from Google Cloud Console. */
function repent_google_client_ids(): array {
    $ids = [repent_setting('GOOGLE_WEB_CLIENT_ID')];
    foreach (explode(',', repent_setting('GOOGLE_EXTRA_CLIENT_IDS')) as $x) $ids[] = trim($x);
    return array_values(array_filter($ids, fn($x) => $x !== ''));
}
