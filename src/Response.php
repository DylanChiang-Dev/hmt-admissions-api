<?php

namespace App;

class Response
{
    private int $statusCode = 200;
    private array $headers = [];
    private $content;

    public function __construct($content = '', int $status = 200, array $headers = [])
    {
        $this->content = $content;
        $this->statusCode = $status;
        $this->headers = array_merge(['Content-Type' => 'application/json'], $headers);
    }

    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function setStatus(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    public function send(): void
    {
        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }

        echo $this->content;
    }

    public static function json($data, int $status = 200): self
    {
        return new self(json_encode($data), $status);
    }

    public static function error(string $code, string $message, $details = null, int $status = 400): self
    {
        $response = [
            'error' => [
                'code' => $code,
                'message' => $message
            ]
        ];

        if ($details !== null) {
            $response['error']['details'] = $details;
        }

        return new self(json_encode($response), $status);
    }
}
