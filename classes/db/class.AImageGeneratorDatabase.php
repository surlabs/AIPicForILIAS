<?php

namespace db;

use exceptions\AImageGeneratorException;
use interfaces\BasicDbChildInterface;

class AImageGeneratorDatabase extends BasicDb implements BasicDbChildInterface {
    const ALLOWED_TABLES = [
        'aig_config'
    ];

    public function __construct() {

        parent::__construct(self::ALLOWED_TABLES);
    }


    public function throwException(string $message): void {
        throw new AImageGeneratorException($message);
    }

    /**
     * @throws \Exception
     */
    public function getConfig(): array {
        return $this->select('aig_config', null, null);
    }

}