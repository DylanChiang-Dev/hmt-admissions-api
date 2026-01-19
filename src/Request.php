<?php

namespace App;

class Request
{
    private string $method;
    private string $path;
    private array $params;
    private array $headers;
    private $body;
    private array $attributes = [];

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $this->params = array_merge($_GET, $_POST);
        $this->headers = $this->parseHeaders();
        $this->body = file_get_contents('php://input');

        // Parse JSON body if applicable
        if ($this->getHeader('Content-Type') === 'application/json' && !empty($this->body)) {
            $json = json_decode($this->body, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->params = array_merge($this->params, $json);
            }
        }
    }

    private function parseHeaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = str_replace('_', '-', strtolower(substr($key, 5)));
                // Convert back to Title-Case for standard headers (optional but nice)
                $name = ucwords($name, '-');
                $headers[$name] = $value;
            } elseif ($key === 'CONTENT_TYPE') {
                $headers['Content-Type'] = $value;
            } elseif ($key === 'CONTENT_LENGTH') {
                $headers['Content-Length'] = $value;
            }
        }
        return $headers;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function getHeader(string $name): ?string
    {
        // Case-insensitive header lookup
        foreach ($this->headers as $key => $value) {
            if (strcasecmp($key, $name) === 0) {
                return $value;
            }
        }
        return null;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function setAttribute(string $key, $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function getAttribute(string $key, $default = null)
    {
        return $this->attributes[$key] ?? $default;
    }
}
