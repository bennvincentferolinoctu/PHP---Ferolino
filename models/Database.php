<?php
// models/Database.php
// Thin wrapper around PDO so model classes can use a common interface.
class Database {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function pdo(): PDO {
        return $this->pdo;
    }

    // Run a SELECT and return all rows
    public function select(string $table, string $columns = '*', array $filters = [], array $options = []): array {
        $sql = 'SELECT ' . $columns . ' FROM ' . $table;
        $where = $this->buildWhere($filters);
        if ($where !== '') {
            $sql .= ' WHERE ' . $where;
        }
        if (isset($options['order'])) {
            $sql .= ' ORDER BY ' . $options['order'];
        }
        if (isset($options['limit'])) {
            $sql .= ' LIMIT ' . (int)$options['limit'];
        }
        if (isset($options['offset'])) {
            $sql .= ' OFFSET ' . (int)$options['offset'];
        }
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(string $table, int $id, string $pk = 'id'): ?array {
        $stmt = $this->pdo->prepare('SELECT * FROM ' . $table . ' WHERE ' . $pk . ' = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function insert(string $table, array $data): array {
        $cols = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $stmt = $this->pdo->prepare('INSERT INTO ' . $table . ' (' . $cols . ') VALUES (' . $placeholders . ') RETURNING *');
        $stmt->execute($data);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: $data;
    }

    public function update(string $table, array $data, array $filters = []): array {
        $set = [];
        foreach (array_keys($data) as $k) {
            $set[] = $k . ' = :' . $k;
        }
        $sql = 'UPDATE ' . $table . ' SET ' . implode(', ', $set);
        $where = $this->buildWhere($filters, $data);
        if ($where !== '') {
            $sql .= ' WHERE ' . $where;
        }
        $sql .= ' RETURNING *';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: [];
    }

    public function delete(string $table, array $filters = []): array {
        $sql = 'DELETE FROM ' . $table;
        $where = $this->buildWhere($filters);
        if ($where !== '') {
            $sql .= ' WHERE ' . $where;
        }
        $sql .= ' RETURNING *';
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function buildWhere(array $filters, array $params = []): string {
        // filters is an array of raw SQL fragments like "crop_id=eq.5" (Supabase style)
        // or simple "column = value" strings. Convert "col=eq.val" -> "col = val".
        $parts = [];
        foreach ($filters as $f) {
            if (strpos($f, '=eq.') !== false) {
                [$col, $val] = explode('=eq.', $f, 2);
                $parts[] = $col . " = " . $this->quote($val);
            } elseif (strpos($f, '=ilike.') !== false) {
                [$col, $val] = explode('=ilike.', $f, 2);
                $parts[] = $col . " ILIKE " . $this->quote($val);
            } elseif (strpos($f, 'or=(') === 0) {
                // or=(a.ilike.%x%,b.ilike.%y%)
                $inner = substr($f, 4, -1);
                $conds = [];
                foreach (explode(',', $inner) as $c) {
                    if (strpos($c, '.ilike.') !== false) {
                        [$col, $val] = explode('.ilike.', $c, 2);
                        $conds[] = $col . " ILIKE " . $this->quote($val);
                    } elseif (strpos($c, '.eq.') !== false) {
                        [$col, $val] = explode('.eq.', $c, 2);
                        $conds[] = $col . " = " . $this->quote($val);
                    }
                }
                $parts[] = '(' . implode(' OR ', $conds) . ')';
            } else {
                $parts[] = $f;
            }
        }
        return implode(' AND ', $parts);
    }

    private function quote(string $v): string {
        // handle percent wildcards
        if (is_numeric($v)) {
            return $v;
        }
        return "'" . str_replace("'", "''", $v) . "'";
    }
}
?>