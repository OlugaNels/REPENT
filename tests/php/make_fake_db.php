<?php
/** TEST ONLY: php make_fake_db.php <repent_database.sql> <out.sqlite>
 *  Mechanically converts the MySQL schema+seed into SQLite so tests can run without MySQL. */
[$self, $in, $out] = $argv + [null, null, null];
if (!$in || !$out) { fwrite(STDERR, "usage: make_fake_db.php in.sql out.sqlite\n"); exit(2); }
$sql = file_get_contents($in);
$sql = preg_replace('/^(CREATE DATABASE|USE|SET FOREIGN_KEY_CHECKS|DROP TABLE).*$/mi', '', $sql);
$sql = str_replace('INT AUTO_INCREMENT PRIMARY KEY', 'INTEGER PRIMARY KEY AUTOINCREMENT', $sql);
$sql = preg_replace('/ENUM\([^)]*\)/', 'TEXT', $sql);
$sql = preg_replace('/\)\s*ENGINE=\w+[^;]*;/', ');', $sql);
$sql = preg_replace('/TINYINT\(1\)/', 'INTEGER', $sql);
$sql = preg_replace('/DECIMAL\(\d+,\s*\d+\)/', 'NUMERIC', $sql);
@unlink($out);
$pdo = new PDO('sqlite:' . $out);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec($sql);
echo "created $out\n";
