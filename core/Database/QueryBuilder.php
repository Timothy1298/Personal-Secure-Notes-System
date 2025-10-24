<?php
namespace Core\Database;

use PDO;
use Exception;

class QueryBuilder {
    private $db;
    private $query;
    private $params = [];
    private $table;
    private $select = '*';
    private $where = [];
    private $joins = [];
    private $orderBy = [];
    private $groupBy = [];
    private $having = [];
    private $limit = null;
    private $offset = null;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function table(string $table): self {
        $this->table = $table;
        return $this;
    }

    public function select(array $columns = ['*']): self {
        $this->select = implode(', ', $columns);
        return $this;
    }

    public function where(string $column, $operator, $value = null): self {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $paramKey = ':' . uniqid();
        $this->where[] = "{$column} {$operator} {$paramKey}";
        $this->params[$paramKey] = $value;
        return $this;
    }

    public function whereIn(string $column, array $values): self {
        $placeholders = [];
        foreach ($values as $value) {
            $paramKey = ':' . uniqid();
            $placeholders[] = $paramKey;
            $this->params[$paramKey] = $value;
        }
        $this->where[] = "{$column} IN (" . implode(', ', $placeholders) . ")";
        return $this;
    }

    public function whereNull(string $column): self {
        $this->where[] = "{$column} IS NULL";
        return $this;
    }

    public function whereNotNull(string $column): self {
        $this->where[] = "{$column} IS NOT NULL";
        return $this;
    }

    public function join(string $table, string $first, string $operator, string $second, string $type = 'INNER'): self {
        $this->joins[] = "{$type} JOIN {$table} ON {$first} {$operator} {$second}";
        return $this;
    }

    public function leftJoin(string $table, string $first, string $operator, string $second): self {
        return $this->join($table, $first, $operator, $second, 'LEFT');
    }

    public function rightJoin(string $table, string $first, string $operator, string $second): self {
        return $this->join($table, $first, $operator, $second, 'RIGHT');
    }

    public function orderBy(string $column, string $direction = 'ASC'): self {
        $this->orderBy[] = "{$column} {$direction}";
        return $this;
    }

    public function groupBy(string $column): self {
        $this->groupBy[] = $column;
        return $this;
    }

    public function having(string $column, $operator, $value = null): self {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $paramKey = ':' . uniqid();
        $this->having[] = "{$column} {$operator} {$paramKey}";
        $this->params[$paramKey] = $value;
        return $this;
    }

    public function limit(int $limit, int $offset = null): self {
        $this->limit = $limit;
        if ($offset !== null) {
            $this->offset = $offset;
        }
        return $this;
    }

    public function offset(int $offset): self {
        $this->offset = $offset;
        return $this;
    }

    public function get(): array {
        $this->buildSelectQuery();
        $stmt = $this->db->prepare($this->query);
        $stmt->execute($this->params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function first(): ?array {
        $this->limit(1);
        $results = $this->get();
        return $results[0] ?? null;
    }

    public function count(): int {
        $this->select = 'COUNT(*) as count';
        $result = $this->first();
        return (int)($result['count'] ?? 0);
    }

    public function insert(array $data): int {
        $columns = implode(', ', array_keys($data));
        $placeholders = [];
        
        foreach ($data as $key => $value) {
            $paramKey = ':' . $key;
            $placeholders[] = $paramKey;
            $this->params[$paramKey] = $value;
        }
        
        $this->query = "INSERT INTO {$this->table} ({$columns}) VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->db->prepare($this->query);
        $stmt->execute($this->params);
        return $this->db->lastInsertId();
    }

    public function update(array $data): int {
        $setParts = [];
        foreach ($data as $key => $value) {
            $paramKey = ':' . $key;
            $setParts[] = "{$key} = {$paramKey}";
            $this->params[$paramKey] = $value;
        }
        
        $this->query = "UPDATE {$this->table} SET " . implode(', ', $setParts);
        $this->buildWhereClause();
        
        $stmt = $this->db->prepare($this->query);
        $stmt->execute($this->params);
        return $stmt->rowCount();
    }

    public function delete(): int {
        $this->query = "DELETE FROM {$this->table}";
        $this->buildWhereClause();
        
        $stmt = $this->db->prepare($this->query);
        $stmt->execute($this->params);
        return $stmt->rowCount();
    }

    public function raw(string $sql, array $params = []): array {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function explain(): array {
        $this->buildSelectQuery();
        $explainQuery = "EXPLAIN " . $this->query;
        $stmt = $this->db->prepare($explainQuery);
        $stmt->execute($this->params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function buildSelectQuery(): void {
        $this->query = "SELECT {$this->select} FROM {$this->table}";
        
        if (!empty($this->joins)) {
            $this->query .= ' ' . implode(' ', $this->joins);
        }
        
        $this->buildWhereClause();
        
        if (!empty($this->groupBy)) {
            $this->query .= ' GROUP BY ' . implode(', ', $this->groupBy);
        }
        
        if (!empty($this->having)) {
            $this->query .= ' HAVING ' . implode(' AND ', $this->having);
        }
        
        if (!empty($this->orderBy)) {
            $this->query .= ' ORDER BY ' . implode(', ', $this->orderBy);
        }
        
        if ($this->limit !== null) {
            $this->query .= " LIMIT {$this->limit}";
            if ($this->offset !== null) {
                $this->query .= " OFFSET {$this->offset}";
            }
        }
    }

    private function buildWhereClause(): void {
        if (!empty($this->where)) {
            $this->query .= ' WHERE ' . implode(' AND ', $this->where);
        }
    }

    public function reset(): self {
        $this->query = '';
        $this->params = [];
        $this->table = null;
        $this->select = '*';
        $this->where = [];
        $this->joins = [];
        $this->orderBy = [];
        $this->groupBy = [];
        $this->having = [];
        $this->limit = null;
        $this->offset = null;
        return $this;
    }
}