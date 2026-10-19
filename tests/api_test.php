<?php
/**
 * REPENT† API test-suite (HTTP level).
 *   php tests/api_test.php http://localhost:8000 [--google]
 * Works against the real MySQL-backed server (CI) or the SQLite-backed test server
 * (tests/run_api_tests.sh). --google also runs the Google sign-in tests, which need
 * the fake Google verifier from tests/php/fake_router.php.
 * Seed data from repent_database.sql is assumed (12 products, admin@repent.co.za ...).
 */
$base   = rtrim($argv[1] ?? 'http://localhost:8000', '/');
$google = in_array('--google', $argv, true);
$pass = 0; $fail = 0;

function call(string $method, string $path, $body = null, ?string $token = null, bool $rawBody = false, ?array &$cookies = null): array {
    global $base;
    $ch = curl_init($base . $path);
    $headers = ['Accept: application/json'];
    if ($body !== null) { $headers[] = 'Content-Type: application/json'; }
    if ($token) $headers[] = 'Authorization: Bearer ' . $token;
    curl_setopt_array($ch, [CURLOPT_CUSTOMREQUEST => $method, CURLOPT_RETURNTRANSFER => true, CURLOPT_HTTPHEADER => $headers, CURLOPT_TIMEOUT => 20, CURLOPT_HEADER => true]);
    if ($body !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, $rawBody ? $body : json_encode($body));
    $raw  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $hs   = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    $text = substr((string)$raw, $hs);
    return ['code' => $code, 'json' => json_decode($text, true), 'text' => $text, 'headers' => substr((string)$raw, 0, $hs)];
}
function check(string $name, bool $ok, string $detail = ''): void {
    global $pass, $fail;
    if ($ok) { $pass++; echo "  ok   $name\n"; } else { $fail++; echo "  FAIL $name  $detail\n"; }
}
function expect(string $name, array $r, int $code, ?callable $more = null): void {
    $ok = $r['code'] === $code && is_array($r['json']) && ($more ? $more($r['json']) : true);
    check($name, $ok, "(HTTP {$r['code']}, wanted $code) " . substr($r['text'], 0, 160));
}
function section(string $t): void { echo "\n== $t\n"; }
$uniq = substr(md5(uniqid('', true)), 0, 8);

section('Health and catalogue');
expect('health is ok', call('GET', '/api/health.php'), 200, fn($j) => $j['status'] === 'ok' && $j['products'] >= 10);
$all = call('GET', '/api/products.php');
expect('12 products listed', $all, 200, fn($j) => count($j['products']) === 12);
expect('category filter: caps', call('GET', '/api/products.php?category=caps'), 200, fn($j) => count($j['products']) === 5 && $j['products'][0]['category'] === 'caps');
expect('unknown category falls back to all', call('GET', '/api/products.php?category=zzz'), 200, fn($j) => count($j['products']) === 12);
expect('single product', call('GET', '/api/product.php?id=1'), 200, fn($j) => $j['product']['name'] === 'Disciples Tee' && $j["product"]["price"] == 350);
expect('missing product is 404', call('GET', '/api/product.php?id=99999'), 404);
expect('bad product id is 422', call('GET', '/api/product.php?id=abc'), 422);
expect('devotionals (12)', call('GET', '/api/devotionals.php'), 200, fn($j) => count($j['devotionals']) === 12);
expect('wrong method is 405', call('GET', '/api/login.php'), 405);

section('Register');
$email = "tester.$uniq@example.com";
expect('weak password (no number)', call('POST', '/api/register.php', ['fullname' => 'Test User', 'email' => $email, 'password' => 'onlyletters']), 422);
expect('weak password (too short)', call('POST', '/api/register.php', ['fullname' => 'Test User', 'email' => $email, 'password' => 'a1']), 422);
expect('bad email', call('POST', '/api/register.php', ['fullname' => 'Test User', 'email' => 'not-an-email', 'password' => 'Passw0rd!x']), 422);
expect('missing name', call('POST', '/api/register.php', ['fullname' => '', 'email' => $email, 'password' => 'Passw0rd!x']), 422);
expect('array instead of string does not crash', call('POST', '/api/register.php', ['fullname' => ['x'], 'email' => [$email], 'password' => ['p']]), 422);
expect('garbage body is 400, not a crash', call('POST', '/api/register.php', '{not json', null, true), 400);
$reg = call('POST', '/api/register.php', ['fullname' => 'Test User', 'email' => strtoupper($email), 'password' => 'Passw0rd!x']);
expect('register succeeds (email lower-cased)', $reg, 201, fn($j) => !empty($j['token']) && $j['user']['email'] === $email && $j['user']['role'] === 'customer');
check('response never contains a password', strpos($reg['text'], 'assw') === false || strpos($reg['text'], '"password"') === false);
expect('duplicate email is 409', call('POST', '/api/register.php', ['fullname' => 'Again', 'email' => $email, 'password' => 'Passw0rd!x']), 409);
$tok = $reg['json']['token'] ?? '';

section('Login');
expect('wrong password', call('POST', '/api/login.php', ['email' => $email, 'password' => 'WrongPass1']), 401);
expect('unknown email', call('POST', '/api/login.php', ['email' => "nobody.$uniq@example.com", 'password' => 'Passw0rd!x']), 401);
expect('empty fields', call('POST', '/api/login.php', ['email' => '', 'password' => '']), 422);
expect('SQL injection text is just a wrong login', call('POST', '/api/login.php', ['email' => "' OR 1=1 --", 'password' => "' OR '1'='1"]), 401);
expect('login with new account', call('POST', '/api/login.php', ['email' => $email, 'password' => 'Passw0rd!x']), 200, fn($j) => !empty($j['token']));
$adm = call('POST', '/api/login.php', ['email' => 'admin@repent.co.za', 'password' => 'admin123']);
expect('seeded admin can log in (bcrypt hash verifies)', $adm, 200, fn($j) => $j['user']['role'] === 'admin');
$admTok = $adm['json']['token'] ?? '';
expect('seeded customer can log in', call('POST', '/api/login.php', ['email' => 'user@repent.co.za', 'password' => 'user123']), 200);
expect('seeded demo customer (Demo@1234)', call('POST', '/api/login.php', ['email' => 'thandeka.mokoena@example.com', 'password' => 'Demo@1234']), 200);

section('Profile');
expect('profile needs a token', call('GET', '/api/me.php'), 401);
expect('tampered token rejected', call('GET', '/api/me.php', null, $tok . 'x'), 401);
expect('garbage token rejected', call('GET', '/api/me.php', null, 'abc.def'), 401);
expect('profile loads', call('GET', '/api/me.php', null, $tok), 200, fn($j) => $j['user']['email'] === $email);
expect('invalid phone rejected', call('PUT', '/api/me.php', ['fullname' => 'Test User', 'phone' => 'call me!', 'preferred_category' => 'all'], $tok), 422);
expect('invalid category rejected', call('PUT', '/api/me.php', ['fullname' => 'Test User', 'preferred_category' => 'shoes'], $tok), 422);
expect('name too short rejected', call('PUT', '/api/me.php', ['fullname' => 'A'], $tok), 422);
expect('profile update saves', call('PUT', '/api/me.php', ['fullname' => 'Test Updated', 'phone' => '082 123 4567', 'address' => '1 Test Road', 'city' => 'Pretoria', 'postal_code' => '0002', 'preferred_category' => 'hoodies'], $tok), 200, fn($j) => $j['user']['fullname'] === 'Test Updated');
expect('profile update persisted', call('GET', '/api/me.php', null, $tok), 200, fn($j) => $j['user']['city'] === 'Pretoria' && $j['user']['preferred_category'] === 'hoodies' && $j['user']['phone'] === '082 123 4567');

section('Change password');
expect('needs sign-in', call('POST', '/api/change_password.php', ['current_password' => 'a', 'new_password' => 'b']), 401);
expect('empty fields', call('POST', '/api/change_password.php', ['current_password' => '', 'new_password' => ''], $tok), 422);
expect('wrong current password', call('POST', '/api/change_password.php', ['current_password' => 'Nope12345', 'new_password' => 'NewPassw0rd'], $tok), 403);
expect('weak new password', call('POST', '/api/change_password.php', ['current_password' => 'Passw0rd!x', 'new_password' => 'short'], $tok), 422);
expect('new equals old', call('POST', '/api/change_password.php', ['current_password' => 'Passw0rd!x', 'new_password' => 'Passw0rd!x'], $tok), 422);
$chg = call('POST', '/api/change_password.php', ['current_password' => 'Passw0rd!x', 'new_password' => 'NewPassw0rd'], $tok);
expect('password changes', $chg, 200, fn($j) => !empty($j['token']));
expect('old token is now invalid', call('GET', '/api/me.php', null, $tok), 401);
$tok = $chg['json']['token'] ?? '';
expect('fresh token works', call('GET', '/api/me.php', null, $tok), 200);
expect('old password no longer logs in', call('POST', '/api/login.php', ['email' => $email, 'password' => 'Passw0rd!x']), 401);
expect('new password logs in', call('POST', '/api/login.php', ['email' => $email, 'password' => 'NewPassw0rd']), 200);

section('Checkout and orders');
$ship = ['shipping_address' => '1 Test Road', 'city' => 'Pretoria', 'postal_code' => '0002', 'phone' => '082 123 4567', 'payment_method' => 'eft'];
expect('needs sign-in', call('POST', '/api/checkout.php', $ship + ['items' => [['product_id' => 1, 'quantity' => 1]]]), 401);
expect('empty cart', call('POST', '/api/checkout.php', $ship + ['items' => []], $tok), 422);
expect('missing address', call('POST', '/api/checkout.php', array_merge($ship, ['shipping_address' => '', 'items' => [['product_id' => 1, 'quantity' => 1]]]), $tok), 422);
expect('bad postal code', call('POST', '/api/checkout.php', array_merge($ship, ['postal_code' => '!!', 'items' => [['product_id' => 1, 'quantity' => 1]]]), $tok), 422);
expect('bad phone', call('POST', '/api/checkout.php', array_merge($ship, ['phone' => 'abc', 'items' => [['product_id' => 1, 'quantity' => 1]]]), $tok), 422);
expect('quantity 0', call('POST', '/api/checkout.php', $ship + ['items' => [['product_id' => 1, 'quantity' => 0]]], $tok), 422);
expect('negative quantity', call('POST', '/api/checkout.php', $ship + ['items' => [['product_id' => 1, 'quantity' => -5]]], $tok), 422);
expect('unknown product', call('POST', '/api/checkout.php', $ship + ['items' => [['product_id' => 99999, 'quantity' => 1]]], $tok), 422);
expect('items not an array', call('POST', '/api/checkout.php', $ship + ['items' => 'lots'], $tok), 422);

$stockBefore = call('GET', '/api/product.php?id=3')['json']['product']['stock'];
expect('more than the stock is refused (409)', call('POST', '/api/checkout.php', $ship + ['items' => [['product_id' => 3, 'quantity' => 20], ['product_id' => 8, 'quantity' => 1]]], $tok), 409);
check('refused order changed no stock (atomic)', call('GET', '/api/product.php?id=3')['json']['product']['stock'] === $stockBefore && true);

$s8 = call('GET', '/api/product.php?id=8')['json']['product']['stock'];
$o1 = call('POST', '/api/checkout.php', $ship + ['items' => [['product_id' => 8, 'quantity' => 1, 'price' => 0.01]]], $tok);
expect('1 cap: R280 + R95 shipping (client price ignored)', $o1, 201, fn($j) => $j['subtotal'] == 280 && $j['shipping'] == 95 && $j['total'] == 375);
check('stock reduced by 1', call('GET', '/api/product.php?id=8')['json']['product']['stock'] === $s8 - 1);
$o2 = call('POST', '/api/checkout.php', array_merge($ship, ['items' => [['product_id' => 8, 'quantity' => 2], ['product_id' => 9, 'quantity' => 1], ['product_id' => 8, 'quantity' => 1]], 'payment_method' => 'card']), $tok);
expect('4 caps R1120: free shipping, duplicate lines merged', $o2, 201, fn($j) => $j['subtotal'] == 1120 && $j['shipping'] == 0 && $j['total'] == 1120);
$ords = call('GET', '/api/orders.php', null, $tok);
expect('my orders lists both, newest first', $ords, 200, fn($j) => count($j['orders']) === 2 && $j['orders'][0]['id'] > $j['orders'][1]['id'] && $j['orders'][0]['payment_method'] === 'card');
expect('order items saved with names', $ords, 200, fn($j) => $j['orders'][1]['items'][0]['product_name'] === 'Cap — Coral' && $j['orders'][1]['items'][0]['quantity'] === 1);
$seeded = call('POST', '/api/login.php', ['email' => 'user@repent.co.za', 'password' => 'user123'])['json']['token'] ?? '';
expect('seeded customer sees only their own orders', call('GET', '/api/orders.php', null, $seeded), 200, fn($j) => count($j['orders']) === 1);

section('Prayer requests and newsletter');
expect('prayer needs a name', call('POST', '/api/prayer.php', ['name' => '', 'prayer' => 'x']), 422);
expect('prayer needs text', call('POST', '/api/prayer.php', ['name' => 'A', 'prayer' => '   ']), 422);
expect('prayer bad email', call('POST', '/api/prayer.php', ['name' => 'A', 'prayer' => 'x', 'email' => 'nope']), 422);
expect('prayer saved', call('POST', '/api/prayer.php', ['name' => 'Tester', 'subject' => 'Other', 'prayer' => "Please pray for my exams. It's important — thank you 🙏", 'is_private' => true]), 201);
expect('subscribe needs valid email', call('POST', '/api/newsletter.php', ['name' => 'A', 'email' => 'bad']), 422);
expect('subscribe', call('POST', '/api/newsletter.php', ['name' => 'Tester', 'email' => "sub.$uniq@example.com", 'frequency' => 'daily']), 201, fn($j) => $j['status'] === 'subscribed');
expect('subscribe twice -> already', call('POST', '/api/newsletter.php', ['name' => 'Tester', 'email' => "sub.$uniq@example.com", 'frequency' => 'daily']), 200, fn($j) => $j['status'] === 'already');

section('Admin');
foreach (['stats', 'orders', 'prayers', 'subscribers', 'products'] as $e) {
    expect("admin/$e refuses no token", call('GET', "/api/admin/$e.php"), 401);
    expect("admin/$e refuses a customer", call('GET', "/api/admin/$e.php", null, $tok), 403);
}
expect('stats', call('GET', '/api/admin/stats.php', null, $admTok), 200, fn($j) => $j['stats']['products'] === 12 && $j['stats']['orders'] >= 14 && $j['stats']['revenue'] > 0);
$ao = call('GET', '/api/admin/orders.php', null, $admTok);
expect('all orders', $ao, 200, fn($j) => count($j['orders']) >= 14);
$newest = $ao['json']['orders'][0]['id'] ?? 0;
expect('order status: invalid refused', call('PUT', '/api/admin/orders.php', ['order_id' => $newest, 'status' => 'teleported'], $admTok), 422);
expect('order status: shipped', call('PUT', '/api/admin/orders.php', ['order_id' => $newest, 'status' => 'shipped'], $admTok), 200);
expect('prayers (private flag is boolean)', call('GET', '/api/admin/prayers.php', null, $admTok), 200, fn($j) => count($j['prayers']) >= 13 && is_bool($j['prayers'][0]['is_private']));
$subs = call('GET', '/api/admin/subscribers.php', null, $admTok);
expect('subscribers', $subs, 200, fn($j) => count($j['subscribers']) >= 13);
$np = call('POST', '/api/admin/products.php', ['name' => 'Test Beanie', 'description' => 'd', 'price' => 199.5, 'category' => 'caps', 'image' => 'cap-mint.jpg', 'stock' => 5], $admTok);
expect('add product', $np, 201, fn($j) => $j['id'] > 12);
$pid = $np['json']['id'] ?? 0;
expect('add product: bad price', call('POST', '/api/admin/products.php', ['name' => 'X', 'price' => -1, 'category' => 'caps', 'stock' => 1], $admTok), 422);
expect('add product: bad category', call('POST', '/api/admin/products.php', ['name' => 'X', 'price' => 10, 'category' => 'shoes', 'stock' => 1], $admTok), 422);
expect('edit product', call('PUT', '/api/admin/products.php', ['id' => $pid, 'name' => 'Test Beanie 2', 'description' => 'd', 'price' => 210, 'category' => 'caps', 'image' => '', 'stock' => 7], $admTok), 200);
expect('edit persisted', call('GET', "/api/product.php?id=$pid"), 200, fn($j) => $j['product']['name'] === 'Test Beanie 2' && $j['product']['stock'] === 7);
expect('delete product', call('DELETE', '/api/admin/products.php', ['id' => $pid], $admTok), 200);
expect('deleted product is gone', call('GET', "/api/product.php?id=$pid"), 404);

section('Sign out');
expect('logout', call('POST', '/api/logout.php', [], $tok), 200);
expect('token dead after logout', call('GET', '/api/me.php', null, $tok), 401);

if ($google) {
    section('Google sign-in (fake verifier)');
    $g = "g$uniq";
    expect('rejects a bad Google token', call('POST', '/api/google_login.php', ['id_token' => 'bad-token-value-123456789']), 401);
    expect('rejects an empty token', call('POST', '/api/google_login.php', ['id_token' => '']), 422);
    $g1 = call('POST', '/api/google_login.php', ['id_token' => "good:$g:google.$uniq@example.com:Gina Google"]);
    expect('new Google user is created', $g1, 200, fn($j) => $j['user']['email'] === "google.$uniq@example.com" && $j['user']['auth_provider'] === 'google' && $j['user']['fullname'] === 'Gina Google');
    $g2 = call('POST', '/api/google_login.php', ['id_token' => "good:$g:google.$uniq@example.com:Gina Google"]);
    expect('same Google account -> same user (no duplicate)', $g2, 200, fn($j) => $j['user']['id'] === $g1['json']['user']['id']);
    expect('Google token works on the API', call('GET', '/api/me.php', null, $g2['json']['token'] ?? ''), 200);
    expect('Google users have no password to change', call('POST', '/api/change_password.php', ['current_password' => 'abc12345', 'new_password' => 'abc123456'], $g2['json']['token'] ?? ''), 400);
    $link = call('POST', '/api/google_login.php', ['id_token' => "good:link$g:user@repent.co.za:Demo User"]);
    expect('Google links to an existing email account', $link, 200, fn($j) => $j['user']['id'] === 2 && $j['user']['role'] === 'customer');
    expect('...and the password still works afterwards', call('POST', '/api/login.php', ['email' => 'user@repent.co.za', 'password' => 'user123']), 200);

    // Website endpoint: same account, PHP session cookie
    $jar = tempnam(sys_get_temp_dir(), 'jar');
    $ch = curl_init("$base/pages/google_login.php");
    curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => ['credential' => "good:$g:google.$uniq@example.com:Gina Google"], CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIEJAR => $jar, CURLOPT_COOKIEFILE => $jar]);
    $txt = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    $j = json_decode($txt, true);
    check('website Google sign-in succeeds', $code === 200 && ($j['success'] ?? false) === true, "($code) $txt");
    $ch = curl_init("$base/index.php");
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIEFILE => $jar]);
    $home = (string)curl_exec($ch); curl_close($ch);
    check('website shows the signed-in Google user', stripos($home, 'Gina') !== false || stripos($home, 'logout') !== false);
    $ch = curl_init("$base/pages/google_login.php");
    curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => ['credential' => 'forged'], CURLOPT_RETURNTRANSFER => true]);
    curl_exec($ch); $c = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    check('website rejects a forged Google credential', $c === 401);
    @unlink($jar);
}

echo "\n" . ($fail === 0 ? "ALL PASSED" : "FAILED") . ": $pass passed, $fail failed\n";
exit($fail === 0 ? 0 : 1);
