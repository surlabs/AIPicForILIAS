<?php

namespace platform;

use ilDBInterface;
use platform\BasicDb;
use platform\interfaces\BasicDbChildInterface;

class AImageGeneratorDatabase extends BasicDb implements BasicDbChildInterface {
    const ALLOWED_TABLES = [
        'aimage_generator'
    ];

    public function __construct() {

        parent::__construct(self::ALLOWED_TABLES);
    }

    /**
     * @throws ToDoListException
     */
    public function throwException(string $message): void {
        throw new ToDoListException($message);
    }

    /**
     * @throws \Exception
     */
    public function getGeneratedImages(int $userId, string $orderBy = "title ASC"): array {
        return $this->select('aimage_generator', ['user_id' => $userId], null);
    }

}