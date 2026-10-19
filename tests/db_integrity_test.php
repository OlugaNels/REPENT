<?php
/**
 * Checks the seeded database against the assignment rules:
 * every table has 10+ rows, no orphan rows, order totals add up, stock is sane.
 *   php tests/db_integrity_test.php          (uses includes/db.php, i.e. the real MySQL)
 *   REPENT_TEST_DB=file.sqlite php tests/db_integrity_test.php   (SQLite copy, no MySQL needed)
 */
if (getenv('REPENT_TEST_DB')) {
    require __DIR__ . '/php/FakeMysqli.php';
    $conn = new FakeMysqli(getenv('REPENT_TEST_DB'));
} else {
    require __DIR__ . '/../includes/db.php';
    if (!$conn) { fwrite(STDERR, "Cannot connect to MySQL.\n"); exit(2); }
}
$pass = 0; $fail = 0;
function check(string $n, bool $ok, string $d = ''): void { global $pass, $fail; if ($ok) { $pass++; echo "  ok   $n\n"; } else { $fail++; echo "  FAIL $n $d\n"; } }
function one($conn, string $sql) { $r = $conn->query($sql)->fetch_assoc(); return array_values($r)[0]; }

foreach (['users','products','orders','order_items','prayer_requests','newsletter_subscribers','devotionals'] as $t) {
    $n = (int)one($conn, "SELECT COUNT(*) FROM $t");
    check("$t has 10+ rows ($n)", $n >= 10);
}
check('orders belong to existing users', (int)one($conn, "SELECT COUNT(*) FROM orders o LEFT JOIN users u ON u.id=o.user_id WHERE u.id IS NULL") === 0);
check('order_items belong to existing orders', (int)one($conn, "SELECT COUNT(*) FROM order_items i LEFT JOIN orders o ON o.id=i.order_id WHERE o.id IS NULL") === 0);
check('order_items point at existing products', (int)one($conn, "SELECT COUNT(*) FROM order_items i LEFT JOIN products p ON p.id=i.product_id WHERE p.id IS NULL") === 0);
$bad = (int)one($conn, "SELECT COUNT(*) FROM orders o WHERE ABS(o.total - o.shipping - (SELECT COALESCE(SUM(i.price*i.quantity),0) FROM order_items i WHERE i.order_id=o.id)) > 0.005");
check('every order total = items + shipping', $bad === 0, "($bad wrong)");
$badShip = (int)one($conn, "SELECT COUNT(*) FROM orders o WHERE o.shipping <> CASE WHEN o.total - o.shipping >= 800 THEN 0 ELSE 95 END");
check('shipping rule holds (free from R800, else R95)', $badShip === 0);
check('no negative stock or prices', (int)one($conn, "SELECT COUNT(*) FROM products WHERE stock < 0 OR price < 0") === 0);
check('passwords are bcrypt hashes', (int)one($conn, "SELECT COUNT(*) FROM users WHERE password NOT LIKE '\$2y\$%'") === 0);
check('exactly the expected admin exists', (int)one($conn, "SELECT COUNT(*) FROM users WHERE role='admin' AND email='admin@repent.co.za'") === 1);
check('emails are unique', (int)one($conn, "SELECT COUNT(*) - COUNT(DISTINCT email) FROM users") === 0);
check('every seed password verifies', (function () use ($conn) {
    $known = ['admin@repent.co.za' => 'admin123', 'user@repent.co.za' => 'user123'];
    $res = $conn->query("SELECT email, password FROM users");
    while ($r = $res->fetch_assoc()) {
        $pw = $known[$r['email']] ?? 'Demo@1234';
        if (!password_verify($pw, $r['password'])) return false;
    }
    return true;
})());
echo "\n" . ($fail === 0 ? "ALL PASSED" : "FAILED") . ": $pass passed, $fail failed\n";
exit($fail === 0 ? 0 : 1);
