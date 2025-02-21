<?php
abstract class AImageGeneratorRequestAbstract implements AImageGeneratorRequestInterface
{
    private CurlHandle $ch;

    protected array $header = [];
    protected array $body = [];
    protected string $promptContext = "";
    protected string $url;
    protected string|bool|null $response;
    protected string $requestPromptKey = "prompt";
    protected string $responseKey;
    protected ?string $responseSubkey = null;

    public function __construct() {
        $this->ch = curl_init();
    }

    public function withHeader(array $header): AImageGeneratorRequestAbstract {
        $this->header = $header;
        return $this;
    }

    public function withBody(array $body): AImageGeneratorRequestAbstract {
        $this->body = $body;
        return $this;
    }

    public function withPromptContext(string $promptContext): AImageGeneratorRequestAbstract {
        $this->promptContext = $promptContext;
        return $this;
    }

    public function withUrl(string $url): AImageGeneratorRequestAbstract {
        $this->url = $url;
        return $this;
    }

    public function withRequestPromptKey(string $requestPromptKey): AImageGeneratorRequestAbstract {
        $this->requestPromptKey = $requestPromptKey;
        return $this;
    }

    public function withResponseKey(string $responseKey): AImageGeneratorRequestAbstract {
        $this->responseKey = $responseKey;
        return $this;
    }

    public function withResponseSubkey(string $responseSubkey): AImageGeneratorRequestAbstract {
        $this->responseSubkey = $responseSubkey;
        return $this;
    }

    public function getDataArray(): array {
        $res = [];
        if(isset($this->response)) {
            $responseDecoded = $this->response ? json_decode($this->response, true): [];
            $res = $responseDecoded[$this->responseKey];
        }
        return $res;
    }

    public function getImagesUrlsArray(): array {
        $data = $this->getDataArray();
        $imagesUrls = [];
        if(isset($this->responseSubkey)) {
            foreach($data as $image) {
                $imagesUrls[] = $image[$this->responseSubkey];
            }
        } else {
            $imagesUrls[] = $data;
        }

        return $imagesUrls;
    }

    public function sendPrompt(string $prompt): bool|string {
        if(strlen($prompt) > 0) {
            $this->body[$this->requestPromptKey] = $this->promptContext. " " . $prompt;
            $bodyRequest = json_encode($this->getBody());

            curl_setopt($this->ch, CURLOPT_URL, $this->url);
            curl_setopt($this->ch, CURLOPT_POST, true);
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $bodyRequest);
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->header);
            curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
            $this->response = curl_exec($this->ch);
            return $this->response;
        }
        return false;
    }

    public function getHeader(): array {
        return $this->header;
    }

    public function getBody(): array {
        return $this->body;
    }

    public function getResponse(): string|bool|null {
        return $this->response;
    }

    public function getPromptContext(): string
    {
        return $this->promptContext;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getRequestPromptKey(): string
    {
        return $this->requestPromptKey;
    }

    public function getResponseKey(): string
    {
        return $this->responseKey;
    }

    public function getResponseSubkey(): ?string
    {
        return $this->responseSubkey;
    }

    public static function getArrayFromString(string $txt): array {
        $dataLst = explode(",", $txt);
        $data = [];
        foreach($dataLst as $d) {
            if(str_contains($d, ":")) {
                $entries = explode(":", $d);
                $data[] = $entries[0] . ": " . $entries[1];
            }
        }
        return $data;
    }
}