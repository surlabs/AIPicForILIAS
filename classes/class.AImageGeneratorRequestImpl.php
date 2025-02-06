<?php

class AImageGeneratorRequestImpl extends AImageGeneratorRequestAbstract {

    public function __construct(string $url, array $header, ?array $body) {
        parent::__construct($url, $header, $body);
    }

}