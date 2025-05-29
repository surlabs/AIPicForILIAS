<?php

namespace db;

use Exception;
use ilDBInterface;
use interfaces\BaseDbInterface;


class BasicDb implements BaseDbInterface {

    protected ilDBInterface $db;
    protected array $ALLOWED_TABLES_TEMPLATE = [];

    public function __construct(array $ALLOWED_TABLES) {
        global $DIC;

        $this->db = $DIC->database();
        $this->ALLOWED_TABLES_TEMPLATE = $ALLOWED_TABLES;
    }

    public function validateTableName(string $identifier): bool {
        return in_array($identifier, $this->ALLOWED_TABLES_TEMPLATE, true);
    }

    /**
     * @throws Exception
     */
    public function select(string $table, ?array $where = null, ?array $columns = null, ?string $extra = ""): array {

        $this->validateTableNameOrThrow($table);

        try {
            $query = "SELECT " . (isset($columns) ? implode(", ", $columns) : "*") . " FROM " . $table;

            if (isset($where)) {
                $query .= " WHERE " . implode(" AND ", array_map(function ($key, $value) {
                        return $key . " = " . $value;
                    }, array_keys($where), array_map(function ($value) {
                        return $this->db->quote($value);
                    }, array_values($where))));
            }

            if (is_string($extra)) {
                $extra = strip_tags($extra);
                $query .= " " . $extra;
            }

            $result = $this->db->query($query);

            $rows = [];

            while ($row = $this->db->fetchAssoc($result)) {
                $rows[] = $row;
            }

            return $rows;
        } catch (Exception $e) {
            $this->throwException($e->getMessage());
        }
    }

    /**
     * Deletes a row/s in the database
     *
     * @param string $table
     * @param array $where
     * @return void
     * @throws Exception
     */
    public function delete(string $table, array $where): void
    {
        $this->validateTableNameOrThrow($table);

        try {
            $this->db->query("DELETE FROM " . $table . " WHERE " . implode(" AND ", array_map(function ($key, $value) {
                    return $key . " = " . $value;
                }, array_keys($where), array_map(function ($value) {
                    return $this->db->quote($value);
                }, array_values($where)))));
        } catch (Exception $e) {
            $this->throwException($e->getMessage());
        }
    }

    /**
     * Updates a row/s in the database
     *
     * @param string $table
     * @param array $data
     * @param array $where
     * @return void
     *
     * @throws Exception
     */
    public function update(string $table, array $data, array $where): void
    {
        $this->validateTableNameOrThrow($table);

        try {
            $this->db->query("UPDATE " . $table . " SET " . implode(", ", array_map(function ($key, $value) {
                    return $key . " = " . $value;
                }, array_keys($data), array_map(function ($value) {
                    return $this->db->quote($value);
                }, array_values($data)))) . " WHERE " . implode(" AND ", array_map(function ($key, $value) {
                    return $key . " = " . $value;
                }, array_keys($where), array_map(function ($value) {
                    return $this->db->quote($value);
                }, array_values($where)))));
        } catch (Exception $e) {
            $this->throwException($e->getMessage());
        }
    }

    /**
     * Inserts a new row in the database
     *
     * @param string $table
     * @param array $data
     * @return void
     * @throws Exception
     */
    public function insert(string $table, array $data): void
    {
        $this->validateTableNameOrThrow($table);

        try {
            $this->db->query("INSERT INTO " . $table . " (" . implode(", ", array_keys($data)) . ") VALUES (" . implode(", ", array_map(function ($value) {
                    return $this->db->quote($value);
                }, array_values($data))) . ")");
        } catch (Exception $e) {
            $this->throwException($e->getMessage());
        }
    }

    /**
     * Inserts a new row in the database, if the row already exists, updates it
     *
     * @param string $table
     * @param array $data
     * @return void
     * @throws Exception
     */
    public function insertOnDuplicatedKey(string $table, array $data): void
    {
        $this->validateTableNameOrThrow($table);

        try {
            $this->db->query("INSERT INTO " . $table . " (" . implode(", ", array_keys($data)) . ") VALUES (" . implode(", ", array_map(function ($value) {
                    return $this->db->quote($value);
                }, array_values($data))) . ") ON DUPLICATE KEY UPDATE " . implode(", ", array_map(function ($key, $value) {
                    return $key . " = " . $value;
                }, array_keys($data), array_map(function ($value) {
                    return $this->db->quote($value);
                }, array_values($data)))));
        } catch (Exception $e) {
            $this->throwException($e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function nextId(string $table): int
    {
        try {
            return (int) $this->db->nextId($table);
        } catch (Exception $e) {
            $this->throwException($e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function validateTableNameOrThrow(string $identifier): void {
        if(!$this->validateTableName($identifier)) {
            $this->throwInvalidTableNameException($identifier);
        }
    }

    /**
     * @throws Exception
     */
    public function throwInvalidTableNameException(string $identifier): void {
        $this->throwException("Invalid table name: " . $identifier);
    }

    /**
     * @throws Exception
     */
    public function throwException(string $message): void {
        throw new Exception($message);
    }
}