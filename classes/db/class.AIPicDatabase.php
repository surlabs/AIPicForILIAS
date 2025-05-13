<?php

namespace db;

use exceptions\AIPicException;
use interfaces\BasicDbChildInterface;

class AIPicDatabase extends BasicDb implements BasicDbChildInterface {
    const ALLOWED_TABLES = [
        'aip_config'
    ];

    public function __construct() {

        parent::__construct(self::ALLOWED_TABLES);
    }


    public function throwException(string $message): void {
        throw new AIPicException($message);
    }

    /**
     * @throws \Exception
     */
    public function getConfig(): array {
        return $this->select('aip_config', null, null);
    }

}