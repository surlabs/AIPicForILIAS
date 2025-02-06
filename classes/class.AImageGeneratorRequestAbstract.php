<?php
abstract class AImageGeneratorRequestAbstract implements AImageGeneratorRequestInterface
{
    private CurlHandle $ch;

    public function __construct(string $url, array $header, ?array $body) {
        $this->url = $url;
        $this->ch = curl_init();
        $this->body = $this->initializeBody($body);
        $this->header = $header;
    }

    protected array $header;
    protected array $body;
    protected string $promptContext = "";
    protected string $url;
    protected string|bool|null $response;

    public function getDataArray(): array {
        $res = [];
        if(isset($this->response)) {
            $responseDecoded = $this->response ? json_decode($this->response, true): [];
            $res = $responseDecoded["data"];
        }
        return $res;
    }

    public function getImagesUrlsArray(): array {
        $data = $this->getDataArray();
        $imagesUrls = [];
        foreach($data as $image) {
            $imagesUrls[] = $image["url"];
        }
        return $imagesUrls;
    }

    public function sendPrompt(string $prompt): bool|string {
        if(strlen($prompt) > 0) {
            $this->body["prompt"] = $this->promptContext. " " . $prompt;
            $bodyRequest = json_encode($this->getBody());

            curl_setopt($this->ch, CURLOPT_URL, $this->url);
            curl_setopt($this->ch, CURLOPT_POST, true); // Indicamos que es una solicitud POST
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $bodyRequest); // Convertimos el array en una cadena URL-encoded
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

    public function setHeader(array $header): void {
        $this->header = $header;
    }

    public function setBody(array $body): void {
        $this->body = $body;
    }

    public function getResponse(): string|bool|null {
        return $this->response;
    }

    public function setResponse(string|bool|null $response): void {
        $this->response = $response;
    }

    protected function initializeBody(?array $body): array
    {
        if(!isset($body)) {
            $body = [
                'model' => 'dall-e-2',
                'n' => 1,
                'quality' => 'standard'
            ];
        }
        return $body;
    }
}