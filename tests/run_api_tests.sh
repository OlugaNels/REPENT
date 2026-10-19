#!/usr/bin/env bash
# Runs the API test-suite locally WITHOUT MySQL: a SQLite copy of repent_database.sql,
# served by PHP's built-in server through tests/php/fake_router.php.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
TMP="$(mktemp -d)"; PORT="${PORT:-8765}"
trap 'kill $PID 2>/dev/null || true; rm -rf "$TMP"' EXIT
php "$ROOT/tests/php/make_fake_db.php" "$ROOT/repent_database.sql" "$TMP/test.sqlite" >/dev/null
export REPENT_TEST_DB="$TMP/test.sqlite" REPENT_ALLOW_TEST_HOOKS=1 GOOGLE_WEB_CLIENT_ID="test-client.apps.googleusercontent.com" REPENT_APP_SECRET="test-secret-test-secret-test-secret-1234"
php -S "127.0.0.1:$PORT" -t "$ROOT" "$ROOT/tests/php/fake_router.php" >"$TMP/server.log" 2>&1 &
PID=$!
for i in $(seq 1 30); do curl -sf "http://127.0.0.1:$PORT/api/health.php" >/dev/null && break; sleep 0.2; done
php "$ROOT/tests/api_test.php" "http://127.0.0.1:$PORT" --google
