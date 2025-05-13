<?php

interface AIPicRequestInterface
{
    public function sendPrompt(string $prompt): bool|string;
    public function getImagesUrlsArray(): array;
    public function getDataArray(): array;
    public function getHeader(): array;
    public function getBody(): array;
    public function getResponse(): string|bool|null;
}