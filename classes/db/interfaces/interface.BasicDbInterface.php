<?php

namespace interfaces;

use Exception;

interface BaseDbInterface {

    /**
     * @throws Exception
     */
    public function select(string $table, ?array $where = null, ?array $columns = null, ?string $extra = ""): array;

    /**
     * @throws Exception
     */
    public function delete(string $table, array $where): void;

    /**
     * @throws Exception
     */
    public function update(string $table, array $data, array $where): void;

    /**
     * @throws Exception
     */
    public function insert(string $table, array $data): void;

    /**
     * @throws Exception
     */
    public function insertOnDuplicatedKey(string $table, array $data): void;

    /**
     * @throws Exception
     */
    public function nextId(string $table): int;

    /**
     * @throws Exception
     */
    public function validateTableNameOrThrow(string $identifier): void;

    /**
     * @throws Exception
     */
    public function throwInvalidTableNameException(string $identifier): void;


}