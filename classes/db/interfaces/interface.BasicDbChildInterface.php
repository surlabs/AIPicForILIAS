<?php

namespace interfaces;

use Exception;

interface BasicDbChildInterface {

    /**
     * @throws Exception
     */
    public function throwException(string $message): void;
}