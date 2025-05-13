<?php

namespace platform;

use DateTime;
use db\AIPicDatabase;

class AIPicConfig {

    private int $id = 0;
    private string $apiUrl;
    private ?string $autheticationKeyLabel;
    private ?string $autheticationValue;
    private ?string $additionalHeaderOptions;
    private string $requestBodyPromptKey;
    private ?string $promptContext;
    private ?string $model;
    private ?string $additionalRequestBodyOptions;
    private string $responseKey;
    private ?string $responseSubkey;

    private DateTime $createdAt;
    private ?DateTime $updatedAt;



    public function __construct(?int $id = null) {
        global $DIC;

        if($id !== null && $id > 0) {
            $this->id = $id;
            $this->loadFromDB();
        } else {
            $this->createdAt = new DateTime();
            $this->updatedAt = new DateTime();
        }
    }

    /**
     * @throws \Exception
     */
    public function loadFromDB(): void
    {

        $database = new AIPicDatabase();
        $result = $database->getConfig();

        if (isset($result[0])) {
            $this->setId($result[0]["id"]);
            $this->setApiUrl($result[0]["api_url"]);
            $this->setAutheticationKeyLabel($result[0]["authentication_key_label"]);
            $this->setAutheticationValue($result[0]["authentication_value"]);
            $this->setAdditionalHeaderOptions($result[0]["additional_header_options"]);
            $this->setRequestBodyPromptKey($result[0]["request_body_prompt"]);
            $this->setPromptContext($result[0]["prompt_context"]);
            $this->setModel($result[0]["model"]);
            $this->setAdditionalRequestBodyOptions($result[0]["additional_body_options"]);
            $this->setResponseKey($result[0]["response_body_key"]);
            $this->setResponseSubkey($result[0]["response_body_subkey"]);
            $this->setCreatedAt(new DateTime($result[0]["created_at"]));
            $this->setUpdatedAt(new DateTime($result[0]["updated_at"]));
        } else {
            $this->setApiUrl("");
            $this->setAutheticationKeyLabel("");
            $this->setAutheticationValue("");
            $this->setAdditionalHeaderOptions("");
            $this->setRequestBodyPromptKey("");
            $this->setPromptContext("");
            $this->setModel("");
            $this->setAdditionalRequestBodyOptions("");
            $this->setResponseKey("");
            $this->setResponseSubkey("");
            $this->setCreatedAt(new DateTime());
        }
    }

    public function save(): AIPicConfig
    {
        $database = new AIPicDatabase();

        $data = [
            "api_url" => $this->getApiUrl(),
            "authentication_key_label" => $this->getAutheticationKeyLabel(),
            "authentication_value" => $this->getAutheticationValue(),
            "additional_header_options" => $this->getAdditionalHeaderOptions(),
            "request_body_prompt" => $this->getRequestBodyPromptKey(),
            "prompt_context" => $this->getPromptContext(),
            "model" => $this->getModel(),
            "additional_body_options" => $this->getAdditionalRequestBodyOptions(),
            "response_body_key" => $this->getResponseKey(),
            "response_body_subkey" => $this->getResponseSubkey(),
            "created_at" => $this->getCreatedAt()->format("Y-m-d H:i:s"),
            "updated_at" => date("Y-m-d H:i:s")
        ];

        if ($this->getId() > 0) {
            $database->update("aip_config", $data, ["id" => $this->getId()]);
        } else {
            $id = $database->nextId("aip_config");

            $this->setId($id);

            $data["id"] = $id;
            $database->insert("aip_config", $data);
        }
        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getApiUrl(): string
    {
        return $this->apiUrl;
    }

    public function setApiUrl(string $apiUrl): void
    {
        $this->apiUrl = $apiUrl;
    }

    public function getAutheticationKeyLabel(): string
    {
        return $this->autheticationKeyLabel;
    }

    public function setAutheticationKeyLabel(string $autheticationKeyLabel): void
    {
        $this->autheticationKeyLabel = $autheticationKeyLabel;
    }

    public function getAutheticationValue(): string
    {
        return $this->autheticationValue;
    }

    public function setAutheticationValue(string $autheticationValue): void
    {
        $this->autheticationValue = $autheticationValue;
    }

    public function getAdditionalHeaderOptions(): string
    {
        return $this->additionalHeaderOptions;
    }

    public function setAdditionalHeaderOptions(string $additionalHeaderOptions): void
    {
        $this->additionalHeaderOptions = $additionalHeaderOptions;
    }

    public function getRequestBodyPromptKey(): string
    {
        return $this->requestBodyPromptKey;
    }

    public function setRequestBodyPromptKey(string $requestBodyPromptKey): void
    {
        $this->requestBodyPromptKey = $requestBodyPromptKey;
    }

    public function getPromptContext(): string
    {
        return $this->promptContext;
    }

    public function setPromptContext(string $promptContext): void
    {
        $this->promptContext = $promptContext;
    }

    public function getModel(): string
    {
        return $this->model;
    }
    public function setModel(string $model): void
    {
        $this->model = $model;

    }

    public function getAdditionalRequestBodyOptions(): string
    {
        return $this->additionalRequestBodyOptions;
    }

    public function setAdditionalRequestBodyOptions(string $additionalRequestBodyOptions): void
    {
        $this->additionalRequestBodyOptions = $additionalRequestBodyOptions;
    }

    public function getResponseKey(): string
    {
        return $this->responseKey;
    }

    public function setResponseKey(string $responseKey): void
    {
        $this->responseKey = $responseKey;
    }

    public function getResponseSubkey(): string
    {
        return $this->responseSubkey;
    }

    public function setResponseSubkey(string $responseSubkey): void
    {
        $this->responseSubkey = $responseSubkey;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }


}