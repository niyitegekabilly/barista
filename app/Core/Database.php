<?php

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

class Database
{
    private static ?PDO $pdo = null;
    private static ?self $instance = null;

    // ── Singleton instance (for $this->db() in controllers) ──────────────────
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // ── PDO connection ────────────────────────────────────────────────────────
    public static function connect(): PDO
    {
        if (self::$pdo === null) {
            $config = config('database.connections.mysql');
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset']
            );

            try {
                self::$pdo = new PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
                    $config['options']
                );
            } catch (PDOException $e) {
                if (config('app.debug', false)) {
                    throw new RuntimeException('Database Connection Error: ' . $e->getMessage(), (int)$e->getCode(), $e);
                }
                error_log('Database Connection Error: ' . $e->getMessage());
                throw new RuntimeException('Could not connect to the database. Please try again later.');
            }
        }

        return self::$pdo;
    }

    // ── Core query methods (supports both Database::fetchAll() and $db->fetchAll()) ─────
    public static function query(string $sql, array $params = []): \PDOStatement
    {
        return static::staticQuery($sql, $params);
    }

    public static function staticQuery(string $sql, array $params = []): \PDOStatement
    {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetchAll(string $sql, array $params = []): array
    {
        return static::query($sql, $params)->fetchAll();
    }

    public static function fetchOne(string $sql, array $params = []): ?array
    {
        $result = static::query($sql, $params)->fetch();
        return $result ?: null;
    }

    public static function fetchValue(string $sql, array $params = []): mixed
    {
        return static::query($sql, $params)->fetchColumn();
    }

    /**
     * INSERT helper — returns new row ID.
     */
    public static function insert(string $table, array $data): int
    {
        $columns      = implode(', ', array_map(fn($c) => "`$c`", array_keys($data)));
        $placeholders = implode(', ', array_map(fn($c) => ":$c", array_keys($data)));
        static::query("INSERT INTO `$table` ($columns) VALUES ($placeholders)", $data);
        return (int)self::connect()->lastInsertId();
    }

    /**
     * UPDATE helper.
     * $where can be:
     *   - string: "id = 5"            (no extra params)
     *   - array:  ['id' => 5]         (recommended)
     */
    public static function update(string $table, array $data, string|array $where, array $whereParams = []): int
    {
        $set    = [];
        $params = [];

        foreach ($data as $col => $val) {
            $set[]              = "`$col` = :set_$col";
            $params["set_$col"] = $val;
        }

        if (is_array($where)) {
            $whereParts = [];
            foreach ($where as $col => $val) {
                $whereParts[]       = "`$col` = :wh_$col";
                $params["wh_$col"]  = $val;
            }
            $whereStr = implode(' AND ', $whereParts);
        } else {
            $whereStr = $where;
            $params   = array_merge($params, $whereParams);
        }

        $stmt = static::query("UPDATE `$table` SET " . implode(', ', $set) . " WHERE $whereStr", $params);
        return $stmt->rowCount();
    }

    public static function delete(string $table, string|array $where, array $params = []): int
    {
        if (is_array($where)) {
            $parts = [];
            foreach ($where as $col => $val) {
                $parts[]         = "`$col` = :$col";
                $params[":$col"] = $val;
            }
            $whereStr = implode(' AND ', $parts);
        } else {
            $whereStr = $where;
        }
        return static::query("DELETE FROM `$table` WHERE $whereStr", $params)->rowCount();
    }

    // ── Transactions ──────────────────────────────────────────────────────────
    public function beginTransaction(): bool  { return self::connect()->beginTransaction(); }
    public function commit(): bool            { return self::connect()->commit(); }
    public function rollBack(): bool
    {
        if (self::connect()->inTransaction()) {
            return self::connect()->rollBack();
        }
        return false;
    }

    public function transaction(callable $callback): mixed
    {
        $this->beginTransaction();
        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->rollBack();
            throw $e;
        }
    }

    // ── Static shims (backward compat with Model base class) ─────────────────
    public static function staticFetchOne(string $sql, array $params = []): ?array
    {
        $result = self::staticQuery($sql, $params)->fetch();
        return $result ?: null;
    }
    public static function staticFetchAll(string $sql, array $params = []): array
    {
        return self::staticQuery($sql, $params)->fetchAll();
    }
}
