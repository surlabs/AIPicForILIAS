<?php

use platform\AImageGeneratorConfig;

class AImageGeneratorRequestImpl extends AImageGeneratorRequestAbstract {

    private ?string $autheticationKeyLabel;
    private ?string $autheticationValue;
    private ?string $additionalHeaderOptions;
    private ?string $additionalRequestBodyOptions;

    public function __construct() {
        parent::__construct();
    }

    public static function from(AImageGeneratorConfig $config): AImageGeneratorRequestImpl {
        $generatorRequest = new AImageGeneratorRequestImpl();
        $generatorRequest->withUrl($config->getApiUrl());
        $generatorRequest->withPromptContext($config->getPromptContext());
        $generatorRequest->withRequestPromptKey($config->getRequestBodyPromptKey());
        $generatorRequest->withResponseKey($config->getResponseKey());
        $generatorRequest->withResponseSubkey($config->getResponseSubkey());
        $generatorRequest->withAuthenticationKeyLabel($config->getAutheticationKeyLabel());
        $generatorRequest->withAuthenticationValue($config->getAutheticationValue());
        $generatorRequest->withAdditionalHeaderOptions($config->getAdditionalHeaderOptions());
        $generatorRequest->withAdditionalRequestBodyOptions($config->getAdditionalRequestBodyOptions());

        $generatorRequest->withBody(AImageGeneratorRequestAbstract::getArrayFromString($generatorRequest->getAdditionalRequestBodyOptions()));
        $generatorRequest->generateHeader();
        return $generatorRequest;
    }

    public function withAuthenticationKeyLabel(string $autheticationKeyLabel): AImageGeneratorRequestImpl {
        $this->autheticationKeyLabel = $autheticationKeyLabel;
        return $this;
    }

    public function withAuthenticationValue(string $autheticationValue): AImageGeneratorRequestImpl {
        $this->autheticationValue = $autheticationValue;
        return $this;
    }

    public function withAdditionalHeaderOptions(string $additionalHeaderOptions): AImageGeneratorRequestImpl {
        $this->additionalHeaderOptions = $additionalHeaderOptions;
        return $this;
    }

    public function withAdditionalRequestBodyOptions(string $additionalRequestBodyOptions): AImageGeneratorRequestImpl {
        $this->additionalRequestBodyOptions = $additionalRequestBodyOptions;
        return $this;
    }

    public function generateHeader(): array {
        $headerOptions = [];
        if($this->autheticationKeyLabel && $this->autheticationValue) {
            $headerOptions[] = $this->autheticationKeyLabel . ": " . $this->autheticationValue;
        }
        $this->withHeader(array_merge($headerOptions, AImageGeneratorRequestAbstract::getArrayFromString($this->getAdditionalHeaderOptions())));
        return $headerOptions;
    }

    public function getAuthenticationKeyLabel(): ?string {
        return $this->autheticationKeyLabel;
    }

    public function getAutheticationKeyLabel(): ?string
    {
        return $this->autheticationKeyLabel;
    }

    public function getAutheticationValue(): ?string
    {
        return $this->autheticationValue;
    }

    public function getAdditionalHeaderOptions(): ?string
    {
        return $this->additionalHeaderOptions;
    }

    public function getAdditionalRequestBodyOptions(): ?string
    {
        return $this->additionalRequestBodyOptions;
    }

}