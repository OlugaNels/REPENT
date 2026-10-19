<?php
/**
 * TEST ONLY — a tiny mysqli look-alike backed by SQLite (PDO).
 * It lets the API test-suite run on machines without MySQL. It supports just the
 * mysqli calls the REPENT† code uses: prepare/bind_param/execute/get_result,
 * query, insert_id, affected_rows, begin_transaction/commit/rollback.
 * The real site always uses the real mysqli (see includes/db.php).
 */
class FakeResult {
    public int $num_rows;
    private array $rows; private int $i = 0;
    public function __construct(array $rows) { $this->rows = $rows; $this->num_rows = count($rows); }
    public function fetch_assoc(): ?array { return $this->rows[$this->i++] ?? null; }
    public function fetch_all(int $mode = MYSQLI_NUM): array { return $this->rows; }
}

class FakeStmt {
    public int $affected_rows = 0;
    private PDOStatement $st; private FakeMysqli $db;
    private array $refs = []; private string $types = ''; private ?FakeResult $result = null;
    public function __construct(PDOStatement $st, FakeMysqli $db) { $this->st = $st; $this->db = $db; }
    public function bind_param(string $types, &...$vars): bool { $this->types = $types; $this->refs = []; foreach ($vars as $i => &$v) $this->refs[$i] = &$v; return true; }
    public function execute(): bool {
        $vals = [];
        foreach ($this->refs as $i => $v) {
            $t = $this->types[$i] ?? 's';
            $vals[] = $v === null ? null : ($t === 'i' ? (int)$v : ($t === 'd' ? (float)$v : (string)$v));
        }
        try { $this->st->execute($vals); }
        catch (PDOException $e) { throw FakeMysqli::wrap($e); }
        $this->affected_rows = $this->st->rowCount();
        $this->result = null;
        if ($this->st->columnCount() > 0) $this->result = new FakeResult($this->st->fetchAll(PDO::FETCH_ASSOC));
        else $this->db->insert_id = (int)$this->db->pdo->lastInsertId();
        return true;
    }
    public function get_result(): ?FakeResult { return $this->result; }
}

class FakeMysqli {
    public PDO $pdo; public int $insert_id = 0;
    public function __construct(string $file) {
        $this->pdo = new PDO('sqlite:' . $file);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec('PRAGMA foreign_keys = ON');
    }
    public static function wrap(PDOException $e): mysqli_sql_exception {
        $dup = stripos($e->getMessage(), 'UNIQUE constraint failed') !== false;
        return new mysqli_sql_exception(($dup ? 'Duplicate entry: ' : '') . $e->getMessage(), $dup ? 1062 : 1);
    }
    public function prepare(string $sql): FakeStmt {
        try { return new FakeStmt($this->pdo->prepare($sql), $this); }
        catch (PDOException $e) { throw self::wrap($e); }
    }
    public function query(string $sql) {
        try { $st = $this->pdo->query($sql); }
        catch (PDOException $e) { throw self::wrap($e); }
        if ($st->columnCount() > 0) return new FakeResult($st->fetchAll(PDO::FETCH_ASSOC));
        $this->insert_id = (int)$this->pdo->lastInsertId();
        return true;
    }
    public function begin_transaction(): bool { return $this->pdo->inTransaction() ? true : $this->pdo->beginTransaction(); }
    public function commit(): bool { return $this->pdo->inTransaction() ? $this->pdo->commit() : true; }
    public function rollback(): bool { return $this->pdo->inTransaction() ? $this->pdo->rollBack() : true; }
    public function set_charset(string $c): bool { return true; }
    public function close(): bool { return true; }
}
