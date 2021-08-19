<?php

namespace PikaJew002\Handrolled\Http;

class Request
{
    public const HTTP_GET = 'GET';
    public const HTTP_HEAD = 'HEAD';
    public const HTTP_POST = 'POST';
    public const HTTP_PUT = 'PUT';
    public const HTTP_PATCH = 'PATCH';
    public const HTTP_DELETE = 'DELETE';

    public string $method;
    public string $uri;
    public array $server;
    public array $headers;
    public array $request;
    public array $query;
    public array $files;
    public array $cookies;
    public $body;

    public function __construct()
    {
        $this->server = $_SERVER;
        $this->method = strtoupper($this->server['REQUEST_METHOD']);
        $this->uri = $this->parseURI($this->server['REQUEST_URI']);
        $this->headers = $this->parseHeaders($this->server);
        $this->body = $this->getBody();
        $this->query = $this->parseQuery($this->method, $this->server['REQUEST_URI']);
        $this->request = $this->parseRequest($this->method, $this->headers, $this->body);
        $this->files = $this->parseFiles();
        $this->cookies = $this->parseCookies();
    }

    protected function parseURI(string $uri): string
    {
        if(false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }

        return rawurldecode($uri);
    }

    protected function parseHeaders(array $server): array
    {
        $headers = [];
        foreach($server as $key => $value) {
            if(strncmp($key, 'HTTP_', 5) === 0) {
                $headers[substr($key, 5)] = $value;
            } else if(in_array($key, ['body_TYPE', 'body_LENGTH', 'body_MD5'], true)) {
                $headers[$key] = $value;
            }
        }

        return $headers;
    }

    public function getBody()
    {
        $isResource = is_resource($this->body);
        if($isResource) {
            rewind($this->body);
            return stream_get_contents($this->body);
        }
        if($this->body === null || $this->body === false) {
            $this->body = file_get_contents('php://input');
        }

        return $this->body;
    }

    protected function parseRequest(string $method, array $headers, string $body): array
    {
        if(
            $method === self::HTTP_POST &&
            array_key_exists('CONTENT_TYPE', $headers) &&
            in_array($headers['CONTENT_TYPE'], ['application/x-www-form-urlencoded'])
        ) {
            return isset($_POST) ? $_POST : [];
        }
        if(
            array_key_exists('CONTENT_TYPE', $headers) &&
            in_array($headers['CONTENT_TYPE'], ['application/json', 'application/x-json']) &&
            $body !== ''
        ) {
            return json_decode($body, true);
        }
        return [];
    }

    protected function parseQuery(string $method = 'GET', string $uri = ''): array
    {
        if($method === self::HTTP_GET) {
            return isset($_GET) ? $_GET : [];
        }
        $components = parse_url($uri);
        if(isset($components['query'])) {
            $queryArr = [];
            foreach(explode('&', rawurldecode($components['query'])) as $query) {
                if(false !== strpos($query, '=')) {
                    $pair = explode('=', $query);
                    $queryArr[$pair[0]] = $pair[1];
                    continue;
                }
                $queryArr[$query] = '';
            }
            return $queryArr;
        }

        return [];
    }

    protected function parseFiles(): array
    {
        return isset($_FILES) ? $_FILES : [];
    }

    protected function parseCookies(): array
    {
        return isset($_COOKIES) ? $_COOKIES : [];
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function query($key, $default = null)
    {
        return isset($this->query[$key]) ? $this->query[$key] : $default;
    }

    public function request($key, $default = null)
    {
        return isset($this->request[$key]) ? $this->request[$key] : $default;
    }

    public function input($key, $default = null)
    {
        if($this->query($key) !== null) {
            return $this->query($key, $default);
        }
        if($this->request($key) !== null) {
            return $this->request($key, $default);
        }

        return $default;
    }
}
