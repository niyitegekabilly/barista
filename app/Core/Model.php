<?php

namespace App\Core;

abstract class Model {
    protected static string $table = '';
    protected static string $primaryKey = 'id';

    protected array $builderWheres = [];
    protected array $builderParams = [];
    protected string $builderOrderBy = '';
    protected ?int $builderLimit = null;
    protected ?int $builderOffset = null;
    protected array $builderWith = [];

    public static function getTable(): string {
        return static::$table;
    }

    public static function all(string $orderBy = 'id DESC'): array {
        $table = static::$table;
        return Database::fetchAll("SELECT * FROM `{$table}` ORDER BY {$orderBy}");
    }

    public static function find(int|string $id): ?array {
        $table = static::$table;
        $pk = static::$primaryKey;
        return Database::fetchOne("SELECT * FROM `{$table}` WHERE `{$pk}` = :id LIMIT 1", ['id' => $id]);
    }

    public static function findBy(string $column, mixed $value): ?array {
        $table = static::$table;
        return Database::fetchOne("SELECT * FROM `{$table}` WHERE `{$column}` = :val LIMIT 1", ['val' => $value]);
    }

    /**
     * Supports both static calls Model::where('is_published = 1')
     * and fluent calls (new Model())->where('is_published', 1)->get()
     */
    public static function where(string $columnOrSql, mixed $valOrParams = null, string $orderBy = 'id DESC', ?int $limit = null, ?int $offset = null): mixed {
        $instance = new static();

        // Legacy/Static format: Model::where("is_published = :p", ['p' => 1], "id DESC", 10)
        if (is_array($valOrParams) || ($valOrParams === null && (str_contains($columnOrSql, ' ') || str_contains($columnOrSql, '=')))) {
            $table = static::$table;
            $params = is_array($valOrParams) ? $valOrParams : [];
            $sql = "SELECT * FROM `{$table}` WHERE {$columnOrSql} ORDER BY {$orderBy}";
            if ($limit !== null) {
                $sql .= " LIMIT {$limit}";
                if ($offset !== null) {
                    $sql .= " OFFSET {$offset}";
                }
            }
            return Database::fetchAll($sql, $params);
        }

        // Fluent format: ->where('is_published', 1)
        $paramKey = 'p_' . count($instance->builderWheres) . '_' . preg_replace('/[^a-zA-Z0-9_]/', '', $columnOrSql);
        $instance->builderWheres[] = "`{$columnOrSql}` = :{$paramKey}";
        $instance->builderParams[$paramKey] = $valOrParams;
        return $instance;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self {
        $this->builderOrderBy = "`{$column}` {$direction}";
        return $this;
    }

    public function limit(int $limit, ?int $offset = null): self {
        $this->builderLimit = $limit;
        if ($offset !== null) {
            $this->builderOffset = $offset;
        }
        return $this;
    }

    public function with(array|string $relations): self {
        $this->builderWith = is_array($relations) ? $relations : [$relations];
        return $this;
    }

    public function get(): array {
        $table = static::$table;
        $whereSql = !empty($this->builderWheres) ? 'WHERE ' . implode(' AND ', $this->builderWheres) : '';
        $orderSql = !empty($this->builderOrderBy) ? 'ORDER BY ' . $this->builderOrderBy : 'ORDER BY id DESC';
        $limitSql = '';
        if ($this->builderLimit !== null) {
            $limitSql = 'LIMIT ' . $this->builderLimit;
            if ($this->builderOffset !== null) {
                $limitSql .= ' OFFSET ' . $this->builderOffset;
            }
        }

        $sql = "SELECT * FROM `{$table}` {$whereSql} {$orderSql} {$limitSql}";
        $records = Database::fetchAll(trim($sql), $this->builderParams);

        if (!empty($this->builderWith) && !empty($records)) {
            foreach ($records as &$row) {
                foreach ($this->builderWith as $relation) {
                    if (($relation === 'student' || $relation === 'user' || $relation === 'author') && !empty($row['user_id'])) {
                        $user = Database::fetchOne("SELECT u.id, u.name, u.email, p.avatar, p.headline FROM users u LEFT JOIN user_profiles p ON u.id = p.user_id WHERE u.id = :uid", ['uid' => $row['user_id']]);
                        $row['student'] = $user;
                        $row['user'] = $user;
                        $row['author'] = $user;
                        $row['user_name'] = $user['name'] ?? '';
                        $row['user_avatar'] = $user['avatar'] ?? null;
                    } elseif ($relation === 'course' && !empty($row['course_id'])) {
                        $row['course'] = Database::fetchOne("SELECT * FROM courses WHERE id = :cid", ['cid' => $row['course_id']]);
                    } elseif ($relation === 'category' && !empty($row['category_id'])) {
                        $row['category'] = Database::fetchOne("SELECT * FROM categories WHERE id = :catid", ['catid' => $row['category_id']]);
                    }
                }
            }
        }

        return $records;
    }

    public function first(): ?array {
        $this->limit(1);
        $results = $this->get();
        return $results[0] ?? null;
    }

    public static function count(string $where = '1=1', array $params = []): int {
        $table = static::$table;
        return (int) Database::fetchValue("SELECT COUNT(*) FROM `{$table}` WHERE {$where}", $params);
    }

    public static function create(array $data): int {
        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        if (!isset($data['updated_at'])) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        return Database::insert(static::$table, $data);
    }

    public static function update(int|string $id, array $data): int {
        if (!isset($data['updated_at'])) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        $pk = static::$primaryKey;
        return Database::update(static::$table, $data, "`{$pk}` = :pk_id", ['pk_id' => $id]);
    }

    public static function delete(int|string $id): int {
        $pk = static::$primaryKey;
        return Database::delete(static::$table, "`{$pk}` = :pk_id", ['pk_id' => $id]);
    }
}
