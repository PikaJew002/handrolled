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

    protected string $method;
    protected string $uri;
    protected array $server;
    protected array $headers;
    protected array $request;
    protected array $query;
    protected array $files;
    protected array $cookies;
    protected $user;
    protected $body;

    public function __construct(
        string $uri = '',
        string $method = 'GET',
        array $server = [],
        array $headers = [],
        array $request = [],
        array $query = [],
        array $cookies = [],
        array $files = [],
        string $body = ''
    )
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->server = $server;
        $this->headers = $headers;
        $this->body = $body;
        $this->query = $query;
        $this->request = $request;
        $this->files = $files;
        $this->cookies = $cookies;
    }

    public static function createFromGlobals(): self
    {
        $uri = $_SERVER['REQUEST_URI'];
        $method = strtoupper($_SERVER['REQUEST_METHOD']);
        $server = $_SERVER;
        $headers = self::parseHeaders($server);
        $body = file_get_contents('php://input');
        $query = self::parseQuery($method, $uri);
        $request = self::parseRequest($method, $headers, $body);
        $files = self::parseFiles();
        $cookies = self::parseCookies($headers);
        return new self($uri, $method, $server, $headers, $request, $query, $cookies, $files, $body);
    }

    protected function parseURI(string $uri): string
    {
        if(false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }

        return rawurldecode($uri);
    }

    protected static function parseHeaders(array $server): array
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
        if(is_resource($this->body)) {
            rewind($this->body);
            return stream_get_contents($this->body);
        }
        if($this->body === null || $this->body === false) {
            $this->body = file_get_contents('php://input');
        }

        return $this->body;
    }

    protected static function parseRequest(string $method, array $headers, string $body): array
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

    protected static function parseQuery(string $method = 'GET', string $uri = ''): array
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

    protected static function parseFiles(): array
    {
        return isset($_FILES) ? $_FILES : [];
    }

    protected static function parseCookies(array $headers): array
    {
        $cookiesFromHeaders = [];
        $cookiesFromHeadersStr = isset($headers['COOKIE']) ? $headers['COOKIE'] : '';
        $headerCookieArr = $cookiesFromHeadersStr !== '' ? explode(';', $cookiesFromHeadersStr) : [];
        foreach($headerCookieArr as $cookieRaw) {
            if($cookieRaw === '') continue;
            $cookieParsed = explode('=', $cookieRaw);
            if(isset($cookieParsed[0]) && isset($cookieParsed[1])) {
                $cookiesFromHeaders[$cookieParsed[0]] = $cookieParsed[1];
            }
        }

        return $cookiesFromHeaders + (isset($_COOKIES) ? $_COOKIES : []);
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getServer(): array
    {
        return $this->server;
    }

    protected function normalizeHeader(string $header): string
    {
        return str_replace('-', '_', strtoupper($header));
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function hasHeader(string $header): bool
    {
        return isset($this->headers[$this->normalizeHeader($header)]);
    }

    public function getHeader(string $header): ?string
    {
        return $this->hasHeader($header) ? $this->headers[$this->normalizeHeader($header)] : null;
    }

    public function getCookies(): array
    {
        return $this->cookies;
    }

    public function hasCookie(string $cookie): bool
    {
        return isset($this->cookies[$cookie]);
    }

    public function getCookie(string $cookie): ?string
    {
        return $this->hasCookie($cookie) ? $this->cookies[$cookie] : null;
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function user()
    {
        return $this->user;
    }

    public function getQuery(): array
    {
        return $this->query;
    }

    public function hasQuery($key): bool
    {
        return isset($this->query[$key]);
    }

    public function query($key, $default = null)
    {
        return $this->hasQuery($key) ? $this->query[$key] : $default;
    }

    public function getRequest(): array
    {
        return $this->request;
    }

    public function hasRequest($key): bool
    {
        return isset($this->request[$key]);
    }

    public function request($key, $default = null)
    {
        return $this->hasRequest($key) ? $this->request[$key] : $default;
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
