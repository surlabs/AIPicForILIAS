<?php

use platform\AIPicConfig;

class AIPicRequestImpl extends AIPicRequestAbstract {

    private ?string $autheticationKeyLabel;
    private ?string $autheticationValue;
    private ?string $additionalHeaderOptions;
    private ?string $additionalRequestBodyOptions;
    private ?string $modelValue;


    public function __construct() {
        parent::__construct();
    }

    public static function from(AIPicConfig $config): AIPicRequestImpl {
        $generatorRequest = new AIPicRequestImpl();
        $generatorRequest->withUrl($config->getApiUrl());
        $generatorRequest->withPromptContext($config->getPromptContext());
        $generatorRequest->withRequestPromptKey($config->getRequestBodyPromptKey());
        $generatorRequest->withResponseKey($config->getResponseKey());
        $generatorRequest->withResponseSubkey($config->getResponseSubkey());
        $generatorRequest->withAuthenticationKeyLabel($config->getAutheticationKeyLabel());
        $generatorRequest->withAuthenticationValue($config->getAutheticationValue());
        $generatorRequest->withAdditionalHeaderOptions($config->getAdditionalHeaderOptions());
        $generatorRequest->withAdditionalRequestBodyOptions($config->getAdditionalRequestBodyOptions());

        $generatorRequest->modelValue = $config->getModel();
        $initialBody = [];
        $additionalBodyParams = self::parseBodyOptionsStringToArray($generatorRequest->getAdditionalRequestBodyOptions());
        $initialBody = array_merge($initialBody, $additionalBodyParams);

        if (isset($generatorRequest->modelValue) && !empty(trim($generatorRequest->modelValue))) {
            $initialBody["model"] = trim($generatorRequest->modelValue);
        }

        $generatorRequest->withBody($initialBody);

        $generatorRequest->generateHeader();

        return $generatorRequest;
    }

    public function withAuthenticationKeyLabel(string $autheticationKeyLabel): AIPicRequestImpl {
        $this->autheticationKeyLabel = $autheticationKeyLabel;
        return $this;
    }

    public function withAuthenticationValue(string $autheticationValue): AIPicRequestImpl {
        $this->autheticationValue = $autheticationValue;
        return $this;
    }

    public function withAdditionalHeaderOptions(string $additionalHeaderOptions): AIPicRequestImpl {
        $this->additionalHeaderOptions = $additionalHeaderOptions;
        return $this;
    }

    public function withAdditionalRequestBodyOptions(string $additionalRequestBodyOptions): AIPicRequestImpl {
        $this->additionalRequestBodyOptions = $additionalRequestBodyOptions;
        return $this;
    }

    protected static function parseBodyOptionsStringToArray(string $optionsString): array {
        $optionsArray = [];

        if (empty(trim($optionsString))) {
            return $optionsArray;
        }
        $pairs = explode(',', $optionsString);
        foreach ($pairs as $pair) {
            $parts = explode(':', $pair, 2);
            if (count($parts) === 2) {
                $key = trim($parts[0]);
                $value = trim($parts[1]);
                $optionsArray[$key] = $value;
            }
        }
        return $optionsArray;
    }

    // #TODO Check config
    public function generateHeader(): array {
        $headerOptions = [];
        if($this->autheticationKeyLabel && $this->autheticationValue) {
            $headerOptions[] = $this->autheticationKeyLabel . ": " . $this->autheticationValue;
        }
        $additionalParsedHeaders = AIPicRequestAbstract::getArrayFromString($this->getAdditionalHeaderOptions() ?? '');
        $this->withHeader(array_merge($headerOptions, $additionalParsedHeaders));
        return $this->header;
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